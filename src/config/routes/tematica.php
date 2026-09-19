<?php

$tematicaDAO = new TematicaDAO($pdo);

$tematicaService = new TematicaService($tematicaDAO);

$tematicaController = new TematicaController($tematicaService);

$auth = [[JwtMiddleware::class, 'verificar']];

$router->add("GET", "/api/v1/tematicas", [$tematicaController, "listar"], $auth);
//$router->add("GET", "/api/v1/tematicas/{id}", [$tematicaController, "buscarPorId"], $auth);
//$router->add("POST", "/api/v1/tematicas", [$tematicaController, "salvar"], $auth);
//$router->add("PUT", "/api/v1/tematicas/{id}", [$tematicaController, "atualizar"], $auth);
//$router->add("DELETE", "/api/v1/tematicas/{id}", [$tematicaController, "deletar"], $auth);
