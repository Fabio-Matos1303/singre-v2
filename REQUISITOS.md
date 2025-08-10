# Requisitos do Sistema SINGRE (legado) e Diretrizes de Modernização

Este documento reúne os requisitos funcionais observados no projeto legado (`/home/asus/programming/starnew/singre`) e requisitos não funcionais tanto observáveis quanto propostos (com base nos backlogs de modernização). Serve como base para o planejamento e rastreabilidade no `singre-v2`.

## Escopo

Sistema de gestão com módulos de Cadastro, Atendimento (Ordens de Serviço), Comercial (Compras/Vendas), Financeiro (Contas a Pagar/Receber e Caixa), Movimento (Estoque), Utilitário (Configurações/Backup/Restore) e Relatórios.

---

## Requisitos Funcionais (por módulo)

### 1) Autenticação e Autorização
- **Login de usuário**: tela inicial (`index.php`) com usuário/senha; sessão autenticada.
- **Perfis/Acessos**: manutenção de perfis e permissões (menu “Perfil Acesso” em `cadastro.php?cadastro=perfil_acesso`).
- **Esqueci a senha**: link visível na tela de login (funcionalidade a detalhar/confirmar no legado).

### 2) Cadastro
- **Empresas**: CRUD de empresas.
- **Empregados**: CRUD de empregados/colaboradores.
- **Clientes**: CRUD; envio de **e-mail** a partir do cadastro; relatórios.
- **Fornecedores**: CRUD; relatórios.
- **Autorizadas**: CRUD (parceiros/assistências técnicas).
- **Produtos**: CRUD; seleção e associação em vendas, compras e estoque; relatórios.
- **Serviços**: CRUD; associação a Ordens de Serviço; relatórios.
- **Ativo Fixo**: cadastro e controle básico de ativos.
- **Localidades**: cadastro de regiões/cidades/estados/bairros para endereços.
- **Finanças (parâmetros)**: parâmetros financeiros (plano de contas etc.).
- **Parâmetros OS**: configurações de fluxo e atributos de Ordem de Serviço.
- **Diversos**: tabela(s) auxiliares.
- **Perfil de Acesso**: definição de papéis/regras de acesso por módulo/ação.

Ações padrão por entidade: Incluir, Alterar, Excluir, Pesquisar, Relatório; para Cliente há também Email.

### 3) Atendimento / Ordens de Serviço
- **OS – CRUD**: criação, alteração, exclusão e pesquisa de OS.
- **Fluxo e estados**:
  - Concluir OS
  - Fase (controle de etapas)
  - Forma (formas relacionadas, ex.: pagamento/execução)
  - Consulta Cliente (busca/associação)
  - Histórico (registro de eventos/mudanças)
  - Ativar (reativar OS)
  - Orçamento (elaboração/associação de orçamento)
- **Relatórios**: OS, Clientes, Empregados, Equipamentos.

### 4) Comercial
- **Compras**:
  - CRUD de compras e itens
  - Relatórios: Geral, Produto, Estoque, Plano de Contas
- **Vendas**:
  - CRUD de pedidos e itens; geração de número de pedido
  - Diversos: Despachar Pedido, Pagamento, Recibo
  - Relatórios: Geral, Vencidos, A Receber, Recebidos, Plano de Contas, Vendedor, Contabilidade, Produto, Estoque, Produção, Expedição

### 5) Financeiro
- **Contas a Pagar**: CRUD; Relatórios (Geral, A Pagar, Pagas, Baixa Fatura, Plano de Contas, Contabilidade)
- **Contas a Receber**: CRUD; Relatórios (Geral, A Receber, Recebidas, Baixa Fatura, Plano de Contas, Vendedor)
- **Caixa**:
  - Ações: Abrir, Fechar, Suprimento, Retirada, Estorno
  - Diversos: Saída, Resumo, Impressão
  - Relatórios: Fita Detalhe, Resumo, Faturamento

### 6) Movimento (Estoque) e Auditoria
- **Estoque – Movimentações**: Saída, Entrada, Perda, Falta, Transferência, Transformação
- **Relatórios de Estoque**: Saída, Entrada, Perda, Falta, Transferência, Transformação, Estoque (posição)
- **Auditoria**: consultas e relatórios de auditoria (Geral, por Empregado)

### 7) Utilitário
- **Configurações do Sistema**: parâmetros gerais (empresa, e-mail, diretórios, etc.)
- **Backup**: iniciar backup do banco de dados (no legado, acionado via menu)
- **Restauração**: restauração a partir de backup (atenção a permissões/segurança)

