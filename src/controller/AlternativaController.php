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

            
            AlternativaDTO::create($dados, null, $questao);

            $this->questaoService->atualizar($questao);

            Response::json([
                "mensagem" => "Alternativa salva com sucesso!"
            ]);
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
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
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
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
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
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
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
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
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
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
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }
}
