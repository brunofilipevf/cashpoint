<?php $this->block('title', 'Usuários'); ?>
<?php $this->partial('layout/header'); ?>
<?php $this->partial('layout/navbar'); ?>

<main>
    <h2><?php $this->yield('title'); ?></h2>

    <?php $this->partial('layout/flash'); ?>
</main>

<?php $this->partial('layout/footer'); ?>