### 8) Relatórios (consolidação)
- Relatórios por módulo (Cadastro, Comercial, Financeiro, Movimento, Atendimento), com filtros por período, entidade, status e agrupamentos (produto, vendedor, plano de contas, etc.).
- Emissão visual e impressão; alguns fluxos levam a telas de relatório dedicadas (ex.: `*_relatorio.php`).

### 9) Comunicação
- **E-mails**: envio a partir de cadastro de clientes; possibilidade de outros envios transacionais a confirmar.

---

## Regras de Negócio (principais evidências do legado)
- **OS**: ciclo com estados (fase/ativação/conclusão), histórico de eventos, associação a clientes, serviços e equipamentos.
- **Vendas**: emissão de pedido com itens, controle de entrega/expedição, pagamento e recibo; integração a contas a receber.
- **Compras**: itens vinculados a fornecedores; integração a estoque e contas a pagar.
- **Estoque**: movimentações diversas com impacto em saldos; relatórios por tipo de movimento.
- **Financeiro**: integração com plano de contas, baixa de faturas, relatórios contábeis e por vendedor.
- **Caixa**: abertura/fechamento com suprimentos/retiradas/estornos; emissão de fita detalhe e resumo.
- **Perfis/Acessos**: restrição por módulo/ação conforme perfil.

---

## Requisitos Não Funcionais (observados no legado)
- **Banco de Dados**: MySQL (uso de `mysql_*`), consultas SQL embutidas em páginas PHP.
- **Codificação**: páginas usam `charset=iso-8859-1` (necessário migrar para UTF-8 na modernização).
- **Front-end legado**: HTML/CSS/JS clássicos; detecção de navegador para CSS; foco em desktop.
- **Segurança**: autenticação por sessão; sem evidência de CSRF/XSS hardening; manipulação direta de `$_GET/$_POST` e `extract()`.
- **Relatórios/Impressão**: emissão via páginas PHP específicas; algumas rotinas abrem janelas para impressão/retorno.
- **Organização**: estrutura monolítica com múltiplos arquivos PHP por módulo.

---

## Requisitos Não Funcionais (diretrizes de modernização)
- **Backend**: PHP 8+, Laravel 10+, API RESTful, Eloquent ORM, validação robusta, cache e otimizações.
- **Autenticação**: Laravel Sanctum (tokens/cookies), CORS configurado, controles de autorização por perfil/role.
- **Banco de Dados**: MySQL/MariaDB com migrations/seeders; índice e integridade referencial.
- **Relatórios**: geração de PDF (ex.: dompdf) e e-mails transacionais (Mailpit/SMTP em dev).
- **Frontend**: Vue 3 (Vite), Pinia, Vue Router, Axios; UI responsiva (ex.: Vuetify/Element Plus).
- **Infra**: Docker Compose para dev; pipelines CI/CD; logs centralizados e monitoramento; backups automatizados.
- **Qualidade**: testes de unidade/integração; documentação da API (OpenAPI/Swagger); segurança (CSRF/XSS/input sanitization).

---

## Rastreabilidade (mapa rápido menu → arquivos do legado)
- **Cadastro**: `empresa.php`, `empregado.php`, `cliente.php`, `fornecedor.php`, `autorizada*.php`, `produto.php`, `servico.php`, `ativo.php`, `localidade.php`, `financas.php`, `parametros_os.php`, `diversos.php`, `perfil_acesso.php`
- **Atendimento (OS)**: `ordem_servico*.php`, `equipamento_selecao.php`
- **Comercial**: `compra*.php`, `venda*.php`, `vendas_diversos.php`, `valor_venda.php`
- **Financeiro**: `contas_pagar*.php`, `contas_receber*.php`, `financeiro*.php`, `contas*principal.php`, `contas*login.php`, `contas*pesquisa.php`, `contas*relatorio.php`
- **Movimento**: `produto_movimento.php`, `movimento*.php`, `auditoria.php`
- **Utilitário/Configurações**: `configura.php`, `utilitario.php`
- **Infra/Include**: `include/menu_*.php`, `include/header_*.php`, `include/conexaoDB.php`, `include/funcoes.php`

---

## Suposições e Lacunas
- **“Esqueci a senha”**: link presente, mas fluxo não evidenciado; precisa especificação.
- **E-mails**: há ação “Email” em Clientes; demais envios a confirmar.
- **Relatórios PDF**: no legado os relatórios são páginas; geração de PDF na modernização é um requisito.
- **Perfil de Acesso**: granularidade de permissões a detalhar por módulo/ação.

---

## Referências
- Código legado em `/home/asus/programming/starnew/singre` (menus: `include/menu*.php`).
- Backlogs de modernização: `backlog-backend.md`, `backlog-frontend.md`, `backlog-infra.md` (neste diretório `singre-v2`).
