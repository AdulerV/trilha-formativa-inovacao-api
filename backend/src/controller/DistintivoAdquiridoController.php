<?php

class DistintivoAdquiridoController
{
    private DistintivoAdquiridoService $service;

    public function __construct(DistintivoAdquiridoService $service)
    {
        $this->service = $service;
    }

    public function salvar(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $this->service->salvar(DistintivoAdquiridoDTO::create((int) $dados["idUsuario"], (int) $dados["idDistintivo"]));

            Response::json([
                "mensagem" => "Distintivo adquirido salvo com sucesso!"
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

            $resultado = array_map(function ($distintivoAdquirido) {
                return DistintivoAdquiridoDTO::toArray($distintivoAdquirido);
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

            $resultado = array_map(function ($distintivoAdquirido) {
                return DistintivoAdquiridoDTO::toArray($distintivoAdquirido);
            }, $lista);

            Response::json($resultado);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }

    public function buscarPorId(int $idUsuario, int $idDistintivo): void
    {
        try {
            $distintivoAdquirido = $this->service->buscarPorId($idUsuario, $idDistintivo);

            Response::json(DistintivoAdquiridoDTO::toArray($distintivoAdquirido));
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 404);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    }

/*     public function atualizar(int $idUsuario, int $idDistintivoAtual, int $idNovoDistintivo): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $this->service->atualizar(
                DistintivoAdquiridoDTO::create($idUsuario, $idDistintivoAtual),
                DistintivoAdquiridoDTO::create($idUsuario, $idNovoDistintivo),
            );

            Response::json([
                "mensagem" => "Distintivo adquirido atualizado com sucesso!"
            ]);
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Exception $e) {
            Response::error("Erro interno", 500);
        }
    } */

    public function deletar(int $idUsuario, int $idDistintivo): void
    {
        try {
            $this->service->deletar($idUsuario, $idDistintivo);

            Response::json([
                "mensagem" => "Distintivo adquirido deletado com sucesso!"
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
