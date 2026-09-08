<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Regressão do erro 500 nas listagens.
 *
 * Os DAOs reconstruíam as entidades pelo construtor, que reaplica as
 * validações de criação. Bastava um registro legítimo, mas fora do
 * padrão atual — usuário com nome de uma única palavra, progresso
 * intermediário, tentativas acima do limite vigente — para a listagem
 * INTEIRA responder "Erro interno" (500):
 *
 *   GET /api/v1/progresso-missao
 *   GET /api/v1/usuarios
 *   GET /api/v1/usuarios/{id}
 *
 * Os testes abaixo travam o contrato inverso: na LEITURA o dado
 * persistido é aceito como está; na ESCRITA as validações continuam
 * valendo integralmente.
 */
class RehidratacaoDeEntidadesTest extends TestCase
{
    // -----------------------------------------------------------------
    // Usuario
    // -----------------------------------------------------------------

    public function testRehidratacaoAceitaNomeDeUmaUnicaPalavra(): void
    {
        $usuario = $this->rehidratarUsuario(nomeUsuario: "Aduler");

        $this->assertSame("Aduler", $usuario->getNomeUsuario());
    }

    public function testCriacaoContinuaRecusandoNomeDeUmaUnicaPalavra(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Nome de usuário inválido!");

        new Usuario(
            null,
            "Aduler",
            "aduler",
            "aduler@email.com",
            "1995-01-01",
            false,
            true,
            false,
            "Senha@123",
            new Ocupacao(1, "estudante")
        );
    }

    public function testRehidratacaoPreservaFotoDePerfil(): void
    {
        $usuario = $this->rehidratarUsuario(fotoPerfil: "default.png");

        $this->assertSame("default.png", $usuario->getFotoPerfil());
    }

    public function testRehidratacaoTrataAusenciaDeFotoComoNula(): void
    {
        /* USUARIO.FotoPerfil é NOT NULL: "sem foto" chega como "". */
        $usuario = $this->rehidratarUsuario(fotoPerfil: "");

        $this->assertSame("", $usuario->getFotoPerfil());
    }

    public function testRehidratacaoNaoGeraHashQuandoNaoHaSenha(): void
    {
        $usuario = $this->rehidratarUsuario();

        /*
         * O construtor obrigava os DAOs a passar uma senha de fachada,
         * e cada uma custava um bcrypt por linha lida.
         */
        $this->assertFalse($usuario->verificarSenha("Senha@123"));
    }

    public function testRehidratacaoMantemHashParaOLogin(): void
    {
        $hash = password_hash("Senha@123", PASSWORD_DEFAULT);

        $usuario = $this->rehidratarUsuario(hashSenha: $hash);

        $this->assertSame($hash, $usuario->getHashSenha());
        $this->assertTrue($usuario->verificarSenha("Senha@123"));
        $this->assertFalse($usuario->verificarSenha("OutraSenha@123"));
    }

    public function testRehidratacaoConverteDataDeNascimento(): void
    {
        $usuario = $this->rehidratarUsuario(dataNascimento: "1995-01-01");

        $this->assertSame(
            "1995-01-01",
            $usuario->getDataNascimento()?->format("Y-m-d")
        );
    }

    public function testRehidratacaoAceitaDataDeNascimentoAusente(): void
    {
        $this->assertNull($this->rehidratarUsuario(dataNascimento: null)->getDataNascimento());
        $this->assertNull($this->rehidratarUsuario(dataNascimento: "")->getDataNascimento());
    }

    // -----------------------------------------------------------------
    // Ocupacao
    // -----------------------------------------------------------------

    public function testRehidratacaoDeOcupacaoNaoNormalizaOTitulo(): void
    {
        /*
         * Normalizar na leitura alteraria o texto que a tela de edição
         * usa para casar a ocupação do usuário com a lista de opções.
         */
        $ocupacao = Ocupacao::rehidratar(3, "Analista de TI");

        $this->assertSame("Analista de TI", $ocupacao->getTitulo());
    }

    public function testCriacaoDeOcupacaoContinuaRecusandoTituloVazio(): void
    {
        $this->expectException(DomainException::class);

        new Ocupacao(null, "   ");
    }

    // -----------------------------------------------------------------
    // ProgressoMissao
    // -----------------------------------------------------------------

    public function testRehidratacaoAceitaProgressoIntermediario(): void
    {
        $progresso = ProgressoMissao::rehidratar(
            $this->rehidratarUsuario(),
            $this->criarMissaoConteudo(),
            50
        );

        $this->assertSame(50, $progresso->getProgresso());
    }

    public function testCriacaoContinuaRecusandoProgressoIntermediario(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Progresso inválido!");

        new ProgressoMissao(
            $this->rehidratarUsuario(),
            $this->criarMissaoConteudo(),
            50
        );
    }

    // -----------------------------------------------------------------
    // ProgressoMissaoAtividade
    // -----------------------------------------------------------------

