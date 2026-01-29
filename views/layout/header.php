<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= $appDescription ?>">
    <title><?= $this->yield('title') ?> | <?= $appName ?></title>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <style>
        a { text-decoration: none; color: blue; }
        a:hover { text-decoration: underline; }
        body { font-family: arial, helvetica, sans-serif; font-size: 1rem; }
        .alert { margin-bottom: 1rem; }
        .alert-success { color: green; }
        .alert-danger { color: red; }
        form > div { margin-bottom: 0.5rem; }
        form > div > label { display: block; margin-bottom: 0.25rem; }
    </style>
</head>
<body>

<header>
    <h1><?= $appName ?></h1>
</header>
