<?php $this->block('title', 'Usuários'); ?>
<?php $this->partial('layout/header'); ?>
<?php $this->partial('layout/navbar'); ?>

<main>
    <h2><?php $this->yield('title'); ?></h2>

    <?php $this->partial('layout/flash'); ?>

    <p><a href="<?= $this->url('/users/add'); ?>">+ Adicionar</a></p>

    <table border="1" cellpadding="3">
        <tr>
            <th>#</th>
            <th>Usuário</th>
            <th>Nome Completo</th>
            <th>Nível de Acesso</th>
            <th>Ativo?</th>
            <th>Criado Em</th>
        </tr>
        <?php if ($users) : foreach ($users as $user) : ?>
            <tr>
                <td><?= $this->e($user['id']) ?></td>
                <td><a href="<?= $this->url('/users/edit', $user['id']); ?>"><?= $this->e($user['username']) ?></a></td>
                <td><?= $this->e($user['fullname']) ?></td>
                <td><?= $this->e($user['level_name']) ?></td>
                <td><?= $this->e($user['is_active'], 'bool') ?></td>
                <td><?= $this->e($user['created_at'], 'datetime') ?></td>
            </tr>
        <?php endforeach; else : ?>
            <tr>
                <td colspan="6">Nenhum registro encontrado</td>
            </tr>
        <?php endif; ?>
    </table>
</main>

<?php $this->partial('layout/footer'); ?>
