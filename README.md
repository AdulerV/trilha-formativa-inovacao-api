# Trilha Formativa de Inovação — API

<p align="center">

API responsável pelo fornecimento, gerenciamento e persistência dos dados da plataforma **Trilha Formativa de Inovação**, disponibilizando os serviços necessários para o funcionamento da aplicação frontend.

</p>

---

## Tecnologias utilizadas

Este projeto foi desenvolvido utilizando:

* **PHP 8**
* **Composer**
* **MySQL**
* **JWT (JSON Web Token)**
* **PHPMailer (SMTP)**
* **API RESTful**
* **PDO**

---

## Pré-requisitos

Antes de iniciar, é necessário possuir as seguintes ferramentas instaladas:

* **PHP 8**
* **Composer**
* **MySQL**

Para verificar se estão instalados corretamente:

```bash
php -v
composer -V
mysql --version
```

> **Observação:** caso esteja utilizando o XAMPP, o PHP e o MySQL já são disponibilizados pelo pacote. Nesse caso, é necessário apenas verificar se as versões e configurações utilizadas pelo projeto estão corretas.

---

### Espaço em disco

Recomenda-se possuir **pelo menos 1 GB de espaço livre em disco para este projeto**.

Esse espaço contempla a aplicação, suas dependências, arquivos de configuração, banco de dados e arquivos temporários utilizados durante a execução.

Para a instalação completa da plataforma, considerando **Frontend e API**, recomenda-se reservar aproximadamente **2 GB de espaço livre**, sendo:

* **Frontend:** aproximadamente 1 GB
* **API:** aproximadamente 1 GB

> **Observação:** o espaço necessário pode variar de acordo com o ambiente de desenvolvimento e com o volume de dados armazenado durante a utilização da plataforma.

## Instalação

### Clone o repositório

```bash
git clone https://github.com/AdulerV/trilha-formativa-inovacao-api.git
```

### Acesse a pasta do projeto

```bash
cd trilha-formativa-inovacao-api
```

### Dependências

O projeto já disponibiliza no repositório os arquivos `composer.json` e `composer.lock`, responsáveis pelo gerenciamento e registro das dependências utilizadas pela API.

A pasta `vendor/`, contendo as dependências instaladas pelo Composer, também é disponibilizada no projeto.

Dessa forma, **não é necessário executar `composer install` para uma configuração inicial da aplicação**.

Caso seja necessário reinstalar ou atualizar as dependências, o Composer pode ser utilizado através do comando:

```bash
composer install
```

> **Observação:** o `composer.json` e o `composer.lock` devem ser mantidos no repositório para registrar as dependências e suas respectivas versões.

---

## Configuração do ambiente

A API utiliza um arquivo `.env` para armazenar as configurações específicas do ambiente de execução.

Crie um arquivo `.env` na raiz do projeto:

```text
.env
```

Entre as configurações utilizadas pela aplicação estão:

* Chave utilizada para autenticação JWT;
* Tempo de expiração dos tokens;
* Outras configurações específicas do ambiente.

Exemplo de estrutura:

```env
APP_NAME="Trilha Formativa de Inovação"
APP_FRONTEND_URL=http://localhost:5173

JWT_SECRET=sua_chave_secreta
JWT_EXPIRATION=28800

PASSWORD_RESET_TTL_MINUTES=30
PASSWORD_RESET_MAX_REQUESTS=3
PASSWORD_RESET_WINDOW_MINUTES=15

MAIL_HOST=smtp.seu-provedor.com
MAIL_PORT=587
MAIL_USERNAME=usuario@seu-dominio.com
MAIL_PASSWORD=sua_senha_de_aplicativo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=nao-responda@seu-dominio.com
MAIL_FROM_NAME="Trilha Formativa de Inovação"
MAIL_SMTP_DEBUG=0
```

O arquivo `.env.example`, disponível na raiz do projeto, traz a lista completa e comentada dessas variáveis.

Os valores devem ser configurados de acordo com o ambiente em que a API será executada.

> **Importante:** o arquivo `.env` pode conter informações sensíveis e **não deve ser versionado no repositório**. Utilize um arquivo `.env.example`, caso disponibilizado pelo projeto, como referência para a configuração do ambiente.

---

## Conexão com o banco de dados

A conexão da API com o banco de dados é realizada através da classe `Conexao`, utilizando a extensão **PDO (PHP Data Objects)**.

