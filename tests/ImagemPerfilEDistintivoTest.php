<?php

declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Imagem de perfil (salvar/remover) e concessão de distintivo.
 *
 * Dois contratos travados aqui:
 *
 *  1. O caminho gravado no banco tem de ser o caminho pelo qual o
 *     servidor entrega o arquivo. Antes gravava-se "uploads/perfis/...",
 *     que não corresponde a nenhuma URL servida, e o frontend remontava
 *     a pasta por conta própria — quebrando quando o nome de aventureiro
 *     tinha acento, espaço ou maiúscula.
 *
 *  2. Conceder um distintivo que o usuário já possui não é erro. A
 *     verificação roda a cada conclusão de tarefa com 100%, então o
 *     caso é rotineiro; recusá-lo com 400 fazia a tela de conclusão
 *     acusar falha em uma operação correta.
 */
class ImagemPerfilEDistintivoTest extends TestCase
{
    private string $diretorio;
    private UploadService $uploadService;

    protected function setUp(): void
    {
        $this->diretorio = sys_get_temp_dir() . '/perfil-teste-' . bin2hex(random_bytes(4)) . '/';
        mkdir($this->diretorio, 0777, true);

        $this->uploadService = new UploadService($this->diretorio);
    }

    protected function tearDown(): void
    {
        $this->apagarRecursivo($this->diretorio);
    }

    // -----------------------------------------------------------------
    // Nome da pasta
    // -----------------------------------------------------------------

    public function testNomeDaPastaCombinaIdENomeSanitizado(): void
    {
        $this->assertSame(
            "2_mariah_dev/",
            $this->uploadService->nomePastaUsuario(2, "mariah_dev")
        );
    }

    /**
     * @dataProvider nomesQueMudamNaSanitizacao
     */
    public function testSanitizacaoDoNomeDeAventureiro(string $nome, string $esperado): void
    {
        /*
         * É exatamente por isso que o caminho precisa vir do banco: o
         * nome da pasta NÃO é o nome de aventureiro.
         */
        $this->assertSame(
            $esperado,
            $this->uploadService->nomePastaUsuario(7, $nome)
        );
    }

    public static function nomesQueMudamNaSanitizacao(): array
    {
        return [
            "com espaco" => ["Maria Dev", "7_maria_dev/"],
            "com acento" => ["joão", "7_joo/"],
            "com maiuscula" => ["MariaDev", "7_mariadev/"],
            "com pontuacao" => ["maria.dev!", "7_mariadev/"],
        ];
    }

    // -----------------------------------------------------------------
    // Remoção
    // -----------------------------------------------------------------

    public function testRemocaoApagaOArquivoEAPasta(): void
    {
        $pasta = $this->diretorio . "2_mariah_dev/";
        mkdir($pasta, 0777, true);
        file_put_contents($pasta . "foto.png", "conteudo");

        $this->uploadService->removerImagemPerfil(2, "mariah_dev");

        $this->assertFileDoesNotExist($pasta . "foto.png");
        $this->assertDirectoryDoesNotExist($pasta);
    }

    public function testRemocaoUsaTambemAPastaGravadaNoBanco(): void
    {
        /*
         * Cenário real: o usuário trocou o nome de aventureiro depois
         * de enviar a foto, então a pasta no disco tem o nome antigo.
         * O caminho registrado é a referência confiável.
         */
        $pastaAntiga = $this->diretorio . "2_nome_antigo/";
        mkdir($pastaAntiga, 0777, true);
        file_put_contents($pastaAntiga . "foto.png", "conteudo");

        $this->uploadService->removerImagemPerfil(
            2,
            "nome_novo",
            "image/upload/perfil/2_nome_antigo/foto.png"
        );

        $this->assertDirectoryDoesNotExist($pastaAntiga);
    }

    public function testRemocaoDeArquivoInexistenteNaoFalha(): void
    {
        $this->uploadService->removerImagemPerfil(99, "ninguem");

        $this->addToAssertionCount(1);
    }

    public function testRemocaoIgnoraCaminhoComTentativaDeEscapeDeDiretorio(): void
    {
        $alvo = sys_get_temp_dir() . '/nao-deve-sumir-' . bin2hex(random_bytes(4)) . '.txt';
        file_put_contents($alvo, "importante");

        $this->uploadService->removerImagemPerfil(2, "mariah_dev", "../../../" . basename($alvo));

        $this->assertFileExists($alvo, "A remoção não pode sair do diretório de perfis.");

        unlink($alvo);
    }

