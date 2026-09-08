<?php
class DistintivoAdquiridoService
{
    private DistintivoAdquiridoDAO $distintivoAdquiridoDAO;

    public function __construct(DistintivoAdquiridoDAO $distintivoAdquiridoDAO)
    {
        $this->distintivoAdquiridoDAO = $distintivoAdquiridoDAO;
    }

    /**
     * Registra a conquista do distintivo.
     *
     * Idempotente: conceder de novo um distintivo que o usuário já
     * possui não é erro, é o mesmo estado final. A verificação de
     * conquista roda a cada conclusão de tarefa com 100%, então esse
     * caso acontece naturalmente — e recusá-lo com 400 fazia a tela de
     * conclusão exibir falha em uma operação que não tinha nada de
     * errado.
     */
    public function salvar(DistintivoAdquirido $distintivoAdquirido): bool
    {
        $this->validarCriacao($distintivoAdquirido);

        $idUsuario = $distintivoAdquirido->getUsuario()->getIdUsuario();
        $idDistintivo = $distintivoAdquirido->getDistintivo()->getIdDistintivo();

        if ($this->distintivoAdquiridoDAO->verificarSeDistintivoAdquiridoExiste($idUsuario, $idDistintivo)) {
            return false;
        }

        $this->distintivoAdquiridoDAO->salvar($distintivoAdquirido);

        return true;
    }

    public function buscarPorId(int $idUsuario, int $idDistintivo): DistintivoAdquirido
    {
        if (!$this->distintivoAdquiridoDAO->verificarSeDistintivoAdquiridoExiste($idUsuario, $idDistintivo)) {
            throw new RegraDeNegocioException("Distintivo adquirido não encontrado!");
        }

        $distintivoAdquirido = $this->distintivoAdquiridoDAO->buscarPorId($idUsuario, $idDistintivo);

        return $distintivoAdquirido;
    }

    public function listar(): array
    {
        return $this->distintivoAdquiridoDAO->listar();
    }

    public function listarPorUsuario(int $idUsuario): array
    {
        return $this->distintivoAdquiridoDAO->listarPorUsuario($idUsuario);
    }

/*     public function atualizar(DistintivoAdquirido $distintivoAtual, DistintivoAdquirido $novoDistintivo): void
    {
        $this->validarAtualizacao($distintivoAtual);
        $this->distintivoAdquiridoDAO->atualizar($distintivoAtual, $novoDistintivo);
    }
 */
    public function deletar(int $idUsuario, int $idDistintivo): void
    {
        if (!$this->distintivoAdquiridoDAO->verificarSeDistintivoAdquiridoExiste($idUsuario, $idDistintivo)) {
            throw new RegraDeNegocioException("Distintivo adquirido não encontrado!");
        }

        $this->distintivoAdquiridoDAO->deletar($idUsuario, $idDistintivo);
    }

    private function validarCriacao(DistintivoAdquirido $distintivoAdquirido): void
    {
        $idUsuario = $distintivoAdquirido->getUsuario()->getIdUsuario();
        $idDistintivo = $distintivoAdquirido->getDistintivo()->getIdDistintivo();

        /*
         * A checagem era apenas contra null, então um id 0 (corpo sem
         * a chave) passava e só falhava na chave estrangeira, virando
         * 500. Agora vira 400 com mensagem de negócio.
         */
        if (
            $idUsuario === null || $idUsuario <= 0 ||
            $idDistintivo === null || $idDistintivo <= 0
        ) {
            throw new RegraDeNegocioException(
                "Distintivo adquirido deve possuir IDs válidos!"
            );
        }

        /*
         * A duplicidade é tratada em salvar(), devolvendo false em vez
         * de lançar: repetir a concessão não é violação de regra.
         */
    }

    private function validarAtualizacao(DistintivoAdquirido $distintivoAdquirido): void
    {
        if (
            $distintivoAdquirido->getUsuario()->getIdUsuario() === null ||
            $distintivoAdquirido->getDistintivo()->getIdDistintivo() === null
        ) {
            throw new RegraDeNegocioException(
                "Distintivo adquirido deve possuir IDs válidos!"
            );
        }

        if (
            !$this->distintivoAdquiridoDAO->verificarSeDistintivoAdquiridoExiste(
                $distintivoAdquirido->getUsuario()->getIdUsuario(),
                $distintivoAdquirido->getDistintivo()->getIdDistintivo()
            )
        ) {
            throw new RegraDeNegocioException(
                "Distintivo adquirido não encontrado!"
            );
        }
    }
}
