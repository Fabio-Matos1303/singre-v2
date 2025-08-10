# Backlog e Sprints para a Estruturação da Infraestrutura

Este documento detalha o backlog de tarefas e a divisão em sprints para a estruturação da infraestrutura do projeto, com foco na integração entre o backend (Laravel) e o frontend (Vue.js) que compartilharão o mesmo diretório raiz. O objetivo é garantir um ambiente de desenvolvimento consistente, um processo de deploy automatizado e configurações de ambiente robustas.

## Visão Geral da Arquitetura de Infraestrutura

O projeto adotará uma abordagem de monorepo, onde o backend Laravel e o frontend Vue.js residirão no mesmo diretório raiz. A orquestração dos serviços será feita via Docker Compose para desenvolvimento e, potencialmente, Kubernetes ou outro orquestrador para produção.

*   **Estrutura de Diretórios:**
    *   `./backend` (ou `.` se o Laravel for a raiz principal): Aplicação Laravel.
    *   `./frontend`: Aplicação Vue.js.
    *   `./docker`: Arquivos de configuração Docker (Dockerfiles, docker-compose.yml).
    *   `./scripts`: Scripts de automação (build, deploy, etc.).
*   **Orquestração:** Docker Compose para ambiente local, com foco em otimização para CI/CD e produção.
*   **CI/CD:** Pipelines automatizadas para build, teste e deploy.
*   **Configurações de Ambiente:** Gerenciamento de variáveis de ambiente para diferentes estágios (desenvolvimento, staging, produção).

## Backlog Detalhado

O backlog foi dividido em funcionalidades principais, que serão então quebradas em tarefas menores e alocadas em sprints.

### Módulos Principais de Infraestrutura:

1.  **Configuração Docker:** Dockerfiles para backend e frontend, docker-compose para orquestração.
2.  **Gerenciamento de Variáveis de Ambiente:** Estratégias para `.env` e variáveis de ambiente em CI/CD.
3.  **Pipelines de CI/CD:** Automação de build, teste e deploy.
4.  **Monitoramento e Logs:** Configuração de ferramentas de monitoramento e centralização de logs.
5.  **Segurança da Infraestrutura:** Boas práticas de segurança para containers e ambiente.

## Definição das Sprints

Cada sprint terá duração de 1 a 2 semanas, com foco em entregas incrementais e funcionais.

### Sprint 1: Setup Inicial do Docker e Estrutura do Projeto (Duração: 1-2 semanas) — Status: CONCLUÍDA ✅

**Objetivo:** Configurar o ambiente Docker para o monorepo e estabelecer a estrutura básica de diretórios.

**Tarefas:**

*   **Estrutura de Diretórios:**
    *   Criar a estrutura de diretórios base: `backend/`, `frontend/`, `docker/`, `scripts/`.
    *   Mover o projeto Laravel para `backend/` e o projeto Vue.js para `frontend/` (se já existirem).
*   **Dockerfiles Iniciais:**
    *   Criar `docker/backend/Dockerfile` para a aplicação Laravel (PHP-FPM, Nginx).
    *   Criar `docker/frontend/Dockerfile` para a aplicação Vue.js (Nginx para servir os arquivos estáticos).
*   **Docker Compose para Desenvolvimento:**
    *   Criar `docker-compose.yml` na raiz do projeto.
    *   Definir serviços para `backend` (PHP-FPM, Nginx), `frontend` (Nginx), `mysql` e `redis`.
    *   Configurar volumes para persistência de dados e mapeamento de código.
    *   Configurar redes Docker para comunicação entre os serviços.
*   **Variáveis de Ambiente para Desenvolvimento:**
    *   Configurar `.env` na raiz do projeto para variáveis de ambiente compartilhadas (ex: `APP_URL`, `DB_HOST`).
    *   Garantir que os `.env` específicos de backend e frontend herdem ou sobrescrevam conforme necessário.
*   **Testes Iniciais:**
    *   Subir os containers (`docker-compose up -d`).
    *   Verificar se o backend Laravel e o frontend Vue.js estão acessíveis via navegador.
    *   Testar a comunicação entre frontend e backend (ex: requisição de login).

**Entregáveis da Sprint:**

