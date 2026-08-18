USE `mydb`;

-- ============================================================================
-- 1. TABELAS INDEPENDENTES (BASE)
-- ============================================================================

-- População da tabela OCUPACAO
INSERT INTO `OCUPACAO` (`IdOcupacao`, `Titulo`) VALUES
(1, 'Estudante'),
(2, 'Professor'),
(3, 'Desenvolvedor Júnior');


-- População da tabela TEMATICA
INSERT INTO `TEMATICA` (`IdTematica`, `Titulo`) VALUES
(1, 'Desenvolvimento Web Front-End'),
(2, 'Banco de Dados Relacionais');


-- População da tabela DISTINTIVO
INSERT INTO `DISTINTIVO`
(`IdDistintivo`, `Titulo`, `Pontuacao`, `NomeArquivo`)
VALUES
(1, 'Explorador do HTML', 50.0, 'badge_html_explorer.png'),
(2, 'Mestre do CSS', 75.0, 'badge_css_master.png'),
(3, 'Arquiteto SQL', 100.0, 'badge_sql_architect.png');


-- ============================================================================
-- 2. TABELAS QUE DEPENDEM DOS CADASTROS BASE
-- ============================================================================

-- Hash BCrypt com 60 caracteres
-- FotoPerfil adicionada pois é NOT NULL no banco.

INSERT INTO `USUARIO`
(
    `IdUsuario`,
    `Nome`,
    `NomeAventureiro`,
    `CorreioEletronico`,
    `DataNascimento`,
    `PossuiConhecimento`,
    `PrimeiroAcesso`,
    `FotoPerfil`,
    `Admin`,
    `HashSenha`,
    `IdOcupacao`
)
VALUES
(
    1,
    'João Silva',
    'joaogamer',
    'joao@email.com',
    '2000-05-12',
    1,
    0,
    'default.png',
    0,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCqkqP7Y5XQ3xZ6K5Q2',
    1
),
(
    2,
    'Maria Santos',
    'mariah_dev',
    'maria@email.com',
    '1998-08-24',
    0,
    0,
    'default.png',
    0,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCqkqP7Y5XQ3xZ6K5Q2',
    3
),
(
    3,
    'Carlos Oliveira',
    'carlos_prof',
    'carlos@email.com',
    '1985-03-15',
    1,
    0,
    'default.png',
    0,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCqkqP7Y5XQ3xZ6K5Q2',
    2
);


-- População da tabela MISSAO

INSERT INTO `MISSAO`
(
    `IdMissao`,
    `Titulo`,
    `Pontuacao`,
    `TipoMissao`,
    `IdTematica`
)
VALUES
(
    1,
    'Leitura Obrigatória: Introdução à Web',
    20.0,
    'conteudo',
    1
),
(
    2,
    'Desafio Prático: Questionário Fundamentos',
    100.0,
    'atividade',
    1
),
(
    3,
    'Modelagem de Dados: Entidades e Relacionamentos',
    120.0,
    'atividade',
    2
);


-- ============================================================================
-- 3. ESPECIALIZAÇÕES DAS MISSÕES
-- ============================================================================

-- MISSÃO DE CONTEÚDO

INSERT INTO `MISSAO_CONTEUDO`
(
    `IdMissao`,
    `URL`,
    `Resumo`,
    `TipoMaterial`
)
VALUES
(
    1,
    'https://exemplo.com/docs/intro-web',
    'Neste material você aprenderá sobre o surgimento da WWW, a diferença entre cliente e servidor e a introdução ao protocolo HTTP.',
    'texto'
);

-- ============================================================================
-- MISSÕES DE ATIVIDADE
-- ============================================================================

INSERT INTO `MISSAO_ATIVIDADE`
(`IdMissao`, `TipoAtividade`)
VALUES
(2, 'quiz'),
(3, 'tarefa');


-- ============================================================================
-- TAREFAS COM DISTINTIVO
-- ============================================================================

INSERT INTO `MISSAO_ATIVIDADE_TAREFA`
(`IdMissao`, `IdDistintivo`)
VALUES
(3, 3);


-- ============================================================================
-- 4. QUESTÕES
-- ============================================================================

INSERT INTO `QUESTAO`
(
    `IdQuestao`,
    `Enunciado`,
    `MensagemCorrecao`,
    `IdMissao`
)
VALUES
(
    1,
    'Qual tag é utilizada nativamente para criar um link ou hiperlink em HTML?',
    'A tag <a> (âncora), utilizando o atributo href, é utilizada para criar hiperlinks em HTML.',
    2
),
(
    2,
    'Ordene as tags estruturais do HTML abaixo seguindo a ordem correta de abertura dentro de um documento HTML.',
    'A estrutura básica do documento HTML utiliza a tag <html> como elemento principal, contendo as estruturas <head> e <body>.',
    2
),
(
    3,
    'Associe as siglas de Front-End com as suas respectivas definições corretas.',
    'HTML realiza a marcação estrutural, CSS cuida da estilização visual e JavaScript adiciona comportamento e interatividade.',
    2
);


