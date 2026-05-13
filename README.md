CashPoint Framework
==================
Micro-framework PHP OOP criado por Bruno Freitas para o sistema CashPoint.


Requisitos
----------
- PHP 8.1 ou superior
- MySQL 8.0 ou superior
- Servidor Apache com mod_rewrite


Estrutura de Diretórios
-----------------------
```
raiz/
  ├── app/
  │   ├── controllers/  # Controllers
  │   ├── middlewares/  # Middlewares
  │   ├── models/       # Models
  │   └── views/        # Templates (php/html)
  ├── boot/
  │   ├── autoload.php
  │   ├── config.php
  │   └── routes.php
  ├── core/
  │   ├── Database.php
  │   ├── Request.php
  │   ├── Response.php
  │   ├── Router.php
  │   ├── Session.php
  │   ├── Validator.php
  │   └── View.php
  ├── public/           # Document root
  │   ├── assets/       # (css/js)
  │   ├── index.php
  │   └── .htaccess
  ├── storage/          # Logs e sessões
  └── .gitignore
```


Instalação
----------
1. Aponte o document root do servidor para o diretório public
2. Crie o banco de dados executando o arquivo schema.sql
3. Configure as credenciais do banco no arquivo config.php
4. Certifique-se de que o diretório storage tenha permissão de escrita


Configuração
------------
Edite o arquivo config.php para ajustar:

- APP_DEBUG: true para desenvolvimento, false para produção
- DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT: dados do banco
- APP_NAME, APP_AUTHOR, APP_DESCRIPTION: informações da aplicação


Rotas
-----
Definidas em routes.php usando:

````php
Router::get('/caminho', 'Controller@action', ['Middleware1', 'Middleware2']);
Router::post('/caminho', 'Controller@action', ['Middleware1', 'Middleware2']);
````

Parâmetros dinâmicos usam {id} ou {page} e aceitam apenas inteiros positivos
de 1 a 9999999999.


Middlewares Disponíveis
----------------------
- AuthOnly: exige autenticação
- GuestOnly: exige que o usuário NÃO esteja autenticado
- ValidateCsrf: valida token CSRF (obrigatório em todas as rotas POST)


Autoload
--------
PSR-4 simplificado com dois namespaces:

- App\Controllers\NomeController  =>  app/controllers/NomeController.php
- App\Middlewares\NomeMiddleware  =>  app/middlewares/NomeMiddleware.php
- Core\NomeClasse                 =>  core/NomeClasse.php


Banco de Dados (Database)
-------------------------
Métodos estáticos disponíveis:

```php
Database::select($sql, $params)
    # Retorna array com todos os resultados.

Database::selectOne($sql, $params)
    # Retorna array com um resultado ou false.

Database::insert($sql, $params)
    # Retorna int com o ID inserido ou false.

Database::update($sql, $params)
    # Retorna true se alguma linha foi afetada, false caso contrário.

Database::delete($sql, $params)
    # Retorna true se excluiu, false caso contrário ou em erro de constraint.

Database::prepare($sql)
    # Retorna o PDOStatement para queries customizadas.

Database::lastInsertId()
    # Retorna o último ID inserido.

Database::beginTransaction()
Database::commit()
Database::rollBack()
    # Controle de transações manuais.
```


Requisição (Request)
--------------------
```php
Request::method()
    # Retorna o método HTTP (GET, POST, etc).

Request::uri()
    # Retorna a URI atual limpa e sanitizada.

Request::input('campo')
    # Retorna o valor do campo POST sanitizado ou null.
```


Resposta (Response)
-------------------
```php
Response::send($conteudo, $statusCode)
    # Envia resposta HTML com headers de segurança e encerra a execução.

Response::view('caminho/da/view', $dados, $statusCode)
    # Renderiza uma view, envia a resposta e encerra a execução.

Response::redirect('/caminho', $statusCode)
    # Redireciona para outra rota e encerra a execução.

Response::previous($statusCode)
    # Redireciona para a URI anterior e encerra a execução.
```


