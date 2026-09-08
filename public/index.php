<?php

/*
 * Servidor embutido do PHP (php -S ... public/index.php): quando um
 * script de rota é informado, TODA requisição passa por ele, inclusive
 * as de arquivos estáticos. Sem devolver false para os arquivos que
 * existem, as imagens de perfil e de distintivo em
 * public/image/... respondiam 404 no ambiente de desenvolvimento,
 * mesmo estando no disco.
 *
 * Sob Apache o servidor entrega o arquivo antes de chegar ao PHP, então
 * este bloco simplesmente não é alcançado.
 */
if (php_sapi_name() === 'cli-server') {
    $caminhoRequisitado = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $arquivo = __DIR__ . '/' . ltrim(rawurldecode($caminhoRequisitado), '/');

    /* realpath resolve ".." e garante que o alvo está dentro de public/. */
    $arquivoReal = realpath($arquivo);
    $raizPublica = realpath(__DIR__);

    if (
        $arquivoReal !== false
        && $raizPublica !== false
        && is_file($arquivoReal)
        && str_starts_with($arquivoReal, $raizPublica . DIRECTORY_SEPARATOR)
        && basename($arquivoReal) !== 'index.php'
    ) {
        return false;
    }
}

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