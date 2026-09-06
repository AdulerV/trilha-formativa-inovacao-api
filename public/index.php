<?php

require_once __DIR__ . '/../vendor/autoload.php';

\App\Middleware\CorsMiddleware::aplicar();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$pdo = Conexao::getConexao();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$router = new Router();

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

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$basePath = "/trilha-formativa-gamificacao/backend/public";
$uri = str_replace($basePath, "", $uri);
$uri = $uri ?: "/";

$router->dispatch($_SERVER["REQUEST_METHOD"], $uri);