-- -----------------------------------------------------
-- Migração 002 — Verificação de e-mail no cadastro
-- -----------------------------------------------------
-- Cria a tabela que sustenta a confirmação do endereço de
-- e-mail ANTES de a conta existir.
--
-- Observações de modelagem:
--   * Não há chave estrangeira para USUARIO, e isso é
--     proposital: no momento da verificação o usuário
--     ainda não foi criado. O vínculo é o próprio
--     endereço de e-mail.
--   * HashCodigo guarda o resumo SHA-256 do código de seis
--     dígitos, nunca o código em claro.
--   * HashComprovante guarda o resumo do comprovante de
--     256 bits emitido após a confirmação. É ele que o
--     frontend envia no POST /usuarios.
--   * Tentativas é o contador que limita a força bruta
--     sobre o código curto. Ver EMAIL_VERIFICATION_MAX_ATTEMPTS.
--
-- Ciclo de vida de um registro:
--   emitido    -> DataVerificacao NULL, DataConsumo NULL
--   verificado -> DataVerificacao preenchida, comprovante emitido
--   consumido  -> DataConsumo preenchida (conta criada ou invalidado)
--
-- Execução:
--   mysql -u root -p mydb < src/config/sql/migrations/002_verificacao_email.sql
-- -----------------------------------------------------

USE `mydb`;

CREATE TABLE IF NOT EXISTS `mydb`.`VERIFICACAO_EMAIL` (
  `IdVerificacaoEmail` INT NOT NULL AUTO_INCREMENT,
  `CorreioEletronico` VARCHAR(320) NOT NULL,
  `HashCodigo` CHAR(64) NOT NULL,
  `HashComprovante` CHAR(64) NULL DEFAULT NULL,
  `Tentativas` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `DataCriacao` DATETIME NOT NULL,
  `DataExpiracao` DATETIME NOT NULL,
  `DataVerificacao` DATETIME NULL DEFAULT NULL,
  `DataExpiracaoComprovante` DATETIME NULL DEFAULT NULL,
  `DataConsumo` DATETIME NULL DEFAULT NULL,
  `EnderecoIp` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`IdVerificacaoEmail`),
  UNIQUE INDEX `VerificacaoEmail_Comprovante_UNIQUE` (`HashComprovante` ASC),
  INDEX `VerificacaoEmail_Correio_idx` (`CorreioEletronico` ASC),
  INDEX `VerificacaoEmail_Expiracao_idx` (`DataExpiracao` ASC))
ENGINE = InnoDB;
