<?php

declare(strict_types=1);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtMiddleware
{
    private static string $algoritmo = 'HS256';

    public static function verificar(): void
    {
        $secretKey = $_ENV['JWT_SECRET'] ?? '';

        $headers = function_exists('getallheaders') ? getallheaders() : [];

        $authHeader = $headers['Authorization']
            ?? $headers['authorization']
            ?? $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Response::error("Token não fornecido ou inválido.", 401);
            exit;
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key($secretKey, self::$algoritmo));
            $_REQUEST['usuario_autenticado'] = $decoded->data;
        } catch (Exception $e) {
            Response::error("Token expirado ou inválido.", 401);
            exit;
        }
    }
}
