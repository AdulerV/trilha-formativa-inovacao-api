<?php

class ProgressoMissaoController
{
    private ProgressoMissaoService $service;
    private UsuarioService $usuarioService;
    private MissaoService $missaoService;

    public function __construct(ProgressoMissaoService $service, UsuarioService $usuarioService, MissaoService $missaoService)
    {
        $this->service = $service;
        $this->usuarioService = $usuarioService;
        $this->missaoService = $missaoService;
    }

    public function salvar(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $idUsuario = (int) $dados["idUsuario"];
            $idMissao = (int) $dados["idMissao"];

            $usuario = $this->usuarioService
                ->buscarPorId($idUsuario);

            $missao = $this->missaoService
                ->buscarPorId($idMissao);

            $this->service->salvar(ProgressoMissaoDTO::create(
                $dados,
                $usuario,
                $missao
            ));

            Response::json([
                "mensagem" => "Progresso da missão salvo com sucesso!"
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
                "[ProgressoMissao] %s: %s em %s:%d",
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

            $resultado = array_map(function ($progresso) {
                return ProgressoMissaoDTO::toArray($progresso);
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
                "[ProgressoMissao] %s: %s em %s:%d",
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

            $resultado = array_map(function ($progressoMissao) {
                return ProgressoMissaoDTO::toArray($progressoMissao);
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
                "[ProgressoMissao] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function buscarPorId(int $idUsuario, int $idMissao): void
    {
        try {
            $progressoMissao = $this->service->buscarPorId($idUsuario, $idMissao);

            Response::json(ProgressoMissaoDTO::toArray($progressoMissao));
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
                "[ProgressoMissao] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function atualizar(int $idUsuario, int $idMissao): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $usuario = $this->usuarioService
                ->buscarPorId($idUsuario);

            $missao = $this->missaoService
                ->buscarPorId($idMissao);

            $this->service->atualizar(ProgressoMissaoDTO::create(
                $dados,
                $usuario,
                $missao
            ));

            Response::json([
                "mensagem" => "Progresso da missão atualizado com sucesso!"
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
                "[ProgressoMissao] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function deletar(int $idUsuario, int $idMissao): void
    {
        try {
            $this->service->deletar($idUsuario, $idMissao);

            Response::json([
                "mensagem" => "Progresso da missão deletado com sucesso!"
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
                "[ProgressoMissao] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }
}