A aplicação está configurada para utilizar um servidor **MySQL** executado localmente, através do endereço `127.0.0.1` e da porta `3307`, utilizando o banco de dados `mydb`.

A configuração atual da conexão utiliza:

```text
Host:     127.0.0.1
Porta:    3307
Banco:    mydb
Usuário:  root
Senha:    vazia
Charset:  utf8mb4
```

A conexão é obtida através do método:

```php
Conexao::getConexao();
```

O PDO está configurado com `PDO::ERRMODE_EXCEPTION`, fazendo com que erros ocorridos durante a conexão ou execução de operações no banco de dados sejam tratados através de exceções.

> **Observação:** as configurações de conexão apresentadas correspondem ao ambiente de desenvolvimento local. Em ambientes de produção, recomenda-se utilizar variáveis de ambiente para armazenar as credenciais e demais configurações sensíveis do banco de dados.

---

## CORS e CorsMiddleware

A API possui um `CorsMiddleware` responsável por configurar o **CORS (Cross-Origin Resource Sharing)**, permitindo que o frontend realize requisições para a API quando as aplicações estão sendo executadas em origens diferentes.

Durante o desenvolvimento, o frontend utiliza o servidor do **Vite**, normalmente disponível em:

```text
http://localhost:5173
```

Como a API é executada separadamente, o navegador considera as aplicações como origens diferentes. Por isso, o middleware adiciona os cabeçalhos necessários para permitir a comunicação entre elas.

Atualmente, são permitidas requisições provenientes de:

```text
http://localhost:5173
```

Os seguintes métodos HTTP são permitidos:

```text
GET
POST
PUT
DELETE
OPTIONS
```

Também são permitidos os seguintes cabeçalhos:

```text
Content-Type
Authorization
X-Requested-With
```

O middleware trata ainda as requisições `OPTIONS`, utilizadas pelo navegador em determinadas requisições como parte do processo de **preflight** do CORS. Nesse caso, a API retorna o status HTTP `200` e encerra a requisição.

### Necessidade do CorsMiddleware

O middleware é necessário quando o frontend e a API estão hospedados em **origens diferentes**. Por exemplo:

```text
Frontend → http://localhost:5173
API      → http://localhost:8000
```

Nesse cenário, o navegador aplica as políticas de CORS e a API precisa informar explicitamente quais origens, métodos e cabeçalhos são permitidos.

Caso o frontend e a API sejam disponibilizados pela **mesma origem**, o CORS deixa de ser necessário para essa comunicação.

> **Observação:** o `CorsMiddleware` é especialmente relevante para o ambiente atual de desenvolvimento, no qual o frontend e a API são executados separadamente. Em produção, sua necessidade dependerá da forma como as duas aplicações serão hospedadas.

---

## Banco de dados

A API utiliza **MySQL** para armazenamento e gerenciamento dos dados da plataforma.

Antes de executar a aplicação, é necessário criar o banco de dados e executar o script SQL disponibilizado no projeto.

### Script do banco de dados

O script SQL contém:

* Criação do banco de dados;
* Criação das tabelas;
* Chaves primárias;
* Chaves estrangeiras;
* Restrições de integridade;
* Dados iniciais necessários para a aplicação.

O arquivo está localizado em:

```text
src/config/sql/database.sql
```

O script pode ser executado utilizando o **MySQL**, **phpMyAdmin** ou outra ferramenta compatível.

Caso esteja utilizando o XAMPP, o arquivo pode ser acessado através do caminho:

```text
C:\xampp\htdocs\trilha-formativa-inovacao-api\src\config\sql\database.sql
```

> **Importante:** o servidor MySQL deve estar em execução e configurado de acordo com as informações utilizadas pela classe `Conexao` antes de iniciar a API.

---

## Modelo de dados

O banco de dados da plataforma é composto pelas entidades responsáveis pelo gerenciamento dos usuários, missões, questões, alternativas, progresso, distintivos e demais informações utilizadas pela aplicação.

O modelo de dados completo pode ser visualizado abaixo:

![Modelo de Dados](docs/modelo-dados.png)

---

## Executando a API

Após configurar o arquivo `.env` e preparar o banco de dados, a API pode ser executada localmente.

Para atualizar o autoload das classes:

```bash
composer dump-autoload -o
```

Em seguida, inicie o servidor embutido do PHP:

