<?php

$verificacaoEmailDAO = new VerificacaoEmailDAO($pdo);

$usuarioDAOVerificacao = new UsuarioDAO($pdo);

$emailServiceVerificacao = new EmailService();

$verificacaoEmailService = new VerificacaoEmailService(
    $verificacaoEmailDAO,
    $usuarioDAOVerificacao,
    $emailServiceVerificacao
);

$verificacaoEmailController = new VerificacaoEmailController($verificacaoEmailService);

/*
 * Rotas públicas por definição: a verificação acontece antes de a conta
 * existir, então não há como exigir um JWT.
 */
$router->add("POST", "/api/v1/verificacao-email/solicitar", [$verificacaoEmailController, "solicitar"]);
$router->add("POST", "/api/v1/verificacao-email/confirmar", [$verificacaoEmailController, "confirmar"]);
