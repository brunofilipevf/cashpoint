<?php

/*
|--------------------------------------------------------------------------
| Autoload do Composer
|--------------------------------------------------------------------------
*/

require_once(dirname(__DIR__) . '/vendor/autoload.php');

/*
|--------------------------------------------------------------------------
| Proteção de rota
|--------------------------------------------------------------------------
*/

if (!auth()->isLogged()) {
    redirector()->to('login.php');
}

/*
|--------------------------------------------------------------------------
| Renderização da página
|--------------------------------------------------------------------------
*/

$pageTitle = 'Início';

require_once(ABSPATH . '/public/header.php');
require_once(ABSPATH . '/public/navbar.php');

?>

<main class="container mb-4">
    <h2 class="display-4 mb-4"><?php echo e($pageTitle); ?></h2>

    <?php if ($flash = session()->getFlash()) : ?>
        <div class="alert alert-<?php echo e($flash['type']); ?>">
            <?php echo nl2br(e($flash['message'])); ?>
        </div>
    <?php endif; ?>

    <p>Olá, bem vindo(a).</p>
</main>

<?php
require_once(ABSPATH . '/public/footer.php');