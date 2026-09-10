<?php

/*
 * Front Controller da API
 *
 * Este arquivo é responsável por:
 * 1. Permitir o funcionamento de arquivos estáticos no servidor embutido do PHP;
 * 2. Carregar o autoload do Composer;
 * 3. Aplicar o middleware de CORS;
 * 4. Carregar as variáveis de ambiente;
 * 5. Inicializar a conexão com o banco de dados;
 * 6. Registrar as rotas da aplicação;
 * 7. Encaminhar a requisição ao Router.
 */

/*
 * ------------------------------------------------------------
 * 1. Arquivos estáticos no servidor embutido do PHP
 * ------------------------------------------------------------
 *
 * Quando a aplicação é executada com:
 *
 * php -S 0.0.0.0:8000 -t public public/index.php
 *
 * todas as requisições passam pelo Front Controller.
 *
 * Caso a requisição corresponda a um arquivo físico existente
 * dentro de public/, devolvemos false para que o próprio PHP
 * entregue o arquivo.
 */
if (php_sapi_name() === 'cli-server') {

    $caminhoRequisitado = parse_url(
        $_SERVER['REQUEST_URI'] ?? '/',
        PHP_URL_PATH
    ) ?? '/';

    $caminhoRequisitado = rawurldecode($caminhoRequisitado);

    $arquivo = __DIR__ . '/' . ltrim($caminhoRequisitado, '/');

    $arquivoReal = realpath($arquivo);
    $raizPublica = realpath(__DIR__);

    if (
        $arquivoReal !== false &&
        $raizPublica !== false &&
        is_file($arquivoReal) &&
        str_starts_with(
            $arquivoReal,
            $raizPublica . DIRECTORY_SEPARATOR
        ) &&
        basename($arquivoReal) !== 'index.php'
    ) {
        return false;
    }
}


/*
 * ------------------------------------------------------------
 * 2. Autoload do Composer
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../vendor/autoload.php';


/*
 * ------------------------------------------------------------
 * 3. Variáveis de ambiente
 * ------------------------------------------------------------
 *
 * O arquivo .env deve estar na raiz do projeto:
 *
 * projeto/
 * ├── .env
 * ├── composer.json
 * ├── public/
 * │   └── index.php
 * └── src/
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();


/*
 * ------------------------------------------------------------
 * 4. CORS
 * ------------------------------------------------------------
 */
\App\Middleware\CorsMiddleware::aplicar();


/*
 * ------------------------------------------------------------
 * 5. Conexão com o banco de dados
 * ------------------------------------------------------------
 */
$pdo = Conexao::getConexao();

$pdo->setAttribute(
    PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION
);


/*
 * ------------------------------------------------------------
 * 6. Inicialização do Router
 * ------------------------------------------------------------
 */
$router = new Router();


/*
 * ------------------------------------------------------------
 * 7. Registro das rotas
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../src/config/routes/ocupacao.php';
require_once __DIR__ . '/../src/config/routes/tematica.php';
require_once __DIR__ . '/../src/config/routes/usuario.php';
require_once __DIR__ . '/../src/config/routes/distintivo.php';
require_once __DIR__ . '/../src/config/routes/distintivoAdquirido.php';
require_once __DIR__ . '/../src/config/routes/progressoMissao.php';
require_once __DIR__ . '/../src/config/routes/alternativa.php';
require_once __DIR__ . '/../src/config/routes/questao.php';
require_once __DIR__ . '/../src/config/routes/missao.php';
require_once __DIR__ . '/../src/config/routes/alternativaMarcada.php';
require_once __DIR__ . '/../src/config/routes/recuperacaoSenha.php';


/*
 * ------------------------------------------------------------
 * 8. Identificação da URI
 * ------------------------------------------------------------
 *
 * Utilizamos somente o caminho da requisição.
 *
 * Exemplos:
 *
 * http://localhost:8000/api/v1/questoes
 * -> /api/v1/questoes
 *
 * http://10.0.0.166:8000/api/v1/questoes
 * -> /api/v1/questoes
 *
 * https://api.exemplo.com/api/v1/questoes
 * -> /api/v1/questoes
 *
 * Portanto, o endereço utilizado para acessar a API
 * (localhost, IP ou domínio) não interfere na rota.
 */
$uri = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
) ?? '/';

$uri = '/' . trim($uri, '/');

if ($uri === '//') {
    $uri = '/';
}


/*
 * ------------------------------------------------------------
 * 9. Despacho da requisição
 * ------------------------------------------------------------
 */
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($metodo, $uri);