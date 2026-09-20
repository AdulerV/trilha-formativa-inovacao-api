<?php

class TematicaController
{
    private TematicaService $service;

    public function __construct(TematicaService $service)
    {
        $this->service = $service;
    }

    public function salvar(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $this->service->salvar(TematicaDTO::create($dados, null));

            Response::json([
                "mensagem" => "Temática salva com sucesso!"
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
                "[Tematica] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            // Falha inesperada: o motivo fica no log, e a resposta
            // leva uma mensagem genérica com 500.
            //
            // Devolver $e->getMessage() com 400 fazia duas coisas
            // ruins de uma vez: expunha texto técnico (caminho de
            // arquivo, erro de PDO) e disfarçava a falha de regra de
            // negócio, já que o frontend usa a faixa 4xx para decidir
            // se a mensagem da API pode ser mostrada ao usuário.
            Response::error(
                "Não foi possível concluir a operação. Tente novamente mais tarde.",
                500
            );
        }
    }

    public function listar(): void
    {
        try {
            $lista = $this->service->listar();

            $resultado = array_map(function ($tematica) {
                return TematicaDTO::toArray($tematica);
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
                "[Tematica] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            // Falha inesperada: o motivo fica no log, e a resposta
            // leva uma mensagem genérica com 500.
            //
            // Devolver $e->getMessage() com 400 fazia duas coisas
            // ruins de uma vez: expunha texto técnico (caminho de
            // arquivo, erro de PDO) e disfarçava a falha de regra de
            // negócio, já que o frontend usa a faixa 4xx para decidir
            // se a mensagem da API pode ser mostrada ao usuário.
            Response::error(
                "Não foi possível concluir a operação. Tente novamente mais tarde.",
                500
            );
        }
    }

    public function buscarPorId(int $id): void
    {
        try {
            $tematica = $this->service->buscarPorId((int) $id);

            Response::json(TematicaDTO::toArray($tematica));
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
                "[Tematica] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            // Falha inesperada: o motivo fica no log, e a resposta
            // leva uma mensagem genérica com 500.
            //
            // Devolver $e->getMessage() com 400 fazia duas coisas
            // ruins de uma vez: expunha texto técnico (caminho de
            // arquivo, erro de PDO) e disfarçava a falha de regra de
            // negócio, já que o frontend usa a faixa 4xx para decidir
            // se a mensagem da API pode ser mostrada ao usuário.
            Response::error(
                "Não foi possível concluir a operação. Tente novamente mais tarde.",
                500
            );
        }
    }

    public function atualizar(int $id): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true);

            $this->service->atualizar(TematicaDTO::create($dados, $id));

            Response::json([
                "mensagem" => "Temática atualizada com sucesso!"
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
                "[Tematica] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            // Falha inesperada: o motivo fica no log, e a resposta
            // leva uma mensagem genérica com 500.
            //
            // Devolver $e->getMessage() com 400 fazia duas coisas
            // ruins de uma vez: expunha texto técnico (caminho de
            // arquivo, erro de PDO) e disfarçava a falha de regra de
            // negócio, já que o frontend usa a faixa 4xx para decidir
            // se a mensagem da API pode ser mostrada ao usuário.
            Response::error(
                "Não foi possível concluir a operação. Tente novamente mais tarde.",
                500
            );
        }
    }

    public function deletar(int $id): void
    {
        try {
            $this->service->deletar((int) $id);

            Response::json([
                "mensagem" => "Temática deletada com sucesso!"
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
                "[Tematica] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            // Falha inesperada: o motivo fica no log, e a resposta
            // leva uma mensagem genérica com 500.
            //
            // Devolver $e->getMessage() com 400 fazia duas coisas
            // ruins de uma vez: expunha texto técnico (caminho de
            // arquivo, erro de PDO) e disfarçava a falha de regra de
            // negócio, já que o frontend usa a faixa 4xx para decidir
            // se a mensagem da API pode ser mostrada ao usuário.
            Response::error(
                "Não foi possível concluir a operação. Tente novamente mais tarde.",
                500
            );
        }
    }
}
