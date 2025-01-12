<!DOCTYPE html>
<html lang="es">
<head>
    <?= view('templates/easypml/main/head') ?>
</head>
    <body>
        <?= view('templates/easypml/main/navbar') ?>
        <div class="container">
            <?php if ( isset($backLink) ) : ?>
                <div id="backLink" class="only-sm mb-2">
                    <a class="btn btn-light" href="<?= base_url($backLink) ?>">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </div>
            <?php endif; ?>
            <div class="header">
                <h1 id="page-title">
                    <?php if ( isset($backLink) ) : ?>
                        <a class="btn btn-light mr-2 only-lg" href="<?= base_url($backLink) ?>">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                    <?php endif; ?>
                </h1>
                <?php if ( isset($nav2) ): ?>
                    <div id="nav2">
                        <?= view($nav2) ?>
                    </div>
                <?php endif ?>
            </div>
            <div class="text-center my-3" id="loadingIndicator" style="display: none;">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <?= view($viewA); ?>
        </div>
    </body>
</html>