```bash
php -S localhost:8000 -t public
```

A API estará disponível em:

```text
http://localhost:8000
```

> **Observação:** a porta pode ser alterada de acordo com a configuração utilizada no ambiente de desenvolvimento.

---

## Autenticação

A API utiliza **JWT (JSON Web Token)** para autenticação e autorização dos usuários.

Após realizar a autenticação, o token deverá ser enviado nas requisições que exigem acesso autenticado.

O formato esperado é:

```http
Authorization: Bearer <TOKEN>
```

A chave utilizada para geração e validação dos tokens é definida através da variável `JWT_SECRET` no arquivo `.env`.

O tempo de validade do token é definido através da variável:

```env
JWT_EXPIRATION=28800
```

---

## Recuperação de senha

A API disponibiliza um mecanismo de redefinição de senha para o usuário que perdeu o acesso à própria conta. O fluxo segue as recomendações do **OWASP Forgot Password Cheat Sheet**.

### Como funciona

1. O usuário informa o e-mail cadastrado.
2. A API gera um token aleatório de **256 bits** (64 caracteres hexadecimais).
3. Apenas o resumo **SHA-256** do token é gravado na tabela `RECUPERACAO_SENHA`. O token em claro existe somente no e-mail enviado.
4. O usuário recebe um link para o frontend contendo o token.
5. Ao redefinir, a API confere o token, aplica a política de senha, grava o novo hash e **invalida o token**.
6. Um e-mail de confirmação avisa que a senha foi alterada.

### Endpoints

| Método | Rota | Autenticação | Descrição |
| --- | --- | --- | --- |
| `POST` | `/api/v1/recuperacao-senha/solicitar` | Pública | Solicita o envio do link de redefinição |
| `GET` | `/api/v1/recuperacao-senha/validar?token=...` | Pública | Verifica se o token ainda é válido, sem consumi-lo |
| `POST` | `/api/v1/recuperacao-senha/redefinir` | Pública | Grava a nova senha e consome o token |

**Solicitar**

```http
POST /api/v1/recuperacao-senha/solicitar
Content-Type: application/json

{
  "correioEletronico": "aventureiro@exemplo.com"
}
```

```json
{
  "mensagem": "Se o e-mail informado estiver cadastrado, enviaremos as instruções de redefinição de senha."
}
```

A resposta é sempre `200` com essa mesma mensagem, exista ou não a conta.

**Validar**

```http
GET /api/v1/recuperacao-senha/validar?token=1f4c...9ab2
```

```json
{
  "valido": true,
  "expiraEm": "2026-09-06T15:42:00-03:00"
}
```

Token ausente, expirado ou já utilizado retorna `400` com `"valido": false`.

**Redefinir**

```http
POST /api/v1/recuperacao-senha/redefinir
Content-Type: application/json

{
  "token": "1f4c...9ab2",
  "novaSenha": "NovaSenha@2026",
  "novaSenhaRepeticao": "NovaSenha@2026"
}
```

```json
{
  "mensagem": "Senha redefinida com sucesso! Faça login com a nova senha."
}
```

A nova senha passa pela mesma política do cadastro: mínimo de 8 caracteres, com letra, número e caractere especial.

### Decisões de segurança

| Controle | Implementação |
| --- | --- |
| Entropia do token | `random_bytes(32)` — gerador criptograficamente seguro |
| Armazenamento | Somente o resumo SHA-256, nunca o token |
| Uso único | `UPDATE ... WHERE DataUtilizacao IS NULL`, consumo atômico mesmo sob concorrência |
| Validade | `PASSWORD_RESET_TTL_MINUTES`, 30 minutos por padrão |
| Enumeração de contas | Mensagem única e piso de tempo de resposta de 400 ms, independentemente do desfecho |
| Rate limiting | Máximo de solicitações por conta dentro de uma janela configurável |
| Tokens anteriores | Invalidados a cada nova solicitação e após a redefinição |
| Login automático | Não ocorre: o usuário precisa autenticar-se com a senha nova |
| Host Header Injection | O link vem de `APP_FRONTEND_URL`, jamais do cabeçalho `Host` |
| Confirmação | E-mail de aviso após a alteração, sem conter a senha |

> **Limitação conhecida:** por serem *stateless*, os JWT emitidos antes da redefinição continuam válidos até expirarem. Invalidá-los exigiria versionar a senha no token e consultar o banco a cada requisição, o que altera o desenho atual do `JwtMiddleware`.

