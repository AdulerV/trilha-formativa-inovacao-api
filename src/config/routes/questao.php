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

$missaoService = new MissaoService($missaoDAO, $questaoService);

$questaoController = new QuestaoController($questaoService, $missaoService);

$adminAuth = [
    [JwtMiddleware::class, 'verificar'],
    [AdminMiddleware::class, 'verificar']
];

$router->add("GET", "/api/v1/questoes", [$questaoController, "listar"], $adminAuth);
//$router->add("GET", "/api/v1/missoes/{idMissao}/questoes", [$questaoController, "listarPorMissao"], $adminAuth);
$router->add("GET", "/api/v1/missoes/{idMissao}/questoes/{idQuestao}", [$questaoController, "buscarPorId"], $adminAuth);
$router->add("POST", "/api/v1/missoes/{idMissao}/questoes", [$questaoController, "salvar"], $adminAuth);
$router->add("PUT", "/api/v1/missoes/{idMissao}/questoes/{idQuestao}", [$questaoController, "atualizar"], $adminAuth);
$router->add("DELETE", "/api/v1/missoes/{idMissao}/questoes/{idQuestao}", [$questaoController, "deletar"], $adminAuth);
