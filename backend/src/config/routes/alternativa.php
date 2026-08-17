<?php

$alternativaOrdenacaoDAO = new AlternativaOrdenacaoDAO($pdo);

$alternativaAssociacaoDAO = new AlternativaAssociacaoDAO($pdo);

$alternativaMultiplaEscolhaDAO = new AlternativaMultiplaEscolhaDAO($pdo);

$alternativaDAO = new AlternativaDAO($pdo, $alternativaOrdenacaoDAO, $alternativaAssociacaoDAO, $alternativaMultiplaEscolhaDAO);

$questaoDAO = new QuestaoDAO($pdo, $alternativaDAO);

$missaoAtividadeTarefaDAO = new MissaoAtividadeTarefaDAO($pdo, $questaoDAO);

$missaoAtividadeDAO = new MissaoAtividadeDAO($pdo, $missaoAtividadeTarefaDAO, $questaoDAO);

$missaoConteudoDAO = new MissaoConteudoDAO($pdo);

$missaoDAO = new MissaoDAO($pdo, $missaoConteudoDAO, $missaoAtividadeDAO);

$alternativaService = new AlternativaService($alternativaDAO);

$questaoService = new QuestaoService($questaoDAO, $missaoDAO, $alternativaService);

$alternativaController = new AlternativaController($alternativaService, $questaoService);

/* $auth = [[JwtMiddleware::class, 'verificar']];

$adminAuth = [
    [JwtMiddleware::class, 'verificar'],
    [AdminMiddleware::class, 'verificar']
]; */

$router->add("GET", "/api/v1/alternativas", [$alternativaController, "listar"]);
$router->add("GET", "/api/v1/questoes/{idQuestao}/alternativas", [$alternativaController, "listarPorQuestao"]);
$router->add("GET", "/api/v1/questoes/{idQuestao}/alternativas/{idAlternativa}", [$alternativaController, "buscarPorId"]);
$router->add("POST", "/api/v1/questoes/{idQuestao}/alternativas", [$alternativaController, "salvar"]);
$router->add("PUT", "/api/v1/questoes/{idQuestao}/alternativas/{idAlternativa}", [$alternativaController, "atualizar"]);
$router->add("DELETE", "/api/v1/questoes/{idQuestao}/alternativas/{idAlternativa}", [$alternativaController, "deletar"]);
