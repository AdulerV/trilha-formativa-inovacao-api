-- 1. Desativa a checagem de chaves estrangeiras para evitar erros de relacionamento
SET FOREIGN_KEY_CHECKS = 0;

-- 2. Gera os comandos de truncate dinamicamente para o seu banco de dados
SELECT CONCAT('TRUNCATE TABLE `', table_schema, '`.`', table_name, '`;') 
FROM INFORMATION_SCHEMA.TABLES 
WHERE table_schema = 'NOME_DO_SEU_BANCO';

-- 3. Reativa a checagem de chaves estrangeiras após executar os truncates
SET FOREIGN_KEY_CHECKS = 1;