<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Regras da edição de perfil.
 *
 * A alteração de senha na edição é OPCIONAL. O que impedia isso de
 * funcionar:
 *
 *  - o construtor exigia `string $senha`, então uma atualização sem
 *    senha nova nem podia ser montada;
 *  - UsuarioDTO::create lia `$dados["senha"] ?? $dados["novaSenha"]` e,
 *    sem nenhuma das duas chaves, passava null a um parâmetro `string`
 *    — TypeError, que não é Exception e escapava dos catch do
 *    controller, virando 500 sem corpo;
 *  - o UPDATE do DAO sempre sobrescrevia HashSenha, o que forçava o
 *    frontend a reenviar alguma senha em toda edição.
 *
 * A política de senha continua íntegra: qualquer senha informada passa
 * pelas mesmas regras do cadastro e da redefinição.
 */
class EdicaoDeUsuarioTest extends TestCase
{
    private const DADOS_BASE = [
        "nomeUsuario" => "Maria Santos Silva",
        "nomeAventureiro" => "mariah_dev",
        "correioEletronico" => "maria@email.com",
        "dataNascimento" => "1998-08-20",
        "possuiConhecimento" => true,
        "primeiroAcesso" => false,
        "idOcupacao" => 1,
    ];

    // -----------------------------------------------------------------
    // Senha opcional na edição
    // -----------------------------------------------------------------

    public function testAtualizacaoSemNovaSenhaNaoDefineSenha(): void
    {
        $usuario = UsuarioDTO::create(self::DADOS_BASE, 2);

        $this->assertFalse(
            $usuario->temSenhaDefinida(),
            "Sem nova senha no corpo, nada deve ser gravado em HashSenha."
        );
        $this->assertSame("", $usuario->getSenha());
    }

    public function testNovaSenhaVaziaNaoContaComoNovaSenha(): void
    {
        $usuario = UsuarioDTO::create(
            self::DADOS_BASE + ["novaSenha" => ""],
            2
        );

        $this->assertFalse($usuario->temSenhaDefinida());
    }

    public function testNovaSenhaApenasComEspacosNaoContaComoNovaSenha(): void
    {
        $usuario = UsuarioDTO::create(
            self::DADOS_BASE + ["novaSenha" => "   "],
            2
        );

        $this->assertFalse($usuario->temSenhaDefinida());
    }

    public function testNovaSenhaValidaGeraHash(): void
    {
        $usuario = UsuarioDTO::create(
            self::DADOS_BASE + ["novaSenha" => "NovaMaria@2026"],
            2
        );

        $this->assertTrue($usuario->temSenhaDefinida());
        $this->assertTrue($usuario->verificarSenha("NovaMaria@2026"));
        $this->assertNotSame("NovaMaria@2026", $usuario->getSenha(), "A senha nunca é guardada em claro.");
    }

    public function testCadastroUsaAChaveSenha(): void
    {
        $usuario = UsuarioDTO::create(
            self::DADOS_BASE + ["senha" => "Cadastro@2026"],
            null
        );

        $this->assertTrue($usuario->temSenhaDefinida());
        $this->assertTrue($usuario->verificarSenha("Cadastro@2026"));
    }

    // -----------------------------------------------------------------
    // Política de senha preservada
    // -----------------------------------------------------------------

    /**
     * @dataProvider senhasInvalidas
     */
    public function testPoliticaDeSenhaContinuaValendoParaSenhaInformada(string $senha, string $motivo): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Senha inválida!");

        UsuarioDTO::create(self::DADOS_BASE + ["novaSenha" => $senha], 2);

        $this->fail("Deveria recusar: {$motivo}");
    }

    public static function senhasInvalidas(): array
    {
        return [
            "curta demais" => ["Ab@1234", "menos de 8 caracteres"],
            "sem numero" => ["SemNumero@abc", "não tem dígito"],
            "sem caractere especial" => ["SemEspecial123", "não tem caractere especial"],
            "sem letra" => ["1234567@", "não tem letra"],
        ];
    }

    public function testSenhaComExatamenteOitoCaracteresValidosEAceita(): void
    {
        $usuario = UsuarioDTO::create(
            self::DADOS_BASE + ["novaSenha" => "Ab@12345"],
            2
        );

        $this->assertTrue($usuario->verificarSenha("Ab@12345"));
    }

    // -----------------------------------------------------------------
    // Conferências do serviço
    // -----------------------------------------------------------------

    public function testRepeticaoDivergenteERecusada(): void
    {
        $service = $this->criarService();

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage("Senhas não conferem!");

        $service->verificarSenhaRepeticao("NovaMaria@2026", "Outra@2026");
    }

    public function testRepeticaoAusenteNaoEstouraTypeError(): void
    {
        $service = $this->criarService();

        /*
         * Os parâmetros eram `string`; sem as chaves no corpo o PHP
         * lançava TypeError e a resposta virava 500 sem corpo.
         */
        $this->expectException(RegraDeNegocioException::class);

        $service->verificarSenhaRepeticao("NovaMaria@2026", null);
    }

    public function testSenhaAtualAusenteTemMensagemDeNegocio(): void
    {
        $usuarioDAO = $this->createMock(UsuarioDAO::class);
        $usuarioDAO->expects($this->never())->method("verificarSenhaAtual");

        $service = new UsuarioService($usuarioDAO, $this->createMock(OcupacaoDAO::class));

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage("Informe a senha atual para continuar!");

        $service->verificarSenhaAtual(2, null);
    }

    public function testSenhaAtualIncorretaERecusada(): void
    {
        $usuarioDAO = $this->createMock(UsuarioDAO::class);
        $usuarioDAO->method("verificarSenhaAtual")->willReturn(false);

        $service = new UsuarioService($usuarioDAO, $this->createMock(OcupacaoDAO::class));

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage("Senha atual não confere!");

        $service->verificarSenhaAtual(2, "ErradaTotal@1");
    }

    // -----------------------------------------------------------------
    // Demais campos
    // -----------------------------------------------------------------

    public function testPossuiConhecimentoAusenteFicaNuloEmVezDeFalso(): void
    {
        $dados = self::DADOS_BASE;
        unset($dados["possuiConhecimento"]);

        $usuario = UsuarioDTO::create($dados, 2);

        $this->assertNull(
            $usuario->isPossuiConhecimento(),
            "Campo ausente é indefinido, não 'não possui'."
        );
    }

    public function testDataDeNascimentoAusenteEAceita(): void
    {
        $dados = self::DADOS_BASE;
        unset($dados["dataNascimento"]);

        $this->assertNull(UsuarioDTO::create($dados, 2)->getDataNascimento());
    }

    private function criarService(): UsuarioService
    {
        return new UsuarioService(
            $this->createMock(UsuarioDAO::class),
            $this->createMock(OcupacaoDAO::class)
        );
    }
}
