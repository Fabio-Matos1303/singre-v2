
# Backlog e Sprints para a Modernização do Backend

Este documento detalha o backlog de tarefas e a divisão em sprints para a atualização do sistema de gerenciamento de ordens de serviço. O objetivo é migrar a aplicação legada para uma arquitetura moderna baseada em PHP 8, Laravel (API RESTful) e Docker, garantindo segurança, performance e manutenibilidade.

## Visão Geral da Arquitetura

O novo sistema será composto por:

*   **Backend:** Laravel 10+ (PHP 8+), atuando como uma API RESTful. Será responsável pela lógica de negócio, persistência de dados e autenticação via Laravel Sanctum.
*   **Banco de Dados:** MySQL/MariaDB, gerenciado pelo Laravel Eloquent ORM e Migrations.
*   **Ambiente de Desenvolvimento/Produção:** Docker, utilizando Laravel Sail para facilitar a configuração e padronização do ambiente.

## Backlog Detalhado

O backlog foi dividido em funcionalidades principais, que serão então quebradas em tarefas menores e alocadas em sprints.

### Módulos Principais a Serem Migrados/Desenvolvidos:

1.  **Autenticação e Autorização:** Gerenciamento de usuários, login, logout, controle de acesso (ACL).
2.  **Gerenciamento de Clientes:** CRUD de clientes.
3.  **Gerenciamento de Produtos/Serviços:** CRUD de produtos/serviços.
4.  **Gerenciamento de Ordens de Serviço:** Criação, edição, visualização, status, histórico.
5.  **Relatórios:** Geração de relatórios (PDFs, gráficos).
6.  **Configurações do Sistema:** Gerenciamento de configurações gerais (e-mail, diretórios, etc.).
7.  **Backup/Restore:** Funcionalidade de backup e restauração do banco de dados.

## Definição das Sprints

Cada sprint terá duração de 1 a 2 semanas, com foco em entregas incrementais e funcionais. As tarefas relacionadas ao Docker serão integradas em cada sprint conforme a necessidade.

### Sprint 1: Setup do Ambiente e Core da Aplicação (Duração: 1-2 semanas) — Status: CONCLUÍDA ✅

**Objetivo:** Configurar o ambiente de desenvolvimento com Docker, inicializar o projeto Laravel e implementar a base de autenticação.

**Tarefas:**

*   **Configuração do Docker e Laravel Sail:**
    *   Instalar Docker e Docker Compose no ambiente de desenvolvimento.
    *   Criar um novo projeto Laravel com Laravel Sail (`laravel new project-name --with=mysql,redis,mailpit`).
    *   Configurar o arquivo `.env` do Laravel para as variáveis de ambiente do Docker (DB_CONNECTION, DB_HOST, etc.).
    *   Testar o ambiente Docker (subir containers, acessar o shell do container PHP).
*   **Inicialização do Projeto Laravel:**
    *   Executar `composer install` dentro do container PHP.
    *   Gerar a chave da aplicação (`php artisan key:generate`).
    *   Configurar o banco de dados no `.env` e executar as migrations iniciais do Laravel (`php artisan migrate`).
*   **Autenticação Básica (Laravel Sanctum):**
    *   Instalar e configurar Laravel Sanctum para autenticação de API.
    *   Criar o modelo `User` e a migration correspondente (se não usar o padrão do Laravel).
    *   Implementar endpoints de registro e login de usuários (API).
    *   Testar a autenticação via Postman/Insomnia.
*   **Gerenciamento de Dependências:**
    *   Remover dependências legadas (PHPMailer, FPDF, JpGraph) do código-fonte.
    *   Identificar e adicionar as novas dependências via Composer (ex: `dompdf/dompdf` para PDFs, se necessário).

**Entregáveis da Sprint:**

*   Ambiente de desenvolvimento Docker funcional com Laravel.
*   Projeto Laravel inicializado e configurado.
*   Sistema de autenticação de API (login/registro) funcional via Laravel Sanctum. [CONCLUÍDO]
*   `composer.json` atualizado com as dependências necessárias.

### Sprint 2: Gerenciamento de Clientes e Produtos/Serviços (Duração: 1-2 semanas) — Status: EM ANDAMENTO 🚧

**Objetivo:** Migrar as funcionalidades de CRUD para Clientes e Produtos/Serviços, utilizando o Eloquent ORM e criando endpoints de API.

**Tarefas:**

