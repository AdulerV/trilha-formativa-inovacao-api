<?php

$recuperacaoSenhaDAO = new RecuperacaoSenhaDAO($pdo);

$usuarioDAORecuperacao = new UsuarioDAO($pdo);

$emailService = new EmailService();

$recuperacaoSenhaService = new RecuperacaoSenhaService(
    $recuperacaoSenhaDAO,
    $usuarioDAORecuperacao,
    $emailService
);

$recuperacaoSenhaController = new RecuperacaoSenhaController($recuperacaoSenhaService);

/*
 * Rotas públicas por definição: quem esqueceu a senha não tem como
 * apresentar um token JWT válido.
 */
$router->add("POST", "/api/v1/recuperacao-senha/solicitar", [$recuperacaoSenhaController, "solicitar"]);
$router->add("GET", "/api/v1/recuperacao-senha/validar", [$recuperacaoSenhaController, "validar"]);
$router->add("POST", "/api/v1/recuperacao-senha/redefinir", [$recuperacaoSenhaController, "redefinir"]);
