-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: mydb
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `missao`
--

DROP TABLE IF EXISTS `missao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `missao` (
  `IdMissao` int(11) NOT NULL AUTO_INCREMENT,
  `Titulo` varchar(255) NOT NULL,
  `Pontuacao` float NOT NULL,
  `TipoMissao` varchar(45) NOT NULL,
  `IdTematica` int(11) NOT NULL,
  PRIMARY KEY (`IdMissao`),
  UNIQUE KEY `Titulo_UNIQUE` (`Titulo`),
  KEY `fk_MISSAO_TEMATICA1_idx` (`IdTematica`),
  CONSTRAINT `fk_MISSAO_TEMATICA1` FOREIGN KEY (`IdTematica`) REFERENCES `tematica` (`IdTematica`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `missao`
--

LOCK TABLES `missao` WRITE;
/*!40000 ALTER TABLE `missao` DISABLE KEYS */;
INSERT INTO `missao` VALUES (1,'Lei de inovação',50,'conteudo',2),(2,'Decreto regulamentador',50,'conteudo',2),(11,'Política Nacional de Inovação',50,'conteudo',2),(12,'Estratégia Nacional de Inovação',50,'conteudo',2),(13,'Lei de Propriedade Industrial',50,'conteudo',2),(14,'Lei da proteção de Programa de Computador',50,'conteudo',2),(15,'Lei de Direitos Autorais',50,'conteudo',2),(16,'Lei de cultivares',50,'conteudo',2),(17,'Marco legal das startups',50,'conteudo',2),(18,'Contratos de Transferência de Tecnologia',50,'conteudo',4),(19,'Pareceres sobre instrumentos jurídicos',50,'conteudo',4),(20,'Guias de Orientações do FORTEC',50,'conteudo',4),(21,'Notas Técnicas do FORTEC',50,'conteudo',4),(22,'Ciclo de Estudos sobre o Marco Legal da Ciência',50,'conteudo',4),(23,'Ciclo de Estudos sobre o Marco Legal da Ciência 02',50,'conteudo',4),(24,'Contratos de Tecnologia e de Franquia - INPI',50,'conteudo',4),(25,'Guia para Atividades de Valoração de Tecnologias',50,'conteudo',4),(26,'Livro PROFNIT - Transferência de Tecnologia',50,'conteudo',4),(27,'Guia de Boas Práticas Para a Interação ICT-empresa',50,'conteudo',4),(28,'Manual de Parcerias',50,'conteudo',4),(29,'Inovação e Propriedade Intelectual',50,'conteudo',3),(30,'Materiais de Consulta e Apoio - INPI',50,'conteudo',3),(31,'Playlist PI na prática - Marcas e Patentes - INPI',50,'conteudo',3),(32,'Agenda de cursos INPI',50,'conteudo',3),(33,'(OMPI/WIPO) - Cursos de ensino à distância',50,'conteudo',3),(34,'Livro PROFNIT - Série Conceitos e Aplicações de Propriedade Intelectual',50,'conteudo',3),(35,'Livro PROFNIT - Série Prospecção Tecnológica',50,'conteudo',3),(36,'Minicursos em Propriedade Intelectual - INPI/IF Sudeste MG',50,'conteudo',3),(37,'Minha tecnologia pode ser patenteada? - INPI/IF Goiano',50,'conteudo',3),(38,'Manual de Parcerias - Mecanismos e Instrumentos para a Dinamização de Habitats e Ecossistemas de Empreendedorismo',50,'conteudo',1),(39,'Definições de Mecanismos',50,'conteudo',1),(40,'E-books Anprotec',50,'conteudo',1),(41,'Ambientes Promotores de Inovação - Palestra Online da Escola da AGU',50,'conteudo',1),(42,'Ambientes que geram inovação - Sebrae',48,'conteudo',1),(43,'Conheça as diferenças entre ambientes de inovação - Sebrae',50,'conteudo',1),(44,'Estudos sobre Ambientes de Inovação - Anprotec',50,'conteudo',1),(45,'Transformando ideias em inovação - Programa Centelha',50,'conteudo',1);
/*!40000 ALTER TABLE `missao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `missao_conteudo`
--

