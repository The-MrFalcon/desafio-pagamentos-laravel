# Desafio Pagamentos Laravel

Sistema de pagamentos em Laravel com suporte a transações Pix e Saques, utilizando Docker para facilitar o ambiente de desenvolvimento. Inclui processamento de webhooks e integração com subadquirentes.

## Visão Geral

- Laravel 12
- Suporte a Pix e Saques
- Processamento assíncrono com Jobs e Redis
- Docker Compose para ambiente local

## Rodando Localmente (com Docker)

### Pré-requisitos
- Docker e Docker Compose instalados
- Git

### Passos para Iniciar o Projeto pela Primeira Vez

1. **Clone o repositório:**
   ```bash
   git clone <url-do-repositorio>
   cd desafio-pagamentos-laravel
   ```

2. **Configure o ambiente:**
   - Copie o arquivo de exemplo de configuração:
     ```bash
     cp .env.example .env
     ```
   - Edite o `.env` e ajuste as variáveis de ambiente, como `APP_KEY`, `DB_HOST=db` (para Docker), e outras credenciais necessárias.

3. **Instale as dependências do PHP:**
   ```bash
   composer install
   ```

4. **Suba os containers Docker:**
   ```bash
   docker-compose up -d --build
   ```
   Isso irá construir e iniciar os serviços: app (Laravel), db (MySQL) e redis.

5. **Execute as migrações do banco de dados:**
   ```bash
   docker-compose exec app php artisan migrate
   ```

6. **(Opcional) Execute os seeders para dados de teste:**
   ```bash
   docker-compose exec app php artisan db:seed
   ```

7. **Acesse a aplicação:**
   - A aplicação estará rodando em: http://localhost:8000
   - Para parar os containers: `docker-compose down`

## Endpoints Principais

### Pix
- `POST /api/pix` - Criar transação Pix
- `GET /api/pix/{id}` - Consultar status da transação Pix

### Saques (Withdraw)
- `POST /api/withdraw` - Solicitar saque
- `GET /api/withdraw/{id}` - Consultar status do saque

### Outros
- Webhooks são processados assincronamente via Jobs.

## Estrutura do Projeto

- **app/Adapters/**: Adaptadores para subadquirentes (SubadqA, SubadqB)
- **app/Http/Controllers/**: Controladores para Pix e Withdraw
- **app/Jobs/**: Jobs para processamento de webhooks
- **app/Services/**: Serviço de pagamentos
- **database/migrations/**: Migrações para users, pix, withdraws, etc.

## Dicas e Troubleshooting

- Se houver problemas com permissões, execute `chmod -R 775 storage bootstrap/cache` dentro do container.
- Para logs, verifique `storage/logs/laravel.log` ou use `docker-compose logs app`.
- Certifique-se de que as portas 8000, 3306 e 6379 não estejam em uso.

## Testes

Execute os testes com:
```bash
docker-compose exec app php artisan test
```

--
