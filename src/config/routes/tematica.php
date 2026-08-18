<?php

$tematicaDAO = new TematicaDAO($pdo);

$tematicaService = new TematicaService($tematicaDAO);

$tematicaController = new TematicaController($tematicaService);

$router->add("GET", "/api/v1/tematicas", [$tematicaController, "listar"]);
$router->add("GET", "/api/v1/tematicas/{id}", [$tematicaController, "buscarPorId"]);
$router->add("POST", "/api/v1/tematicas", [$tematicaController, "salvar"]);
$router->add("PUT", "/api/v1/tematicas/{id}", [$tematicaController, "atualizar"]);
$router->add("DELETE", "/api/v1/tematicas/{id}", [$tematicaController, "deletar"]);
