<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Responsável pelo envio das mensagens transacionais da plataforma.
 *
 * Toda a configuração vem do arquivo .env, de modo que a troca do
 * provedor de SMTP não exige alteração de código.
 */
class EmailService
{
    private string $host;
    private int $porta;
    private string $usuario;
    private string $senha;
    private string $criptografia;
    private string $remetenteEndereco;
    private string $remetenteNome;
    private int $nivelDepuracao;
    private string $urlFrontend;
    private string $nomeAplicacao;

    public function __construct(?array $configuracao = null)
    {
        $configuracao = $configuracao ?? $_ENV;

        $this->host              = trim((string) ($configuracao["MAIL_HOST"] ?? ""));
        $this->porta            = (int) ($configuracao["MAIL_PORT"] ?? 587);
        $this->usuario          = (string) ($configuracao["MAIL_USERNAME"] ?? "");
        $this->senha            = (string) ($configuracao["MAIL_PASSWORD"] ?? "");
        $this->criptografia     = strtolower((string) ($configuracao["MAIL_ENCRYPTION"] ?? "tls"));
        $this->remetenteEndereco = (string) ($configuracao["MAIL_FROM_ADDRESS"] ?? "");
        $this->remetenteNome     = (string) ($configuracao["MAIL_FROM_NAME"] ?? "Trilha Formativa de Inovação");
        $this->nivelDepuracao    = (int) ($configuracao["MAIL_SMTP_DEBUG"] ?? 0);
        $this->urlFrontend       = rtrim((string) ($configuracao["APP_FRONTEND_URL"] ?? ""), "/");
        $this->nomeAplicacao     = (string) ($configuracao["APP_NAME"] ?? "Trilha Formativa de Inovação");
    }

    /**
     * Envia o e-mail com o link de redefinição de senha.
     *
     * O link é montado a partir de APP_FRONTEND_URL e nunca a partir
     * do cabeçalho Host da requisição — é assim que se evita o Host
     * Header Injection, em que um atacante induz a API a emitir um
     * link apontando para um domínio controlado por ele.
     */
    public function enviarRecuperacaoSenha(Usuario $usuario, string $token, int $minutosDeValidade): void
    {
        $link = $this->montarLinkDeRedefinicao($token);

        $nome = htmlspecialchars($usuario->getNomeAventureiro(), ENT_QUOTES, "UTF-8");
        $linkSeguro = htmlspecialchars($link, ENT_QUOTES, "UTF-8");

        $corpoHtml = <<<HTML
        <p>Olá, <strong>{$nome}</strong>!</p>
        <p>Recebemos um pedido para redefinir a senha da sua conta na plataforma {$this->nomeAplicacao}.</p>
        <p><a href="{$linkSeguro}">Clique aqui para cadastrar uma nova senha</a></p>
        <p>Se o botão não funcionar, copie e cole o endereço abaixo no seu navegador:</p>
        <p>{$linkSeguro}</p>
        <p>O link é de uso único e expira em {$minutosDeValidade} minutos.</p>
        <p>Se você não solicitou a redefinição, ignore esta mensagem: sua senha atual continua valendo.</p>
        HTML;

        $corpoTexto = "Olá, {$usuario->getNomeAventureiro()}!\n\n"
            . "Recebemos um pedido para redefinir a senha da sua conta na plataforma {$this->nomeAplicacao}.\n\n"
            . "Acesse o endereço abaixo para cadastrar uma nova senha:\n{$link}\n\n"
            . "O link é de uso único e expira em {$minutosDeValidade} minutos.\n\n"
            . "Se você não solicitou a redefinição, ignore esta mensagem: sua senha atual continua valendo.";

        $this->enviar(
            $usuario->getCorreioEletronico(),
            $usuario->getNomeAventureiro(),
            "Redefinição de senha - {$this->nomeAplicacao}",
            $corpoHtml,
            $corpoTexto
        );
    }

