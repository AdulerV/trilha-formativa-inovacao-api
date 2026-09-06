-- MySQL Workbench Forward Engineering

-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`OCUPACAO`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`OCUPACAO` (
  `IdOcupacao` INT NOT NULL AUTO_INCREMENT,
  `Titulo` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`IdOcupacao`),
  UNIQUE INDEX `Nome_UNIQUE` (`Titulo` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`USUARIO`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`USUARIO` (
  `IdUsuario` INT NOT NULL AUTO_INCREMENT,
  `Nome` VARCHAR(255) NOT NULL,
  `NomeAventureiro` VARCHAR(100) NOT NULL,
  `CorreioEletronico` VARCHAR(320) NOT NULL,
  `DataNascimento` DATE NULL,
  `PossuiConhecimento` TINYINT NOT NULL DEFAULT 0,
  `PrimeiroAcesso` TINYINT NOT NULL,
  `FotoPerfil` VARCHAR(255) NOT NULL,
  `Admin` TINYINT NOT NULL DEFAULT 0,
  `HashSenha` VARCHAR(60) NOT NULL,
  `IdOcupacao` INT NOT NULL,
  PRIMARY KEY (`IdUsuario`),
  UNIQUE INDEX `NomeAventureiro_UNIQUE` (`NomeAventureiro` ASC),
  INDEX `fk_USUARIO_OCUPACAO1_idx` (`IdOcupacao` ASC),
  UNIQUE INDEX `CorreioEletronico_UNIQUE` (`CorreioEletronico` ASC),
  CONSTRAINT `fk_USUARIO_OCUPACAO1`
    FOREIGN KEY (`IdOcupacao`)
    REFERENCES `mydb`.`OCUPACAO` (`IdOcupacao`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`RECUPERACAO_SENHA`
-- -----------------------------------------------------
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


-- -----------------------------------------------------
-- Table `mydb`.`TEMATICA`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`TEMATICA` (
  `IdTematica` INT NOT NULL AUTO_INCREMENT,
  `Titulo` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`IdTematica`),
  UNIQUE INDEX `Nome_UNIQUE` (`Titulo` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`MISSAO`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`MISSAO` (
  `IdMissao` INT NOT NULL AUTO_INCREMENT,
  `Titulo` VARCHAR(255) NOT NULL,
  `Pontuacao` FLOAT NOT NULL,
  `TipoMissao` VARCHAR(45) NOT NULL,
  `IdTematica` INT NOT NULL,
  PRIMARY KEY (`IdMissao`),
  UNIQUE INDEX `Titulo_UNIQUE` (`Titulo` ASC),
  INDEX `fk_MISSAO_TEMATICA1_idx` (`IdTematica` ASC),
  CONSTRAINT `fk_MISSAO_TEMATICA1`
    FOREIGN KEY (`IdTematica`)
    REFERENCES `mydb`.`TEMATICA` (`IdTematica`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`MISSAO_ATIVIDADE`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`MISSAO_ATIVIDADE` (
  `IdMissao` INT NOT NULL,
  `TipoAtividade` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`IdMissao`),
  CONSTRAINT `fk_MISSAO_ATIVIDADE_MISSAO1`
    FOREIGN KEY (`IdMissao`)
    REFERENCES `mydb`.`MISSAO` (`IdMissao`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`MISSAO_CONTEUDO`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`MISSAO_CONTEUDO` (
  `IdMissao` INT NOT NULL,
  `URL` VARCHAR(2048) NOT NULL,
  `Resumo` MEDIUMTEXT NOT NULL,
  `TipoMaterial` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`IdMissao`),
  CONSTRAINT `fk_MISSAO_DOCUMENTO_MISSAO1`
    FOREIGN KEY (`IdMissao`)
    REFERENCES `mydb`.`MISSAO` (`IdMissao`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`QUESTAO`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`QUESTAO` (
  `IdQuestao` INT NOT NULL AUTO_INCREMENT,
  `Enunciado` TEXT NOT NULL,
  `MensagemCorrecao` TEXT NOT NULL,
  `IdMissao` INT NOT NULL,
  PRIMARY KEY (`IdQuestao`),
  INDEX `fk_QUESTAO_MISSAO_ATIVIDADE1_idx` (`IdMissao` ASC),
  CONSTRAINT `fk_QUESTAO_MISSAO_ATIVIDADE1`
    FOREIGN KEY (`IdMissao`)
    REFERENCES `mydb`.`MISSAO_ATIVIDADE` (`IdMissao`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`DISTINTIVO`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`DISTINTIVO` (
  `IdDistintivo` INT NOT NULL AUTO_INCREMENT,
  `Titulo` VARCHAR(255) NOT NULL,
  `Pontuacao` FLOAT NOT NULL,
  `NomeArquivo` VARCHAR(100) NOT NULL,
  UNIQUE INDEX `Nome_UNIQUE` (`NomeArquivo` ASC),
  PRIMARY KEY (`IdDistintivo`),
  UNIQUE INDEX `Titulo_UNIQUE` (`Titulo` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`MISSAO_ATIVIDADE_TAREFA`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`MISSAO_ATIVIDADE_TAREFA` (
  `IdMissao` INT NOT NULL,
  `IdDistintivo` INT NOT NULL,
  PRIMARY KEY (`IdMissao`),
  INDEX `fk_MISSAO_ATIVIDADE_TAREFA_DISTINTIVO1_idx` (`IdDistintivo` ASC),
  CONSTRAINT `fk_QUESTIONARIO_MISSAO_ATIVIDADE1`
    FOREIGN KEY (`IdMissao`)
    REFERENCES `mydb`.`MISSAO_ATIVIDADE` (`IdMissao`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_MISSAO_ATIVIDADE_TAREFA_DISTINTIVO1`
    FOREIGN KEY (`IdDistintivo`)
    REFERENCES `mydb`.`DISTINTIVO` (`IdDistintivo`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`PROGRESSO_MISSAO`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`PROGRESSO_MISSAO` (
  `IdUsuario` INT NOT NULL,
  `IdMissao` INT NOT NULL,
  `Progresso` INT NOT NULL,
  PRIMARY KEY (`IdUsuario`, `IdMissao`),
  INDEX `fk_USUARIO_has_MISSAO_MISSAO1_idx` (`IdMissao` ASC),
  INDEX `fk_USUARIO_has_MISSAO_USUARIO1_idx` (`IdUsuario` ASC),
  CONSTRAINT `fk_USUARIO_has_MISSAO_USUARIO1`
    FOREIGN KEY (`IdUsuario`)
    REFERENCES `mydb`.`USUARIO` (`IdUsuario`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_USUARIO_has_MISSAO_MISSAO1`
    FOREIGN KEY (`IdMissao`)
    REFERENCES `mydb`.`MISSAO` (`IdMissao`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`PROGRESSO_MISSAO_ATIVIDADE`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`PROGRESSO_MISSAO_ATIVIDADE` (
  `IdUsuario` INT NOT NULL,
  `IdMissao` INT NOT NULL,
  `TentativasRealizadas` INT NOT NULL,
  `PontuacaoObtida` FLOAT NOT NULL,
  PRIMARY KEY (`IdUsuario`, `IdMissao`),
  CONSTRAINT `fk_PROGRESSO_ATIVIDADE_PROGRESSO1`
    FOREIGN KEY (`IdUsuario` , `IdMissao`)
    REFERENCES `mydb`.`PROGRESSO_MISSAO` (`IdUsuario` , `IdMissao`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`ALTERNATIVA`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`ALTERNATIVA` (
  `IdAlternativa` INT NOT NULL AUTO_INCREMENT,
  `Texto` VARCHAR(255) NOT NULL,
  `TipoAlternativa` VARCHAR(45) NOT NULL,
  `IdQuestao` INT NOT NULL,
  PRIMARY KEY (`IdAlternativa`),
  INDEX `fk_ALTERNATIVA_QUESTAO1_idx` (`IdQuestao` ASC),
  CONSTRAINT `fk_ALTERNATIVA_QUESTAO1`
    FOREIGN KEY (`IdQuestao`)
    REFERENCES `mydb`.`QUESTAO` (`IdQuestao`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`DISTINTIVO_ADQUIRIDO`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`DISTINTIVO_ADQUIRIDO` (
  `IdUsuario` INT NOT NULL,
  `IdDistintivo` INT NOT NULL,
  PRIMARY KEY (`IdUsuario`, `IdDistintivo`),
  INDEX `fk_DISTINTIVO_has_USUARIO_USUARIO1_idx` (`IdUsuario` ASC),
  INDEX `fk_DISTINTIVO_has_USUARIO_DISTINTIVO1_idx` (`IdDistintivo` ASC),
  CONSTRAINT `fk_DISTINTIVO_has_USUARIO_DISTINTIVO1`
    FOREIGN KEY (`IdDistintivo`)
    REFERENCES `mydb`.`DISTINTIVO` (`IdDistintivo`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_DISTINTIVO_has_USUARIO_USUARIO1`
    FOREIGN KEY (`IdUsuario`)
    REFERENCES `mydb`.`USUARIO` (`IdUsuario`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`ALTERNATIVA_MARCADA`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`ALTERNATIVA_MARCADA` (
  `IdUsuario` INT NOT NULL,
  `IdAlternativa` INT NOT NULL,
  `Correta` TINYINT NOT NULL,
  `SequenciaRespondida` INT NULL,
  `IdAlternativaAssociadaRespondida` INT NULL,
  PRIMARY KEY (`IdUsuario`, `IdAlternativa`),
  INDEX `fk_USUARIO_has_ALTERNATIVA_ALTERNATIVA1_idx` (`IdAlternativa` ASC),
  INDEX `fk_USUARIO_has_ALTERNATIVA_USUARIO1_idx` (`IdUsuario` ASC),
  CONSTRAINT `fk_USUARIO_has_ALTERNATIVA_USUARIO1`
    FOREIGN KEY (`IdUsuario`)
    REFERENCES `mydb`.`USUARIO` (`IdUsuario`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_USUARIO_has_ALTERNATIVA_ALTERNATIVA1`
    FOREIGN KEY (`IdAlternativa`)
    REFERENCES `mydb`.`ALTERNATIVA` (`IdAlternativa`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `mydb`.`ALTERNATIVA_ASSOCIACAO`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`ALTERNATIVA_ASSOCIACAO` (
  `IdAlternativa` INT NOT NULL,
  `IdAlternativaAssociada` INT NOT NULL,
  PRIMARY KEY (`IdAlternativa`, `IdAlternativaAssociada`),
  INDEX `fk_ALTERNATIVA_ASSOCIACAO_ALTERNATIVA1_idx` (`IdAlternativaAssociada` ASC),
  CONSTRAINT `fk_ALTERNATIVA_COLUNA_ALTERNATIVA1`
    FOREIGN KEY (`IdAlternativa`)
    REFERENCES `mydb`.`ALTERNATIVA` (`IdAlternativa`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ALTERNATIVA_ASSOCIACAO_ALTERNATIVA1`
    FOREIGN KEY (`IdAlternativaAssociada`)
    REFERENCES `mydb`.`ALTERNATIVA` (`IdAlternativa`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`ALTERNATIVA_MULTIPLA_ESCOLHA`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`ALTERNATIVA_MULTIPLA_ESCOLHA` (
  `IdAlternativa` INT NOT NULL,
  `Correta` TINYINT NOT NULL,
  `TipoMultiplaEscolha` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`IdAlternativa`),
  CONSTRAINT `fk_ALTERNATIVA_MULTIPLA_ESCOLHA_ALTERNATIVA1`
    FOREIGN KEY (`IdAlternativa`)
    REFERENCES `mydb`.`ALTERNATIVA` (`IdAlternativa`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`ALTERNATIVA_ORDENACAO`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`ALTERNATIVA_ORDENACAO` (
  `IdAlternativa` INT NOT NULL,
  `NumeroSequencia` INT NOT NULL,
  PRIMARY KEY (`IdAlternativa`),
  CONSTRAINT `fk_ALTERNATIVA_SEQUENCIA_ALTERNATIVA1`
    FOREIGN KEY (`IdAlternativa`)
    REFERENCES `mydb`.`ALTERNATIVA` (`IdAlternativa`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- ===========================
-- MISSAO
-- ===========================

ALTER TABLE MISSAO
DROP FOREIGN KEY fk_MISSAO_TEMATICA1;

ALTER TABLE MISSAO
ADD CONSTRAINT fk_MISSAO_TEMATICA1
FOREIGN KEY (IdTematica)
REFERENCES TEMATICA(IdTematica)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ===========================
-- MISSAO_ATIVIDADE
-- ===========================

ALTER TABLE MISSAO_ATIVIDADE
DROP FOREIGN KEY fk_MISSAO_ATIVIDADE_MISSAO1;

ALTER TABLE MISSAO_ATIVIDADE
ADD CONSTRAINT fk_MISSAO_ATIVIDADE_MISSAO1
FOREIGN KEY (IdMissao)
REFERENCES MISSAO(IdMissao)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ===========================
-- MISSAO_CONTEUDO
-- ===========================

ALTER TABLE MISSAO_CONTEUDO
DROP FOREIGN KEY fk_MISSAO_DOCUMENTO_MISSAO1;

ALTER TABLE MISSAO_CONTEUDO
ADD CONSTRAINT fk_MISSAO_DOCUMENTO_MISSAO1
FOREIGN KEY (IdMissao)
REFERENCES MISSAO(IdMissao)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ===========================
-- QUESTAO
-- ===========================

ALTER TABLE QUESTAO
DROP FOREIGN KEY fk_QUESTAO_MISSAO_ATIVIDADE1;

ALTER TABLE QUESTAO
ADD CONSTRAINT fk_QUESTAO_MISSAO_ATIVIDADE1
FOREIGN KEY (IdMissao)
REFERENCES MISSAO_ATIVIDADE(IdMissao)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ===========================
-- ALTERNATIVA
-- ===========================

ALTER TABLE ALTERNATIVA
DROP FOREIGN KEY fk_ALTERNATIVA_QUESTAO1;

ALTER TABLE ALTERNATIVA
ADD CONSTRAINT fk_ALTERNATIVA_QUESTAO1
FOREIGN KEY (IdQuestao)
REFERENCES QUESTAO(IdQuestao)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ===========================
-- MISSAO_ATIVIDADE_TAREFA
-- ===========================

ALTER TABLE MISSAO_ATIVIDADE_TAREFA
DROP FOREIGN KEY fk_QUESTIONARIO_MISSAO_ATIVIDADE1;

ALTER TABLE MISSAO_ATIVIDADE_TAREFA
ADD CONSTRAINT fk_QUESTIONARIO_MISSAO_ATIVIDADE1
FOREIGN KEY (IdMissao)
REFERENCES MISSAO_ATIVIDADE(IdMissao)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- Mantém DISTINTIVO como NO ACTION

-- ===========================
-- PROGRESSO_MISSAO
-- ===========================

ALTER TABLE PROGRESSO_MISSAO
DROP FOREIGN KEY fk_USUARIO_has_MISSAO_USUARIO1,
DROP FOREIGN KEY fk_USUARIO_has_MISSAO_MISSAO1;

ALTER TABLE PROGRESSO_MISSAO
ADD CONSTRAINT fk_USUARIO_has_MISSAO_USUARIO1
FOREIGN KEY (IdUsuario)
REFERENCES USUARIO(IdUsuario)
ON DELETE CASCADE
ON UPDATE CASCADE;

ALTER TABLE PROGRESSO_MISSAO
ADD CONSTRAINT fk_USUARIO_has_MISSAO_MISSAO1
FOREIGN KEY (IdMissao)
REFERENCES MISSAO(IdMissao)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ===========================
-- PROGRESSO_MISSAO_ATIVIDADE
-- ===========================

ALTER TABLE PROGRESSO_MISSAO_ATIVIDADE
DROP FOREIGN KEY fk_PROGRESSO_ATIVIDADE_PROGRESSO1;

ALTER TABLE PROGRESSO_MISSAO_ATIVIDADE
ADD CONSTRAINT fk_PROGRESSO_ATIVIDADE_PROGRESSO1
FOREIGN KEY (IdUsuario, IdMissao)
REFERENCES PROGRESSO_MISSAO(IdUsuario, IdMissao)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ===========================
-- DISTINTIVO_ADQUIRIDO
-- ===========================

ALTER TABLE DISTINTIVO_ADQUIRIDO
DROP FOREIGN KEY fk_DISTINTIVO_has_USUARIO_USUARIO1,
DROP FOREIGN KEY fk_DISTINTIVO_has_USUARIO_DISTINTIVO1;

ALTER TABLE DISTINTIVO_ADQUIRIDO
ADD CONSTRAINT fk_DISTINTIVO_has_USUARIO_USUARIO1
FOREIGN KEY (IdUsuario)
REFERENCES USUARIO(IdUsuario)
ON DELETE CASCADE
ON UPDATE CASCADE;

ALTER TABLE DISTINTIVO_ADQUIRIDO
ADD CONSTRAINT fk_DISTINTIVO_has_USUARIO_DISTINTIVO1
FOREIGN KEY (IdDistintivo)
REFERENCES DISTINTIVO(IdDistintivo)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ===========================
-- ALTERNATIVA_MARCADA
-- ===========================

ALTER TABLE ALTERNATIVA_MARCADA
DROP FOREIGN KEY fk_USUARIO_has_ALTERNATIVA_USUARIO1,
DROP FOREIGN KEY fk_USUARIO_has_ALTERNATIVA_ALTERNATIVA1;

ALTER TABLE ALTERNATIVA_MARCADA
ADD CONSTRAINT fk_USUARIO_has_ALTERNATIVA_USUARIO1
FOREIGN KEY (IdUsuario)
REFERENCES USUARIO(IdUsuario)
ON DELETE CASCADE
ON UPDATE CASCADE;

ALTER TABLE ALTERNATIVA_MARCADA
ADD CONSTRAINT fk_USUARIO_has_ALTERNATIVA_ALTERNATIVA1
FOREIGN KEY (IdAlternativa)
REFERENCES ALTERNATIVA(IdAlternativa)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ===========================
-- ALTERNATIVA_ASSOCIACAO
-- ===========================

ALTER TABLE ALTERNATIVA_ASSOCIACAO
DROP FOREIGN KEY fk_ALTERNATIVA_COLUNA_ALTERNATIVA1,
DROP FOREIGN KEY fk_ALTERNATIVA_ASSOCIACAO_ALTERNATIVA1;

ALTER TABLE ALTERNATIVA_ASSOCIACAO
ADD CONSTRAINT fk_ALTERNATIVA_COLUNA_ALTERNATIVA1
FOREIGN KEY (IdAlternativa)
REFERENCES ALTERNATIVA(IdAlternativa)
ON DELETE CASCADE
ON UPDATE CASCADE;

ALTER TABLE ALTERNATIVA_ASSOCIACAO
ADD CONSTRAINT fk_ALTERNATIVA_ASSOCIACAO_ALTERNATIVA1
FOREIGN KEY (IdAlternativaAssociada)
REFERENCES ALTERNATIVA(IdAlternativa)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ===========================
-- ALTERNATIVA_MULTIPLA_ESCOLHA
-- ===========================

ALTER TABLE ALTERNATIVA_MULTIPLA_ESCOLHA
DROP FOREIGN KEY fk_ALTERNATIVA_MULTIPLA_ESCOLHA_ALTERNATIVA1;

ALTER TABLE ALTERNATIVA_MULTIPLA_ESCOLHA
ADD CONSTRAINT fk_ALTERNATIVA_MULTIPLA_ESCOLHA_ALTERNATIVA1
FOREIGN KEY (IdAlternativa)
REFERENCES ALTERNATIVA(IdAlternativa)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- ===========================
-- ALTERNATIVA_ORDENACAO
-- ===========================

ALTER TABLE ALTERNATIVA_ORDENACAO
DROP FOREIGN KEY fk_ALTERNATIVA_SEQUENCIA_ALTERNATIVA1;

ALTER TABLE ALTERNATIVA_ORDENACAO
ADD CONSTRAINT fk_ALTERNATIVA_SEQUENCIA_ALTERNATIVA1
FOREIGN KEY (IdAlternativa)
REFERENCES ALTERNATIVA(IdAlternativa)
ON DELETE CASCADE
ON UPDATE CASCADE;