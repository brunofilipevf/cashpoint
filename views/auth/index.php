<?php $this->block('title', 'Entrar'); ?>
<?php $this->partial('layout/header'); ?>

<main>
    <h2><?php $this->yield('title'); ?></h2>

    <?php $this->partial('layout/flash'); ?>

    <form method="post" action="<?= $this->url('/login') ?>">
        <input type="hidden" name="token" value="<?= $this->e($csrf) ?>">

        <div>
            <label for="username">Usuário</label>
            <input type="text" name="username" id="username">
        </div>

        <div>
            <label for="password">Senha</label>
            <input type="password" name="password" id="password">
        </div>

        <button type="submit">Entrar</button>
    </form>
</main>

<?php $this->partial('layout/footer'); ?>
