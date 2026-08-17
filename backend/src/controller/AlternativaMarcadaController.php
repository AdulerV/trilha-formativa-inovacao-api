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
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
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
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
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
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
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
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
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
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }

    public function deletar(array $params): void
    {
        try {
            $idUsuario = (int) ($params["idUsuario"] ?? 0);
            $idAlternativa = (int) ($params["idAlternativa"] ?? 0);

            $this->service->deletar($idUsuario, $idAlternativa);

            Response::json([
                "mensagem" => "Alternativa marcada deletada com sucesso!"
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