### Envio de e-mail

O envio usa **PHPMailer** sobre SMTP, encapsulado na classe `EmailService`. Todos os parâmetros vêm do `.env`, então trocar de provedor não exige alteração de código.

Se o SMTP falhar, o erro vai para o log do PHP e o token é invalidado. A resposta HTTP continua sendo a mensagem genérica — qualquer variação reintroduziria a enumeração de contas.

### Papel do frontend

A API não renderiza nenhuma tela: ela expõe os três endpoints e deixa toda a experiência do usuário por conta do frontend.

O link enviado por e-mail aponta para `APP_FRONTEND_URL/redefinir-senha?token=...`. Isso significa que o frontend precisa ter uma rota `/redefinir-senha` que:

1. Lê o `token` da query string da URL.
2. Opcionalmente, chama `GET /api/v1/recuperacao-senha/validar?token=...` assim que a página carrega, para mostrar "link expirado" sem obrigar o usuário a preencher o formulário à toa.
3. Mostra um formulário com dois campos: nova senha e confirmação.
4. Ao enviar, chama `POST /api/v1/recuperacao-senha/redefinir` passando o `token` (o mesmo lido no passo 1, não algo digitado pelo usuário) junto com `novaSenha` e `novaSenhaRepeticao`.
5. Em caso de sucesso, redireciona para a tela de login. A API nunca autentica automaticamente depois da redefinição — por desenho — então o usuário precisa logar de novo com a senha nova.
6. Em caso de erro (token inválido/expirado, senhas não conferem, senha fora da política), exibe a mensagem que a API devolveu no campo `erro` ou `mensagem`.

Enquanto essa tela não existir, o fluxo pode ser validado diretamente via Postman/curl: o token copiado do e-mail (recebido, durante o desenvolvimento, na inbox do provedor de teste configurado no `.env`) é exatamente o que a tela leria da URL.

### Migração do banco

A tabela `RECUPERACAO_SENHA` já consta do `database.sql`. Em uma base existente, aplique apenas a migração:

```bash
mysql -u root -p mydb < src/config/sql/migrations/001_recuperacao_senha.sql
```

---

## Verificação de e-mail no cadastro

Antes de criar uma conta, a API confirma que o endereço informado existe e pertence a quem está preenchendo o formulário. A confirmação é feita por um **código de seis dígitos** enviado por e-mail.

### Como funciona

1. O usuário preenche o formulário de cadastro e o frontend envia apenas o e-mail.
2. A API gera um código de seis dígitos, grava o resumo SHA-256 e envia o código por e-mail.
3. O usuário digita o código recebido.
4. Conferido o código, a API devolve um **comprovante de 256 bits**.
5. O frontend envia esse comprovante junto com o `POST /usuarios`. Sem ele, o cadastro é recusado.

A conta só nasce no passo 5, já com o e-mail validado. Nenhum registro de usuário é criado antes disso.

### Endpoints

| Método | Rota | Autenticação | Descrição |
| --- | --- | --- | --- |
| `POST` | `/api/v1/verificacao-email/solicitar` | Pública | Envia o código de seis dígitos |
| `POST` | `/api/v1/verificacao-email/confirmar` | Pública | Confere o código e devolve o comprovante |

**Solicitar**

```http
POST /api/v1/verificacao-email/solicitar
Content-Type: application/json

{
  "correioEletronico": "novo.aventureiro@exemplo.com"
}
```

```json
{
  "mensagem": "Código de verificação enviado para o e-mail informado.",
  "expiraEmMinutos": 15
}
```

E-mail já cadastrado retorna `400` com `"Este e-mail já está cadastrado."`. Diferente da recuperação de senha, aqui a duplicidade é revelada de propósito: o `POST /usuarios` já recusa e-mail repetido com `"Email já utilizado!"`, então esconder a informação neste endpoint não protegeria segredo nenhum e deixaria o usuário sem saber por que o cadastro não avança.

**Confirmar**

```http
POST /api/v1/verificacao-email/confirmar
Content-Type: application/json

{
  "correioEletronico": "novo.aventureiro@exemplo.com",
  "codigo": "418302"
}
```

```json
{
  "mensagem": "E-mail verificado com sucesso!",
  "comprovanteVerificacao": "1f4c...9ab2",
  "expiraEmMinutos": 30
}
```

