<?php

declare(strict_types=1);

namespace App\Middleware;

class CorsMiddleware
{
    public static function aplicar(): void
    {
        header("Access-Control-Allow-Origin: https://sisgame.jf.ifsudestemg.edu.br/");

        //header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");

        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
