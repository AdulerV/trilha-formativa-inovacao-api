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

$router->add("GET", "/api/v1/questoes", [$questaoController, "listar"]);
$router->add("GET", "/api/v1/missoes/{idMissao}/questoes", [$questaoController, "listarPorMissao"]);
$router->add("GET", "/api/v1/missoes/{idMissao}/questoes/{idQuestao}", [$questaoController, "buscarPorId"]);
$router->add("POST", "/api/v1/missoes/{idMissao}/questoes", [$questaoController, "salvar"]);
$router->add("PUT", "/api/v1/missoes/{idMissao}/questoes/{idQuestao}", [$questaoController, "atualizar"]);
$router->add("DELETE", "/api/v1/missoes/{idMissao}/questoes/{idQuestao}", [$questaoController, "deletar"]);
