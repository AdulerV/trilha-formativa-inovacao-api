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
    public function enviarRecuperacaoSenha(
        Usuario $usuario,
        string $token,
        int $minutosDeValidade
    ): void {
        $link = $this->montarLinkDeRedefinicao($token);

        $nome = htmlspecialchars(
            $usuario->getNomeAventureiro(),
            ENT_QUOTES,
            "UTF-8"
        );

        $linkSeguro = htmlspecialchars(
            $link,
            ENT_QUOTES,
            "UTF-8"
        );

        $nomeAplicacao = htmlspecialchars(
            $this->nomeAplicacao,
            ENT_QUOTES,
            "UTF-8"
        );

        $corpoHtml = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinição de senha</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f1f4d7;
    font-family: Arial, Helvetica, sans-serif;
    color: #281d15;
">

    <div style="
        max-width: 600px;
        margin: 40px auto;
        background-color: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e6c5;
    ">

        <!-- Cabeçalho -->
        <div style="
            background-color: #154c21;
            padding: 28px 32px;
            text-align: center;
        ">
            <h1 style="
                margin: 0;
                color: #ffffff;
                font-size: 24px;
                font-weight: 600;
            ">
                {$nomeAplicacao}
            </h1>
        </div>

        <!-- Conteúdo -->
        <div style="
            padding: 36px 40px;
        ">

            <p style="
                margin: 0 0 20px;
                font-size: 16px;
                line-height: 1.6;
                color: #281d15;
            ">
                Olá, <strong>{$nome}</strong>!
            </p>

            <p style="
                margin: 0 0 20px;
                font-size: 15px;
                line-height: 1.6;
                color: #281d15;
            ">
                Recebemos um pedido para redefinir a senha da sua
                conta na plataforma <strong>{$nomeAplicacao}</strong>.
            </p>

            <p style="
                margin: 0 0 28px;
                font-size: 15px;
                line-height: 1.6;
                color: #281d15;
            ">
                Para cadastrar uma nova senha, clique no botão abaixo:
            </p>

            <!-- Botão principal -->
            <div style="
                text-align: center;
                margin: 0 0 30px;
            ">
                <a href="{$linkSeguro}" style="
                    display: inline-block;
                    padding: 14px 30px;
                    background-color: #2f9e41;
                    color: #ffffff;
                    text-decoration: none;
                    font-size: 15px;
                    font-weight: bold;
                    border-radius: 8px;
                ">
                    Redefinir minha senha
                </a>
            </div>

            <!-- Link alternativo -->
            <div style="
                padding: 18px 20px;
                background-color: #f1f4d7;
                border-left: 4px solid #2f9e41;
                border-radius: 6px;
            ">
                <p style="
                    margin: 0;
                    font-size: 13px;
                    line-height: 1.6;
                    color: #281d15;
                ">
                    <strong>Problemas com o botão?</strong><br>
                    Você também pode acessar a redefinição de senha
                    através deste
                    <a href="{$linkSeguro}" style="
                        color: #1100FF;
                        font-weight: bold;
                        text-decoration: underline;
                    ">
                        link alternativo
                    </a>.
                </p>
            </div>

            <!-- Validade -->
            <div style="
                margin-top: 28px;
                padding: 16px 18px;
                background-color: #f1f4d7;
                border-radius: 8px;
            ">
                <p style="
                    margin: 0;
                    font-size: 13px;
                    line-height: 1.6;
                    color: #154c21;
                ">
                    <strong>Validade do link</strong><br>
                    Por segurança, este link é de uso único e expira
                    em <strong>{$minutosDeValidade} minutos</strong>.
                </p>
            </div>

            <p style="
                margin: 24px 0 0;
                font-size: 13px;
                line-height: 1.6;
                color: #281d15;
            ">
                Se você não solicitou a redefinição da sua senha,
                pode ignorar este e-mail. Sua senha atual continuará
                válida.
            </p>

        </div>

        <!-- Rodapé -->
        <div style="
            padding: 20px 32px;
            background-color: #154c21;
            text-align: center;
        ">
            <p style="
                margin: 0;
                font-size: 12px;
                line-height: 1.5;
                color: #ffffff;
            ">
                Este é um e-mail automático.
                Por favor, não responda a esta mensagem.
            </p>
        </div>

    </div>

</body>
</html>
HTML;

        $corpoTexto = "Olá, {$usuario->getNomeAventureiro()}!\n\n"
            . "Recebemos um pedido para redefinir a senha da sua conta "
            . "na plataforma {$this->nomeAplicacao}.\n\n"
            . "Acesse o endereço abaixo para cadastrar uma nova senha:\n"
            . "{$link}\n\n"
            . "O link é de uso único e expira em "
            . "{$minutosDeValidade} minutos.\n\n"
            . "Se você não solicitou a redefinição da sua senha, "
            . "pode ignorar este e-mail. Sua senha atual continuará válida.";

        $this->enviar(
            $usuario->getCorreioEletronico(),
            $usuario->getNomeAventureiro(),
            "Redefinição de senha - {$this->nomeAplicacao}",
            $corpoHtml,
            $corpoTexto
        );
    }

    /**
     * Envia o código de verificação de e-mail usado no cadastro.
     *
     * Recebe o endereço em vez de um objeto Usuario porque, neste
     * ponto do fluxo, a conta ainda não existe: é justamente disso que
     * se trata a verificação.
     *
     * O código aparece grande e espaçado no corpo da mensagem porque
     * será digitado à mão — e a fonte monoespaçada evita a confusão
     * clássica entre 0 e O, 1 e l.
     */
    public function enviarCodigoVerificacao(
        string $destinatario,
        string $codigo,
        int $minutosDeValidade
    ): void {
        $codigoSeguro = htmlspecialchars(
            $codigo,
            ENT_QUOTES,
            "UTF-8"
        );

        $nomeAplicacao = htmlspecialchars(
            $this->nomeAplicacao,
            ENT_QUOTES,
            "UTF-8"
        );

        $corpoHtml = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de e-mail</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f1f4d7;
    font-family: Arial, Helvetica, sans-serif;
    color: #281d15;
">

    <div style="
        max-width: 600px;
        margin: 40px auto;
        background-color: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e6c5;
    ">

        <!-- Cabeçalho -->
        <div style="
            background-color: #154c21;
            padding: 28px 32px;
            text-align: center;
        ">
            <h1 style="
                margin: 0;
                color: #ffffff;
                font-size: 24px;
                font-weight: 600;
            ">
                {$nomeAplicacao}
            </h1>
        </div>

        <!-- Conteúdo -->
        <div style="
            padding: 36px 40px;
        ">

            <p style="
                margin: 0 0 20px;
                font-size: 16px;
                line-height: 1.6;
                color: #281d15;
            ">
                Olá, futuro aventureiro!
            </p>

            <p style="
                margin: 0 0 28px;
                font-size: 15px;
                line-height: 1.6;
                color: #281d15;
            ">
                Para concluir seu cadastro na plataforma
                <strong>{$nomeAplicacao}</strong>, confirme este
                endereço de e-mail informando o código abaixo:
            </p>

            <!-- Código -->
            <div style="
                text-align: center;
                margin: 0 0 30px;
            ">
                <div style="
                    display: inline-block;
                    padding: 18px 34px;
                    background-color: #f1f4d7;
                    border: 2px dashed #2f9e41;
                    border-radius: 10px;
                ">
                    <span style="
                        font-family: 'Courier New', Courier, monospace;
                        font-size: 34px;
                        font-weight: bold;
                        letter-spacing: 10px;
                        color: #154c21;
                    ">
                        {$codigoSeguro}
                    </span>
                </div>
            </div>

            <!-- Validade -->
            <div style="
                padding: 16px 18px;
                background-color: #f1f4d7;
                border-left: 4px solid #2f9e41;
                border-radius: 6px;
            ">
                <p style="
                    margin: 0;
                    font-size: 13px;
                    line-height: 1.6;
                    color: #154c21;
                ">
                    <strong>Validade do código</strong><br>
                    Por segurança, este código expira em
                    <strong>{$minutosDeValidade} minutos</strong> e só
                    pode ser usado uma vez.
                </p>
            </div>

            <p style="
                margin: 24px 0 0;
                font-size: 13px;
                line-height: 1.6;
                color: #281d15;
            ">
                Se você não tentou criar uma conta na plataforma, pode
                ignorar este e-mail. Nenhum cadastro será feito sem que
                o código acima seja informado.
            </p>

        </div>

        <!-- Rodapé -->
        <div style="
            padding: 20px 32px;
            background-color: #154c21;
            text-align: center;
        ">
            <p style="
                margin: 0;
                font-size: 12px;
                line-height: 1.5;
                color: #ffffff;
            ">
                Este é um e-mail automático.
                Por favor, não responda a esta mensagem.
            </p>
        </div>

    </div>

</body>
</html>
HTML;

        $corpoTexto = "Olá, futuro aventureiro!\n\n"
            . "Para concluir seu cadastro na plataforma {$this->nomeAplicacao}, "
            . "confirme este endereço de e-mail informando o código abaixo:\n\n"
            . "    {$codigo}\n\n"
            . "O código expira em {$minutosDeValidade} minutos e só pode "
            . "ser usado uma vez.\n\n"
            . "Se você não tentou criar uma conta na plataforma, pode ignorar "
            . "este e-mail. Nenhum cadastro será feito sem que o código acima "
            . "seja informado.";

        $this->enviar(
            $destinatario,
            $destinatario,
            "Confirme seu e-mail - {$this->nomeAplicacao}",
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
