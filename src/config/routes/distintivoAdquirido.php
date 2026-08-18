<?php

$distintivoAdquiridoDAO = new DistintivoAdquiridoDAO($pdo);

$distintivoAdquiridoService = new DistintivoAdquiridoService($distintivoAdquiridoDAO);

$distintivoAdquiridoController = new DistintivoAdquiridoController($distintivoAdquiridoService);

$router->add("GET", "/api/v1/distintivo-adquirido", [$distintivoAdquiridoController, "listar"]);
$router->add("GET", "/api/v1/usuarios/{idUsuario}/distintivos", [$distintivoAdquiridoController, "listarPorUsuario"]);
$router->add("GET", "/api/v1/usuarios/{idUsuario}/distintivos/{idDistintivo}", [$distintivoAdquiridoController, "buscarPorId"]);
$router->add("POST", "/api/v1/usuarios/distintivos", [$distintivoAdquiridoController, "salvar"]);
/* $router->add("PUT", "/api/v1/usuarios/{idUsuario}/distintivos/{idDistintivo}", [$distintivoAdquiridoController, "atualizar"]); */
$router->add("DELETE", "/api/v1/usuarios/{idUsuario}/distintivos/{idDistintivo}", [$distintivoAdquiridoController, "deletar"]);