*   **Modelos e Migrations:** [EM PROGRESSO]
    *   Analisar a estrutura das tabelas `clientes` e `produtos/serviços` do banco de dados legado.
    *   Criar modelos Eloquent (`Client.php`, `Product.php`, `Service.php`) correspondentes.
    *   Criar migrations para recriar as tabelas `clientes` e `produtos/serviços` com as colunas necessárias, incluindo tipos de dados corretos e índices.
    *   Executar as novas migrations.
*   **Seeders (Dados Iniciais/Teste):** [CONCLUÍDO]
    *   Criar seeders para popular as tabelas `clientes` e `produtos/serviços` com dados de teste ou dados iniciais, se aplicável.
*   **Controladores e Rotas de API:** [CONCLUÍDO]
    *   Criar controladores (`ClientController.php`, `ProductController.php`, `ServiceController.php`) para gerenciar as operações CRUD.
    *   Definir rotas de API (GET, POST, PUT, DELETE) para clientes e produtos/serviços no `routes/api.php`.
    *   Implementar a lógica CRUD nos controladores, utilizando o Eloquent ORM.
*   **Validação de Dados:** [CONCLUÍDO]
    *   Adicionar regras de validação para os dados de entrada nos requests de criação e atualização.
*   **Testes de API:** [CONCLUÍDO PARCIAL — CRUD de listagem/criação cobertos]
    *   Testar todos os endpoints CRUD via Postman/Insomnia.

**Entregáveis da Sprint:**

*   Modelos Eloquent e Migrations para Clientes e Produtos/Serviços. [CONCLUÍDO]
*   Endpoints de API RESTful para CRUD de Clientes e Produtos/Serviços, com validação de dados. [CONCLUÍDO]
*   Dados de teste populados via Seeders. [CONCLUÍDO]

### Sprint 3: Gerenciamento de Ordens de Serviço (Duração: 2 semanas) — Status: CONCLUÍDA ✅

**Objetivo:** Migrar a funcionalidade central de Ordens de Serviço, incluindo relacionamentos e lógica de negócio.

**Tarefas:**

*   **Modelos e Migrations:** [CONCLUÍDO]
    *   Analisar a estrutura da tabela `ordens_de_servico` e suas relações com `clientes` e `produtos/serviços`.
    *   Criar o modelo Eloquent `ServiceOrder.php`.
    *   Criar a migration para a tabela `ordens_de_servico`, definindo chaves estrangeiras e relacionamentos.
    *   Executar a migration.
*   **Relacionamentos Eloquent:** [CONCLUÍDO]
    *   Definir os relacionamentos (`belongsTo`, `hasMany`, `belongsToMany`) nos modelos `ServiceOrder`, `Client`, `Product` e `Service`.
*   **Controladores e Rotas de API:** [CONCLUÍDO]
    *   Criar o controlador `ServiceOrderController.php`.
    *   Definir rotas de API para CRUD de Ordens de Serviço, incluindo listagem, visualização detalhada, criação, atualização e exclusão.
    *   Implementar a lógica de negócio para o ciclo de vida da Ordem de Serviço (status, datas, etc.).
*   **Validação de Dados e Lógica de Negócio:** [CONCLUÍDO]
    *   Adicionar validação robusta para a criação e atualização de Ordens de Serviço.
    *   Migrar a lógica de negócio existente (ex: cálculo de valores, histórico de status) para o controlador ou para Service Classes dedicadas.
*   **Testes de API:** [CONCLUÍDO]
    *   Testar todos os endpoints de Ordens de Serviço.

**Entregáveis da Sprint:**

*   Modelos Eloquent e Migrations para Ordens de Serviço com relacionamentos definidos. [CONCLUÍDO]
*   Endpoints de API RESTful completos para CRUD de Ordens de Serviço. [CONCLUÍDO]
*   Lógica de negócio central de Ordens de Serviço implementada no backend. [CONCLUÍDO]

### Sprint 4: Relatórios e E-mails (Duração: 1-2 semanas) — Status: EM ANDAMENTO 🚧

**Objetivo:** Implementar a geração de relatórios (PDFs) e o envio de e-mails transacionais.

**Tarefas:**

*   **Geração de PDFs:** [EM PROGRESSO — endpoint PDF de OS implementado]
    *   Integrar uma biblioteca de geração de PDF (ex: `dompdf/dompdf` via `barryvdh/laravel-dompdf`).
    *   Criar views Blade para os layouts dos relatórios (ex: Ordem de Serviço, Resumo de Clientes).
    *   Implementar endpoints de API para gerar e baixar PDFs de Ordens de Serviço ou outros relatórios.
