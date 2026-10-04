<?php

$distintivoDAO = new DistintivoDAO($pdo);

$distintivoService = new DistintivoService($distintivoDAO);

$distintivoController = new DistintivoController($distintivoService);

$auth = [[JwtMiddleware::class, 'verificar']];

$router->add("GET", "/api/v1/distintivos", [$distintivoController, "listar"], $auth);
$router->add("GET", "/api/v1/distintivos/{id}", [$distintivoController, "buscarPorId"], $auth);
//$router->add("POST", "/api/v1/distintivos", [$distintivoController, "salvar"], $auth);
//$router->add("PUT", "/api/v1/distintivos/{id}", [$distintivoController, "atualizar"], $auth);
//$router->add("DELETE", "/api/v1/distintivos/{id}", [$distintivoController, "deletar"], $auth);
