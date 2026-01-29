<?php $this->block('title', 'Início'); ?>
<?php $this->partial('layout/header'); ?>
<?php $this->partial('layout/navbar'); ?>

<main>
    <h2><?php $this->yield('title'); ?></h2>

    <?php $this->partial('layout/flash'); ?>

    <p>Página inicial</p>
</main>

<?php $this->partial('layout/footer'); ?>
