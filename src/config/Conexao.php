<?php

class Conexao
{
    public static function getConexao(): PDO
    {
        return new PDO(
            "mysql:host=10.0.0.166;port=3306;dbname=mydb;charset=utf8mb4",
            "sisgame_user",
            "user#sisgame#mysql",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }
}
