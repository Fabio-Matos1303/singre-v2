# Backlog e Sprints para a Modernização do Frontend

Este documento detalha o backlog de tarefas e a divisão em sprints para a modernização do frontend do sistema de gerenciamento de ordens de serviço. O objetivo é migrar a interface legada para uma aplicação moderna baseada em Vue.js, consumindo a API RESTful desenvolvida com Laravel.

## Visão Geral da Arquitetura

O novo frontend será uma Single Page Application (SPA) desenvolvida com Vue.js, utilizando:

*   **Framework:** Vue.js 3 (com Composition API).
*   **Gerenciamento de Estado:** Pinia (recomendado para Vue 3).
*   **Roteamento:** Vue Router.
*   **Requisições HTTP:** Axios.
*   **Componentes UI:** Um framework de componentes UI (ex: Vuetify, Element Plus, Quasar) ou um sistema de design customizado.
*   **Empacotador:** Vite (para desenvolvimento rápido e build otimizado).
*   **Ambiente de Desenvolvimento:** Docker (para padronização e isolamento do ambiente).

## Backlog Detalhado

O backlog foi dividido em funcionalidades principais, que serão então quebradas em tarefas menores e alocadas em sprints.

### Módulos Principais a Serem Migrados/Desenvolvidos:

1.  **Autenticação e Autorização:** Telas de login, registro, recuperação de senha e controle de acesso baseado em permissões.
2.  **Dashboard:** Visão geral com informações importantes (resumo de OS, clientes, etc.).
3.  **Gerenciamento de Clientes:** Telas de listagem, criação, edição e visualização de clientes.
4.  **Gerenciamento de Produtos/Serviços:** Telas de listagem, criação, edição e visualização de produtos/serviços.
5.  **Gerenciamento de Ordens de Serviço:** Telas de listagem, criação, edição, visualização detalhada, atualização de status e histórico.
6.  **Relatórios:** Interface para visualização e download de relatórios (PDFs).
7.  **Configurações do Sistema:** Interface para gerenciamento das configurações gerais.

## Definição das Sprints (com status)

Cada sprint terá duração de 1 a 2 semanas, com foco em entregas incrementais e funcionais. As tarefas relacionadas ao Docker serão integradas conforme a necessidade.

### Sprint 1: Setup do Ambiente e Autenticação (concluída)

Objetivo: Configurar o ambiente de desenvolvimento Vue.js com Vite e Docker, e implementar as telas de autenticação.

Checklist:
- [x] Ambiente Vue + Vite configurado e rodando em Docker
- [x] Proxy `/api` e CSP ajustados no Nginx do frontend
- [x] Router e Pinia configurados
- [x] Login integrado ao backend (Sanctum tokens via Bearer)
- [x] Guard de rotas para áreas autenticadas
- [x] Registro de usuário (tela + integração)
- [x] Componentes UI básicos reutilizáveis (botão, input, select) aplicados nas telas de auth

Entregáveis:
- SPA base funcional com autenticação e proteção de rotas
- Telas de Login e Registro

Próximas ações (Sprint 1):
- Implementar tela de registro e integrar com `/api/auth/register`
- Criar componentes `UIButton`, `UIInput`, `UISelect` e usá-los nas telas de auth

### Sprint 2: Dashboard e Gerenciamento de Clientes (concluída)

Objetivo: Desenvolver a tela de Dashboard e as funcionalidades de CRUD para Clientes.

Checklist:
- [x] Listagem de clientes com busca e filtros (soft delete)
- [x] Paginação completa (página atual, total)
- [x] Criação, edição e visualização de cliente
- [x] Exclusão e restauração
- [x] Exibição de validações do backend nos formulários
- [x] Dashboard com KPIs reais (KPIs, resumo, tops)

### Sprint 3: Produtos/Serviços e Ordens de Serviço (concluída)

Objetivo: CRUD de Produtos/Serviços e OS com relacionamento e PDF.

Checklist:
- [x] CRUD de Produtos com busca, soft delete, paginação e validações
- [x] CRUD de Serviços com busca, soft delete, paginação e validações
- [x] Listagem de OS com filtros (q, status, período) e paginação
- [x] Criação de OS (seleção de cliente/produtos/serviços, totais)
- [x] PDF de OS
- [x] Alteração de status com fechamento automático (`closed_at`)
- [x] Autocomplete de cliente/produto/serviço na criação de OS (com debounce)
- [x] Edição de itens da OS após criada (adicionar/remover/quantidades)
- [x] Timeline/histórico de status

### Sprint 4: Relatórios e Configurações (concluída)
- [x] UI para relatórios (listagem, filtros, downloads de PDF)
- [x] Gráficos de relatórios (série temporal e por status)
- [x] UI para configurações gerais (empresa, e-mail)

### Sprint 5: Refinamento, Testes e Otimizações (concluída)
- [x] Testes de unidade básicos (utilitários)
- [x] Otimizações de performance (lazy loading de rotas, code-splitting)
- [x] Refinos de UX/UI e feedbacks de erro (toasts, estados de loading/empty)

### Sprint 6: Deploy e Documentação (iniciada)
- [x] Pipeline de build e publicação (CI configurado com GitHub Actions para frontend e backend)
- [x] Documentação técnica e guia de deploy

## Próximos Passos

Com a conclusão dessas sprints, o frontend estará modernizado e pronto para ser utilizado. A integração com o backend ocorrerá de forma contínua ao longo das sprints, à medida que os endpoints da API forem sendo disponibilizados e estabilizados.

Este plano é um guia e pode ser ajustado conforme as necessidades e descobertas durante o processo de desenvolvimento. A comunicação constante entre as equipes de backend e frontend é crucial para o sucesso do projeto.