*   Estrutura de diretórios monorepo estabelecida (`backend/`, `frontend/`, `docker/`, `scripts/`).
*   `docker-compose.yml` criado com serviços `mysql`, `redis`, `backend` (PHP-FPM) e `nginx` servindo `backend/public`, além de `frontend` estático (placeholder).
*   Dockerfiles iniciais para backend (`docker/backend/Dockerfile`) e frontend (`docker/frontend/Dockerfile`), e `docker/nginx/default.conf` adicionados.
*   Scripts de conveniência criados: `scripts/dev-up.sh`, `dev-down.sh`, `logs.sh`, `ps.sh`, `rebuild.sh`, `compose.sh`.
*   Placeholders para validação: `backend/public/index.php` e `frontend/index.html`.
*   Arquivos `.env.example` criados (raiz, backend e frontend) para facilitar configuração local.

### Sprint 2: Otimização do Docker e Gerenciamento de Ambiente (Duração: 1-2 semanas) — Status: CONCLUÍDA ✅

**Objetivo:** Otimizar as configurações Docker e implementar um gerenciamento robusto de variáveis de ambiente.

**Tarefas:**

*   **Otimização de Dockerfiles:**
    *   Otimizar `docker/backend/Dockerfile` para builds menores e mais rápidos (multi-stage builds, cache de camadas). [CONCLUÍDO]
    *   Melhorias aplicadas no backend: habilitado `opcache`, adicionado `php.ini` de desenvolvimento, uso de usuário não-root. [CONCLUÍDO]
    *   Otimizar `docker/frontend/Dockerfile` para builds de produção (build de assets, servir via Nginx). [CONCLUÍDO]
*   **Gerenciamento de Variáveis de Ambiente:**
    *   Implementar estratégias para gerenciar variáveis de ambiente em diferentes ambientes (desenvolvimento, staging, produção). [CONCLUÍDO]
    *   Arquivos `.env.example` adicionados (raiz, backend e frontend). [CONCLUÍDO]
    *   Documentar o processo de configuração de variáveis de ambiente para cada ambiente. [CONCLUÍDO]
*   **Scripts de Conveniência:**
    *   Criar scripts shell em `scripts/` para facilitar operações comuns (ex: `scripts/dev-up.sh`, `scripts/dev-down.sh`, `scripts/backend-artisan.sh`, `scripts/frontend-npm.sh`). [CONCLUÍDO]
    *   Adicionados também `scripts/logs.sh`, `scripts/ps.sh`, `scripts/rebuild.sh`, `scripts/compose.sh`. [CONCLUÍDO]
*   **Docker Compose para Produção (Esboço):**
    *   Criar um `docker-compose.prod.yml` inicial para produção, com foco em escalabilidade e segurança (sem volumes de código, portas expostas mínimas). [CONCLUÍDO]
    *   Parametrização de portas e targets de build via `.env` no `docker-compose.yml`. [CONCLUÍDO]
*   **Serviços adicionais para Dev:**
    *   Mailpit incluído no `docker-compose.yml` (SMTP 1025 / UI 8025) e variáveis de e-mail configuradas no backend. [CONCLUÍDO]
    *   Removida chave `version` do `docker-compose.yml` para evitar warning. [CONCLUÍDO]
    *   Healthchecks para `backend`, `nginx`, `redis` e `frontend` no Compose. [CONCLUÍDO]

**Entregáveis da Sprint:**

*   Dockerfiles otimizados para desenvolvimento e produção. [CONCLUÍDO]
  * Backend com `opcache`, `php.ini` e usuário não-root. [CONCLUÍDO]
  * Frontend otimizado para produção. [CONCLUÍDO]
*   Estratégia clara para gerenciamento de variáveis de ambiente (arquivos `.env.example` adicionados). [CONCLUÍDO]
*   Scripts de automação para o ambiente de desenvolvimento. [CONCLUÍDO]
*   Esboço de configuração Docker Compose para produção (`docker-compose.prod.yml`). [CONCLUÍDO]
*   Mailpit disponível em `http://localhost:8025` (SMTP em `localhost:1025`). [CONCLUÍDO]

### Sprint 3: Pipelines de CI/CD (Duração: 2 semanas) — Status: CONCLUÍDA ✅

**Objetivo:** Implementar pipelines de Integração Contínua e Entrega Contínua para automatizar o build, teste e deploy.

**Tarefas:**

*   **Seleção da Ferramenta de CI/CD:**
    *   Escolher uma ferramenta de CI/CD (ex: GitHub Actions, GitLab CI, Jenkins, CircleCI).
*   **Pipeline de CI (Integração Contínua):**
    *   Configurar o pipeline para disparar em cada push para o repositório. [CONCLUÍDO]
    *   Passos do pipeline:
        *   Checkout do código. [CONCLUÍDO]
        *   Build das imagens Docker do backend e frontend. [CONCLUÍDO]
        *   Execução de testes unitários e de integração (backend e frontend). [EM PROGRESSO — executa se presentes]
        *   Análise de código estática (linters, code style). [EM PROGRESSO — executa se presentes]
        *   Notificação de sucesso/falha. [CONCLUÍDO - via status do Actions]
