-- -----------------------------------------------------
-- Migração 001 — Mecanismo de recuperação de senha
-- -----------------------------------------------------
-- Cria a tabela responsável por armazenar os tokens de
-- redefinição de senha emitidos pela API.
--
-- Observações de segurança:
--   * A coluna HashToken NÃO armazena o token enviado ao
--     usuário, e sim o seu resumo SHA-256. Caso a base seja
--     comprometida, os tokens em trânsito continuam inúteis.
--   * O token é de uso único: DataUtilizacao passa a conter o
--     instante do consumo e o registro nunca mais é aceito.
--   * DataExpiracao materializa o TTL definido em
--     PASSWORD_RESET_TTL_MINUTES (.env).
--
-- Execução:
--   mysql -u root -p mydb < src/config/sql/migrations/001_recuperacao_senha.sql
-- -----------------------------------------------------

USE `mydb`;

CREATE TABLE IF NOT EXISTS `mydb`.`RECUPERACAO_SENHA` (
  `IdRecuperacaoSenha` INT NOT NULL AUTO_INCREMENT,
  `IdUsuario` INT NOT NULL,
  `HashToken` CHAR(64) NOT NULL,
  `DataCriacao` DATETIME NOT NULL,
  `DataExpiracao` DATETIME NOT NULL,
  `DataUtilizacao` DATETIME NULL DEFAULT NULL,
  `EnderecoIp` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`IdRecuperacaoSenha`),
  UNIQUE INDEX `HashToken_UNIQUE` (`HashToken` ASC),
  INDEX `fk_RECUPERACAO_SENHA_USUARIO1_idx` (`IdUsuario` ASC),
  INDEX `RecuperacaoSenha_Expiracao_idx` (`DataExpiracao` ASC),
  CONSTRAINT `fk_RECUPERACAO_SENHA_USUARIO1`
    FOREIGN KEY (`IdUsuario`)
    REFERENCES `mydb`.`USUARIO` (`IdUsuario`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
