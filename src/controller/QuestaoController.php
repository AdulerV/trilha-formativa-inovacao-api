<?php

class QuestaoController
{
    private QuestaoService $service;
    private MissaoService $missaoService;

    public function __construct(QuestaoService $service, MissaoService $missaoService)
    {
        $this->service = $service;
        $this->missaoService = $missaoService;
    }

    public function salvar(int $idMissao): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $missao = $this->missaoService->buscarPorId($idMissao);

            QuestaoDTO::create($dados, null, $missao);

            $idQuestao = $this->missaoService->atualizar($missao);

            Response::json([
                "mensagem" => "Questão salva com sucesso!",
                "idQuestao" =>  $idQuestao
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

            $resultado = array_map(function ($questao) {
                return QuestaoDTO::toArray($questao);
            }, $lista);

            Response::json($resultado);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }

    public function listarPorMissao(int $idMissao): void
    {
        try {
            $this->missaoService->buscarPorId($idMissao);

            $lista = $this->service->listarPorMissao($idMissao);

            $resultado = array_map(function ($questao) {
                return QuestaoDTO::toArray($questao);
            }, $lista);

            Response::json($resultado);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }

    public function buscarPorId(int $idMissao, int $idQuestao): void
    {
        try {
            $this->missaoService->buscarPorId($idMissao);

            $questao = $this->service->buscarPorId($idQuestao, $idMissao);

            Response::json(QuestaoDTO::toArray($questao));
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 404);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }

    public function atualizar(int $idMissao, int $idQuestao): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $missao = $this->missaoService->buscarPorId($idMissao);

            QuestaoDTO::create($dados, $idQuestao, $missao);

            $this->missaoService->atualizar($missao);

            Response::json([
                "mensagem" => "Questão atualizada com sucesso!"
            ]);
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }

    public function deletar(int $idMissao, int $idQuestao): void
    {
        try {
            $this->service->deletar($idMissao, $idQuestao);

            Response::json([
                "mensagem" => "Questão deletada com sucesso!"
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
