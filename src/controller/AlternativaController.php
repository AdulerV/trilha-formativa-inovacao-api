<?php

class AlternativaController
{
    private AlternativaService $service;
    private QuestaoService $questaoService;

    public function __construct(AlternativaService $service, QuestaoService $questaoService)
    {
        $this->service = $service;
        $this->questaoService = $questaoService;
    }

    public function salvar(int $idQuestao): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $questao = $this->questaoService->buscarSomentePorId($idQuestao);

            $criadas = AlternativaDTO::create($dados, null, $questao);

            $this->questaoService->atualizar($questao);

            /* Mesmo motivo da questão: o frontend precisa dos IDs gerados. */
            Response::json([
                "mensagem" => "Alternativa salva com sucesso!",
                "alternativas" => array_map(
                    fn(Alternativa $alternativa) => AlternativaDTO::toArray($alternativa),
                    $criadas
                )
            ], 201);
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
                "[Alternativa] %s: %s em %s:%d",
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

            $resultado = array_map(function ($alternativa) {
                return AlternativaDTO::toArray($alternativa);
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
                "[Alternativa] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function listarPorQuestao(int $idQuestao): void
    {
        try {
            $this->questaoService->buscarSomentePorId($idQuestao);

            $lista = $this->service->listarPorQuestao($idQuestao);

            $resultado = array_map(function ($alternativa) {
                return AlternativaDTO::toArray($alternativa);
            }, $lista);

            Response::json($resultado);
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
                "[Alternativa] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function buscarPorId(int $idQuestao, int $idAlternativa): void
    {
        try {
            $this->questaoService->buscarSomentePorId($idQuestao);

            $alternativa = $this->service->buscarPorId($idAlternativa, $idQuestao);

            Response::json(AlternativaDTO::toArray($alternativa));
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
                "[Alternativa] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function atualizar(int $idQuestao, int $idAlternativa): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $questao = $this->questaoService->buscarSomentePorId($idQuestao);

            AlternativaDTO::create($dados, $idAlternativa, $questao);

            $this->questaoService->atualizar($questao);

            Response::json([
                "mensagem" => "Alternativa atualizada com sucesso!"
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
                "[Alternativa] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function deletar(int $idQuestao, int $idAlternativa): void
    {
        try {
            $this->service->deletar($idQuestao, $idAlternativa);

            Response::json([
                "mensagem" => "Alternativa deletada com sucesso!"
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
                "[Alternativa] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }
}
