<?php

declare(strict_types=1);

class Usuario
{
    private ?int $idUsuario = null;
    private string $nomeUsuario;
    private string $nomeAventureiro;
    private string $correioEletronico;

    /*
     * Inicializadas para que uma entidade lida do banco sem hash de
     * senha não estoure "must not be accessed before initialization"
     * — Error, que não é Exception e por isso escapava dos catch dos
     * controllers e virava 500 sem corpo JSON.
     */
    private string $senha = "";
    private ?DateTime $dataNascimento = null;
    private ?bool $possuiConhecimento = null;
    private ?string $fotoPerfil = null;
    private bool $primeiroAcesso;
    private bool $admin;
    private Ocupacao $ocupacao;
    private string $hashSenha = "";

    public function __construct(
        ?int $idUsuario,
        string $nomeUsuario,
        string $nomeAventureiro,
        string $correioEletronico,
        ?string $dataNascimento,
        ?bool $possuiConhecimento,
        bool $primeiroAcesso,
        bool $admin,
        /*
         * Nulo significa "nenhuma senha informada", situação legítima
         * na edição de perfil: quem só troca o nome não deve ter a
         * senha reprocessada. Quem decide gravar ou não o hash é o
         * serviço, olhando temSenhaDefinida().
         */
        ?string $senha,
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

    /**
     * Reconstrói um usuário já persistido, sem repetir as validações
     * de criação.
     *
     * As regras dos setters existem para barrar dado ruim na ENTRADA.
     * Aplicá-las de novo na LEITURA transforma qualquer registro
     * antigo ou fora do padrão atual em erro 500 na listagem inteira:
     * bastava um usuário cadastrado com nome de uma única palavra para
     * derrubar GET /api/v1/progresso-missao, GET /api/v1/usuarios e o
     * ranking junto com eles.
     *
     * Também evita o password_hash("Senha@123") que os DAOs faziam
     * apenas para satisfazer setSenha(): eram ~180 ms de bcrypt por
     * linha retornada, gastos para produzir um hash descartável.
     */
    public static function rehidratar(
        int $idUsuario,
        string $nomeUsuario,
        string $nomeAventureiro,
        string $correioEletronico,
        ?string $dataNascimento,
        ?bool $possuiConhecimento,
        bool $primeiroAcesso,
        bool $admin,
        Ocupacao $ocupacao,
        ?string $fotoPerfil = null,
        ?string $hashSenha = null
    ): self {
        $usuario = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();

        $usuario->idUsuario = $idUsuario;
        $usuario->nomeUsuario = $nomeUsuario;
        $usuario->nomeAventureiro = $nomeAventureiro;
        $usuario->correioEletronico = $correioEletronico;
        $usuario->possuiConhecimento = $possuiConhecimento;
        $usuario->primeiroAcesso = $primeiroAcesso;
        $usuario->admin = $admin;
        $usuario->ocupacao = $ocupacao;
        $usuario->fotoPerfil = $fotoPerfil;

        $usuario->dataNascimento = self::converterDataNascimento($dataNascimento);

        /*
         * A senha em claro não existe na leitura. O hash persistido é
         * atribuído aos dois campos porque verificarSenha() compara
         * contra $senha e o login usa getHashSenha().
         */
        if ($hashSenha !== null && $hashSenha !== "") {
            $usuario->senha = $hashSenha;
            $usuario->hashSenha = $hashSenha;
        }

        return $usuario;
    }

    private static function converterDataNascimento(?string $dataNascimento): ?DateTime
    {
        if ($dataNascimento === null || $dataNascimento === "") {
            return null;
        }

        $data = DateTime::createFromFormat('Y-m-d', $dataNascimento);

        return $data === false ? null : $data;
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
        if ($this->senha === "") {
            return false;
        }

        return password_verify($senha, $this->senha);
    }

    /**
     * Define a senha, quando houver uma.
     *
     * `null` e string vazia significam "não informada" e não geram
     * hash algum. Qualquer valor informado passa pela política
     * completa: mínimo de 8 caracteres, com letra, número e caractere
     * especial. A política não foi afrouxada — apenas deixou de ser
     * obrigatória em uma atualização que não mexe na senha.
     */
    public function setSenha(?string $senha): self
    {
        if ($senha === null || $senha === "") {
            return $this;
        }

        if (
            strlen($senha) < 8 ||
            strlen($senha) > 255 ||
            !preg_match('/\W/', $senha) ||
            !preg_match('/\d/', $senha) ||
            !preg_match('/[a-zA-Z]/', $senha)
        ) {
            throw new DomainException("Senha inválida!");
        }

        $this->senha = password_hash($senha, PASSWORD_DEFAULT);

        return $this;
    }

    /**
     * Informa se a entidade carrega um hash de senha a ser gravado.
     *
     * O DAO usa isso para montar o UPDATE com ou sem a coluna
     * HashSenha, em vez de sobrescrevê-la sempre.
     */
    public function temSenhaDefinida(): bool
    {
        return $this->senha !== "";
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