    public function testRehidratacaoAceitaTentativasAcimaDoLimiteAtual(): void
    {
        $progresso = ProgressoMissaoAtividade::rehidratarAtividade(
            $this->rehidratarUsuario(),
            $this->criarMissaoAtividade(),
            100,
            9,
            66.6
        );

        $this->assertSame(9, $progresso->getTentativasRealizadas());
        $this->assertSame(100, $progresso->getProgresso());
        $this->assertSame(66.6, $progresso->getPontuacaoObtida());
    }

    public function testRehidratacaoAceitaPontuacaoAcimaDaPontuacaoVigenteDaMissao(): void
    {
        /*
         * Cenário real: a pontuação da missão foi reduzida depois de o
         * usuário já ter pontuado com o valor antigo.
         */
        $progresso = ProgressoMissaoAtividade::rehidratarAtividade(
            $this->rehidratarUsuario(),
            $this->criarMissaoAtividade(pontuacao: 50.0),
            100,
            1,
            120.0
        );

        $this->assertSame(120.0, $progresso->getPontuacaoObtida());
    }

    public function testCriacaoContinuaRecusandoTentativasAcimaDoLimite(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Quantidade de tentativas inválida!");

        new ProgressoMissaoAtividade(
            $this->rehidratarUsuario(),
            $this->criarMissaoAtividade(),
            100,
            9,
            10.0
        );
    }

    public function testRehidratacaoDeAtividadeProduzInstanciaDaEspecializacao(): void
    {
        $progresso = ProgressoMissaoAtividade::rehidratarAtividade(
            $this->rehidratarUsuario(),
            $this->criarMissaoAtividade(),
            100,
            1,
            10.0
        );

        /* O DTO decide o formato da resposta pelo tipo da instância. */
        $this->assertInstanceOf(ProgressoMissaoAtividade::class, $progresso);
        $this->assertInstanceOf(ProgressoMissao::class, $progresso);
    }

    // -----------------------------------------------------------------
    // DTOs: funções internas que quebravam na segunda chamada
    // -----------------------------------------------------------------

    public function testDtosPodemSerChamadosMaisDeUmaVezNaMesmaExecucao(): void
    {
        /*
         * criarUsuario() e criarAlternativa() eram declaradas dentro
         * dos métodos estáticos, indo para o escopo global na primeira
         * execução. A segunda chamada abortava o processo com "Cannot
         * redeclare", impedindo salvar mais de uma resposta ou mais de
         * um distintivo na mesma requisição.
         */
        $primeira = AlternativaMarcadaDTO::create(1, 10);
        $segunda = AlternativaMarcadaDTO::create(1, 11);

        $this->assertSame(10, $primeira->getAlternativa()->getIdAlternativa());
        $this->assertSame(11, $segunda->getAlternativa()->getIdAlternativa());

        $distintivo = DistintivoAdquiridoDTO::create(1, 2);
        $outroDistintivo = DistintivoAdquiridoDTO::create(1, 3);

        $this->assertSame(2, $distintivo->getDistintivo()->getIdDistintivo());
        $this->assertSame(3, $outroDistintivo->getDistintivo()->getIdDistintivo());
    }

    public function testDtoDeAlternativaMarcadaPreservaOsDadosDaResposta(): void
    {
        $marcada = AlternativaMarcadaDTO::create(5, 42, [
            "sequenciaRespondida" => 3,
            "idAlternativaAssociadaRespondida" => 77,
        ]);

        $this->assertSame(5, $marcada->getUsuario()->getIdUsuario());
        $this->assertSame(42, $marcada->getAlternativa()->getIdAlternativa());
        $this->assertSame(3, $marcada->getSequenciaRespondida());
        $this->assertSame(77, $marcada->getIdAlternativaAssociadaRespondida());
    }

    // -----------------------------------------------------------------
    // Apoio
    // -----------------------------------------------------------------

    private function rehidratarUsuario(
        string $nomeUsuario = "Aduler Viana",
        ?string $dataNascimento = "1995-01-01",
        ?string $fotoPerfil = null,
        ?string $hashSenha = null
    ): Usuario {
        return Usuario::rehidratar(
            7,
            $nomeUsuario,
            "aduler",
            "aduler@email.com",
            $dataNascimento,
            false,
            true,
            false,
            Ocupacao::rehidratar(1, "estudante"),
            $fotoPerfil,
            $hashSenha
        );
    }

    private function criarMissaoConteudo(): MissaoConteudo
    {
        return new MissaoConteudo(
            1,
            "Introdução à Web",
            20.0,
            new Tematica(1, "Fundamentos"),
            "https://exemplo.com/intro",
            "Resumo do material",
            MissaoConteudo::TEXTO
        );
    }

    private function criarMissaoAtividade(float $pontuacao = 100.0): MissaoAtividade
    {
        return new MissaoAtividade(
            2,
            "Questionário de Fundamentos",
            $pontuacao,
            new Tematica(1, "Fundamentos"),
            MissaoAtividade::QUIZ
        );
    }
}
