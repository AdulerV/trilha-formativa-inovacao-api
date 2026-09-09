<?php

$usuarioDAO = new UsuarioDAO($pdo);

$ocupacaoDAO = new OcupacaoDAO($pdo);

$usuarioService = new UsuarioService($usuarioDAO, $ocupacaoDAO);

$usuarioController = new UsuarioController($usuarioService);

$auth = [[JwtMiddleware::class, 'verificar']];

/* 
$adminAuth = [
    [JwtMiddleware::class, 'verificar'],
    [AdminMiddleware::class, 'verificar']
];
 */

$router->add("POST", "/api/v1/usuarios/{idUsuario}/foto", [$usuarioController, "atualizarFotoPerfil"]);
$router->add("DELETE", "/api/v1/usuarios/{idUsuario}/foto", [$usuarioController, "removerFotoPerfil"]);
$router->add("POST", "/api/v1/login", [$usuarioController, "login"]);
$router->add("POST", "/api/v1/usuarios", [$usuarioController, "salvar"]);
$router->add("GET", "/api/v1/usuarios", [$usuarioController, "listar"]);
$router->add("GET", "/api/v1/usuarios/{idUsuario}", [$usuarioController, "buscarPorId"]);
$router->add("PUT", "/api/v1/usuarios/{idUsuario}", [$usuarioController, "atualizar"]);
$router->add("DELETE", "/api/v1/usuarios/{idUsuario}", [$usuarioController, "deletar"]);

$router->add("PATCH", "/api/v1/usuarios/{idUsuario}/primeiro-acesso",[$usuarioController, "alterarPrimeiroAcesso"]);
