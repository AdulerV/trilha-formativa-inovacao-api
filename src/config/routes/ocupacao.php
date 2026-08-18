<?php

$ocupacaoDAO = new OcupacaoDAO($pdo);

$ocupacaoService = new OcupacaoService($ocupacaoDAO);

$ocupacaoController = new OcupacaoController($ocupacaoService);

$router->add("GET", "/api/v1/ocupacoes", [$ocupacaoController, "listar"]);
$router->add("GET", "/api/v1/ocupacoes/{id}", [$ocupacaoController, "buscarPorId"]);
$router->add("POST", "/api/v1/ocupacoes", [$ocupacaoController, "salvar"]);
$router->add("PUT", "/api/v1/ocupacoes/{id}", [$ocupacaoController, "atualizar"]);
$router->add("DELETE", "/api/v1/ocupacoes/{id}", [$ocupacaoController, "deletar"]);