-- ============================================================================
-- 5. ALTERNATIVAS
-- ============================================================================

INSERT INTO `ALTERNATIVA`
(
    `IdAlternativa`,
    `Texto`,
    `TipoAlternativa`,
    `IdQuestao`
)
VALUES

-- --------------------------------------------------------------------------
-- Questão 1 - Múltipla Escolha
-- --------------------------------------------------------------------------

(1, '<a>', 'multipla_escolha', 1),
(2, '<link>', 'multipla_escolha', 1),
(3, '<href>', 'multipla_escolha', 1),


-- --------------------------------------------------------------------------
-- Questão 2 - Ordenação
-- --------------------------------------------------------------------------

(4, '<head>', 'ordenacao', 2),
(5, '<body>', 'ordenacao', 2),
(6, '<html>', 'ordenacao', 2),


-- --------------------------------------------------------------------------
-- Questão 3 - Associação
-- Lado esquerdo
-- --------------------------------------------------------------------------

(7, 'HTML', 'associacao', 3),
(8, 'CSS', 'associacao', 3),
(9, 'JavaScript', 'associacao', 3),


-- Lado direito
-- --------------------------------------------------------------------------

(10, 'Estruturação e Marcação de Conteúdo', 'associacao', 3),
(11, 'Estilização e Layout Visual', 'associacao', 3),
(12, 'Comportamento e Dinamicidade', 'associacao', 3);


-- ============================================================================
-- 6. CONFIGURAÇÃO DAS ALTERNATIVAS ESPECIALIZADAS
-- ============================================================================

-- --------------------------------------------------------------------------
-- Múltipla Escolha
-- --------------------------------------------------------------------------

INSERT INTO `ALTERNATIVA_MULTIPLA_ESCOLHA`
(
    `IdAlternativa`,
    `Correta`,
    `TipoMultiplaEscolha`
)
VALUES
(1, 1, 'multipla_escolha'),
(2, 0, 'multipla_escolha'),
(3, 0, 'multipla_escolha');


-- --------------------------------------------------------------------------
-- Ordenação
-- --------------------------------------------------------------------------

INSERT INTO `ALTERNATIVA_ORDENACAO`
(
    `IdAlternativa`,
    `NumeroSequencia`
)
VALUES
(6, 1), -- <html>
(4, 2), -- <head>
(5, 3); -- <body>


-- --------------------------------------------------------------------------
-- Associação
-- --------------------------------------------------------------------------

INSERT INTO `ALTERNATIVA_ASSOCIACAO`
(
    `IdAlternativa`,
    `IdAlternativaAssociada`
)
VALUES
(7, 10), -- HTML -> Estruturação
(8, 11), -- CSS -> Estilização
(9, 12); -- JavaScript -> Comportamento


-- ============================================================================
-- 7. PROGRESSO DAS MISSÕES
-- ============================================================================

INSERT INTO `PROGRESSO_MISSAO`
(
    `IdUsuario`,
    `IdMissao`,
    `Progresso`
)
VALUES
(1, 1, 100),
(1, 2, 0),
(2, 1, 100),
(2, 2, 0);


-- ============================================================================
-- 8. PROGRESSO DAS ATIVIDADES
-- ============================================================================

INSERT INTO `PROGRESSO_MISSAO_ATIVIDADE`
(
    `IdUsuario`,
    `IdMissao`,
    `TentativasRealizadas`,
    `PontuacaoObtida`
)
VALUES
(1, 2, 1, 66.6);


-- ============================================================================
-- 9. DISTINTIVOS ADQUIRIDOS
-- ============================================================================

INSERT INTO `DISTINTIVO_ADQUIRIDO`
(
    `IdUsuario`,
    `IdDistintivo`
)
VALUES
(1, 1);


-- ============================================================================
-- 10. ALTERNATIVAS MARCADAS
-- ============================================================================

INSERT INTO `ALTERNATIVA_MARCADA`
(
    `IdUsuario`,
    `IdAlternativa`,
    `Correta`,
    `SequenciaRespondida`,
    `IdAlternativaAssociadaRespondida`
)
VALUES

-- --------------------------------------------------------------------------
-- João acertou a questão de Múltipla Escolha
-- <a>
-- --------------------------------------------------------------------------

(1, 1, 1, NULL, NULL),


-- --------------------------------------------------------------------------
-- João respondeu a questão de Ordenação incorretamente
-- Respondeu <head> como 3ª posição.
-- --------------------------------------------------------------------------

(1, 4, 0, 3, NULL),


-- --------------------------------------------------------------------------
-- João acertou uma associação
-- HTML -> Estruturação
-- --------------------------------------------------------------------------

(1, 7, 1, NULL, 10),


-- --------------------------------------------------------------------------
-- Maria errou uma associação
-- CSS -> Comportamento
-- --------------------------------------------------------------------------

(2, 8, 0, NULL, 12);