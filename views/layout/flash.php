
<?php if ($flash) : ?>
    <div class="alert alert-<?= $this->e($flash['type']) ?>">
        <?= $this->e($flash['message'], 'nl2br') ?>
    </div>
<?php endif; ?>
