<?php

class MissaoController
{
    private MissaoService $service;

    public function __construct(MissaoService $service)
    {
        $this->service = $service;
    }

    public function salvar(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $this->service->salvar(MissaoDTO::create($dados, null));

            Response::json([
                "mensagem" => "Missão salva com sucesso!"
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

            $resultado = array_map(function ($missao) {
                return MissaoDTO::toArray($missao);
            }, $lista);

            Response::json($resultado);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }

    public function buscarPorId(int $id): void
    {
        try {
            $missao = $this->service->buscarPorId((int) $id);

            Response::json(MissaoDTO::toArray($missao));
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 404);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }

    public function atualizar(int $id): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $this->service->atualizar(MissaoDTO::create($dados, $id));

            Response::json([
                "mensagem" => "Missão atualizada com sucesso!"
            ]);
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }

    public function deletar(int $id): void
    {
        try {
            $this->service->deletar((int) $id);

            Response::json([
                "mensagem" => "Missão deletada com sucesso!"
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
