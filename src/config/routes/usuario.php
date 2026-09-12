<?php

$usuarioDAO = new UsuarioDAO($pdo);

$ocupacaoDAO = new OcupacaoDAO($pdo);

/*
 * O cadastro só aceita e-mails previamente verificados, então o
 * UsuarioService precisa saber consumir o comprovante emitido em
 * /api/v1/verificacao-email/confirmar.
 */
$usuarioService = new UsuarioService(
    $usuarioDAO,
    $ocupacaoDAO,
    new VerificacaoEmailService(
        new VerificacaoEmailDAO($pdo),
        $usuarioDAO,
        new EmailService()
    )
);

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