Sessão (Session)
----------------
```php
Session::set('chave', $valor)
    # Define um valor na sessão. Suporta notação de ponto.

Session::get('chave')
    # Recupera um valor da sessão ou null.

Session::unset('chave')
    # Remove um valor da sessão.

Session::destroy()
    # Destroi a sessão completamente.

Session::regenerate()
    # Regenera o ID da sessão.

Session::setFlash('tipo', 'mensagem')
    # Define mensagem flash (disponível apenas na próxima requisição).

Session::getFlash()
    # Recupera e remove a mensagem flash. Retorna array ou [].

Session::getCsrf()
    # Gera ou recupera o token CSRF armazenado em sessão.

Session::validateCsrf($token)
    # Valida o token CSRF usando comparação timing-safe.
```


Validação (Validator)
---------------------
```php
Validator::fields($valores, $regras, $labels)
    # Retorna array de erros encontrados ou array vazio.
```

Regras disponíveis:

```
required            O campo é obrigatório
integer             Número inteiro entre 0 e 4294967295
numeric             Número decimal com até 2 casas entre 0 e 99999999.99
alpha               Apenas letras sem acentos
alphanum            Letras sem acentos e números
string              Qualquer string com limite de 255 caracteres
email               E-mail válido com até 254 caracteres
phone               Apenas números com exatamente 11 dígitos
document            11 dígitos (CPF) ou 14 dígitos (CNPJ)
date                Data nos formatos Y-m-d ou Y-m-d H:i:s
after_or_equal      Valor deve ser maior ou igual ao campo referenciado
before_or_equal     Valor deve ser menor ou igual ao campo referenciado
in                  Valor deve estar na lista separada por vírgulas
min                 Valor mínimo (numérico) ou comprimento mínimo (string)
max                 Valor máximo (numérico) ou comprimento máximo (string)
exist               Valor deve existir na tabela e coluna especificadas
unique              Valor deve ser único na tabela e coluna especificadas
```

Exemplo de uso:

```php
$values = [
    'nome' => Request::input('nome'),
    'email' => Request::input('email'),
    'idade' => Request::input('idade')
];

$rules = [
    'nome' => 'required|alpha|min:3|max:60',
    'email' => 'required|email|unique:user,email',
    'idade' => 'required|integer|min:18|max:120'
];

$labels = [
    'nome' => 'Nome',
    'email' => 'E-mail',
    'idade' => 'Idade'
];

$errors = Validator::fields($values, $rules, $labels);
```


Views (View)
------------
Helpers disponíveis dentro das views:

```php
$e($valor, $formato, $dash)
    # Escapa e formata um valor para saída HTML segura.
    # Formatos: currency, date, document, status.
    # O formato 'date' aceita um parâmetro opcional de saída (ex: date:d/m/Y).
    # Se nenhum parâmetro for informado, o padrão é d/m/Y H:i:s.
    # Use $dash = true para exibir travessão em valores vazios.

$get('chave')
    # Recupera variáveis globais como app_name, csrf_token, flash_message.
    # Também acessa variáveis definidas com set().

$set('chave', $valor)
    # Define uma variável global para a view.

$include('caminho/view')
    # Inclui uma subview.

$isBaseRoute('/caminho')
    # Verifica se a URI atual começa com o caminho informado.
```


Segurança
---------
- Todas as queries usam prepared statements
- Proteção CSRF em todas as rotas POST
- Headers de segurança HTTP completos (CSP, HSTS, X-Frame-Options, etc)
- Sessão configurada com httponly, secure e samesite strict
- Escape automático de saída HTML com htmlspecialchars
- Validação de entrada via regex e tipos nativos
- URI sanitizada contra path traversal e caracteres nulos


Contratos dos Métodos do Banco de Dados
---------------------------------------
```
select()    -> array (sempre, mesmo vazio)
selectOne() -> array ou false
insert()    -> int ou false
update()    -> true ou false
delete()    -> true ou false
```


Observações
-----------
- O framework não utiliza type hints em métodos públicos por opção do autor
- Operadores ternários e de coalescência não são utilizados
- Views são puramente fixas e digitadas pelo desenvolvedor
- Usuários não manipulam views, validators ou responses
- As mensagens de exceção do banco de dados são logadas internamente
  e nunca expostas ao usuário final