    /**
     * Avisa o usuário de que a senha foi alterada.
     *
     * Recomendação do OWASP: a confirmação permite que a vítima de um
     * comprometimento perceba a alteração. A mensagem jamais contém a
     * senha nova.
     */
    public function enviarConfirmacaoAlteracaoSenha(Usuario $usuario): void
    {
        $nome = htmlspecialchars($usuario->getNomeAventureiro(), ENT_QUOTES, "UTF-8");
        $momento = (new DateTime())->format("d/m/Y H:i");

        $corpoHtml = <<<HTML
        <p>Olá, <strong>{$nome}</strong>!</p>
        <p>A senha da sua conta na plataforma {$this->nomeAplicacao} foi alterada em {$momento}.</p>
        <p>Se foi você, nenhuma ação é necessária.</p>
        <p>Se não foi você, procure imediatamente a equipe responsável pela plataforma.</p>
        HTML;

        $corpoTexto = "Olá, {$usuario->getNomeAventureiro()}!\n\n"
            . "A senha da sua conta na plataforma {$this->nomeAplicacao} foi alterada em {$momento}.\n\n"
            . "Se foi você, nenhuma ação é necessária.\n"
            . "Se não foi você, procure imediatamente a equipe responsável pela plataforma.";

        $this->enviar(
            $usuario->getCorreioEletronico(),
            $usuario->getNomeAventureiro(),
            "Sua senha foi alterada - {$this->nomeAplicacao}",
            $corpoHtml,
            $corpoTexto
        );
    }

    public function montarLinkDeRedefinicao(string $token): string
    {
        if ($this->urlFrontend === "") {
            throw new RuntimeException("APP_FRONTEND_URL não configurada no arquivo .env.");
        }

        return $this->urlFrontend . "/redefinir-senha?token=" . rawurlencode($token);
    }

    protected function enviar(
        string $destinatarioEndereco,
        string $destinatarioNome,
        string $assunto,
        string $corpoHtml,
        string $corpoTexto
    ): void {
        $this->validarConfiguracao();

        $mail = $this->criarMailer();

        try {
            $mail->isSMTP();
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Host = $this->host;
            $mail->Port = $this->porta;
            $mail->SMTPDebug = $this->nivelDepuracao;

            if ($this->usuario !== "") {
                $mail->SMTPAuth = true;
                $mail->Username = $this->usuario;
                $mail->Password = $this->senha;

                // Sem isto, o PHPMailer tenta CRAM-MD5 primeiro (ordem
                // padrão da biblioteca) e não tenta outro método se
                // falhar. O suporte a CRAM-MD5 de vários provedores de
                // teste (Mailtrap incluso) é instável e derruba a
                // autenticação mesmo com credenciais corretas — LOGIN é
                // universalmente suportado e evita o problema.
                $mail->AuthType = "LOGIN";
            }

            if ($this->criptografia === "ssl") {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($this->criptografia === "tls") {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->setFrom($this->remetenteEndereco, $this->remetenteNome);
            $mail->addAddress($destinatarioEndereco, $destinatarioNome);

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $corpoHtml;
            $mail->AltBody = $corpoTexto;

            $mail->send();
        } catch (PHPMailerException $e) {
            throw new RuntimeException("Falha no envio do e-mail: " . $mail->ErrorInfo, 0, $e);
        }
    }

    /**
     * Isolado em um método próprio para permitir a substituição da
     * instância nos testes automatizados.
     */
    protected function criarMailer(): PHPMailer
    {
        return new PHPMailer(true);
    }

    private function validarConfiguracao(): void
    {
        if ($this->host === "") {
            throw new RuntimeException("MAIL_HOST não configurado no arquivo .env.");
        }

        if ($this->remetenteEndereco === "") {
            throw new RuntimeException("MAIL_FROM_ADDRESS não configurado no arquivo .env.");
        }
    }
}