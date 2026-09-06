<?php

declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Cobertura das regras do mecanismo de recuperação de senha.
 *
 * Os DAOs e o serviço de e-mail são substituídos por dublês: o alvo
 * aqui é a regra de negócio, não a persistência nem o SMTP.
 */
class RecuperacaoSenhaServiceTest extends TestCase
{
    private const CONFIGURACAO = [
        "PASSWORD_RESET_TTL_MINUTES"    => 30,
        "PASSWORD_RESET_MAX_REQUESTS"   => 3,
        "PASSWORD_RESET_WINDOW_MINUTES" => 15,
    ];

    private RecuperacaoSenhaDAO&MockObject $recuperacaoSenhaDAO;
    private UsuarioDAO&MockObject $usuarioDAO;
    private EmailService&MockObject $emailService;
    private RecuperacaoSenhaService $service;

    protected function setUp(): void
    {
        $this->recuperacaoSenhaDAO = $this->createMock(RecuperacaoSenhaDAO::class);
        $this->usuarioDAO = $this->createMock(UsuarioDAO::class);
        $this->emailService = $this->createMock(EmailService::class);

        $this->service = new RecuperacaoSenhaService(
            $this->recuperacaoSenhaDAO,
            $this->usuarioDAO,
            $this->emailService,
            self::CONFIGURACAO
        );
    }

    // -----------------------------------------------------------------
    // Solicitação
    // -----------------------------------------------------------------

    public function testSolicitacaoComEmailCadastradoPersisteTokenEEnviaEmail(): void
    {
        $usuario = $this->criarUsuario();

        $this->usuarioDAO
            ->method("buscarPorCorreioEletronico")
            ->willReturn($usuario);

        $this->recuperacaoSenhaDAO
            ->method("contarSolicitacoesRecentes")
            ->willReturn(0);

        $this->recuperacaoSenhaDAO
            ->expects($this->once())
            ->method("invalidarTokensDoUsuario")
            ->with(7);

        $this->recuperacaoSenhaDAO
            ->expects($this->once())
            ->method("salvar")
            ->with($this->callback(function (RecuperacaoSenha $recuperacao): bool {
                $this->assertSame(7, $recuperacao->getIdUsuario());
                $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $recuperacao->getHashToken());
                $this->assertFalse($recuperacao->foiUtilizado());
                $this->assertFalse($recuperacao->estaExpirado());

                return true;
            }));

        $this->emailService
            ->expects($this->once())
            ->method("enviarRecuperacaoSenha")
            ->with($usuario, $this->matchesRegularExpression('/^[a-f0-9]{64}$/'), 30);

