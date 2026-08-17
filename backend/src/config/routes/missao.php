<?php

$alternativaOrdenacaoDAO = new AlternativaOrdenacaoDAO($pdo);

$alternativaAssociacaoDAO = new AlternativaAssociacaoDAO($pdo);

$alternativaMultiplaEscolhaDAO = new AlternativaMultiplaEscolhaDAO($pdo);

$alternativaDAO = new AlternativaDAO($pdo, $alternativaOrdenacaoDAO, $alternativaAssociacaoDAO, $alternativaMultiplaEscolhaDAO);

$alternativaService = new AlternativaService($alternativaDAO);

$questaoDAO = new QuestaoDAO($pdo, $alternativaDAO);

$questaoService = new QuestaoService($questaoDAO, null, $alternativaService);

$missaoAtividadeTarefaDAO = new MissaoAtividadeTarefaDAO($pdo, $questaoDAO);

$missaoAtividadeDAO = new MissaoAtividadeDAO($pdo, $missaoAtividadeTarefaDAO, $questaoDAO);

$missaoConteudoDAO = new MissaoConteudoDAO($pdo);

$missaoDAO = new MissaoDAO($pdo, $missaoConteudoDAO, $missaoAtividadeDAO);

$questaoService = new QuestaoService($questaoDAO, $missaoDAO, $alternativaService);

$missaoService = new MissaoService($missaoDAO, $questaoService);

$missaoController = new MissaoController($missaoService);

$router->add("GET", "/api/v1/missoes", [$missaoController, "listar"]);
$router->add("GET", "/api/v1/missoes/{idMissao}", [$missaoController, "buscarPorId"]);
$router->add("POST", "/api/v1/missoes", [$missaoController, "salvar"]);
$router->add("PUT", "/api/v1/missoes/{idMissao}", [$missaoController, "atualizar"]);
$router->add("DELETE", "/api/v1/missoes/{idMissao}", [$missaoController, "deletar"]);
