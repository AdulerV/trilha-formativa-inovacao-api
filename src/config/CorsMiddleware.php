<?php

declare(strict_types=1);

namespace App\Middleware;

class CorsMiddleware
{
    public static function aplicar(): void
    {
        // Removida a barra '/' do final da URL
        header("Access-Control-Allow-Origin: https://sisgame.jf.ifsudestemg.edu.br");
        
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Access-Control-Request-Private-Network");
        
        // Permite requisições de rede privada (HTTPS para IP Local)
        header("Access-Control-Allow-Private-Network: true");

        // Trata requisições Preflight (OPTIONS)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}