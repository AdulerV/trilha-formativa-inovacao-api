<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Contrato entre o cadastro de questões do frontend e a API.
 *
 * Dois pontos travados aqui:
 *
 *  1. O POST precisa devolver o ID gerado. Sem isso o frontend listava
 *     todas as questões para reencontrar a que acabara de criar.
 *
 *  2. A alternativa associada é LIDA com a chave "id" e era ESCRITA com
 *     "idAlternativaAssociada". Reenviar para edição o objeto recebido
 *     da API perdia o ID e recriava o par em vez de atualizá-lo.
 */
class QuestaoAlternativaContratoTest extends TestCase
{
    // -----------------------------------------------------------------
    // IDs devolvidos na criação
    // -----------------------------------------------------------------

    public function testDtoDeQuestaoDevolveAQuestaoCriada(): void
    {
        $missao = $this->criarMissaoQuiz();

        $questao = QuestaoDTO::create([
            "enunciado" => "Qual a função da chave primária?",
            "mensagemCorrecao" => "Reveja o material de modelagem.",
        ], null, $missao);

        $this->assertInstanceOf(Questao::class, $questao);
        $this->assertSame("Qual a função da chave primária?", $questao->getEnunciado());
        $this->assertSame($questao, $missao->getQuestoes()[0]);
    }

    public function testDtoDeAlternativaDevolveAsAlternativasCriadasNaOrdem(): void
    {
        $questao = $this->criarQuestao();

        $criadas = AlternativaDTO::create([
            ["texto" => "Primeira", "tipoAlternativa" => "multipla_escolha", "correta" => true, "subtipo" => "multipla_escolha"],
            ["texto" => "Segunda", "tipoAlternativa" => "multipla_escolha", "correta" => false, "subtipo" => "multipla_escolha"],
        ], null, $questao);

        $this->assertCount(2, $criadas);
        $this->assertSame("Primeira", $criadas[0]->getTexto());
        $this->assertSame("Segunda", $criadas[1]->getTexto());
    }

    public function testDtoDeAlternativaAceitaUmObjetoUnico(): void
    {
        $questao = $this->criarQuestao();

        $criadas = AlternativaDTO::create(
            ["texto" => "Única", "tipoAlternativa" => "ordenacao", "numeroSequencia" => 1],
            null,
            $questao
        );

        $this->assertCount(1, $criadas);
        $this->assertInstanceOf(AlternativaOrdenacao::class, $criadas[0]);
    }

    // -----------------------------------------------------------------
    // Múltipla escolha em uma única requisição
    // -----------------------------------------------------------------

    public function testQuestaoComMultiplaEscolhaPodeSerCriadaEmUmaRequisicao(): void
    {
        $missao = $this->criarMissaoQuiz();

        /*
         * "correta" não era repassado por QuestaoDTO::create e isso
         * fazia a criação conjunta falhar sempre.
         */
        $questao = QuestaoDTO::create([
            "enunciado" => "O que é normalização?",
            "mensagemCorrecao" => "Reveja o material.",
            "alternativas" => [
                ["texto" => "Organizar os dados", "tipoAlternativa" => "multipla_escolha", "correta" => true, "subtipo" => "multipla_escolha"],
                ["texto" => "Apagar os dados", "tipoAlternativa" => "multipla_escolha", "correta" => false, "subtipo" => "multipla_escolha"],
            ],
        ], null, $missao);

        $alternativas = $questao->getAlternativas();

        $this->assertCount(2, $alternativas);
        $this->assertTrue($alternativas[0]->isCorreta());
        $this->assertFalse($alternativas[1]->isCorreta());
    }

    // -----------------------------------------------------------------
    // Alternativa de associação
    // -----------------------------------------------------------------

    public function testAssociacaoNovaNaoExigeIdDaAssociada(): void
    {
        $questao = $this->criarQuestao();

        $criadas = AlternativaDTO::create([
            "texto" => "Chave primária",
            "tipoAlternativa" => "associacao",
            "alternativaAssociada" => [
                "texto" => "Identifica a linha",
                "tipoAlternativa" => "associacao",
            ],
        ], null, $questao);

        $associacao = $criadas[0];

        $this->assertInstanceOf(AlternativaAssociacao::class, $associacao);
        $this->assertNull($associacao->getIdAlternativaAssociada());
        $this->assertSame("Identifica a linha", $associacao->getAlternativaAssociada()->getTexto());
    }

