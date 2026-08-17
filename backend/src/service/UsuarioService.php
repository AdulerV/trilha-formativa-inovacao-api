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
        $this->validarCriacao($usuario);
        $this->usuarioDAO->salvar($usuario);
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

    public function verificarSenhaRepeticao(string $senha, string $senhaRepeticao)
    {
        if (trim($senha) !== trim($senhaRepeticao)) {
            throw new RegraDeNegocioException("Senhas não conferem!");
        }
    }

    public function verificarSenhaAtual(int $idUsuario, string $senhaAtual)
    {
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
}
