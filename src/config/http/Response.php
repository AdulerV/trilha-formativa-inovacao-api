<?php

class Response
{
    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header("Content-Type: application/json");

        echo json_encode($data);
    }

    public static function error(string $msg, int $status = 400): void
    {
        self::json(["erro" => $msg], $status);
    }
}