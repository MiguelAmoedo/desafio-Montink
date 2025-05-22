# Mini ERP Laravel - Desafio Montink

Sistema de gerenciamento de produtos, pedidos e cupons de desconto.

## Desenvolvedor

Desenvolvido por [Miguel Amoedo](https://www.linkedin.com/in/miguel-amoedo/)

## Estrutura do Banco de Dados

O banco de dados pode ser criado de duas formas:

### 1. Usando Migrations (Recomendado)
```bash
php artisan migrate
```

### 2. Usando SQL Diretamente
O arquivo `database/schema.sql` contém todo o SQL necessário para criar o banco de dados. Você pode executá-lo diretamente no MySQL:

```bash
mysql -u seu_usuario -p < database/schema.sql
```

O schema inclui as seguintes tabelas:
- `produtos`: Cadastro de produtos
- `variacoes`: Variações de produtos (tamanhos, cores, etc)
- `cupons`: Cupons de desconto
- `pedidos`: Pedidos realizados
- `pedido_items`: Itens de cada pedido

## Requisitos

### Com Docker
- Docker
- Docker Compose

### Sem Docker
- PHP 8.2 ou superior
- Composer
- MySQL 8.0 ou superior
- Node.js e NPM (para assets)
- Extensões PHP:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

## Instalação

### Usando Docker

1. Clone o repositório:
```bash
git clone [URL_DO_REPOSITORIO]
cd [NOME_DO_DIRETORIO]
```

2. Configure o arquivo .env:
```bash
cp .env.example .env
```

3. Inicie os containers:
```bash
docker-compose up -d
```

4. Acesse a aplicação:
- Web: http://localhost:8000
- MySQL: localhost:3306

O sistema já estará configurado com:
- Banco de dados criado
- Migrações executadas
- Chave da aplicação gerada
- Permissões configuradas

### Instalação Manual (Sem Docker)

1. Clone o repositório:
```bash
git clone https://github.com/MiguelAmoedo/desafio-Montink.git
cd desafio-Montink
```

2. Instale as dependências do PHP:
```bash
composer install
```

3. Configure o arquivo .env:
```bash
cp .env.example .env
```

4. Gere a chave da aplicação:
```bash
php artisan key:generate
```

5. Configure o banco de dados no arquivo .env:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

6. Execute as migrações:
```bash
php artisan migrate
```

7. Configure as permissões:
```bash
chmod -R 777 storage bootstrap/cache
```

8. Instale as dependências do Node.js e compile os assets:
```bash
npm install
npm run dev
```

9. Inicie o servidor:
```bash
php artisan serve
```

## Estrutura do Docker

O ambiente Docker inclui:

### Containers
- **app**: Aplicação Laravel (PHP-FPM)
- **nginx**: Servidor web
- **db**: Banco de dados MySQL

### Volumes
- **dbdata**: Persistência dos dados do MySQL

### Redes
- **laravel_net**: Rede para comunicação entre containers

## Comandos Úteis

### Com Docker
```bash
# Iniciar containers
docker-compose up -d

# Parar containers
docker-compose down

# Ver logs
docker-compose logs -f

# Executar comandos no container da aplicação
docker-compose exec app php artisan [comando]

# Executar composer no container
docker-compose exec app composer [comando]
```

### Sem Docker
```bash
# Iniciar servidor
php artisan serve

# Executar migrações
php artisan migrate

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Compilar assets
npm run dev
```

## Funcionalidades

- Gerenciamento de produtos
- Sistema de pedidos
- Cupons de desconto
- Cálculo de frete
- Carrinho de compras
- Notificações por e-mail

## Configuração de E-mail

O sistema usa o Mailtrap para envio de e-mails em ambiente de desenvolvimento. Configure as credenciais no arquivo `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_username
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@exemplo.com"
MAIL_FROM_NAME="${APP_NAME}"
```