        $this->service->solicitar("aventureiro@exemplo.com", "203.0.113.10");
    }

    public function testSolicitacaoComEmailInexistenteNaoVazaAExistenciaDaConta(): void
    {
        $this->usuarioDAO
            ->method("buscarPorCorreioEletronico")
            ->willReturn(null);

        $this->recuperacaoSenhaDAO->expects($this->never())->method("salvar");
        $this->emailService->expects($this->never())->method("enviarRecuperacaoSenha");

        // Nenhuma exceção: o controller devolve a mesma mensagem dos
        // e-mails cadastrados.
        $this->service->solicitar("nao-existe@exemplo.com");

        $this->addToAssertionCount(1);
    }

    public function testSolicitacaoComEmailMalformadoNaoConsultaOBanco(): void
    {
        $this->usuarioDAO->expects($this->never())->method("buscarPorCorreioEletronico");
        $this->recuperacaoSenhaDAO->expects($this->never())->method("salvar");

        $this->service->solicitar("isto-nao-e-um-email");

        $this->addToAssertionCount(1);
    }

    public function testSolicitacaoAcimaDoLimiteNaoEmiteNovoToken(): void
    {
        $this->usuarioDAO
            ->method("buscarPorCorreioEletronico")
            ->willReturn($this->criarUsuario());

        $this->recuperacaoSenhaDAO
            ->method("contarSolicitacoesRecentes")
            ->willReturn(3);

        $this->recuperacaoSenhaDAO->expects($this->never())->method("salvar");
        $this->emailService->expects($this->never())->method("enviarRecuperacaoSenha");

        $this->service->solicitar("aventureiro@exemplo.com");

        $this->addToAssertionCount(1);
    }

    public function testFalhaNoEnvioDeEmailInvalidaOTokenESilencia(): void
    {
        $this->usuarioDAO
            ->method("buscarPorCorreioEletronico")
            ->willReturn($this->criarUsuario());

        $this->recuperacaoSenhaDAO
            ->method("contarSolicitacoesRecentes")
            ->willReturn(0);

        $this->recuperacaoSenhaDAO
            ->method("salvar")
            ->willReturnCallback(function (RecuperacaoSenha $recuperacao): void {
                $recuperacao->setIdRecuperacaoSenha(99);
            });

        $this->emailService
            ->method("enviarRecuperacaoSenha")
            ->willThrowException(new RuntimeException("SMTP indisponível"));

        $this->recuperacaoSenhaDAO
            ->expects($this->once())
            ->method("marcarComoUtilizado")
            ->with(99);

        $this->service->solicitar("aventureiro@exemplo.com");

        $this->addToAssertionCount(1);
    }

    // -----------------------------------------------------------------
    // Validação do token
    // -----------------------------------------------------------------

    public function testTokenValidoERetornadoPelaValidacao(): void
    {
        $token = str_repeat("a", 64);

        $this->recuperacaoSenhaDAO
            ->method("buscarPorHashToken")
            ->with(hash("sha256", $token))
            ->willReturn($this->criarRecuperacao($token));

        $recuperacao = $this->service->validarToken($token);

        $this->assertTrue($recuperacao->estaValido());
    }

    public function testTokenExpiradoERejeitado(): void
    {
        $token = str_repeat("b", 64);

        $this->recuperacaoSenhaDAO
            ->method("buscarPorHashToken")
            ->willReturn($this->criarRecuperacao($token, expiracaoEmMinutos: -1));

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage(RecuperacaoSenhaService::MENSAGEM_TOKEN_INVALIDO);

        $this->service->validarToken($token);
    }

    public function testTokenJaUtilizadoERejeitado(): void
    {
        $token = str_repeat("c", 64);

        $this->recuperacaoSenhaDAO
            ->method("buscarPorHashToken")
            ->willReturn($this->criarRecuperacao($token, utilizado: true));

        $this->expectException(RegraDeNegocioException::class);

        $this->service->validarToken($token);
    }

    public function testTokenInexistenteERejeitado(): void
    {
        $this->recuperacaoSenhaDAO
            ->method("buscarPorHashToken")
            ->willReturn(null);

        $this->expectException(RegraDeNegocioException::class);

        $this->service->validarToken(str_repeat("d", 64));
    }

    public function testTokenComFormatoInvalidoNemChegaAoBanco(): void
    {
        $this->recuperacaoSenhaDAO->expects($this->never())->method("buscarPorHashToken");

        $this->expectException(RegraDeNegocioException::class);

        $this->service->validarToken("token-curto");
    }

    // -----------------------------------------------------------------
    // Redefinição
    // -----------------------------------------------------------------

    public function testRedefinicaoGravaNovoHashEConsomeOToken(): void
    {
        $token = str_repeat("e", 64);
        $novaSenha = "NovaSenha@2026";

        $this->recuperacaoSenhaDAO
            ->method("buscarPorHashToken")
            ->willReturn($this->criarRecuperacao($token, id: 42));

        $this->usuarioDAO
            ->method("buscarPorId")
            ->with(7)
            ->willReturn($this->criarUsuario());

        $this->recuperacaoSenhaDAO
            ->expects($this->once())
            ->method("marcarComoUtilizado")
            ->with(42)
            ->willReturn(true);

        $this->recuperacaoSenhaDAO
            ->expects($this->once())
            ->method("invalidarTokensDoUsuario")
            ->with(7);

        $this->usuarioDAO
            ->expects($this->once())
            ->method("atualizarHashSenha")
            ->with(7, $this->callback(function (string $hash) use ($novaSenha): bool {
                // O DAO recebe um hash, jamais a senha em claro.
                $this->assertNotSame($novaSenha, $hash);
                $this->assertTrue(password_verify($novaSenha, $hash));

                return true;
            }));

        $this->emailService
            ->expects($this->once())
            ->method("enviarConfirmacaoAlteracaoSenha");

        $this->service->redefinir($token, $novaSenha, $novaSenha);
    }

    public function testRedefinicaoComSenhasDivergentesFalhaAntesDeTocarNoToken(): void
    {
        $this->recuperacaoSenhaDAO->expects($this->never())->method("buscarPorHashToken");

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage("Senhas não conferem!");

        $this->service->redefinir(str_repeat("f", 64), "NovaSenha@2026", "OutraSenha@2026");
    }

    public function testRedefinicaoComSenhaForaDaPoliticaNaoConsomeOToken(): void
    {
        $token = str_repeat("0", 64);

        $this->recuperacaoSenhaDAO
            ->method("buscarPorHashToken")
            ->willReturn($this->criarRecuperacao($token, id: 42));

        $this->usuarioDAO
            ->method("buscarPorId")
            ->willReturn($this->criarUsuario());

        $this->recuperacaoSenhaDAO->expects($this->never())->method("marcarComoUtilizado");
        $this->usuarioDAO->expects($this->never())->method("atualizarHashSenha");

        $this->expectException(DomainException::class);

        $this->service->redefinir($token, "123456", "123456");
    }

    public function testConsumoConcorrenteDoMesmoTokenFalhaNaSegundaRequisicao(): void
    {
        $token = str_repeat("1", 64);

        $this->recuperacaoSenhaDAO
            ->method("buscarPorHashToken")
            ->willReturn($this->criarRecuperacao($token, id: 42));

        $this->usuarioDAO
            ->method("buscarPorId")
            ->willReturn($this->criarUsuario());

        // O UPDATE condicional não afetou nenhuma linha: outra
        // requisição já havia consumido o token.
        $this->recuperacaoSenhaDAO
            ->method("marcarComoUtilizado")
            ->willReturn(false);

        $this->usuarioDAO->expects($this->never())->method("atualizarHashSenha");

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage(RecuperacaoSenhaService::MENSAGEM_TOKEN_INVALIDO);

        $this->service->redefinir($token, "NovaSenha@2026", "NovaSenha@2026");
    }

    // -----------------------------------------------------------------
    // Modelo
    // -----------------------------------------------------------------

    public function testTokenEmitidoPossuiEntropiaEPrazoEsperados(): void
    {
        [$recuperacao, $token] = RecuperacaoSenha::emitir(7, 30, "203.0.113.10");

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        $this->assertSame(hash("sha256", $token), $recuperacao->getHashToken());
        $this->assertNotSame($token, $recuperacao->getHashToken());
        $this->assertTrue($recuperacao->corresponde($token));
        $this->assertFalse($recuperacao->corresponde(str_repeat("9", 64)));

        $minutosRestantes = (new DateTime())->diff($recuperacao->getDataExpiracao())->i;
        $this->assertGreaterThanOrEqual(28, $minutosRestantes);
    }

    public function testDoisTokensNuncaSaoIguais(): void
    {
        [, $primeiro] = RecuperacaoSenha::emitir(7, 30);
        [, $segundo] = RecuperacaoSenha::emitir(7, 30);

        $this->assertNotSame($primeiro, $segundo);
    }

    // -----------------------------------------------------------------
    // Auxiliares
    // -----------------------------------------------------------------

    private function criarUsuario(): Usuario
    {
        return new Usuario(
            7,
            "Aventureiro da Inovação",
            "aventureiro",
            "aventureiro@exemplo.com",
            "1998-04-20",
            true,
            false,
            false,
            "SenhaAtual@2026",
            new Ocupacao(1, "estudante")
        );
    }

    private function criarRecuperacao(
        string $token,
        int $id = 1,
        int $expiracaoEmMinutos = 30,
        bool $utilizado = false
    ): RecuperacaoSenha {
        $agora = new DateTime();

        return new RecuperacaoSenha(
            $id,
            7,
            hash("sha256", $token),
            $agora,
            (clone $agora)->modify("{$expiracaoEmMinutos} minutes"),
            $utilizado ? new DateTime() : null,
            "203.0.113.10"
        );
    }
}
