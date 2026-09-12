<?php

declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Cobertura da verificação de e-mail anterior ao cadastro.
 *
 * O foco dos casos abaixo é o que diferencia este fluxo do de
 * recuperação de senha: o segredo tem seis dígitos, então o limite de
 * tentativas, o prazo e o teto de emissões precisam estar de pé.
 */
class VerificacaoEmailServiceTest extends TestCase
{
    private const EMAIL = "novo.aventureiro@exemplo.com";

    private const CONFIGURACAO = [
        "EMAIL_VERIFICATION_TTL_MINUTES"       => 15,
        "EMAIL_VERIFICATION_MAX_ATTEMPTS"      => 5,
        "EMAIL_VERIFICATION_MAX_REQUESTS"      => 3,
        "EMAIL_VERIFICATION_WINDOW_MINUTES"    => 15,
        "EMAIL_VERIFICATION_PROOF_TTL_MINUTES" => 30,
    ];

    private VerificacaoEmailDAO&MockObject $verificacaoEmailDAO;
    private UsuarioDAO&MockObject $usuarioDAO;
    private EmailService&MockObject $emailService;
    private VerificacaoEmailService $service;

    protected function setUp(): void
    {
        $this->verificacaoEmailDAO = $this->createMock(VerificacaoEmailDAO::class);
        $this->usuarioDAO = $this->createMock(UsuarioDAO::class);
        $this->emailService = $this->createMock(EmailService::class);

        $this->service = new VerificacaoEmailService(
            $this->verificacaoEmailDAO,
            $this->usuarioDAO,
            $this->emailService,
            self::CONFIGURACAO
        );
    }

    // -----------------------------------------------------------------
    // Solicitação do código
    // -----------------------------------------------------------------

    public function testSolicitacaoPersisteVerificacaoEEnviaCodigoDeSeisDigitos(): void
    {
        $this->usuarioDAO->method("verificarCorreioEletronicoExiste")->willReturn(false);
        $this->verificacaoEmailDAO->method("contarSolicitacoesRecentes")->willReturn(0);

        $this->verificacaoEmailDAO
            ->expects($this->once())
            ->method("invalidarPendentesPorEmail")
            ->with(self::EMAIL);

        $this->verificacaoEmailDAO
            ->expects($this->once())
            ->method("salvar")
            ->with($this->callback(function (VerificacaoEmail $verificacao): bool {
                $this->assertSame(self::EMAIL, $verificacao->getCorreioEletronico());
                $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $verificacao->getHashCodigo());
                $this->assertSame(0, $verificacao->getTentativas());
                $this->assertFalse($verificacao->foiVerificado());

                return true;
            }));

        $this->emailService
            ->expects($this->once())
            ->method("enviarCodigoVerificacao")
            ->with(self::EMAIL, $this->matchesRegularExpression('/^\d{6}$/'), 15);