    // -----------------------------------------------------------------
    // Serviço de usuário
    // -----------------------------------------------------------------

    public function testRemocaoLimpaAReferenciaNoBanco(): void
    {
        $usuarioDAO = $this->criarUsuarioDAOCom("image/upload/perfil/2_mariah_dev/foto.png");

        $usuarioDAO
            ->expects($this->once())
            ->method("atualizarFotoPerfil")
            ->with(2, "");

        $service = new UsuarioService($usuarioDAO, $this->createMock(OcupacaoDAO::class));

        $service->removerFotoPerfil(2, $this->uploadService);
    }

    public function testRemocaoSemFotoTemMensagemDeNegocio(): void
    {
        $usuarioDAO = $this->criarUsuarioDAOCom("");

        $service = new UsuarioService($usuarioDAO, $this->createMock(OcupacaoDAO::class));

        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage("Este usuário não possui imagem de perfil.");

        $service->removerFotoPerfil(2, $this->uploadService);
    }

    // -----------------------------------------------------------------
    // Distintivo adquirido
    // -----------------------------------------------------------------

    public function testPrimeiraConcessaoGravaEDevolveVerdadeiro(): void
    {
        $dao = $this->createMock(DistintivoAdquiridoDAO::class);
        $dao->method("verificarSeDistintivoAdquiridoExiste")->willReturn(false);
        $dao->expects($this->once())->method("salvar");

        $service = new DistintivoAdquiridoService($dao);

        $this->assertTrue($service->salvar(DistintivoAdquiridoDTO::create(1, 3)));
    }

    public function testConcessaoRepetidaNaoGravaEDevolveFalso(): void
    {
        $dao = $this->createMock(DistintivoAdquiridoDAO::class);
        $dao->method("verificarSeDistintivoAdquiridoExiste")->willReturn(true);
        $dao->expects($this->never())->method("salvar");

        $service = new DistintivoAdquiridoService($dao);

        $this->assertFalse(
            $service->salvar(DistintivoAdquiridoDTO::create(1, 3)),
            "Repetir a concessão é o mesmo estado final, não um erro."
        );
    }

    /**
     * @dataProvider idsInvalidos
     */
    public function testIdsInvalidosSaoRecusadosComRegraDeNegocio(int $idUsuario, int $idDistintivo): void
    {
        $dao = $this->createMock(DistintivoAdquiridoDAO::class);
        $dao->expects($this->never())->method("salvar");

        $service = new DistintivoAdquiridoService($dao);

        /* Antes id 0 passava e só falhava na chave estrangeira, virando 500. */
        $this->expectException(RegraDeNegocioException::class);
        $this->expectExceptionMessage("Distintivo adquirido deve possuir IDs válidos!");

        $service->salvar(DistintivoAdquiridoDTO::create($idUsuario, $idDistintivo));
    }

    public static function idsInvalidos(): array
    {
        return [
            "sem distintivo" => [1, 0],
            "sem usuario" => [0, 3],
            "nenhum dos dois" => [0, 0],
            "negativos" => [-1, -3],
        ];
    }

    // -----------------------------------------------------------------
    // Apoio
    // -----------------------------------------------------------------

    private function criarUsuarioDAOCom(string $fotoPerfil): UsuarioDAO&MockObject
    {
        $usuario = Usuario::rehidratar(
            2,
            "Maria Santos",
            "mariah_dev",
            "maria@email.com",
            "1998-08-20",
            true,
            false,
            false,
            Ocupacao::rehidratar(1, "estudante"),
            $fotoPerfil
        );

        $usuarioDAO = $this->createMock(UsuarioDAO::class);
        $usuarioDAO->method("verificarSeUsuarioExiste")->willReturn(true);
        $usuarioDAO->method("buscarPorId")->willReturn($usuario);

        return $usuarioDAO;
    }

    private function apagarRecursivo(string $caminho): void
    {
        if (!is_dir($caminho)) return;

        foreach (glob(rtrim($caminho, '/') . '/*') ?: [] as $item) {
            is_dir($item) ? $this->apagarRecursivo($item) : unlink($item);
        }

        @rmdir($caminho);
    }
}