Qualquer falha (código errado, expirado, tentativas esgotadas) retorna `400` com a mesma mensagem: `"Código de verificação inválido ou expirado."`.

**Cadastro com o comprovante**

```http
POST /api/v1/usuarios
Content-Type: application/json

{
  "nomeUsuario": "João Silva",
  "nomeAventureiro": "joaogamer",
  "correioEletronico": "novo.aventureiro@exemplo.com",
  "idOcupacao": 1,
  "senha": "SenhaForte@2026",
  "senhaRepeticao": "SenhaForte@2026",
  "comprovanteVerificacao": "1f4c...9ab2"
}
```

O comprovante precisa pertencer ao mesmo e-mail do cadastro, estar dentro do prazo e ainda não ter sido usado. Caso contrário, a resposta é `400` com `"Verificação de e-mail ausente ou expirada. Solicite um novo código."`.

### Por que um código de seis dígitos exige cuidados extras

Um código de seis dígitos tem apenas 10<sup>6</sup> combinações. Comparado ao token de 256 bits da recuperação de senha, é um segredo fraco: sem proteção, um atacante que conhece o e-mail alvo acerta por força bruta em poucos minutos de requisições.

São três controles que tornam o número curto aceitável, e nenhum é dispensável:

| Controle | Variável | Padrão | Papel |
| --- | --- | --- | --- |
| Limite de tentativas | `EMAIL_VERIFICATION_MAX_ATTEMPTS` | 5 | Queima o código após N erros, reduzindo a chance de acerto a N/10<sup>6</sup> |
| Prazo de validade | `EMAIL_VERIFICATION_TTL_MINUTES` | 15 | Fecha a janela de ataque |
| Teto de emissões | `EMAIL_VERIFICATION_MAX_REQUESTS` | 3 | Impede renovar o código para recuperar tentativas |

O contador de tentativas é incrementado no banco (`Tentativas = Tentativas + 1`) e não em PHP, para que requisições paralelas não se sobrescrevam — caso contrário o limite seria contornável disparando tudo de uma vez.

Confirmado o código, o segredo fraco sai de cena: o comprovante que autoriza o cadastro tem 256 bits, gerado por `random_bytes(32)`.

> **Nota sobre o resumo do código.** O `HashCodigo` protege contra exposição casual (dump, log, backup), não contra um atacante com o banco em mãos: com 10<sup>6</sup> combinações, o SHA-256 é revertido por força bruta em segundos. A defesa real é o limite de tentativas. Para endurecer, troque por `hash_hmac` com um segredo de aplicação, que impede a força bruta offline.

### Demais controles

| Controle | Implementação |
| --- | --- |
| Geração do código | `random_int`, gerador criptográfico. `rand()` e `mt_rand()` seriam previsíveis a partir de algumas amostras |
| Zero à esquerda | Preservado com `str_pad`: `"007321"` é válido e tratá-lo como inteiro quebraria a conferência |
| Comparação | `hash_equals`, em tempo constante, contra ataque de temporização |
| Confirmação única | `UPDATE ... WHERE DataVerificacao IS NULL`, atômico sob concorrência |
| Consumo único | `UPDATE ... WHERE DataConsumo IS NULL`, impede duas contas com o mesmo comprovante |
| Vínculo com o e-mail | O comprovante só vale para o endereço que foi verificado |
| Códigos anteriores | Invalidados a cada nova solicitação |

### Ordem de consumo no cadastro

O comprovante é consumido **antes** do `INSERT` do usuário, e não depois. Consumir depois deixaria a janela em que duas requisições simultâneas com o mesmo comprovante passariam pela verificação e criariam duas contas. O custo dessa ordem é que uma falha no `INSERT` queima o comprovante e obriga o usuário a pedir um código novo — é o lado seguro para errar.

### Migração do banco

A tabela `VERIFICACAO_EMAIL` já consta do `database.sql`. Em uma base existente, aplique apenas a migração:

```bash
mysql -u root -p mydb < src/config/sql/migrations/002_verificacao_email.sql
```

A tabela **não** tem chave estrangeira para `USUARIO`, e isso é proposital: no momento da verificação o usuário ainda não existe. O vínculo é o próprio endereço de e-mail.

---

## Estrutura do projeto

A API está organizada em diferentes camadas, buscando separar as responsabilidades relacionadas à configuração, comunicação HTTP, regras de negócio, persistência de dados, segurança e tratamento de exceções.

