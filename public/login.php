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

if (auth()->isLogged()) {
    redirector()->to('index.php');
}

/*
|--------------------------------------------------------------------------
| Processamento do formulário de login
|--------------------------------------------------------------------------
*/

if (request()->isPost()) {
    if (auth()->login()) {
        redirector()->to('index.php');
    } else {
        redirector()->back();
    }
}

/*
|--------------------------------------------------------------------------
| Renderização da página
|--------------------------------------------------------------------------
*/

$pageTitle = 'Entrar';

require_once(ABSPATH . '/public/header.php');
require_once(ABSPATH . '/public/navbar.php');

?>

<main class="container mb-4">
    <div class="col-md-3 m-auto">
        <h2 class="display-4 mb-4"><?php echo e($pageTitle); ?></h2>

        <?php if ($flash = session()->getFlash()) : ?>
            <div class="alert alert-<?php echo e($flash['type']); ?>">
                <?php echo nl2br(e($flash['message'])); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo e(session()->generateCSRF()); ?>">

            <div class="mb-3">
                <label for="username">Nome de usuário</label>
                <input type="text" name="username" id="username" class="form-control">
            </div>

            <div class="mb-3">
                <label for="password">Senha</label>
                <input type="password" name="password" id="password" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</main>

<?php
require_once(ABSPATH . '/public/footer.php');