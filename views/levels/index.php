<?php $this->block('title', 'Níveis de Acesso'); ?>
<?php $this->partial('layout/header'); ?>
<?php $this->partial('layout/navbar'); ?>

<main>
    <h2><?php $this->yield('title'); ?></h2>

    <?php $this->partial('layout/flash'); ?>

    <p><a href="<?= $this->url('/levels/add'); ?>">+ Adicionar</a></p>

    <table border="1" cellpadding="3">
        <tr>
            <th>#</th>
            <th>Nome</th>
            <th>Hierarquia</th>
            <th>Descrição</th>
            <th>Criado Em</th>
        </tr>
        <?php if ($levels) : foreach ($levels as $level) : ?>
            <tr>
                <td><?= $this->e($level['id']) ?></td>
                <td><a href="<?= $this->url('/levels/edit', $level['id']); ?>"><?= $this->e($level['name']) ?></a></td>
                <td><?= $this->e($level['hierarchy']) ?></td>
                <td><?= $this->e($level['description']) ?></td>
                <td><?= $this->e($level['created_at'], 'datetime') ?></td>
            </tr>
        <?php endforeach; else : ?>
            <tr>
                <td colspan="5">Nenhum registro encontrado</td>
            </tr>
        <?php endif; ?>
    </table>
</main>

<?php $this->partial('layout/footer'); ?>
