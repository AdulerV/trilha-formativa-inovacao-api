<?php

$alternativaOrdenacaoDAO = new AlternativaOrdenacaoDAO($pdo);

$alternativaAssociacaoDAO = new AlternativaAssociacaoDAO($pdo);

$alternativaMultiplaEscolhaDAO = new AlternativaMultiplaEscolhaDAO($pdo);

$alternativaDAO = new AlternativaDAO($pdo, $alternativaOrdenacaoDAO, $alternativaAssociacaoDAO, $alternativaMultiplaEscolhaDAO);

$alternativaService = new AlternativaService($alternativaDAO);

$questaoDAO = new QuestaoDAO($pdo, $alternativaDAO);

$missaoAtividadeTarefa = new MissaoAtividadeTarefaDAO($pdo, $questaoDAO);

$missaoAtividade = new MissaoAtividadeDAO($pdo, $missaoAtividadeTarefa, $questaoDAO);

$progressoAtividadeDAO = new ProgressoMissaoAtividadeDAO($pdo, $missaoAtividade);

$progressoDAO = new ProgressoMissaoDAO($pdo, $progressoAtividadeDAO);

$progressoService = new ProgressoMissaoService($progressoDAO);

$usuarioDAO = new UsuarioDAO($pdo);

$ocupacaoDAO = new OcupacaoDAO($pdo);

$usuarioService = new UsuarioService($usuarioDAO, $ocupacaoDAO);

$missaoAtividadeTarefaDAO = new MissaoAtividadeTarefaDAO($pdo, $questaoDAO);

$missaoAtividadeDAO = new MissaoAtividadeDAO($pdo, $missaoAtividadeTarefaDAO, $questaoDAO);

$missaoConteudoDAO = new MissaoConteudoDAO($pdo);

$missaoDAO = new MissaoDAO($pdo, $missaoConteudoDAO, $missaoAtividadeDAO);

$questaoService = new QuestaoService($questaoDAO, $missaoDAO, $alternativaService);

$missaoService = new MissaoService($missaoDAO, $questaoService);

$progressoController = new ProgressoMissaoController($progressoService, $usuarioService, $missaoService);

$router->add("GET", "/api/v1/progresso-missao", [$progressoController, "listar"]);
$router->add("GET", "/api/v1/usuarios/{idUsuario}/missoes", [$progressoController, "listarPorUsuario"]);
$router->add("GET", "/api/v1/usuarios/{idUsuario}/missoes/{idMissao}", [$progressoController, "buscarPorId"]);
$router->add("POST", "/api/v1/usuarios/missoes", [$progressoController, "salvar"]);
$router->add("PUT", "/api/v1/usuarios/{idUsuario}/missoes/{idMissao}", [$progressoController, "atualizar"]);
$router->add("DELETE", "/api/v1/usuarios/{idUsuario}/missoes/{idMissao}", [$progressoController, "deletar"]);
