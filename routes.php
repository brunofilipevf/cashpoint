<?php

/*
|--------------------------------------------------------------------------
| Página inicial
|--------------------------------------------------------------------------
*/

Core\Router::get('/', 'HomeController@index', ['AuthOnly']);

/*
|--------------------------------------------------------------------------
| Autenticação
|--------------------------------------------------------------------------
*/

Core\Router::get('/login', 'AuthController@index', ['GuestOnly']);
Core\Router::post('/login', 'AuthController@login', ['VerifyCsrf', 'GuestOnly']);
Core\Router::get('/logout', 'AuthController@logout', ['AuthOnly']);

/*
|--------------------------------------------------------------------------
| Abastecimentos
|--------------------------------------------------------------------------
*/

Core\Router::get('/supplies{page}', 'SupplyController@index', ['AuthOnly:1']);
Core\Router::get('/supplies/show/{id}', 'SupplyController@show', ['AuthOnly:1']);
Core\Router::post('/api/supplies/add', 'SupplyController@insert', []);

/*
|--------------------------------------------------------------------------
| Pontuações
|--------------------------------------------------------------------------
*/

Core\Router::get('/scores{page}', 'ScoreController@index', ['AuthOnly:1']);
Core\Router::get('/scores/add', 'ScoreController@add', ['AuthOnly:1']);
Core\Router::post('/scores/add', 'ScoreController@insert', ['VerifyCsrf', 'AuthOnly:1']);

/*
|--------------------------------------------------------------------------
| Resgates
|--------------------------------------------------------------------------
*/

Core\Router::get('/redemptions{page}', 'RedemptionController@index', ['AuthOnly:1']);
Core\Router::get('/redemptions/add', 'RedemptionController@add', ['AuthOnly:1']);
Core\Router::post('/redemptions/add', 'RedemptionController@insert', ['VerifyCsrf', 'AuthOnly:1']);

/*
|--------------------------------------------------------------------------
| Clientes
|--------------------------------------------------------------------------
*/

Core\Router::get('/customers{page}', 'CustomerController@index', ['AuthOnly:1']);
Core\Router::get('/customers/add', 'CustomerController@add', ['AuthOnly:1']);
Core\Router::post('/customers/add', 'CustomerController@insert', ['VerifyCsrf', 'AuthOnly:1']);
Core\Router::get('/customers/edit/{id}', 'CustomerController@edit', ['AuthOnly:2']);
Core\Router::post('/customers/edit/{id}', 'CustomerController@update', ['VerifyCsrf', 'AuthOnly:2']);
Core\Router::get('/customers/delete/{id}', 'CustomerController@delete', ['AuthOnly:3']);

/*
|--------------------------------------------------------------------------
| Grupos
|--------------------------------------------------------------------------
*/

Core\Router::get('/groups{page}', 'GroupController@index', ['AuthOnly:1']);
Core\Router::get('/groups/add', 'GroupController@add', ['AuthOnly:3']);
Core\Router::post('/groups/add', 'GroupController@insert', ['VerifyCsrf', 'AuthOnly:3']);
Core\Router::get('/groups/edit/{id}', 'GroupController@edit', ['AuthOnly:3']);
Core\Router::post('/groups/edit/{id}', 'GroupController@update', ['VerifyCsrf', 'AuthOnly:3']);
Core\Router::get('/groups/delete/{id}', 'GroupController@delete', ['AuthOnly:3']);

/*
|--------------------------------------------------------------------------
| Premiações
|--------------------------------------------------------------------------
*/

Core\Router::get('/awards{page}', 'AwardController@index', ['AuthOnly:1']);
Core\Router::get('/awards/add', 'AwardController@add', ['AuthOnly:3']);
Core\Router::post('/awards/add', 'AwardController@insert', ['VerifyCsrf', 'AuthOnly:3']);
Core\Router::get('/awards/edit/{id}', 'AwardController@edit', ['AuthOnly:3']);
Core\Router::post('/awards/edit/{id}', 'AwardController@update', ['VerifyCsrf', 'AuthOnly:3']);
Core\Router::get('/awards/delete/{id}', 'AwardController@delete', ['AuthOnly:3']);

/*
|--------------------------------------------------------------------------
| Produtos
|--------------------------------------------------------------------------
*/

Core\Router::get('/products{page}', 'ProductController@index', ['AuthOnly:3']);
Core\Router::get('/products/add', 'ProductController@add', ['AuthOnly:3']);
Core\Router::post('/products/add', 'ProductController@insert', ['VerifyCsrf', 'AuthOnly:3']);
Core\Router::get('/products/edit/{id}', 'ProductController@edit', ['AuthOnly:3']);
Core\Router::post('/products/edit/{id}', 'ProductController@update', ['VerifyCsrf', 'AuthOnly:3']);
Core\Router::get('/products/delete/{id}', 'ProductController@delete', ['AuthOnly:3']);

/*
|--------------------------------------------------------------------------
| Frentistas
|--------------------------------------------------------------------------
*/

Core\Router::get('/attendants{page}', 'AttendantController@index', ['AuthOnly:2']);
Core\Router::get('/attendants/edit/{id}', 'AttendantController@edit', ['AuthOnly:3']);
Core\Router::post('/attendants/edit/{id}', 'AttendantController@update', ['VerifyCsrf', 'AuthOnly:3']);

/*
|--------------------------------------------------------------------------
| Usuários
|--------------------------------------------------------------------------
*/

Core\Router::get('/users{page}', 'UserController@index', ['AuthOnly:2']);
Core\Router::get('/users/add', 'UserController@add', ['AuthOnly:4']);
Core\Router::post('/users/add', 'UserController@insert', ['VerifyCsrf', 'AuthOnly:4']);
Core\Router::get('/users/edit/{id}', 'UserController@edit', ['AuthOnly:2']);
Core\Router::post('/users/edit/{id}', 'UserController@update', ['VerifyCsrf', 'AuthOnly:2']);
Core\Router::get('/users/delete/{id}', 'UserController@delete', ['AuthOnly:4']);

/*
|--------------------------------------------------------------------------
| Empresas
|--------------------------------------------------------------------------
*/

Core\Router::get('/companies{page}', 'CompanyController@index', ['AuthOnly:3']);
Core\Router::get('/companies/add', 'CompanyController@add', ['AuthOnly:4']);
Core\Router::post('/companies/add', 'CompanyController@insert', ['VerifyCsrf', 'AuthOnly:4']);
Core\Router::get('/companies/edit/{id}', 'CompanyController@edit', ['AuthOnly:4']);
Core\Router::post('/companies/edit/{id}', 'CompanyController@update', ['VerifyCsrf', 'AuthOnly:4']);
Core\Router::get('/companies/delete/{id}', 'CompanyController@delete', ['AuthOnly:4']);

/*
|--------------------------------------------------------------------------
| Executa o roteador
|--------------------------------------------------------------------------
*/

Core\Router::dispatch();
