<?php
class UsuarioService
{
    private UsuarioDAO $usuarioDAO;
    private OcupacaoDAO $ocupacaoDAO;

    public function __construct(UsuarioDAO $usuarioDAO, OcupacaoDAO $ocupacaoDAO)
    {
        $this->usuarioDAO = $usuarioDAO;
        $this->ocupacaoDAO = $ocupacaoDAO;
    }

    public function autenticar(string $email, string $senha): Usuario
    {
        $usuario = $this->usuarioDAO->buscarPorCorreioEletronico($email);

        if (!$usuario) {
            throw new RegraDeNegocioException("E-mail ou senha inválidos.");
        }

        if (!password_verify($senha, $usuario->getHashSenha())) {
            throw new RegraDeNegocioException("E-mail ou senha inválidos.");
        }

        return $usuario;
    }

    public function atualizarFotoPerfil(int $idUsuario, string $caminhoFoto): void
    {
        $usuarioExiste = $this->usuarioDAO->verificarSeUsuarioExiste($idUsuario);

        if (!$usuarioExiste) {
            throw new RegraDeNegocioException("Usuário não encontrado.");
        }

        $this->usuarioDAO->atualizarFotoPerfil($idUsuario, $caminhoFoto);
    }

    public function salvar(Usuario $usuario): void
    {
        echo $usuario;

        $this->validarCriacao($usuario);
        $this->usuarioDAO->salvar($usuario);
    }

    /**
     * Remove a foto de perfil do usuário.
     *
     * Apaga o arquivo do disco antes de limpar a referência: se a
     * ordem fosse invertida e a limpeza falhasse, o banco apontaria
     * para um arquivo que não existe mais.
     */
    public function removerFotoPerfil(int $idUsuario, UploadService $uploadService): void
    {
        $usuario = $this->buscarPorId($idUsuario);

        $caminhoAtual = $usuario->getFotoPerfil();

        if ($caminhoAtual === null || trim($caminhoAtual) === "") {
            throw new RegraDeNegocioException("Este usuário não possui imagem de perfil.");
        }

        $uploadService->removerImagemPerfil(
            $idUsuario,
            $usuario->getNomeAventureiro(),
            $caminhoAtual
        );

        $this->usuarioDAO->atualizarFotoPerfil($idUsuario, "");
    }


    public function buscarPorId(int $idUsuario): Usuario
    {
        if (!$this->usuarioDAO->verificarSeUsuarioExiste($idUsuario)) {
            throw new RegraDeNegocioException("Usuário não encontrado!");
        }

        $usuario = $this->usuarioDAO->buscarPorId($idUsuario);

        return $usuario;
    }

    public function listar(): array
    {
        return $this->usuarioDAO->listar();
    }

    public function atualizar(Usuario $usuario): void
    {
        $this->validarAtualizacao($usuario);
        $this->usuarioDAO->atualizar($usuario);
    }

    public function deletar(int $idUsuario): void
    {
        if (!$this->usuarioDAO->verificarSeUsuarioExiste($idUsuario)) {
            throw new RegraDeNegocioException("Usuário não encontrado!");
        }

        $this->usuarioDAO->deletar($idUsuario);
    }

    /**
     * Parâmetros nulos porque o corpo da requisição pode não trazer as
     * chaves. Declarados como `string`, a ausência virava TypeError e
     * a requisição respondia 500 sem corpo.
     */
    public function verificarSenhaRepeticao(?string $senha, ?string $senhaRepeticao): void
    {
        if (trim((string) $senha) !== trim((string) $senhaRepeticao)) {
            throw new RegraDeNegocioException("Senhas não conferem!");
        }
    }

    public function verificarSenhaAtual(int $idUsuario, ?string $senhaAtual): void
    {
        if ($senhaAtual === null || trim($senhaAtual) === "") {
            throw new RegraDeNegocioException("Informe a senha atual para continuar!");
        }

        if (!$this->usuarioDAO->verificarSenhaAtual($idUsuario, $senhaAtual)) {
            throw new RegraDeNegocioException("Senha atual não confere!");
        }
    }

    private function validarCriacao(Usuario $usuario): void
    {
        if ($usuario->getIdUsuario() !== null) {
            throw new RegraDeNegocioException("Usuário novo não deve possuir ID!");
        }

        if (!$this->ocupacaoDAO->verificarSeOcupacaoExiste($usuario->getOcupacao()->getIdOcupacao())) {
            throw new RegraDeNegocioException("Usuário novo deve possuir uma ocupação válida!");
        }

        if ($this->usuarioDAO->verificarCorreioEletronicoExiste($usuario->getCorreioEletronico())) {
            throw new RegraDeNegocioException("Email já utilizado!");
        }

        if ($this->usuarioDAO->verificarNomeAventureiroExiste($usuario->getNomeAventureiro())) {
            throw new RegraDeNegocioException("Nome de aventureiro já utilizado!");
        }
    }

    private function validarAtualizacao(Usuario $usuario): void
    {
        if ($usuario->getIdUsuario() === null) {
            throw new RegraDeNegocioException("Usuário deve possuir ID para atualização!");
        }

        if (!$this->ocupacaoDAO->verificarSeOcupacaoExiste($usuario->getOcupacao()->getIdOcupacao())) {
            throw new RegraDeNegocioException("Usuário deve possuir uma ocupação válida!");
        }

        if (!$this->usuarioDAO->verificarSeUsuarioExiste($usuario->getIdUsuario())) {
            throw new RegraDeNegocioException("Usuário não encontrado!");
        }

        if ($this->usuarioDAO->verificarEmailParaOutroUsuario(
            $usuario->getCorreioEletronico(),
            $usuario->getIdUsuario()
        )) {
            throw new RegraDeNegocioException("Email já utilizado por outro usuário!");
        }

        if ($this->usuarioDAO->verificarNomeAventureiroParaOutroUsuario(
            $usuario->getNomeAventureiro(),
            $usuario->getIdUsuario()
        )) {
            throw new RegraDeNegocioException("Nome de aventureiro já utilizado por outro usuário!");
        }
    }

    public function alterarPrimeiroAcesso(int $id): void {
        $this->usuarioDAO->alterarPrimeiroAcesso($id);
    }
}
