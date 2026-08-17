<?php

declare(strict_types=1);

class Usuario
{
    private ?int $idUsuario = null;
    private string $nomeUsuario;
    private string $nomeAventureiro;
    private string $correioEletronico;
    private string $senha;
    private ?DateTime $dataNascimento = null;
    private ?bool $possuiConhecimento = null;
    private ?string $fotoPerfil = null;
    private bool $primeiroAcesso;
    private bool $admin;
    private Ocupacao $ocupacao;
    private string $hashSenha;

    public function __construct(
        ?int $idUsuario,
        string $nomeUsuario,
        string $nomeAventureiro,
        string $correioEletronico,
        ?string $dataNascimento,
        ?bool $possuiConhecimento,
        bool $primeiroAcesso,
        bool $admin,
        string $senha,
        Ocupacao $ocupacao
    ) {
        $this->setIdUsuario($idUsuario);
        $this->setNomeUsuario($nomeUsuario);
        $this->setNomeAventureiro($nomeAventureiro);
        $this->setCorreioEletronico($correioEletronico);
        $this->setDataNascimento($dataNascimento);
        $this->setPossuiConhecimento($possuiConhecimento);
        $this->setPrimeiroAcesso($primeiroAcesso);
        $this->setSenha($senha);
        $this->setOcupacao($ocupacao);
        $this->setAdmin($admin);
    }

    public function getIdUsuario(): ?int
    {
        return $this->idUsuario;
    }

    public function setIdUsuario(?int $idUsuario): self
    {
        if ($this->idUsuario !== null && $idUsuario !== $this->idUsuario) {
            throw new Exception("ID já definido!");
        }

        $this->idUsuario = $idUsuario;

        return $this;
    }

    public function getNomeUsuario(): string
    {
        return $this->nomeUsuario;
    }

    public function setNomeUsuario(string $nomeUsuario): self
    {
        if (empty(trim($nomeUsuario)) or count(explode(" ", $nomeUsuario)) == 1) {
            throw new DomainException("Nome de usuário inválido!");
        }

        $this->nomeUsuario = trim($nomeUsuario);

        return $this;
    }

    public function getNomeAventureiro(): string
    {
        return $this->nomeAventureiro;
    }

    public function setNomeAventureiro(string $nomeAventureiro): self
    {
        $nomeAventureiro = str_replace(" ", "", $nomeAventureiro);
        $nomeAventureiro = strtolower($nomeAventureiro);
        $nomeAventureiro =  preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $nomeAventureiro);

        if (empty(trim($nomeAventureiro))) {
            throw new DomainException("Nome de aventureiro inválido!");
        }

        $this->nomeAventureiro = trim($nomeAventureiro);

        return $this;
    }

    public function getCorreioEletronico(): string
    {
        return $this->correioEletronico;
    }

    public function setCorreioEletronico(string $correioEletronico): self
    {
        if (!filter_var($correioEletronico, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException("E-mail inválido!");
        }

        $this->correioEletronico = strtolower(trim($correioEletronico));

        return $this;
    }

    public function verificarSenha(string $senha): bool
    {
        return password_verify($senha, $this->senha);
    }

    public function setSenha(string $senha): self
    {
        if (
            strlen($senha) < 8 ||
            !preg_match('/\W/', $senha) ||
            !preg_match('/\d/', $senha) ||
            !preg_match('/[a-zA-Z]/', $senha)
        ) {
            throw new DomainException("Senha inválida!");
        }

        $this->senha = password_hash($senha, PASSWORD_DEFAULT);

        return $this;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    public function getDataNascimento(): ?DateTime
    {
        return $this->dataNascimento;
    }

    public function setDataNascimento(?string $dataNascimento): self
    {
        if ($dataNascimento === null) {
            $this->dataNascimento = null;
            return $this;
        }

        $data = DateTime::createFromFormat('Y-m-d', $dataNascimento);

        if (!$data) {
            throw new DomainException("Data de nascimento inválida!");
        }

        if ($data > new DateTime()) {
            throw new DomainException("Data de nascimento inválida!");
        }

        $this->dataNascimento = $data;

        return $this;
    }

    public function isPossuiConhecimento(): ?bool
    {
        return $this->possuiConhecimento;
    }

    public function setPossuiConhecimento(?bool $possuiConhecimento): self
    {
        $this->possuiConhecimento = $possuiConhecimento;

        return $this;
    }

    public function isPrimeiroAcesso(): bool
    {
        return $this->primeiroAcesso;
    }

    public function setPrimeiroAcesso(bool $primeiroAcesso): self
    {
        $this->primeiroAcesso = $primeiroAcesso;

        return $this;
    }

    public function getOcupacao(): Ocupacao
    {
        return $this->ocupacao;
    }

    public function setOcupacao(Ocupacao $ocupacao): self
    {
        $this->ocupacao = $ocupacao;

        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getHashSenha(): string
    {
        return $this->hashSenha;
    }

    public function setHashSenha(string $hashSenha): self
    {
        $this->hashSenha = $hashSenha;

        return $this;
    }

    public function getFotoPerfil(): ?string
    {
        return $this->fotoPerfil;
    }

    public function setFotoPerfil(?string $fotoPerfil): self
    {
        $this->fotoPerfil = $fotoPerfil;

        return $this;
    }
}