    public function testAssociacaoAceitaAChaveIdAlternativaAssociada(): void
    {
        $questao = $this->criarQuestao();

        $criadas = AlternativaDTO::create([
            "id" => 19,
            "texto" => "Chave primária",
            "tipoAlternativa" => "associacao",
            "alternativaAssociada" => [
                "idAlternativaAssociada" => 20,
                "texto" => "Identifica a linha",
                "tipoAlternativa" => "associacao",
            ],
        ], null, $questao);

        $this->assertSame(20, $criadas[0]->getIdAlternativaAssociada());
    }

    public function testAssociacaoAceitaAChaveIdDevolvidaPelaLeitura(): void
    {
        $questao = $this->criarQuestao();

        /* Exatamente o formato que AlternativaDTO::toArray produz. */
        $criadas = AlternativaDTO::create([
            "id" => 19,
            "texto" => "Chave primária",
            "tipoAlternativa" => "associacao",
            "alternativaAssociada" => [
                "id" => 20,
                "texto" => "Identifica a linha",
                "tipoAlternativa" => "associacao",
            ],
        ], null, $questao);

        $this->assertSame(
            20,
            $criadas[0]->getIdAlternativaAssociada(),
            "O ID da associada precisa sobreviver ao ciclo ler -> reenviar."
        );
    }

    public function testAssociacaoSemTextoNaAssociadaFalhaComMensagemDeNegocio(): void
    {
        $questao = $this->criarQuestao();

        /* Antes era um warning de índice indefinido; agora é 400. */
        $this->expectException(DomainException::class);

        AlternativaDTO::create([
            "texto" => "Chave primária",
            "tipoAlternativa" => "associacao",
            "alternativaAssociada" => ["id" => 20],
        ], null, $questao);
    }

    public function testLeituraEEscritaDaAssociacaoFecham(): void
    {
        $questao = $this->criarQuestao();

        $original = AlternativaDTO::create([
            "id" => 19,
            "texto" => "Chave primária",
            "tipoAlternativa" => "associacao",
            "alternativaAssociada" => [
                "idAlternativaAssociada" => 20,
                "texto" => "Identifica a linha",
                "tipoAlternativa" => "associacao",
            ],
        ], null, $questao)[0];

        /* Serializa como a API responde e reenvia como o frontend faz. */
        $lido = AlternativaDTO::toArray($original);

        $reenviado = AlternativaDTO::create($lido, null, $this->criarQuestao())[0];

        $this->assertSame(
            $original->getIdAlternativa(),
            $reenviado->getIdAlternativa()
        );
        $this->assertSame(
            $original->getIdAlternativaAssociada(),
            $reenviado->getIdAlternativaAssociada()
        );
        $this->assertSame(
            $original->getAlternativaAssociada()->getTexto(),
            $reenviado->getAlternativaAssociada()->getTexto()
        );
    }

    public function testAlternativaNaoPodeSerAssociadaASiMesma(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Uma alternativa não pode ser associada a ela mesma!");

        AlternativaDTO::create([
            "id" => 19,
            "texto" => "Chave primária",
            "tipoAlternativa" => "associacao",
            "alternativaAssociada" => [
                "id" => 19,
                "texto" => "Identifica a linha",
                "tipoAlternativa" => "associacao",
            ],
        ], null, $this->criarQuestao());
    }

    // -----------------------------------------------------------------
    // Apoio
    // -----------------------------------------------------------------

    private function criarMissaoQuiz(): MissaoAtividade
    {
        return new MissaoAtividade(
            2,
            "Questionário de Fundamentos",
            100.0,
            new Tematica(1, "Fundamentos"),
            MissaoAtividade::QUIZ
        );
    }

    private function criarQuestao(): Questao
    {
        return new Questao(6, "Associe os conceitos", "Reveja o material.", 2);
    }
}
