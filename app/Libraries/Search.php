<?php
namespace App\Libraries;

class Search {

    /**
     * Devuelve condición SQL para filtrar registros es una tabla de la base
     * de datos
     * @param array $input
     * @param array $qFields
     * 2023-02-12
     */
    public function condition($input, $qFields)
    {
        $condition = 'id > 0';  //Valor por defecto

        //Array condiciones por filtros
        $filtersConditions = $this->filtersConditions($input);

        //Arrar condiciones por palabras buscadas en variable q
        $searchedTextConditions = [];
        if ( isset($input['q']) ) {
            $searchedTextConditions = $this->searchedTextConditions($input['q'], $qFields);
        }

        //Juntar los dos listados de condiciones SQL
        $conditions = array_merge($filtersConditions, $searchedTextConditions);
        //Si hay condiciones, concatentar con AND
        if ( count($conditions) > 0 ) $condition = implode(' AND ', $conditions);

        return $condition;
    }

    /**
     * Array de palabras en una texto buscado para filtrar registros
     * 2023-02-12
     */
    public static function words($searchedText):array
    {   
        $searchedText = trim($searchedText);
    
        if (strlen($searchedText) <= 2) {
            return [];
        }
        
        $noSearchWords = ['la', 'el', 'los', 'las', 'del', 'de', 'y', 'en'];
        $words = explode(' ', $searchedText);
        $words = array_diff($words, $noSearchWords);
        
        return $words;
    }

    /**
     * Devuelve array con valores de filtros realizados en una búsqueda a 
     * partir del $input y el listado de nombres de filtros $filtersNames
     * @param array $input :: Criterios de búsqueda
     * @param array $filtersNames :: Nombres de los campos buscados
     * @return array $filters :: array con los filtros requeridos
     * 2023-03-04
     */
    public static function filters($input, $filtersNames)
    {
        foreach ($filtersNames as $filterName) {
            $filters[$filterName] = '';
            if ( isset($input[$filterName]) ) { $filters[$filterName] = $input[$filterName]; }
        }

        return $filters;
    }

    /**
     * Array con variables de configuración de la búsqueda de registros en una
     * tabla
     * @param array $input :: Solicitud del usuario
     * @return array $settings :: Configuración de la búsqueda
     * 2023-02-12
     */
    public static function settings($input)
    {
        //Valores iniciales por defecto
        $settings = [
            'selectFormat' => 'default',
            'numPage' => 1, 'perPage' => 12, 'offset' => 0,
            'orderField' => 'id', 'orderType' => 'DESC',
        ];

        foreach( $settings as $index => $value ) {
            //Si está definido, modificar el valor por defecto
            if ( isset($input[$index]) ) { $settings[$index] = $input[$index]; }
        }

        //Controlar máximo número de resultados por petición
        if ( $settings['perPage'] > 500 ) $settings['perPage'] = 500;

        $settings['offset'] = ($settings['numPage'] - 1) * $settings['perPage'];      //Número de la página de datos que se está consultado

        return $settings;
    }

    /**
     * Array de condiciones SQL (strings) para buscar registros correspondientes
     * a los filtros solicitados en $input
     * @param array $input
     * 2023-02-12
     */
    public function filtersConditions($input):array
    {
        $filters = $input;

        $conditions = [];
        foreach($filters as $filterComparation => $value) {
            $condition = $this->filterToCondition($filterComparation, $value);
            if ( $condition ) $conditions[] = $condition;
        }

        return $conditions;
    }

    /**
     * El filterComparation tiene la estructura (2 partes) nombreCampo__tipoComparacion
     * 2023-02-12
     */
    public function filterToCondition($filterComparation, $value)
    {
        $filterParts = explode('__',$filterComparation);
        $condition = null;
        //Verificar si es un filtro y si el $value tiene valor
        if ( count($filterParts) == 2 & strlen($value) > 0 ) {
            if ( $filterParts[1] == 'eq' ) {
                $condition = "{$filterParts[0]} = '$value'";
            }
            if ( $filterParts[1] == 'gte' ) $condition = "{$filterParts[0]} >= '$value'";
            if ( $filterParts[1] == 'lte' ) $condition = "{$filterParts[0]} <= '$value'";
            if ( $filterParts[1] == 'like' ) $condition = "{$filterParts[0]} LIKE '%$value%'";
            
        }
        
        return $condition;
    }
    
    /**
     * String con la cadena para URL tipo GET, con los valores de la búsqueda
     * 2020-10-26
     * PENDIENTE
     */
    function inputToGetString($input)
    {
        $filtersStr = '';   
        foreach ( $filters as $key => $value ) 
        {
            if ( $value )
            {
                $prep_value = str_replace(' ', '+', $value);
                $filtersStr .= "{$key}={$prep_value}&";
            }
        }
        return $filtersStr;
    }
    
    /**
     * String con segmento SQL de campos con el condicional para concatenar
     * 2023-02-12
     */
    function concatFields($fields):string
    {
        $concatFields = '';    //Valor por defecto
        foreach ( $fields as $field ) $concatFields .= "IFNULL({$field}, ''), ";
        return substr($concatFields, 0, -2);
    }

    /**
     * Condiciones SQL de búsqueda de cada palabra
     * @param string $searchedText
     * @param array $fields :: Campos en los cuales se buscan las palabras
     */
    function searchedTextConditions($searchedText, $fields):array
    {
        $conditions = [];   //Valor inicial por defecto, vacío
        $concatFields = $this->concatFields($fields);
        $words = $this->words($searchedText);
        
        foreach ($words as $word) 
        {
            $conditions[] = "CONCAT({$concatFields}) LIKE '%{$word}%'";
        }
        
        return $conditions;
    }
}