<?php

declare(strict_types=1);

class AlternativaMarcadaController
{
    private AlternativaMarcadaService $service;

    public function __construct(AlternativaMarcadaService $service)
    {
        $this->service = $service;
    }

    public function salvar(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true) ?? [];

            $this->service->salvar(
                AlternativaMarcadaDTO::create(
                    (int) ($dados["idUsuario"] ?? 0),
                    (int) ($dados["idAlternativa"] ?? 0),
                    $dados
                )
            );

            Response::json([
                "mensagem" => "Alternativa marcada salva com sucesso!"
            ]);
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            /*
             * A mensagem genérica protege o usuário, mas o motivo
             * precisa ficar registrado: era exatamente essa perda
             * de informação que tornava o 500 impossível de
             * diagnosticar.
             */
            error_log(sprintf(
                "[AlternativaMarcada] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function listar(): void
    {
        try {
            $lista = $this->service->listar();

            $resultado = array_map(function ($alternativaMarcada) {
                return AlternativaMarcadaDTO::toArray($alternativaMarcada);
            }, $lista);

            Response::json($resultado);
        } catch (Throwable $e) {
            /*
             * A mensagem genérica protege o usuário, mas o motivo
             * precisa ficar registrado: era exatamente essa perda
             * de informação que tornava o 500 impossível de
             * diagnosticar.
             */
            error_log(sprintf(
                "[AlternativaMarcada] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function listarPorUsuario(int $idUsuario): void
    {
        try {
            $lista = $this->service->listarPorUsuario($idUsuario);

            $resultado = array_map(function ($alternativaMarcada) {
                return AlternativaMarcadaDTO::toArray($alternativaMarcada);
            }, $lista);

            Response::json($resultado);
        } catch (Throwable $e) {
            /*
             * A mensagem genérica protege o usuário, mas o motivo
             * precisa ficar registrado: era exatamente essa perda
             * de informação que tornava o 500 impossível de
             * diagnosticar.
             */
            error_log(sprintf(
                "[AlternativaMarcada] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function buscarPorId(int $idUsuario, int $idAlternativa): void
    {
        try {
            $alternativaMarcada = $this->service->buscarPorId($idUsuario, $idAlternativa);

            Response::json(AlternativaMarcadaDTO::toArray($alternativaMarcada));
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 404);
        } catch (Throwable $e) {
            /*
             * A mensagem genérica protege o usuário, mas o motivo
             * precisa ficar registrado: era exatamente essa perda
             * de informação que tornava o 500 impossível de
             * diagnosticar.
             */
            error_log(sprintf(
                "[AlternativaMarcada] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function atualizar(int $idUsuario, int $idAlternativa): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true) ?? [];

            $alternativaMarcada = AlternativaMarcadaDTO::create($idUsuario, $idAlternativa, $dados);

            $this->service->atualizar($idUsuario, $idAlternativa, $alternativaMarcada);

            Response::json([
                "mensagem" => "Alternativa marcada atualizada com sucesso!"
            ]);
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            /*
             * A mensagem genérica protege o usuário, mas o motivo
             * precisa ficar registrado: era exatamente essa perda
             * de informação que tornava o 500 impossível de
             * diagnosticar.
             */
            error_log(sprintf(
                "[AlternativaMarcada] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * O Router entrega os parâmetros da rota como argumentos posicionais
     * (call_user_func_array com os grupos do regex). A assinatura
     * anterior recebia um array e, ao chegar um int, o PHP lançava
     * TypeError — que não é Exception e escapava dos catch, derrubando
     * a requisição com 500 sem corpo. O DELETE de alternativa marcada
     * nunca funcionou.
     */
    public function deletar(int $idUsuario, int $idAlternativa): void
    {
        try {
            $this->service->deletar($idUsuario, $idAlternativa);

            Response::json([
                "mensagem" => "Alternativa marcada deletada com sucesso!"
            ]);
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            /*
             * A mensagem genérica protege o usuário, mas o motivo
             * precisa ficar registrado: era exatamente essa perda
             * de informação que tornava o 500 impossível de
             * diagnosticar.
             */
            error_log(sprintf(
                "[AlternativaMarcada] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }
}