        $this->service->solicitar(self::EMAIL, "203.0.113.10");
    }

    public function testSolicitacaoParaEmailJaCadastradoERecusada(): void
    {
        $this->usuarioDAO->method("verificarCorreioEletronicoExiste")->willReturn(true);

        $this->verificacaoEmailDAO->expects($this->never())->method("salvar");
        $this->emailService->expects($this->never())->method("enviarCodigoVerificacao");

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage(VerificacaoEmailService::MENSAGEM_EMAIL_EM_USO);

        $this->service->solicitar(self::EMAIL);
    }

    public function testSolicitacaoComEmailMalformadoNaoConsultaOBanco(): void
    {
        $this->usuarioDAO->expects($this->never())->method("verificarCorreioEletronicoExiste");

        $this->expectException(DomainException::class);

        $this->service->solicitar("isto-nao-e-um-email");
    }

    public function testSolicitacaoAcimaDoLimiteNaoEmiteNovoCodigo(): void
    {
        $this->usuarioDAO->method("verificarCorreioEletronicoExiste")->willReturn(false);
        $this->verificacaoEmailDAO->method("contarSolicitacoesRecentes")->willReturn(3);

        $this->verificacaoEmailDAO->expects($this->never())->method("salvar");
        $this->emailService->expects($this->never())->method("enviarCodigoVerificacao");

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage(VerificacaoEmailService::MENSAGEM_LIMITE_SOLICITACOES);

        $this->service->solicitar(self::EMAIL);
    }

    public function testFalhaNoEnvioInvalidaAVerificacaoRecemCriada(): void
    {
        $this->usuarioDAO->method("verificarCorreioEletronicoExiste")->willReturn(false);
        $this->verificacaoEmailDAO->method("contarSolicitacoesRecentes")->willReturn(0);

        $this->verificacaoEmailDAO
            ->method("salvar")
            ->willReturnCallback(function (VerificacaoEmail $verificacao): void {
                $verificacao->setIdVerificacaoEmail(77);
            });

        $this->emailService
            ->method("enviarCodigoVerificacao")
            ->willThrowException(new RuntimeException("SMTP indisponível"));

        $this->verificacaoEmailDAO
            ->expects($this->once())
            ->method("marcarComoConsumido")
            ->with(77);

        $this->expectException(RegraDeNegocioException::class);

        $this->service->solicitar(self::EMAIL);
    }

    // -----------------------------------------------------------------
    // Confirmação do código
    // -----------------------------------------------------------------

    public function testCodigoCorretoDevolveComprovanteDeAltaEntropia(): void
    {
        $codigo = "418302";

        $this->verificacaoEmailDAO
            ->method("buscarPendentePorEmail")
            ->willReturn($this->criarVerificacao($codigo));

        $this->verificacaoEmailDAO
            ->expects($this->once())
            ->method("marcarComoVerificado")
            ->with(
                1,
                $this->matchesRegularExpression('/^[a-f0-9]{64}$/'),
                $this->isInstanceOf(DateTime::class)
            )
            ->willReturn(true);

        $comprovante = $this->service->confirmar(self::EMAIL, $codigo);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $comprovante);
        $this->assertNotSame($codigo, $comprovante);
    }

    public function testCodigoErradoContabilizaTentativa(): void
    {
        $this->verificacaoEmailDAO
            ->method("buscarPendentePorEmail")
            ->willReturn($this->criarVerificacao("418302"));

        // É este incremento que reduz 10^6 possibilidades a um punhado
        // de chances. Sem ele, o código de seis dígitos cai.
        $this->verificacaoEmailDAO
            ->expects($this->once())
            ->method("registrarTentativa")
            ->with(1);

        $this->verificacaoEmailDAO->expects($this->never())->method("marcarComoVerificado");

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage(VerificacaoEmailService::MENSAGEM_CODIGO_INVALIDO);

        $this->service->confirmar(self::EMAIL, "000000");
    }

    public function testCodigoCorretoAposEsgotarTentativasERecusado(): void
    {
        $codigo = "418302";

        $this->verificacaoEmailDAO
            ->method("buscarPendentePorEmail")
            ->willReturn($this->criarVerificacao($codigo, tentativas: 5));

        $this->verificacaoEmailDAO->expects($this->never())->method("marcarComoVerificado");

        $this->expectException(RegraDeNegocioException::class);

        $this->service->confirmar(self::EMAIL, $codigo);
    }

    public function testCodigoExpiradoERecusado(): void
    {
        $codigo = "418302";

        $this->verificacaoEmailDAO
            ->method("buscarPendentePorEmail")
            ->willReturn($this->criarVerificacao($codigo, expiracaoEmMinutos: -1));

        $this->expectException(RegraDeNegocioException::class);

        $this->service->confirmar(self::EMAIL, $codigo);
    }

    public function testCodigoForaDoFormatoNemChegaAoBanco(): void
    {
        $this->verificacaoEmailDAO->expects($this->never())->method("buscarPendentePorEmail");

        $this->expectException(RegraDeNegocioException::class);

        $this->service->confirmar(self::EMAIL, "12ab34");
    }

    public function testCodigoComMenosDeSeisDigitosERecusado(): void
    {
        $this->verificacaoEmailDAO->expects($this->never())->method("buscarPendentePorEmail");

        $this->expectException(RegraDeNegocioException::class);

        $this->service->confirmar(self::EMAIL, "4183");
    }

    public function testConfirmacaoSemVerificacaoPendenteERecusada(): void
    {
        $this->verificacaoEmailDAO->method("buscarPendentePorEmail")->willReturn(null);

        $this->expectException(RegraDeNegocioException::class);

        $this->service->confirmar(self::EMAIL, "418302");
    }

    public function testConfirmacaoConcorrenteFalhaNaSegundaRequisicao(): void
    {
        $codigo = "418302";

        $this->verificacaoEmailDAO
            ->method("buscarPendentePorEmail")
            ->willReturn($this->criarVerificacao($codigo));

        // O UPDATE condicional não afetou nenhuma linha: outra
        // requisição já havia confirmado.
        $this->verificacaoEmailDAO->method("marcarComoVerificado")->willReturn(false);

        $this->expectException(RegraDeNegocioException::class);

        $this->service->confirmar(self::EMAIL, $codigo);
    }

    // -----------------------------------------------------------------
    // Consumo do comprovante no cadastro
    // -----------------------------------------------------------------

    public function testComprovanteValidoEConsumido(): void
    {
        $comprovante = str_repeat("a", 64);

        $this->verificacaoEmailDAO
            ->method("buscarPorHashComprovante")
            ->with(hash("sha256", $comprovante))
            ->willReturn($this->criarVerificacaoConfirmada($comprovante));

        $this->verificacaoEmailDAO
            ->expects($this->once())
            ->method("marcarComoConsumido")
            ->with(1)
            ->willReturn(true);

        $this->service->consumirComprovante(self::EMAIL, $comprovante);

        $this->addToAssertionCount(1);
    }

    public function testComprovanteDeOutroEmailERecusado(): void
    {
        $comprovante = str_repeat("b", 64);

        $this->verificacaoEmailDAO
            ->method("buscarPorHashComprovante")
            ->willReturn($this->criarVerificacaoConfirmada($comprovante));

        $this->verificacaoEmailDAO->expects($this->never())->method("marcarComoConsumido");

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage(VerificacaoEmailService::MENSAGEM_COMPROVANTE_INVALIDO);

        // Verificou um endereço e tenta cadastrar outro.
        $this->service->consumirComprovante("outro@exemplo.com", $comprovante);
    }

    public function testComprovanteAusenteERecusado(): void
    {
        $this->verificacaoEmailDAO->expects($this->never())->method("buscarPorHashComprovante");

        $this->expectException(RegraDeNegocioException::class);

        $this->service->consumirComprovante(self::EMAIL, null);
    }

    public function testComprovanteExpiradoERecusado(): void
    {
        $comprovante = str_repeat("c", 64);

        $this->verificacaoEmailDAO
            ->method("buscarPorHashComprovante")
            ->willReturn($this->criarVerificacaoConfirmada($comprovante, expiracaoEmMinutos: -1));

        $this->verificacaoEmailDAO->expects($this->never())->method("marcarComoConsumido");

        $this->expectException(RegraDeNegocioException::class);

        $this->service->consumirComprovante(self::EMAIL, $comprovante);
    }

    public function testComprovanteJaConsumidoNaoCriaSegundaConta(): void
    {
        $comprovante = str_repeat("d", 64);

        $this->verificacaoEmailDAO
            ->method("buscarPorHashComprovante")
            ->willReturn($this->criarVerificacaoConfirmada($comprovante));

        $this->verificacaoEmailDAO->method("marcarComoConsumido")->willReturn(false);

        $this->expectException(RegraDeNegocioException::class);

        $this->service->consumirComprovante(self::EMAIL, $comprovante);
    }

    // -----------------------------------------------------------------
    // Integração com o cadastro
    // -----------------------------------------------------------------

    public function testCadastroSemComprovanteNaoChegaAoBanco(): void
    {
        $usuarioDAO = $this->createMock(UsuarioDAO::class);
        $ocupacaoDAO = $this->createMock(OcupacaoDAO::class);

        $ocupacaoDAO->method("verificarSeOcupacaoExiste")->willReturn(true);
        $usuarioDAO->method("verificarCorreioEletronicoExiste")->willReturn(false);
        $usuarioDAO->method("verificarNomeAventureiroExiste")->willReturn(false);

        $usuarioDAO->expects($this->never())->method("salvar");

        $usuarioService = new UsuarioService($usuarioDAO, $ocupacaoDAO, $this->service);

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage(VerificacaoEmailService::MENSAGEM_COMPROVANTE_INVALIDO);

        $usuarioService->salvar($this->criarUsuarioNovo(), null);
    }

    public function testCadastroComComprovanteValidoPersisteOUsuario(): void
    {
        $comprovante = str_repeat("e", 64);

        $usuarioDAO = $this->createMock(UsuarioDAO::class);
        $ocupacaoDAO = $this->createMock(OcupacaoDAO::class);

        $ocupacaoDAO->method("verificarSeOcupacaoExiste")->willReturn(true);
        $usuarioDAO->method("verificarCorreioEletronicoExiste")->willReturn(false);
        $usuarioDAO->method("verificarNomeAventureiroExiste")->willReturn(false);

        $this->verificacaoEmailDAO
            ->method("buscarPorHashComprovante")
            ->willReturn($this->criarVerificacaoConfirmada($comprovante));

        $this->verificacaoEmailDAO->method("marcarComoConsumido")->willReturn(true);

        $usuarioDAO->expects($this->once())->method("salvar");

        $usuarioService = new UsuarioService($usuarioDAO, $ocupacaoDAO, $this->service);

        $usuarioService->salvar($this->criarUsuarioNovo(), $comprovante);
    }

    // -----------------------------------------------------------------
    // Modelo
    // -----------------------------------------------------------------

    public function testCodigoEmitidoTemSeisDigitosEPreservaZeroAEsquerda(): void
    {
        for ($i = 0; $i < 200; $i++) {
            $codigo = VerificacaoEmail::gerarCodigo();

            $this->assertMatchesRegularExpression('/^\d{6}$/', $codigo);
            $this->assertSame(6, strlen($codigo));
        }
    }

    public function testEmissaoGuardaApenasOResumoDoCodigo(): void
    {
        [$verificacao, $codigo] = VerificacaoEmail::emitir(self::EMAIL, 15, "203.0.113.10");

        $this->assertSame(hash("sha256", $codigo), $verificacao->getHashCodigo());
        $this->assertNotSame($codigo, $verificacao->getHashCodigo());
        $this->assertTrue($verificacao->codigoConfere($codigo));
        $this->assertFalse($verificacao->codigoConfere("999999"));
    }

    public function testDoisComprovantesNuncaSaoIguais(): void
    {
        $this->assertNotSame(
            VerificacaoEmail::gerarComprovante(),
            VerificacaoEmail::gerarComprovante()
        );
    }

    // -----------------------------------------------------------------
    // Auxiliares
    // -----------------------------------------------------------------

    private function criarVerificacao(
        string $codigo,
        int $id = 1,
        int $tentativas = 0,
        int $expiracaoEmMinutos = 15
    ): VerificacaoEmail {
        $agora = new DateTime();

        return new VerificacaoEmail(
            $id,
            self::EMAIL,
            hash("sha256", $codigo),
            $agora,
            (clone $agora)->modify("{$expiracaoEmMinutos} minutes"),
            $tentativas,
            null,
            null,
            null,
            null,
            "203.0.113.10"
        );
    }

    private function criarVerificacaoConfirmada(
        string $comprovante,
        int $id = 1,
        int $expiracaoEmMinutos = 30
    ): VerificacaoEmail {
        $agora = new DateTime();

        return new VerificacaoEmail(
            $id,
            self::EMAIL,
            hash("sha256", "418302"),
            $agora,
            (clone $agora)->modify("15 minutes"),
            0,
            hash("sha256", $comprovante),
            $agora,
            (clone $agora)->modify("{$expiracaoEmMinutos} minutes"),
            null,
            "203.0.113.10"
        );
    }

    private function criarUsuarioNovo(): Usuario
    {
        return new Usuario(
            null,
            "Aventureiro da Inovação",
            "novoaventureiro",
            self::EMAIL,
            "1998-04-20",
            true,
            true,
            false,
            "SenhaForte@2026",
            new Ocupacao(1, "estudante")
        );
    }
}