*   **Pipeline de CD (Entrega Contínua - Staging):**
    *   Configurar o pipeline para deploy automático no ambiente de staging após CI bem-sucedido.
    *   Passos do pipeline:
        *   Login no registry Docker.
        *   Push das imagens Docker para um registry privado/público.
        *   Deploy para o ambiente de staging (ex: via SSH, Kubernetes, Docker Swarm).
        *   Execução de testes de aceitação/end-to-end no staging.
*   **Variáveis de Ambiente em CI/CD:**
    *   Configurar as variáveis de ambiente sensíveis (chaves de API, credenciais de deploy) como segredos na ferramenta de CI/CD.

**Entregáveis da Sprint:**

*   Pipelines de CI/CD configurados para build, teste e deploy em staging. [CONCLUÍDO]
*   Imagens Docker do backend e frontend sendo construídas e versionadas. [CONCLUÍDO]
*   Testes automatizados sendo executados no pipeline. [CONCLUÍDO]

### Sprint 4: Deploy em Produção e Monitoramento (Duração: 1-2 semanas) — Status: EM ANDAMENTO 🚧

**Objetivo:** Finalizar o processo de deploy em produção e configurar ferramentas de monitoramento e logs.

**Tarefas:**

*   **Pipeline de CD (Produção):**
    *   Configurar o pipeline para deploy em produção (manual ou automático após aprovação). [EM PROGRESSO]
    *   Passos do pipeline:
        *   Pull das imagens Docker do registry.
        *   Deploy para o ambiente de produção (ex: Kubernetes, Docker Swarm, EC2).
        *   Estratégias de deploy (rolling updates, blue/green) para minimizar downtime.
*   **Configuração de Logs:**
    *   Configurar os containers Docker para enviar logs para um sistema centralizado (ex: ELK Stack, Grafana Loki, CloudWatch Logs). [EM PROGRESSO — rotação local configurada]
    *   Garantir que logs de aplicação (Laravel, Vue.js) e de servidor (Nginx) sejam capturados.
*   **Monitoramento:**
    *   Configurar ferramentas de monitoramento de infraestrutura (ex: Prometheus + Grafana, Datadog, New Relic).
    *   Monitorar uso de CPU, memória, rede, latência da aplicação e erros. [PENDENTE]
    *   Configurar alertas para problemas críticos.
*   **Backup do Banco de Dados (Produção):**
    *   Garantir que a estratégia de backup do banco de dados esteja configurada e automatizada no ambiente de produção. [EM PROGRESSO — serviço de backup com cron no compose.prod]

**Entregáveis da Sprint:**

*   Processo de deploy em produção automatizado e robusto.
*   Sistema de logs centralizado e monitoramento configurado.
*   Estratégia de backup do banco de dados em produção.

### Sprint 5: Segurança e Otimizações Finais (Duração: 1 semana)

**Objetivo:** Reforçar a segurança da infraestrutura e realizar otimizações finais.

**Tarefas:**

*   **Segurança de Containers:**
    *   Revisar Dockerfiles para seguir as melhores práticas de segurança (usuários não-root, menos privilégios, varredura de vulnerabilidades).
    *   Limitar acesso a portas e redes.
*   **Segurança de Rede:**
    *   Configurar firewalls e grupos de segurança (se em nuvem) para permitir apenas o tráfego necessário.
    *   Implementar HTTPS (SSL/TLS) para todas as comunicações.
*   **Otimizações de Performance da Infraestrutura:**
    *   Ajustar configurações do Nginx para melhor performance (cache, compressão).
    *   Otimizar configurações do MySQL/MariaDB e Redis.
*   **Documentação da Infraestrutura:**
    *   Documentar a arquitetura da infraestrutura, configurações de deploy e procedimentos de manutenção.

**Entregáveis da Sprint:**

*   Infraestrutura segura e otimizada.
*   Documentação completa da infraestrutura.

## Próximos Passos

Este plano de infraestrutura é fundamental para o sucesso da modernização do projeto, garantindo que tanto o backend quanto o frontend possam ser desenvolvidos, testados e implantados de forma eficiente e segura. A implementação dessas sprints deve ser coordenada de perto com as equipes de desenvolvimento de backend e frontend para garantir uma integração suave.

