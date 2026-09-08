<?php

declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Regressão do "somente a primeira resposta é salva".
 *
 * A chave primária de ALTERNATIVA_MARCADA é (IdUsuario, IdAlternativa).
 * O serviço recusava a segunda marcação da mesma alternativa com
 * "Esta alternativa já foi marcada por este usuário!", então refazer um
 * quiz ou uma tarefa não gravava nada — e o Promise.all do frontend
 * abortava a correção inteira no primeiro 400.
 *
 * Marcar de novo passou a substituir a resposta anterior; as regras de
 * tentativas e pontuação seguem na conclusão da missão.
 */
class AlternativaMarcadaReenvioTest extends TestCase
{
    private AlternativaMarcadaDAO&MockObject $alternativaMarcadaDAO;
    private AlternativaDAO&MockObject $alternativaDAO;
    private AlternativaMarcadaService $service;

    protected function setUp(): void
    {
        $this->alternativaMarcadaDAO = $this->createMock(AlternativaMarcadaDAO::class);
        $this->alternativaDAO = $this->createMock(AlternativaDAO::class);

        $this->service = new AlternativaMarcadaService(
            $this->alternativaMarcadaDAO,
            $this->alternativaDAO
        );
    }

    public function testMarcarDeNovoAMesmaAlternativaGravaEmVezDeRecusar(): void
    {
        $this->alternativaDAO
            ->method("buscarPorId")
            ->willReturn(new AlternativaMultiplaEscolha(42, "Belo Horizonte", true, "multipla_escolha"));

        /* A marcação já existe: antes esse cenário virava 400. */
        $this->alternativaMarcadaDAO
            ->method("verificarSeAlternativaMarcadaExiste")
            ->willReturn(true);

        $this->alternativaMarcadaDAO
            ->expects($this->once())
            ->method("salvar");

        $this->service->salvar(AlternativaMarcadaDTO::create(1, 42));

        $this->addToAssertionCount(1);
    }

    public function testMarcacaoRecebeOGabaritoDoBancoENaoODoPayload(): void
    {
        $this->alternativaDAO
            ->method("buscarPorId")
            ->willReturn(new AlternativaMultiplaEscolha(42, "Belo Horizonte", true, "multipla_escolha"));

        $this->alternativaMarcadaDAO
            ->method("verificarSeAlternativaMarcadaExiste")
            ->willReturn(false);

        $marcadaSalva = null;

        $this->alternativaMarcadaDAO
            ->method("salvar")
            ->willReturnCallback(function (AlternativaMarcada $marcada) use (&$marcadaSalva) {
                $marcadaSalva = $marcada;
            });

        $this->service->salvar(AlternativaMarcadaDTO::create(1, 42));

        $this->assertTrue(
            $marcadaSalva?->isCorreta(),
            "A correção precisa vir do gabarito persistido, não do corpo da requisição."
        );
    }

    public function testMarcacaoDeAlternativaErradaEGravadaComoIncorreta(): void
    {
        $this->alternativaDAO
            ->method("buscarPorId")
            ->willReturn(new AlternativaMultiplaEscolha(43, "Juiz de Fora", false, "multipla_escolha"));

        $this->alternativaMarcadaDAO
            ->method("verificarSeAlternativaMarcadaExiste")
            ->willReturn(false);

        $marcadaSalva = null;

        $this->alternativaMarcadaDAO
            ->method("salvar")
            ->willReturnCallback(function (AlternativaMarcada $marcada) use (&$marcadaSalva) {
                $marcadaSalva = $marcada;
            });

        $this->service->salvar(AlternativaMarcadaDTO::create(1, 43));

        $this->assertFalse($marcadaSalva?->isCorreta());
    }

    public function testAlternativaInexistenteContinuaSendoRecusada(): void
    {
        $this->alternativaDAO
            ->method("buscarPorId")
            ->willReturn(null);

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage("A alternativa associada não foi encontrada no sistema!");

        $this->service->salvar(AlternativaMarcadaDTO::create(1, 999));
    }

    public function testMarcacaoSemIdDeAlternativaContinuaSendoRecusada(): void
    {
        $this->expectException(RegraDeNegocioException::class);

        $this->service->salvar(AlternativaMarcadaDTO::create(1, 0));
    }

    public function testOrdenacaoEhCorrigidaPelaSequenciaRespondida(): void
    {
        $this->alternativaDAO
            ->method("buscarPorId")
            ->willReturn(new AlternativaOrdenacao(50, "Primeiro passo", 1));

        $this->alternativaMarcadaDAO
            ->method("verificarSeAlternativaMarcadaExiste")
            ->willReturn(false);

        $marcadas = [];

        $this->alternativaMarcadaDAO
            ->method("salvar")
            ->willReturnCallback(function (AlternativaMarcada $marcada) use (&$marcadas) {
                $marcadas[] = $marcada;
            });

        $this->service->salvar(
            AlternativaMarcadaDTO::create(1, 50, ["sequenciaRespondida" => 1])
        );
        $this->service->salvar(
            AlternativaMarcadaDTO::create(1, 50, ["sequenciaRespondida" => 3])
        );

        $this->assertTrue($marcadas[0]->isCorreta(), "Sequência 1 confere com o gabarito.");
        $this->assertFalse($marcadas[1]->isCorreta(), "Sequência 3 não confere.");
    }
}
