<?php

declare(strict_types=1);

class AdminMiddleware
{
    public static function verificar(): void
    {
        $usuario = $_REQUEST['usuario_autenticado'] ?? null;

        if (!$usuario || empty($usuario->admin)) {
            Response::error("Acesso negado. Apenas administradores podem realizar esta ação.", 403);
            exit;
        }
    }
}
