<?php

class Request
{
    public static function body(): array
    {
        return json_decode(file_get_contents("php://input"), true) ?? [];
    }

    public static function query(string $key): ?string
    {
        return $_GET[$key] ?? null;
    }
}