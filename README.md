# 💳 API RESTful de Sistema de Pagamentos Simplificado

Esta API foi desenvolvida em PHP com o framework Laravel e utiliza MySQL para persistência de dados. Ela gerencia o cadastro de clientes e a criação de cobranças (Pix, Cartão de Crédito, Boleto Bancário) com controle de idempotência.

## 🚀 Como Rodar o Projeto

### Pré-requisitos

Certifique-se de ter instalado em seu ambiente:
1.  **PHP** (versão 8.1+)
2.  **Composer**
3.  **MySQL** ou um serviço de banco de dados compatível.

### 1. Configuração do Ambiente

1.  **Clone o projeto** (ou assumindo que você já está no diretório `api-fluxo-checkout`):
    ```bash
    cd payment-api
    ```

2.  **Instale as dependências do PHP:**
    ```bash
    composer install
    ```

3.  **Crie e configure o arquivo `.env`:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    Edite o arquivo `.env` com suas credenciais de banco de dados:
    ```dotenv
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=payment_api_db
    DB_USERNAME=root
    DB_PASSWORD=sua_senha
    ```
    *(**Importante:** Crie o banco de dados `payment_api_db` no seu servidor MySQL.)*

### 2. Execução das Migrações

Rode as migrações para criar as tabelas `customers` e `charges`:

```bash
php artisan migrate
``` 

### 3. Iniciar o Servidor

Inicie o servidor de desenvolvimento:

```bash
php artisan serve
```

O servidor estará disponível em `http://localhost:8000`

## 🧪 Como Testar a API

### Configuração no Postman ou Insomnia

**Base URL:** `http://localhost:8000`

**Headers necessários:**
- `Content-Type: application/json`
- (Opcional) `Idempotency-Key: sua-chave-unica` (para cobranças)

### 1. Cadastrar um Cliente

**Endpoint:** `POST /api/customers`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "name": "João Silva",
  "email": "joao.silva@example.com",
  "document": "12345678900",
  "phone": "11999888777"
}
```

**Resposta de Sucesso (201 Created):**
```json
{
  "message": "Cliente cadastrado com sucesso.",
  "customer_id": 1,
  "customer": {
    "id": 1,
    "name": "João Silva",
    "email": "joao.silva@example.com",
    "document": "12345678900",
    "phone": "11999888777"
  }
}
```

### 2. Criar uma Cobrança

**Endpoint:** `POST /api/charges`

**Headers:**
```
Content-Type: application/json
Idempotency-Key: unique-key-123
```

**Body (JSON):**
```json
{
  "customer_id": 1,
  "amount": 100.50,
  "payment_method": "pix",
  "due_date": "2025-10-30"
}
```

**Tipos de Pagamento Disponíveis:**
- `pix` - Pagamento via Pix
- `credit_card` - Cartão de Crédito
- `bank_slip` - Boleto Bancário

**Resposta de Sucesso (201 Created):**
```json
{
  "message": "Cobrança criada com sucesso.",
  "charge_id": 1,
  "status": "pending",
  "charge": {
    "id": 1,
    "customer_id": 1,
    "amount": "100.50",
    "payment_method": "pix",
    "status": "pending",
    "customer": {
      "id": 1,
      "name": "João Silva",
      "email": "joao.silva@example.com"
    }
  }
}
```

### 🔄 Testar Idempotência

A API suporta requisições idempotentes usando o header `Idempotency-Key`:

1. Faça a primeira requisição para criar uma cobrança com uma `Idempotency-Key` específica (ex: `"test-key-123"`)
2. Repita a mesma requisição com os mesmos dados e a mesma `Idempotency-Key`
3. A segunda requisição retornará status `200 OK` com a cobrança já existente (sem criar duplicata)

**Exemplo:**
- Primeira chamada: Cria a cobrança e retorna `201 Created`
- Segunda chamada (mesma chave): Retorna `200 OK` com a cobrança existente

### ❌ Erros Comuns

#### Email ou Documento duplicado (422 Unprocessable Entity)

**Resposta:**
```json
{
  "message": "O e-mail informado já está cadastrado. (and 1 more error)",
  "errors": {
    "email": ["O e-mail informado já está cadastrado."],
    "document": ["O documento informado já está cadastrado."]
  }
}
```

#### Cliente não encontrado (404 Not Found)

**Resposta:**
```json
{
  "error": "Not Found",
  "message": "Cliente com ID 999 não encontrado."
}
```

### 💡 Dicas

1. **Use IDs diferentes:** Cada vez que cadastrar um cliente ou cobrança, use emails, documentos e `Idempotency-Key` únicos
2. **Opcional `phone`:** O campo telefone é opcional ao cadastrar um cliente
3. **Data de vencimento:** Use o formato `YYYY-MM-DD` para `due_date`
