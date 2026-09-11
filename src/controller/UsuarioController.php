<?php

use Firebase\JWT\JWT;

class UsuarioController
{
    private UsuarioService $service;

    public function __construct(UsuarioService $service)
    {
        $this->service = $service;
    }

    public function login(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true) ?? [];

            $email = $dados["email"] ?? "";
            $senha = $dados["senha"] ?? "";

            $usuario = $this->service->autenticar($email, $senha);

            $payload = [
                "iss" => "trilha-gamificacao",
                "iat" => time(),
                "exp" => time() + (60 * 60 * 8),
                "data" => [
                    "idUsuario" => $usuario->getIdUsuario(),
                    "email"     => $usuario->getCorreioEletronico(),
                    "admin"     => $usuario->isAdmin()
                ]
            ];

            $chaveSecreta = $_ENV['JWT_SECRET'] ?? '';
            $token = JWT::encode($payload, $chaveSecreta, "HS256");

            Response::json([
                "idUsuario" => $usuario->getIdUsuario(),
                "status" => "sucesso",
                "token"  => $token,
                "nomeAventureiro"     => $usuario->getNomeAventureiro(),
                "admin"     => $usuario->isAdmin()
            ]);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 401);
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Usuario] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 500);
        }
    }

    public function atualizarFotoPerfil(int $idUsuario): void
    {
        try {
            if (!isset($_FILES['foto'])) {
                throw new DomainException("Nenhuma imagem foi enviada.");
            }

            $usuario = $this->service->buscarPorId($idUsuario);

            $uploadService = new UploadService(__DIR__ . '/../../public/image/upload/perfil/');
            $caminhoRelativo = $uploadService->salvarImagemPerfil($_FILES['foto'], $usuario->getIdUsuario(), $usuario->getNomeAventureiro());

            $this->service->atualizarFotoPerfil($idUsuario, $caminhoRelativo);

            Response::json([
                "mensagem" => "Foto de perfil atualizada com sucesso!",
            ]);
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 401);
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Usuario] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function removerFotoPerfil(int $idUsuario): void
    {
        try {
            $uploadService = new UploadService(__DIR__ . '/../../public/image/upload/perfil/');

            $this->service->removerFotoPerfil($idUsuario, $uploadService);

            Response::json([
                "mensagem" => "Imagem de perfil removida com sucesso!",
                "fotoPerfil" => null
            ]);
        } catch (DomainException | RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Usuario] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function salvar(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true) ?? [];

            /* No cadastro a senha é obrigatória. */
            if (!isset($dados["senha"]) || trim((string) $dados["senha"]) === "") {
                throw new RegraDeNegocioException("Informe uma senha para o cadastro!");
            }

            $this->service->verificarSenhaRepeticao(
                $dados["senha"],
                $dados["senhaRepeticao"] ?? null
            );

            $this->service->salvar(UsuarioDTO::create($dados, null));

            Response::json([
                "mensagem" => "Usuario salvo com sucesso!"
            ]);
        } catch (DomainException | RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Usuario] %s: %s em %s:%d",
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

            $resultado = array_map(function ($usuario) {
                return UsuarioDTO::toArray($usuario);
            }, $lista);

            Response::json($resultado);
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Usuario] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function buscarPorId(int $id): void
    {
        try {
            $usuario = $this->service->buscarPorId((int) $id);

            Response::json(UsuarioDTO::toArray($usuario));
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 404);
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Usuario] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function atualizar(int $id): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true) ?? [];

            /*
             * A senha atual continua obrigatória para confirmar a edição
             */
            $this->service->verificarSenhaAtual($id, $dados["senhaAtual"] ?? null);

            /*
             * A nova senha é OPCIONAL. A repetição só é conferida
             * quando o usuário realmente informou uma nova senha
             */
            $novaSenha = $dados["novaSenha"] ?? null;
            $desejaAlterarSenha = is_string($novaSenha) && trim($novaSenha) !== "";

            if ($desejaAlterarSenha) {
                $this->service->verificarSenhaRepeticao(
                    $novaSenha,
                    $dados["novaSenhaRepeticao"] ?? null
                );
            }

            $this->service->atualizar(UsuarioDTO::create($dados, $id));

            Response::json([
                "mensagem" => "Usuario atualizado com sucesso!"
            ]);
        } catch (DomainException | RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Usuario] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function deletar(int $id): void
    {
        try {
            $this->service->deletar((int) $id);

            Response::json([
                "mensagem" => "Usuario deletado com sucesso!"
            ]);
        } catch (DomainException | RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Usuario] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    public function alterarPrimeiroAcesso(int $id): void 
    {
        try {
            $this->service->alterarPrimeiroAcesso($id);
    
            Response::json([
                "mensagem" => "Primeiro acesso atualizado com sucesso!"
            ]);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Usuario] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }
}