*   **Envio de E-mails:** [CONCLUÍDO — envio ao criar OS via Mailpit]
    *   Configurar o Mailpit (ou outro serviço de e-mail de desenvolvimento) no Docker para testar o envio de e-mails.
    *   Criar Mailables no Laravel para e-mails transacionais (ex: confirmação de Ordem de Serviço, notificação de status).
    *   Integrar o envio de e-mails na lógica de negócio (ex: enviar e-mail ao criar/atualizar OS).
*   **Gráficos (Opcional/Se Necessário):**
    *   Se houver necessidade de gráficos, pesquisar e integrar uma biblioteca de gráficos para PHP (ex: `chartisan/php`).
    *   Criar endpoints de API para fornecer dados para os gráficos.

**Entregáveis da Sprint:**

*   Funcionalidade de geração de PDFs de relatórios/Ordens de Serviço. [CONCLUÍDO]
*   Envio de e-mails transacionais configurado e funcional. [CONCLUÍDO]
*   (Opcional) Geração de dados para gráficos via API.

### Sprint 5: Configurações do Sistema e Backup/Restore (Duração: 1 semana) — Status: EM ANDAMENTO 🚧

**Objetivo:** Migrar as configurações do sistema e a funcionalidade de backup/restore.

**Tarefas:**

*   **Configurações do Sistema:** [EM PROGRESSO]
    *   Analisar a tabela `configuracao` legada.
    *   Criar modelo Eloquent e migration para a tabela de configurações.
    *   Implementar endpoints de API para visualizar e atualizar as configurações do sistema.
    *   Utilizar o sistema de cache do Laravel para otimizar o acesso às configurações.
*   **Backup/Restore do Banco de Dados:** [PENDENTE]
    *   Pesquisar e integrar uma solução de backup de banco de dados para Laravel (ex: `spatie/laravel-backup`).
    *   Implementar endpoints de API para iniciar um backup manual e listar backups existentes.
    *   (Opcional) Implementar funcionalidade de restore (com cautela e validação de segurança).
    *   Configurar agendamento de backups automáticos via Laravel Scheduler (executado via cron no Docker).

**Entregáveis da Sprint:**

*   Gerenciamento de configurações do sistema via API.
*   Funcionalidade de backup do banco de dados implementada e testada.
*   (Opcional) Funcionalidade de restore.

### Sprint 6: Refinamento, Testes e Documentação (Duração: 1 semana)

**Objetivo:** Realizar testes finais, otimizações, documentação da API e preparação para o front-end.

**Tarefas:**

*   **Testes de Integração e Unidade:**
    *   Escrever testes de unidade e integração para as funcionalidades críticas do backend (autenticação, CRUD de OS).
    *   Executar a suíte de testes (`php artisan test`).
*   **Otimizações de Performance:**
    *   Revisar queries e otimizar o uso do Eloquent (eager loading, índices).
    *   Configurar cache de rotas, configurações e views do Laravel (`php artisan optimize`).
*   **Documentação da API:**
    *   Gerar documentação da API (ex: com Swagger/OpenAPI, usando `darkaonline/l5-swagger` ou similar).
    *   Detalhar os endpoints, parâmetros, respostas e códigos de status.
*   **Segurança Adicional:**
    *   Revisar e garantir que todas as recomendações de segurança (CSRF, XSS, validação) foram aplicadas.
    *   Garantir que credenciais sensíveis estão apenas no `.env`.
*   **Preparação para o Front-end:**
    *   Garantir que a API está pronta para ser consumida pelo Vue.js.
    *   Fornecer a documentação da API para a equipe de front-end.

**Entregáveis da Sprint:**

*   Backend testado e otimizado.
*   Documentação da API RESTful.
*   Backend pronto para integração com o front-end Vue.js.

## Próximos Passos

Após a conclusão dessas sprints, o backend estará modernizado e pronto para ser integrado com o front-end em Vue.js. O desenvolvimento do front-end pode ocorrer em paralelo a partir da Sprint 2, uma vez que os endpoints de Clientes e Produtos/Serviços estiverem estáveis.

Este plano é flexível e pode ser ajustado conforme as necessidades e descobertas durante o processo de desenvolvimento. Recomenda-se reuniões diárias (Daily Scrums) para acompanhar o progresso e resolver impedimentos.

