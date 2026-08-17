<?php

$alternativaMarcadaDAO = new AlternativaMarcadaDAO($pdo);

$alternativaOrdenacaoDAO = new AlternativaOrdenacaoDAO($pdo);

$alternativaAssociacaoDAO = new AlternativaAssociacaoDAO($pdo);

$alternativaMultiplaEscolhaDAO = new AlternativaMultiplaEscolhaDAO($pdo);

$alternativaDAO = new AlternativaDAO($pdo, $alternativaOrdenacaoDAO, $alternativaAssociacaoDAO, $alternativaMultiplaEscolhaDAO);

$alternativaMarcadaService = new AlternativaMarcadaService($alternativaMarcadaDAO, $alternativaDAO);

$alternativaMarcadaController = new AlternativaMarcadaController($alternativaMarcadaService);

$router->add("GET", "/api/v1/alternativas-marcadas", [$alternativaMarcadaController, "listar"]);
$router->add("GET", "/api/v1/usuarios/{idUsuario}/alternativas-marcadas", [$alternativaMarcadaController, "listarPorUsuario"]);
$router->add("GET", "/api/v1/usuarios/{idUsuario}/alternativas/{idAlternativa}", [$alternativaMarcadaController, "buscarPorId"]);
$router->add("POST", "/api/v1/usuarios/alternativas-marcadas", [$alternativaMarcadaController, "salvar"]);
$router->add("PUT", "/api/v1/usuarios/{idUsuario}/alternativas/{idAlternativa}", [$alternativaMarcadaController, "atualizar"]);
$router->add("DELETE", "/api/v1/usuarios/{idUsuario}/alternativas/{idAlternativa}", [$alternativaMarcadaController, "deletar"]);