DROP TABLE IF EXISTS `missao_conteudo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `missao_conteudo` (
  `IdMissao` int(11) NOT NULL,
  `URL` varchar(2048) NOT NULL,
  `Resumo` mediumtext NOT NULL,
  `TipoMaterial` varchar(45) NOT NULL,
  PRIMARY KEY (`IdMissao`),
  CONSTRAINT `fk_MISSAO_DOCUMENTO_MISSAO1` FOREIGN KEY (`IdMissao`) REFERENCES `missao` (`IdMissao`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `missao_conteudo`
--

LOCK TABLES `missao_conteudo` WRITE;
/*!40000 ALTER TABLE `missao_conteudo` DISABLE KEYS */;
INSERT INTO `missao_conteudo` VALUES (1,'http://www.planalto.gov.br/ccivil_03/_ato2004-2006/2004/lei/l10.973.htm','LEI Nº 10.973, DE 2 DE DEZEMBRO DE 2004, dispõe sobre incentivos à inovação e à pesquisa científica e tecnológica no ambiente produtivo e dá outras providências.','texto'),(2,'http://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/decreto/d9283.htm','Decreto regulamentador do Marco Legal de Ciência, Tecnologia e Inovação:\nDECRETO Nº 9.283, DE 7 DE FEVEREIRO DE 2018, Estabelece medidas de incentivo à inovação e à pesquisa científica e tecnológica no ambiente produtivo, com vistas à capacitação tecnológica, ao alcance da autonomia tecnológica e ao desenvolvimento do sistema produtivo nacional e regional.','texto'),(11,'https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2020/decreto/D10534.htm','DECRETO Nº 10.534, DE 28 DE OUTUBRO DE 2020, institui a Política Nacional de Inovação e dispõe sobre a sua governança','texto'),(12,'https://in.gov.br/en/web/dou/-/resolucao-ci-n-1-de-23-de-julho-de-2021-334125807','RESOLUÇÃO CI Nº 1, DE 23 DE JULHO DE 2021, aprova a Estratégia Nacional de Inovação e os Planos de Ação para os Eixos de Fomento, Base Tecnológica, Cultura de Inovação, Mercado para Produtos e Serviços Inovadores e Sistemas Educacionais','texto'),(13,'https://www.planalto.gov.br/ccivil_03/leis/l9279.htm','LEI Nº 9.279, DE 14 DE MAIO DE 1996, Regula direitos e obrigações relativos à propriedade industrial','texto'),(14,'https://www.planalto.gov.br/ccivil_03/leis/l9609.htm','LEI Nº 9.609 , DE 19 DE FEVEREIRO DE 1998, Dispõe sobre a proteção da propriedade intelectual de programa de computador, sua comercialização no País, e dá outras providências','texto'),(15,'https://www.planalto.gov.br/ccivil_03/leis/l9610.htm','LEI Nº 9.610, DE 19 DE FEVEREIRO DE 1998, Altera, atualiza e consolida a legislação sobre direitos autorais e dá outras providências','texto'),(16,'https://www.planalto.gov.br/ccivil_03/Leis/L9456.htm','LEI Nº 9.456, DE 25 DE ABRIL DE 1997, Institui a Lei de Proteção de Cultivares e dá outras providências','texto'),(17,'https://www.planalto.gov.br/ccivil_03/leis/LCP/Lcp182.htm','LEI COMPLEMENTAR Nº 182, DE 1º DE JUNHO DE 2021, Institui o marco legal das startups e do empreendedorismo inovador; e altera a Lei nº 6.404, de 15 de dezembro de 1976, e a Lei Complementar nº 123, de 14 de dezembro de 2006','texto'),(18,'https://profnit.org.br/wp-content/uploads/2020/11/IFBA-FABRICIO-DOS-SANTOS-SIMOES-PRODUTO.pdf','Contratos de Transferência de Tecnologia - Manual Prático','texto'),(19,'https://www.gov.br/agu/pt-br/composicao/procuradoria-geral-federal-1/subprocuradoria-federal-de-consultoria-juridica/camara-permanente-da-ciencia-tecnologia-e-inovacao-1','Pareceres sobre instrumentos jurídicos do Marco Legal de Ciência, Tecnologia e Inovação','texto'),(20,'https://fortec.org.br/2023/07/27/novos-guias-de-orientacao-para-contratos-de-tt-prestacao-de-servicos-tecnologicos-especializados-e-acordos-de-parceria-para-pesquisa-desenvolvimento-e-inovacao/','Contratos de Transferência de Tecnologia, prestação de serviços tecnologicos especializados e acordo de parceria para pesquisa, desenvolvimento e inovação - FORTEC','texto'),(21,'https://fortec.org.br/notas-tecnicas/','Sobre prestação de serviço e acordos de parcerias pesquisa, desenvolvimento e inovação','texto'),(22,'https://www.youtube.com/watch?v=e3ixc6fGw5E&list=PLmq2IakUljtuCu3lgYxTxsEjHG7zS2Ty8&index=72','Ciclo de Estudos sobre o Marco Legal da Ciência, Tecnologia e Inovação: Panorama atual e desafios - Transferência de tecnologia -AGU','video'),(23,'https://www.youtube.com/live/lFPLsUjX-AQ?si=iXLlzD-AU3_0ymOk','Ciclo de Estudos sobre o Marco Legal da Ciência, Tecnologia e Inovação: Panorama atual e desafios - Contrato de Prestação de Serviços Técnicos Especializados em Pesquisa e Desenvolvimento -AGU','video'),(24,'https://www.gov.br/inpi/pt-br/servicos/contratos-de-tecnologia-e-de-franquia','Contratos de Tecnologia e de Franquia - INPI','texto'),(25,'https://www.ifsudestemg.edu.br/documentos-institucionais/unidades/reitoria/pro-reitorias/pesquisa-posgraduacao-e-inovacao/outros-documentos/nittec/valoracao-tecnologias.pdf','Guia para Atividades de Valoração de Tecnologias','texto'),(26,'https://profnit.org.br/wp-content/uploads/2019/10/PROFNIT-Serie-Transferencia-de-Tecnologia-Volume-I-WEB-2.pdf','Livro PROFNIT - Série Conceitos e Aplicações de Transferência de Tecnologia','texto'),(27,'https://profnit.org.br/wp-content/uploads/2019/10/PROFNIT-Serie-Transferencia-de-Tecnologia-Volume-I-WEB-2.pdf','Guia de Boas Práticas Para a Interação ICT-empresa','texto'),(28,'http://portal.mec.gov.br/index.php?option=com_docman&view=download&alias=39661-manual-parcerias-dinamizacao-habitats-eco-inovacao-rfepct-pdf&Itemid=30192','Manual de Parcerias - Mecanismos e Instrumentos para a Dinamização de Habitats e Ecossistemas de Empreendedorismo e Inovação na RFEPCT','texto'),(29,'https://profnit.org.br/wp-content/uploads/2020/11/IFBA-FABRICIO-DOS-SANT','Inovação e Propriedade Intelectual: guia para o docente','texto'),(30,'https://www.gov.br/inpi/pt-br','Materiais de Consulta e Apoio - INPI','texto'),(31,'https://youtu.be/RwRo30bz9f8?si=49hShf3V9driygx9','Playlist PI na prática - Marcas e Patentes - INPI','video'),(32,'https://www.gov.br/inpi/pt-br/servicos/a-academia/cursos-de-extensao/agenda-de-cursos','Agenda de cursos INPI','texto'),(33,'https://welc.wipo.int/acc/index.jsf?page=courseCatalog.xhtml&lang=pt','Academia da Organização Mundial da Propriedade Intelectual (OMPI/WIPO) - Cursos de ensino à distância','texto'),(34,'https://profnit.org.br/wp-content/uploads/2021/08/PROFNIT-Serie-Conceitos-e-Aplica%E2%80%A1aes-de-Propriedade-Intelectual-Volume-I.pdf','Livro PROFNIT - Série Conceitos e Aplicações de Propriedade Intelectual','texto'),(35,'https://profnit.org.br/wp-content/uploads/2018/08/PROFNIT-Serie-Prospeccao-Tecnologica-Volume-1-1.pdf','Livro PROFNIT - Série Prospecção Tecnológica','texto'),(36,'https://youtu.be/umr753t5JWg','Minicursos em Propriedade Intelectual - INPI/IF Sudeste MG','video'),(37,'https://www.youtube.com/watch?v=hH3k2kMlZS0','Minha tecnologia pode ser patenteada? - INPI/IF Goiano','video'),(38,'https://profnit.org.br/wp-content/uploads/2020/11/IFBA-FABRICIO-DOS-SANT','Manual de Parcerias - Mecanismos e Instrumentos para a Dinamização de Habitats e Ecossistemas de Empreendedorismo e Inovação naRFEPCT','texto'),(39,'https://profnit.org.br/wp-content/uploads/2020/11/IFBA-FABRICIO-DOS-SANT','Definições de Mecanismos de geração de empreendimentos e ecossistemas de inovação','texto'),(40,'https://anprotec.org.br/site/publicacoes-anprotec/ebooks/','E-books Anprotec','texto'),(41,'https://www.youtube.com/live/bmJa_YzZtu8?si=u70lqvbQjWWeirfw','Ambientes Promotores de Inovação - Palestra Online da Escola da AGU','texto'),(42,'https://app2.pr.sebrae.com.br/comunidade/artigo/ambientes-que-geram-inovacao','Ambientes que geram inovação - Sebrae','texto'),(43,'https://sebrae.com.br/sites/PortalSebrae/artigos/conheca-as-diferencas-entre-ambientes-de-inovacao,0176524504816810VgnVCM1000001b00320aRCRD','Conheça as diferenças entre ambientes de inovação - Sebrae','texto'),(44,'https://anprotec.org.br/site/publicacoes-anprotec/estudos-e-pesquisas/','Estudos sobre Ambientes de Inovação - Anprotec','texto'),(45,'https://youtu.be/ds3D9hAizfE?si=vJPAD-JapXrhDEpV','Transformando ideias em inovação - Programa Centelha','texto');
/*!40000 ALTER TABLE `missao_conteudo` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-12 14:09:22
