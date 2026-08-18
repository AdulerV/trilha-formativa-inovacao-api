<?php

$distintivoDAO = new DistintivoDAO($pdo);

$distintivoService = new DistintivoService($distintivoDAO);

$distintivoController = new DistintivoController($distintivoService);

$router->add("GET", "/api/v1/distintivos", [$distintivoController, "listar"]);
$router->add("GET", "/api/v1/distintivos/{id}", [$distintivoController, "buscarPorId"]);
$router->add("POST", "/api/v1/distintivos", [$distintivoController, "salvar"]);
$router->add("PUT", "/api/v1/distintivos/{id}", [$distintivoController, "atualizar"]);
$router->add("DELETE", "/api/v1/distintivos/{id}", [$distintivoController, "deletar"]);
