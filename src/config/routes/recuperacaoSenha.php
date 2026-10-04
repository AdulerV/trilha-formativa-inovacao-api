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

$router->add("POST", "/api/v1/recuperacao-senha/solicitar", [$recuperacaoSenhaController, "solicitar"]);
$router->add("GET", "/api/v1/recuperacao-senha/validar", [$recuperacaoSenhaController, "validar"]);
$router->add("POST", "/api/v1/recuperacao-senha/redefinir", [$recuperacaoSenhaController, "redefinir"]);
