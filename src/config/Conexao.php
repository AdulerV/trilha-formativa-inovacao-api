<?php

class Conexao
{
    public static function getConexao(): PDO
    {
        return new PDO(
            "mysql:host=10.0.0.166;port=3306;dbname=mydb;charset=utf8mb4",
            "root",
            "root#sisgame#mysql",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }
}
/*

<?php

class Conexao
{
    public static function getConexao(): PDO
    {
        return new PDO(
            "mysql:host=127.0.0.1;port=3307;dbname=mydb;charset=utf8mb4",
            "root",
            "",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }
}

*/