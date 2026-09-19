<?php

$distintivoAdquiridoDAO = new DistintivoAdquiridoDAO($pdo);

$distintivoAdquiridoService = new DistintivoAdquiridoService($distintivoAdquiridoDAO);

$distintivoAdquiridoController = new DistintivoAdquiridoController($distintivoAdquiridoService);

$auth = [[JwtMiddleware::class, 'verificar']];

//$router->add("GET", "/api/v1/distintivo-adquirido", [$distintivoAdquiridoController, "listar"], $auth);
$router->add("GET", "/api/v1/usuarios/{idUsuario}/distintivos", [$distintivoAdquiridoController, "listarPorUsuario"], $auth);
//$router->add("GET", "/api/v1/usuarios/{idUsuario}/distintivos/{idDistintivo}", [$distintivoAdquiridoController, "buscarPorId"], $auth);
$router->add("POST", "/api/v1/usuarios/distintivos", [$distintivoAdquiridoController, "salvar"], $auth);
/* $router->add("PUT", "/api/v1/usuarios/{idUsuario}/distintivos/{idDistintivo}", [$distintivoAdquiridoController, "atualizar"]); */
$router->add("DELETE", "/api/v1/usuarios/{idUsuario}/distintivos/{idDistintivo}", [$distintivoAdquiridoController, "deletar"], $auth);
