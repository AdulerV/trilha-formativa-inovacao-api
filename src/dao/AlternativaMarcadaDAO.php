<?php

declare(strict_types=1);

class AlternativaMarcadaDAO
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * Grava a marcação, substituindo a anterior da mesma alternativa.
     *
     * A chave primária de ALTERNATIVA_MARCADA é (IdUsuario,
     * IdAlternativa): ao responder o quiz de novo, o INSERT puro
     * violava a chave e o serviço devolvia "Esta alternativa já foi
     * marcada por este usuário!". O resultado era que apenas a
     * primeira tentativa ficava salva.
     *
     * ON DUPLICATE KEY UPDATE resolve em uma única instrução, sem a
     * janela de corrida de um "verifica e depois grava".
     */
    public function salvar(AlternativaMarcada $alternativaMarcada): void
    {
        try {
            $sql = "INSERT INTO alternativa_marcada (
                IdUsuario,
                IdAlternativa,
                Correta,
                SequenciaRespondida,
                IdAlternativaAssociadaRespondida
            ) VALUES (
                :idUsuario,
                :idAlternativa,
                :correta,
                :sequenciaRespondida,
                :idAlternativaAssociadaRespondida
            )
            ON DUPLICATE KEY UPDATE
                Correta = VALUES(Correta),
                SequenciaRespondida = VALUES(SequenciaRespondida),
                IdAlternativaAssociadaRespondida = VALUES(IdAlternativaAssociadaRespondida)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $alternativaMarcada->getUsuario()->getIdUsuario());
            $stmt->bindValue(":idAlternativa", $alternativaMarcada->getAlternativa()->getIdAlternativa());
            $stmt->bindValue(":correta", $alternativaMarcada->isCorreta(), PDO::PARAM_BOOL);
            $stmt->bindValue(":sequenciaRespondida", $alternativaMarcada->getSequenciaRespondida(), $alternativaMarcada->getSequenciaRespondida() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(":idAlternativaAssociadaRespondida", $alternativaMarcada->getIdAlternativaAssociadaRespondida(), $alternativaMarcada->getIdAlternativaAssociadaRespondida() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao salvar alternativa marcada!");
        }
    }

    public function buscarPorId(int $idUsuario, int $idAlternativa): AlternativaMarcada
    {
        try {
            $sql = "SELECT
                u.IdUsuario, u.Nome, u.NomeAventureiro, u.CorreioEletronico, u.DataNascimento, u.PossuiConhecimento, u.PrimeiroAcesso, u.Admin, u.FotoPerfil,
                o.IdOcupacao, o.Titulo AS TituloOcupacao,
                a.IdAlternativa, a.Texto AS TextoAlternativa, a.TipoAlternativa, a.IdQuestao,
                ame.TipoMultiplaEscolha,
                ao.NumeroSequencia AS NumeroSequenciaGabarito,
                alt_vinculada.IdAlternativa AS IdAlternativaRelacionada, alt_vinculada.Texto AS TextoAlternativaRelacionada, alt_vinculada.TipoAlternativa AS TipoAlternativaRelacionada,
                am.Correta, am.SequenciaRespondida, am.IdAlternativaAssociadaRespondida
                FROM alternativa_marcada am
                INNER JOIN usuario u ON am.IdUsuario = u.IdUsuario
                INNER JOIN ocupacao o ON u.IdOcupacao = o.IdOcupacao
                INNER JOIN alternativa a ON am.IdAlternativa = a.IdAlternativa
                LEFT JOIN alternativa_multipla_escolha ame ON a.IdAlternativa = ame.IdAlternativa
                LEFT JOIN alternativa_ordenacao ao ON a.IdAlternativa = ao.IdAlternativa
                LEFT JOIN alternativa_associacao aa ON a.IdAlternativa = aa.IdAlternativa
                LEFT JOIN alternativa alt_vinculada ON aa.IdAlternativaAssociada = alt_vinculada.IdAlternativa
                WHERE am.IdUsuario = :idUsuario AND am.IdAlternativa = :idAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->bindValue(":idAlternativa", $idAlternativa);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                throw new Exception("Nenhum registro encontrado.");
            }

            return $this->mapearAlternativaMarcada($registro);
        } catch (PDOException) {
            throw new Exception("Erro ao buscar alternativa marcada!");
        }
    }

    public function listar(): array
    {
        try {
            $sql = "SELECT
                u.IdUsuario, u.Nome, u.NomeAventureiro, u.CorreioEletronico, u.DataNascimento, u.PossuiConhecimento, u.PrimeiroAcesso, u.Admin, u.FotoPerfil,
                o.IdOcupacao, o.Titulo AS TituloOcupacao,
                a.IdAlternativa, a.Texto AS TextoAlternativa, a.TipoAlternativa, a.IdQuestao,
                ame.TipoMultiplaEscolha,
                ao.NumeroSequencia AS NumeroSequenciaGabarito,
                alt_vinculada.IdAlternativa AS IdAlternativaRelacionada, alt_vinculada.Texto AS TextoAlternativaRelacionada, alt_vinculada.TipoAlternativa AS TipoAlternativaRelacionada,
                am.Correta, am.SequenciaRespondida, am.IdAlternativaAssociadaRespondida
                FROM alternativa_marcada am
                INNER JOIN usuario u ON am.IdUsuario = u.IdUsuario
                INNER JOIN ocupacao o ON u.IdOcupacao = o.IdOcupacao
                INNER JOIN alternativa a ON am.IdAlternativa = a.IdAlternativa
                LEFT JOIN alternativa_multipla_escolha ame ON a.IdAlternativa = ame.IdAlternativa
                LEFT JOIN alternativa_ordenacao ao ON a.IdAlternativa = ao.IdAlternativa
                LEFT JOIN alternativa_associacao aa ON a.IdAlternativa = aa.IdAlternativa
                LEFT JOIN alternativa alt_vinculada ON aa.IdAlternativaAssociada = alt_vinculada.IdAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $lista = [];

            foreach ($registros as $registro) {
                $lista[] = $this->mapearAlternativaMarcada($registro);
            }

            return $lista;
        } catch (PDOException) {
            throw new Exception("Erro ao listar alternativas marcadas!");
        }
    }

    public function listarPorUsuario(int $idUsuario): array
    {
        try {
            $sql = "SELECT
                u.IdUsuario, u.Nome, u.NomeAventureiro, u.CorreioEletronico, u.DataNascimento, u.PossuiConhecimento, u.PrimeiroAcesso, u.Admin, u.FotoPerfil,
                o.IdOcupacao, o.Titulo AS TituloOcupacao,
                a.IdAlternativa, a.Texto AS TextoAlternativa, a.TipoAlternativa, a.IdQuestao,
                ame.TipoMultiplaEscolha,
                ao.NumeroSequencia AS NumeroSequenciaGabarito,
                alt_vinculada.IdAlternativa AS IdAlternativaRelacionada, alt_vinculada.Texto AS TextoAlternativaRelacionada, alt_vinculada.TipoAlternativa AS TipoAlternativaRelacionada,
                am.Correta, am.SequenciaRespondida, am.IdAlternativaAssociadaRespondida
                FROM alternativa_marcada am
                INNER JOIN usuario u ON am.IdUsuario = u.IdUsuario
                INNER JOIN ocupacao o ON u.IdOcupacao = o.IdOcupacao
                INNER JOIN alternativa a ON am.IdAlternativa = a.IdAlternativa
                LEFT JOIN alternativa_multipla_escolha ame ON a.IdAlternativa = ame.IdAlternativa
                LEFT JOIN alternativa_ordenacao ao ON a.IdAlternativa = ao.IdAlternativa
                LEFT JOIN alternativa_associacao aa ON a.IdAlternativa = aa.IdAlternativa
                LEFT JOIN alternativa alt_vinculada ON aa.IdAlternativaAssociada = alt_vinculada.IdAlternativa
                WHERE am.IdUsuario = :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $lista = [];

            foreach ($registros as $registro) {
                $lista[] = $this->mapearAlternativaMarcada($registro);
            }
            return $lista;
        } catch (PDOException) {
            throw new Exception("Erro ao listar alternativas marcadas pelo usuário de ID {$idUsuario}");
        }
    }

    public function atualizar(AlternativaMarcada $alternativaMarcada): void
    {
        try {
            $sql = "UPDATE alternativa_marcada 
                    SET Correta = :correta, 
                        SequenciaRespondida = :sequenciaRespondida, 
                        IdAlternativaAssociadaRespondida = :idAlternativaAssociadaRespondida
                    WHERE IdUsuario = :idUsuario 
                    AND IdAlternativa = :idAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":correta", $alternativaMarcada->isCorreta(), PDO::PARAM_BOOL);
            $stmt->bindValue(":sequenciaRespondida", $alternativaMarcada->getSequenciaRespondida(), $alternativaMarcada->getSequenciaRespondida() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(":idAlternativaAssociadaRespondida", $alternativaMarcada->getIdAlternativaAssociadaRespondida(), $alternativaMarcada->getIdAlternativaAssociadaRespondida() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(":idUsuario", $alternativaMarcada->getUsuario()->getIdUsuario(), PDO::PARAM_INT);
            $stmt->bindValue(":idAlternativa", $alternativaMarcada->getAlternativa()->getIdAlternativa(), PDO::PARAM_INT);

            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar alternativa marcada!");
        }
    }

    public function deletar(int $idUsuario, int $idAlternativa): void
    {
        try {
            $sql = "DELETE FROM alternativa_marcada WHERE IdUsuario = :idUsuario AND IdAlternativa = :idAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->bindValue(":idAlternativa", $idAlternativa);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao deletar alternativa marcada!");
        }
    }

    public function verificarSeAlternativaMarcadaExiste(int $idUsuario, int $idAlternativa): bool
    {
        $sql = "SELECT COUNT(*) FROM alternativa_marcada WHERE IdUsuario = :idUsuario AND IdAlternativa = :idAlternativa";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idUsuario", $idUsuario);
        $stmt->bindValue(":idAlternativa", $idAlternativa);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function mapearAlternativaMarcada(array $registro): AlternativaMarcada
    {
        $ocupacao = new Ocupacao(
            (int) $registro["IdOcupacao"],
            $registro["TituloOcupacao"]
        );

        $usuario = Usuario::rehidratar(
            (int) $registro["IdUsuario"],
            $registro["Nome"],
            $registro["NomeAventureiro"],
            $registro["CorreioEletronico"],
            $registro["DataNascimento"],
            (bool) $registro["PossuiConhecimento"],
            (bool) $registro["PrimeiroAcesso"],
            (bool) $registro["Admin"],
            $ocupacao,
            $registro["FotoPerfil"] ?? null
        );

        $tipoAlternativa = $registro["TipoAlternativa"];
        $idAlternativa = (int) $registro["IdAlternativa"];
        $textoAlternativa = $registro["TextoAlternativa"];

        if ($tipoAlternativa === "AlternativaMultiplaEscolha" || $tipoAlternativa === "multipla_escolha") {
            $subtipo = $registro["TipoMultiplaEscolha"] ?? AlternativaMultiplaEscolha::SUBTIPO_MULTIPLA_ESCOLHA;

            $alternativa = new AlternativaMultiplaEscolha(
                $idAlternativa,
                $textoAlternativa,
                (bool) $registro["Correta"],
                $subtipo
            );
        } elseif ($tipoAlternativa === "AlternativaOrdenacao" || $tipoAlternativa === "ordenacao") {
            $sequenciaGabarito = isset($registro["NumeroSequenciaGabarito"]) && $registro["NumeroSequenciaGabarito"] !== null 
                ? (int) $registro["NumeroSequenciaGabarito"] 
                : 0;

            $alternativa = new AlternativaOrdenacao($idAlternativa, $textoAlternativa, $sequenciaGabarito);
        } elseif ($tipoAlternativa === "AlternativaAssociacao" || $tipoAlternativa === "associacao") {
            $alternativaAssociada = null;
            if (isset($registro["IdAlternativaRelacionada"]) && $registro["IdAlternativaRelacionada"] !== null) {
                $alternativaAssociada = new AlternativaAssociacao(
                    (int) $registro["IdAlternativaRelacionada"],
                    $registro["TextoAlternativaRelacionada"]
                );
            }

            $alternativa = new AlternativaAssociacao($idAlternativa, $textoAlternativa, $alternativaAssociada);
        } else {
            $alternativa = new AlternativaMultiplaEscolha($idAlternativa, $textoAlternativa, false, AlternativaMultiplaEscolha::SUBTIPO_MULTIPLA_ESCOLHA);
        }

        if (isset($registro["IdQuestao"]) && $registro["IdQuestao"] !== null) {
            $alternativa->setIdQuestao((int) $registro["IdQuestao"]);
        }

        return new AlternativaMarcada(
            $usuario,
            $alternativa,
            (bool) $registro["Correta"],
            $registro["SequenciaRespondida"] !== null ? (int) $registro["SequenciaRespondida"] : null,
            $registro["IdAlternativaAssociadaRespondida"] !== null ? (int) $registro["IdAlternativaAssociadaRespondida"] : null
        );
    }
}