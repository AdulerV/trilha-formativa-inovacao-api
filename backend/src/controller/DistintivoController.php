<?php

class DistintivoController
{
    private DistintivoService $service;

    public function __construct(DistintivoService $service)
    {
        $this->service = $service;
    }

    public function salvar(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $this->service->salvar(DistintivoDTO::create($dados, null));

            Response::json([
                "mensagem" => "Distintivo salvo com sucesso!"
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

            $resultado = array_map(function ($distintivo) {
                return DistintivoDTO::toArray($distintivo);
            }, $lista);

            Response::json($resultado);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }

    public function buscarPorId(int $id): void
    {
        try {
            $distintivo = $this->service->buscarPorId((int) $id);

            Response::json(DistintivoDTO::toArray($distintivo));
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

            $this->service->atualizar(DistintivoDTO::create($dados, $id));

            Response::json([
                "mensagem" => "Distintivo atualizado com sucesso!"
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
                "mensagem" => "Distintivo deletado com sucesso!"
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