```text
trilha-formativa-inovacao-api/
├── src/
│   ├── config/                    → Configurações da aplicação
│   │   ├── http/                  → Configurações relacionadas ao HTTP
│   │   ├── router/                → Configuração do roteamento
│   │   ├── routes/                → Definição das rotas da API
│   │   ├── sql/                   → Scripts relacionados ao banco de dados
│   │   ├── Conexao.php            → Gerenciamento da conexão com o banco
│   │   └── CorsMiddleware.php     → Configuração do CORS
│   │
│   ├── controller/                → Controle das requisições HTTP
│   ├── dao/                       → Acesso e persistência dos dados
│   ├── dto/                       → Objetos de transferência de dados
│   ├── exception/                 → Exceções personalizadas
│   ├── model/                     → Modelos e entidades do sistema
│   ├── security/                  → Autenticação e recursos de segurança
│   └── service/                   → Regras e lógica de negócio
│
├── vendor/                        → Dependências gerenciadas pelo Composer
├── .env                           → Configurações específicas do ambiente
├── .gitignore                     → Arquivos ignorados pelo Git
├── composer.json                  → Dependências e configurações do Composer
├── composer.lock                  → Versões específicas das dependências
├── database.sqlite                → Banco de dados SQLite
├── phpunit.xml                    → Configuração dos testes automatizados
└── README.md                      → Documentação do projeto
```

### Principais diretórios

| Diretório     | Responsabilidade                                                                                  |
| ------------- | ------------------------------------------------------------------------------------------------- |
| `config/`     | Centraliza configurações e componentes relacionados à infraestrutura da API.                      |
| `controller/` | Recebe e processa as requisições HTTP, encaminhando as operações para as camadas apropriadas.     |
| `dao/`        | Responsável pelo acesso e persistência dos dados no banco de dados.                               |
| `dto/`        | Define objetos utilizados para transferência e organização dos dados entre as diferentes camadas. |
| `exception/`  | Contém as exceções personalizadas utilizadas pela aplicação.                                      |
| `model/`      | Representa as entidades e estruturas de dados utilizadas pelo sistema.                            |
| `security/`   | Concentra os recursos relacionados à autenticação e segurança da API.                             |
| `service/`    | Contém as regras de negócio e a lógica da aplicação.                                              |

---

## Testes

O projeto utiliza **PHPUnit** para a execução dos testes automatizados.

As configurações do framework de testes estão definidas no arquivo:

```text
phpunit.xml
```

Os testes podem ser executados através do Composer/PHPUnit conforme a configuração disponibilizada no projeto:

```bash
composer test
```

ou

```bash
vendor/bin/phpunit
```

A suíte `tests/RecuperacaoSenhaServiceTest.php` cobre o mecanismo de recuperação de senha: emissão do token, anti-enumeração de contas, rate limiting, token expirado, token já utilizado, consumo concorrente e aderência à política de senha.

---

## Integração com o Frontend

A API fornece os serviços utilizados pelo frontend da plataforma **Trilha Formativa de Inovação**.

A comunicação entre as aplicações ocorre através de requisições HTTP para os endpoints disponibilizados pela API.

Durante o desenvolvimento, o frontend e a API podem ser executados separadamente:

```text
Frontend → http://localhost:5173
API      → http://localhost:8000
```

Nesse cenário, o `CorsMiddleware` permite que o frontend realize as requisições necessárias à API.

Para utilizar a plataforma completa, é necessário que a API esteja configurada e em execução juntamente com o frontend.

---

## Repositórios

### Frontend

https://github.com/SophiaMFerreira/trilha-formativa-inovacao-frontend

### Backend / API

https://github.com/AdulerV/trilha-formativa-inovacao-api

---

## Resumo da configuração

Para executar o projeto em uma nova máquina:

1. Instale **PHP 8**, **Composer** e **MySQL**;
2. Clone o repositório;
3. Acesse a pasta do projeto;
4. Configure o arquivo `.env`;
5. Inicie o servidor MySQL;
6. Execute o script `database.sql`;
7. Atualize o autoload com `composer dump-autoload -o`;
8. Execute a API com `php -S localhost:8000 -t public`;
9. Execute o frontend em `http://localhost:5173`.

---

<p align="center">

**Desenvolvido para a plataforma Trilha Formativa de Inovação 🎓💡**

</p>
