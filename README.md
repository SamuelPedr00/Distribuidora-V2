# 🚀 Configuração do Projeto Laravel

Este guia fornece instruções completas para configurar e executar este projeto Laravel em seu ambiente local.

## 📋 Pré-requisitos

Antes de começar, certifique-se de ter instalado em sua máquina:

-   **PHP** (versão 8.1 ou superior)
-   **Composer** (gerenciador de dependências do PHP)
-   **Node.js** (versão 20 ou superior) e **npm**
-   **MySQL** ou **PostgreSQL** (ou outro banco de dados suportado)
-   **Git**

### Verificando as versões

```bash
php --version
composer --version
node --version
npm --version
```

## 🔧 Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/SamuelPedr00/Distribuidora-V2.git
cd seu-projeto
```

### 2. Instale as dependências do PHP

```bash
composer install
```

### 3. Instale as dependências do Node.js

```bash
npm install
```

### 4. Configure o arquivo de ambiente

Copie o arquivo de exemplo e configure suas variáveis de ambiente:

```bash
cp .env.example .env
```

Edite o arquivo `.env` e configure as seguintes variáveis:

```env
APP_NAME=Distribuidora
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 5. Gere a chave da aplicação

```bash
php artisan key:generate
```

### 6. Configure o banco de dados

Crie um banco de dados com o nome especificado no arquivo `.env`, depois execute as migrações:

```bash
php artisan migrate
```

### 7. Compile os assets (se usar Laravel Mix/Vite)

Para desenvolvimento:

```bash
npm run dev
```

Para produção:

```bash
npm run build
```

## 🚀 Executando o projeto

### Servidor de desenvolvimento

Inicie o servidor Laravel:

```bash
php artisan serve
```

O projeto estará disponível em `http://localhost:8000`

### Atualizar dependências

```bash
composer update
npm update
```

### Listar rotas

```bash
php artisan route:list
```

## 📚 Recursos adicionais

-   [Documentação oficial do Laravel](https://laravel.com/docs)
-   [Laravel Bootcamp](https://bootcamp.laravel.com/)
-   [Laracasts](https://laracasts.com/)

## 🤝 Contribuindo

Para contribuir com este projeto:

1. Faça um fork do repositório
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request
