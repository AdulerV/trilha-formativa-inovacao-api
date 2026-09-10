USE `mydb`;

-- ============================================================================
-- 1. TABELAS INDEPENDENTES (BASE)
-- ============================================================================

-- População da tabela OCUPACAO
INSERT INTO `OCUPACAO` (`IdOcupacao`, `Titulo`) VALUES
(1, 'Estudante de Nível Técnico'),
(2, 'Estudante de Graduação'),
(3, 'Estudante de PROEJA'),
(4, 'Servidor Público'),
(5, 'Funcionário do NITTEC'),
(6, 'Funcionário Terceirizado'),
(7, 'Professor'),
(8, 'Comunidade Externa'),
(9, 'Outro');

-- População da tabela TEMATICA
INSERT INTO `TEMATICA` (`IdTematica`, `Titulo`) VALUES
(1, 'Ambientes de Inovação'),
(2, 'Legislação'),
(3, 'Propriedade Intelectual'),
(4, 'Transferência Tecnológica'),
(5, 'Tarefa Final');


-- População da tabela DISTINTIVO
INSERT INTO `DISTINTIVO`
(`IdDistintivo`, `Titulo`, `Pontuacao`, `NomeArquivo`)
VALUES
(1, 'Conector de Soluções', 50.0, 'DistintivoConectorDeSolucoes.png'),
(2, 'Doutor Legal', 50.0, 'DistintivoDoutorLegal.png'),
(3, 'Impulsionador de Inovações', 50.0, 'DistintivoImpulsionadorDePossibilidades.png'),
(4, 'Mestre da Criatividade', 50.0, 'DistintivoMestreDaCriatividade.png'),
(5, 'Troféu Final', 100.0, 'TrofeuFinal.png');

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
);