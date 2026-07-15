<?php

/*
|--------------------------------------------------------------------------
| Obtém o roteador do container de injeção de dependências
|--------------------------------------------------------------------------
*/

$router = $container->get(Core\Router::class);

/*
|--------------------------------------------------------------------------
| Página inicial
|--------------------------------------------------------------------------
*/

$router->get('/', 'HomeController@index', ['AuthOnly']);

/*
|--------------------------------------------------------------------------
| Autenticação
|--------------------------------------------------------------------------
*/

$router->get('/login', 'AuthController@index', ['GuestOnly']);
$router->post('/login', 'AuthController@login', ['VerifyCsrf', 'GuestOnly']);
$router->get('/logout', 'AuthController@logout', ['AuthOnly']);

/*
|--------------------------------------------------------------------------
| Pontuações
|--------------------------------------------------------------------------
*/

$router->get('/scores{page}', 'ScoreController@index', ['AuthOnly:1']);
$router->get('/scores/add', 'ScoreController@add', ['AuthOnly:1']);
$router->post('/scores/add', 'ScoreController@insert', ['VerifyCsrf', 'AuthOnly:1']);

$router->post('/api/scores/add', 'ScoreControllerApi@insert', []);

/*
|--------------------------------------------------------------------------
| Resgates
|--------------------------------------------------------------------------
*/

$router->get('/redemptions{page}', 'RedemptionController@index', ['AuthOnly:1']);
$router->get('/redemptions/add', 'RedemptionController@add', ['AuthOnly:1']);
$router->post('/redemptions/add', 'RedemptionController@insert', ['VerifyCsrf', 'AuthOnly:1']);

/*
|--------------------------------------------------------------------------
| Clientes
|--------------------------------------------------------------------------
*/

$router->get('/customers{page}', 'CustomerController@index', ['AuthOnly:1']);
$router->get('/customers/add', 'CustomerController@add', ['AuthOnly:1']);
$router->post('/customers/add', 'CustomerController@insert', ['VerifyCsrf', 'AuthOnly:1']);
$router->get('/customers/edit/{id}', 'CustomerController@edit', ['AuthOnly:2']);
$router->post('/customers/edit/{id}', 'CustomerController@update', ['VerifyCsrf', 'AuthOnly:2']);
$router->get('/customers/delete/{id}', 'CustomerController@delete', ['AuthOnly:3']);

$router->get('/api/customers/add', 'CustomerControllerApi@add', []);
$router->post('/api/customers/add', 'CustomerControllerApi@insert', []);

/*
|--------------------------------------------------------------------------
| Grupos
|--------------------------------------------------------------------------
*/

$router->get('/groups{page}', 'GroupController@index', ['AuthOnly:1']);
$router->get('/groups/add', 'GroupController@add', ['AuthOnly:3']);
$router->post('/groups/add', 'GroupController@insert', ['VerifyCsrf', 'AuthOnly:3']);
$router->get('/groups/edit/{id}', 'GroupController@edit', ['AuthOnly:3']);
$router->post('/groups/edit/{id}', 'GroupController@update', ['VerifyCsrf', 'AuthOnly:3']);
$router->get('/groups/delete/{id}', 'GroupController@delete', ['AuthOnly:3']);

/*
|--------------------------------------------------------------------------
| Premiações
|--------------------------------------------------------------------------
*/

$router->get('/awards{page}', 'AwardController@index', ['AuthOnly:1']);
$router->get('/awards/add', 'AwardController@add', ['AuthOnly:3']);
$router->post('/awards/add', 'AwardController@insert', ['VerifyCsrf', 'AuthOnly:3']);
$router->get('/awards/edit/{id}', 'AwardController@edit', ['AuthOnly:3']);
$router->post('/awards/edit/{id}', 'AwardController@update', ['VerifyCsrf', 'AuthOnly:3']);
$router->get('/awards/delete/{id}', 'AwardController@delete', ['AuthOnly:3']);

/*
|--------------------------------------------------------------------------
| Produtos
|--------------------------------------------------------------------------
*/

$router->get('/products{page}', 'ProductController@index', ['AuthOnly:3']);
$router->get('/products/add', 'ProductController@add', ['AuthOnly:3']);
$router->post('/products/add', 'ProductController@insert', ['VerifyCsrf', 'AuthOnly:3']);
$router->get('/products/edit/{id}', 'ProductController@edit', ['AuthOnly:3']);
$router->post('/products/edit/{id}', 'ProductController@update', ['VerifyCsrf', 'AuthOnly:3']);
$router->get('/products/delete/{id}', 'ProductController@delete', ['AuthOnly:3']);

/*
|--------------------------------------------------------------------------
| Usuários
|--------------------------------------------------------------------------
*/

$router->get('/users{page}', 'UserController@index', ['AuthOnly:2']);
$router->get('/users/add', 'UserController@add', ['AuthOnly:4']);
$router->post('/users/add', 'UserController@insert', ['VerifyCsrf', 'AuthOnly:4']);
$router->get('/users/edit/{id}', 'UserController@edit', ['AuthOnly:2']);
$router->post('/users/edit/{id}', 'UserController@update', ['VerifyCsrf', 'AuthOnly:2']);
$router->get('/users/delete/{id}', 'UserController@delete', ['AuthOnly:4']);

/*
|--------------------------------------------------------------------------
| Empresas
|--------------------------------------------------------------------------
*/

$router->get('/companies{page}', 'CompanyController@index', ['AuthOnly:3']);
$router->get('/companies/add', 'CompanyController@add', ['AuthOnly:4']);
$router->post('/companies/add', 'CompanyController@insert', ['VerifyCsrf', 'AuthOnly:4']);
$router->get('/companies/edit/{id}', 'CompanyController@edit', ['AuthOnly:4']);
$router->post('/companies/edit/{id}', 'CompanyController@update', ['VerifyCsrf', 'AuthOnly:4']);
$router->get('/companies/delete/{id}', 'CompanyController@delete', ['AuthOnly:4']);

/*
|--------------------------------------------------------------------------
| Executa o roteador
|--------------------------------------------------------------------------
*/

$router->dispatch();
