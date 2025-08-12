## Requisitos do Singre — Gestão de Clientes, Equipamentos, Serviços e Ordens de Serviço

### Escopo
Este documento consolida os requisitos funcionais e não funcionais da aplicação Singre, com foco em:
- **Clientes**: cadastro, consulta, atualização e exclusão.
- **Equipamentos**: vínculo ao cliente, controle de séries, histórico/intervenções.
- **Serviços**: catálogo (serviços/produtos), regras de preços.
- **Ordens de Serviço (OS)**: ciclo de vida completo (ativa/concluída), orçamento, comissões, integração financeira e notificações.

Fontes: código dos módulos `ordem_servico.php`, `ordem_servico_1.php`, `ordemservico_relatorio*.php`, `cliente.php`, `parametros_os.php`, `include/funcoes.php`, `equipamento_selecao.php`, `js/ordem_servico.js`, `js/servico.js` e correlatos.

---

### Visão geral de entidades principais
- **Cadastro de Cliente (`cadastrocliente`)**: dados cadastrais, contrato, credenciais, contatos, e-mail.
- **Cadastro de Equipamento (`cadastroequipamento`)**: séries (empresa e fabricante), marca/modelo, configuração, cliente, contador de intervenções.
- **Parâmetros OS**:
  - `cadastrotipoatendimento` (Tipo), `cadastroformaatendimento` (Forma, com flag de garantia/comissão), `cadastrofaseatendimento` (Fase, com pontos/comissão), `cadastroconsultacliente` (Consulta).
- **Ordem de Serviço**: tabelas gêmeas por status:
  - `ordemservico_ativa` (em andamento)
  - `ordemservico_concluida` (finalizada)
- **Orçamento da OS**:
  - `orcamentoordem_ativa` e `orcamentoordem_concluida` (itens: PRODUTO/SERVIÇO, quantidade, valor, comissionável)
- **Comissões de Técnico (`empregado_comissao`)**
- **Contas a Receber (`contasreceber_total`, `contasreceber_fatura`)**
- **Autorizada x Equipamento (`autorizada_equipamento`)**
- **Configuração (`configuracao`)**: parâmetros gerais (numeração, preços/horas, comissões, e-mail, contas a receber etc.)
- **Auditoria (`auditoria`) e Semáforo (`semaforo`)**

---

### Requisitos Funcionais

#### Autenticação e Perfis de Acesso
- **Sessão obrigatória** para acesso; usuários não autenticados são redirecionados para `index.php`.
- **Controle de acesso por perfil** via `verifica_perfil(...)` em cada operação de cada tabela (Acessar/Incluir/Alterar/Excluir).
- **Bloqueio concorrente** por usuário/tabela/registro via `semaforo` quando em inclusão/edição; liberação ao concluir/cancelar.

#### Clientes
- **CRUD de clientes** com validações de campos obrigatórios e formatos (telefone, CEP, CNPJ/CPF com dígitos verificadores).
- **Prevenção de duplicidade** por CNPJ/CPF em inclusão/alteração.
- **Campos de contrato**: número, valor, datas de início/fim.
- **Envio de e-mail em massa ou individual** a clientes (PEAR Mail/PHPMailer), com filtros (por nome, aniversário).
- **Exclusão segura**: ao excluir cliente, reatribuir vínculos a um cliente “Atualizar%” padrão antes de remover o cadastro.

#### Equipamentos
- **Vínculo obrigatório ao cliente**.
- **Identificação por série da empresa e/ou do fabricante**, com atualização automática quando informado apenas um dos dois.
- **Atualização automática ou criação** do equipamento a partir da OS (inclusão/alteração/conclusão), mantendo marca/configuração e incrementando `eqp_qtdintervencao`.
- **Histórico por série**: consulta de OS por série informada (empresa/fabricante) e por cliente.
- **Serviço XML** (`equipamento_selecao.php`) para retornar dados de equipamento à UI.

#### Parâmetros de OS (Tipo/Forma/Fase/Consulta)
- **CRUD dos parâmetros** (Tipo, Forma, Fase, Consulta), com marcação de padrão.
- **Forma** define flag de garantia/comissão e pode exigir seleção de Autorizada/Equipamento na OS.
- **Fase** define pontos e se gera **comissão**.
- **Alterações/exclusões** propagam para registros de OS (ativas/concluídas) conforme necessário.

#### Ordens de Serviço — Ciclo de Vida
- **Numeração automática** da OS em `configuracao.cnf_ordemservico` no formato N/YY, reiniciando em 1 a cada mudança de ano.
- **Estados**: Ativa (`ordemservico_ativa`) e Concluída (`ordemservico_concluida`).
- **Operações**:
  - **Incluir**: cria OS ativa e (se aplicável) registra itens-orçamento e atualiza/insere equipamento.
  - **Alterar**: atualiza dados; regra de categoria de usuário “T” (técnico) pode forçar o próprio como técnico e preencher horas automaticamente.
  - **Excluir**: remove OS e limpa comissões vinculadas quando aplicável.
  - **Concluir**: move OS de ativa → concluída, consolida orçamento (itens), calcula/computa **comissões** e integra com **Contas a Receber** (configurável).
  - **Ativar**: move OS de concluída → ativa (reabre), com reversão de itens e status de comissões.
- **Campos principais da OS**:
  - Cliente, Tipo/Forma/Fase/Departamento, Consulta Cliente, Atendente, Técnico
  - Datas/horas: entrada, início, fim, consulta, conclusão
  - Contato/Setor, Valor, Valor pago (autorizada), Solicitação (defeito), Serviço executado, Observações
  - Equipamento: série empresa, série fabricante, marca/modelo, configuração; contador de intervenções
  - Flags: e-mail enviado na conclusão
- **Validações de negócio na UI** (`js/ordem_servico.js`):
  - Campos obrigatórios (ex.: Nome Fantasia, Solicitação, Data Entrada; datas na conclusão)
  - **Consistência temporal**: Entrada ≤ Início ≤ Fim ≤ Conclusão; nenhuma data pode ser maior que a atual
  - **Formato de datas** DD/MM/AAAA e valores numéricos (com vírgula decimal)
  - **Regras de Autorizada/Equipamento** quando Forma indica garantia/comissão
- **Restrições de técnico/usuário**:
  - Usuário categoria “T” (técnico) não deve operar OS de outro técnico; em caso de divergência, a UI solicita confirmação.

#### Orçamento da OS
- **Itens de orçamento** por OS e por status (ativa/concluída), com tipo `SERVICO` ou `PRODUTO`, quantidade, valor e campo de comissão.
- **Geração automática de itens** conforme regras:
  - Tipo de atendimento “Externo”: incluir `VISITA TÉCNICA` (inclusão) e/ou `HORA TÉCNICA` proporcional às horas (alteração/conclusão) com valores parametrizados por contrato/avulso.
- **Consolidação na conclusão**: itens migrados de ativa → concluída; atualização de valores quando necessário.

#### Comissões de Técnicos
- **Habilitação por Fase** (`fsa_comissao = 'S'`).
- **Cálculo**:
  - Percentuais do técnico: `emp_comissaoproduto`, `emp_comissaoservico`.
  - Base de serviço: soma de itens de `SERVICO`, descontando itens marcados com `orc_comissao` (quando aplicável) + adicional por “garantia” via `os_valorpago`.
  - Base de produto: soma de itens de `PRODUTO` (descontos quando aplicável).
  - Aplicar limites de **mínimo** e **teto percentual**: `configuracao.cnf_valorminimocomissao`, `cnf_percentualmaximocomissao`.
- **Persistência** em `empregado_comissao` com situação `A` (ativa) ou `C` (concluída); atualização/remoção conforme recálculo; data de referência pode ser a de conclusão quando “Histórico”.

#### Integração Financeira — Contas a Receber
- Controlada por `configuracao.cnf_contasreceber`:
  - **Se habilitado** e a OS tiver `os_valor > 0` ou `os_valorpago > 0`, gerar/atualizar:
    - `contasreceber_total` (nota fiscal = número da OS; cliente pode ser a autorizada quando `os_valorpago > 0`)
    - `contasreceber_fatura` (vencimento = data de conclusão; valor da nota)
  - Em cenário de exigência prévia, pode haver bloqueio de conclusão sem fatura (varia conforme ramo do código utilizado).

#### Notificações por E-mail na Conclusão
- Parâmetros de e-mail em `configuracao` (host, porta, usuário/senha, diretório do PHPMailer, flag de envio na conclusão).
- **Envio automático** ao concluir OS ativa quando habilitado, contendo dados de data/hora, técnico, cliente, telefone, solicitação e serviço.

#### Relatórios e Impressão
- **Relatórios** de OS (ativas/concluídas), estatísticas por tipo/forma/técnico/cliente e períodos.
- **Impressão** via páginas dedicadas (ex.: `ordemservico_relatorio*.php`, `impressao_principal.php`).

#### Pontuação por Fase (Gamificação/Controle)
- **Pontos por fase** (`fsa_ponto`) acumulados por empregado na tabela `empregado_fase` por mês/ano, em eventos de inclusão/alteração e mudança de fase.

---

### Regras de Validação (resumo)
- **Datas** em formato DD/MM/AAAA; ordem cronológica obrigatória entre Entrada, Início, Fim e Conclusão; não podem exceder a data atual.
- **Obrigatórios**: Nome Fantasia, Solicitação, Data de Entrada; em conclusão, Fim e Conclusão.
- **CNPJ/CPF** com máscara e dígito verificador corretos; e-mail com expressão regular.
- **Autorizada/Equipamento**: se Forma indicar garantia/comissão, ambos devem ser informados; não permitir Autorizada sem Equipamento.

---

### Requisitos Não Funcionais

#### Arquitetura e Plataforma
- **Linguagem**: PHP (uso extensivo de `mysql_*`) com páginas server-rendered, HTML/CSS/JS.
- **Banco de dados**: MySQL.
- **Codificação**: `ISO-8859-1` predominante.
- **Compatibilidade de navegador**: estilos e ajustes específicos para IE (arquivos CSS `*_ie6.css`, `*_ie7.css`, `*_ie8.css`).

#### Segurança
- **Sessões** com timeout baseado na diferença de datas (`include/funcoes.php::diferenca_data`).
- **Controle de acesso** por perfil/oper ação a nível de tabela.
- Observação: o código utiliza **`extract($_POST/$_GET)`** e concatenação SQL sem prepared statements, o que representa risco de injeção de SQL e XSS se não mitigado em camadas externas.

#### Auditoria e Concorrência
- **Auditoria** opcional em inclusões/alterações/exclusões (tabela `auditoria`), quando sinalizado nas chamadas utilitárias.
- **Semáforo** de edição por usuário/registro/tabela para evitar conflitos de concorrência.

#### Desempenho e Escalabilidade
- Consultas SQL síncronas; algumas páginas executam múltiplas consultas e loops; há `sleep()` e limites de envio em massa de e-mails.
- `max_execution_time` elevado em operações específicas (ex.: `cliente.php`).

#### Internacionalização/Localização
- **Formatação brasileira**: datas `DD/MM/AAAA`, números com vírgula decimal.
- **Textos e mensagens** em português.

#### Manutenibilidade e Qualidade
- Regras de negócio distribuídas em páginas PHP monolíticas e em JS; utilitários em `include/funcoes.php` (conversão de datas/valores, CRUD genérico, auditoria, perfil, e-mail).
- Ausência de camadas separadas (DAO/ORM) e testes automatizados no repositório.

---

### Parâmetros de Configuração relevantes (`configuracao`)
- **Numeração de OS**: `cnf_ordemservico` (controle incremental `N/YY`).
- **Valor/Preço**: `cnf_alteravaloros` (permite editar `os_valor`), `cnf_servicocontrato`, `cnf_servicoavulso`, `cnf_horacontrato`, `cnf_horaavulso`.
- **Comissões**: `cnf_valorminimocomissao`, `cnf_percentualmaximocomissao`.
- **Contas a receber**: `cnf_contasreceber` (habilita integração financeira na conclusão).
- **E-mail**: `cnf_emailosconcluida`, `cnf_enderecoemailconcluida`, `cnf_emailhost`, `cnf_emailautenticacao`, `cnf_emailusuario`, `cnf_emailsenha`, `cnf_emailporta`, `cnf_diretoriophpmailer`.

---

### Assunções e Limitações Observadas
- **Banco de dados** já contém registros “padrão/Atualizar%” para evitar que exclusões quebrem integridades (cliente, tipo/forma/fase/consulta).
- **Integração financeira** depende da flag `cnf_contasreceber` e da existência/geração de faturas/notas.
- **Envio de e-mail** depende de configuração completa de SMTP e diretório do PHPMailer no servidor.
- **Riscos técnicos**: uso de `mysql_*`, `extract`, concatenação SQL e saída HTML sem escaping consistente.

---

### Principais fluxos (resumo por OS)
- **Abertura**: selecionar cliente → gerar número OS → preencher defaults (Tipo/Forma/Fase/Depto padrão) → salvar OS ativa.
- **Execução**: registrar início/serviço/fim; para “Externo”, calcular horas e incluir `HORA TÉCNICA` conforme configurado.
- **Conclusão**: validar datas/obrigatórios → migrar OS e itens para concluída → calcular comissões → integrar Contas a Receber (se habilitado) → opcionalmente enviar e-mail → marcar OS ativa como excluída.
- **Reabertura (Ativar)**: migrar concluída → ativa, itens e comissões reativadas/ajustadas; remover lançamentos financeiros, se aplicável.

---

### Arquivos e pontos de referência
- `ordem_servico.php` e `ordem_servico_1.php`: regras de OS, orçamento, comissões, integração financeira, e-mail.
- `ordemservico_relatorio*.php`: relatórios e estatísticas.
- `cliente.php`: CRUD e e-mail de clientes.
- `parametros_os.php`: tipos/formas/fases/consultas e regras vinculadas.
- `include/funcoes.php`: utilitários (conversões, CRUD genérico, auditoria, semáforo, perfil, e-mail).
- `equipamento_selecao.php`: serviço XML para dados de equipamento.
- `js/ordem_servico.js`: validações de formulário da OS.

---

### Requisitos futuros sugeridos (não implementados, mas recomendados)
- **Segurança**: adoção de prepared statements/ORM; sanitização/escaping centralizada; revisão de uso de `extract`.
- **Arquitetura**: separação em camadas (MVC), cobertura de testes, normalização de encoding (UTF-8), remoção de dependências IE.
- **Observabilidade**: logs estruturados e monitoramento de erros.
- **APIs**: endpoints REST para clientes, OS, equipamentos e relatórios.

---

## Validação de Cobertura no Singre‑v2

Legenda de status:
- [OK] Implementado
- [PARCIAL] Implementado parcialmente (lacunas listadas)
- [FALTANDO] Não implementado

### Visão geral (executivo)
- Cobertura funcional geral: [PARCIAL]
- Itens críticos ausentes: Equipamentos, Parâmetros de OS (Tipo/Forma/Fase/Consulta), Comissões, Contas a Receber, Regras de validação detalhadas, Pontuação por fase, Auditoria/Semáforo.
- Itens presentes: Autenticação básica (Sanctum), CRUD de Clientes/Produtos/Serviços, OS com itens (produtos/serviços), histórico de status, PDF da OS, relatórios básicos, e‑mail ao criar OS, Configurações básicas (empresa).

### Mapeamento requisito → implementação singre‑v2

- **Autenticação e Perfis de Acesso**: [PARCIAL]
  - Presente: autenticação via bearer token (Sanctum) e proteção das rotas de API.
    - Referências: `backend/routes/api.php` (middleware `auth:sanctum`), `backend/app/Http/Controllers/Api/AuthController.php`, `frontend/src/lib/api.ts` (header Authorization), `frontend/src/router.ts` (guard).
  - Lacunas: controle por perfil/ação (RBAC) por entidade; semáforo de edição concorrente.

- **Clientes (CRUD, validações, e‑mail)**: [PARCIAL]
  - Presente: CRUD, paginação, busca, ordenação; validação de e‑mail único; soft‑delete/restauração.
    - Referências: `backend/app/Http/Controllers/Api/ClientController.php`, migrações de `clients`, telas `frontend/src/views/clients/*`.
  - Lacunas: validação de CPF/CNPJ com dígito verificador; prevenção de duplicidade por documento; envio de e‑mail em massa/individual; exclusão segura com reatribuição para "Atualizar%".

- **Equipamentos**: [PARCIAL]
  - Presente: modelo `Equipment`, migrações de tabela e `equipment_id` na OS; API REST (`/api/equipment`); vínculo opcional da OS ao equipamento; incremento automático de `intervention_count` ao criar OS e ao alterar o equipamento vinculado; UI de OS exibe e permite selecionar equipamento.
    - Referências: `backend/app/Models/Equipment.php`, migrações `create_equipment_table` e `add_equipment_id_to_service_orders`, `backend/app/Http/Controllers/Api/EquipmentController.php`, rotas em `backend/routes/api.php`, telas `OrderCreate.vue`/`OrderEdit.vue`/`OrderShow.vue`.
  - Lacunas: histórico por série (consulta de OS por série), atualização/auto‑criação a partir de alterações/conclusão da OS (regras completas), serviço XML legado, telas dedicadas de gestão de equipamentos.

- **Parâmetros de OS (Tipo/Forma/Fase/Consulta)**: [FALTANDO]
  - Não há entidades/CRUD para parâmetros; flags de garantia/comissão não existem; sem propagação para OS.

- **Ordens de Serviço — Ciclo de Vida**: [PARCIAL]
  - Presente: criação/edição/exclusão; status `open/in_progress/closed`; timestamps `opened_at/closed_at`; histórico de status; PDF.
    - Referências: `backend/app/Models/ServiceOrder.php`, `backend/app/Http/Controllers/Api/ServiceOrderController.php` (histórico em `ServiceOrderStatusHistory`), `backend/resources/views/pdf/service_order.blade.php`, telas `frontend/src/views/orders/*`.
  - Atualização: numeração automática `N/YY` implementada via `code` e colunas `sequence_year/sequence_number`, com controle por `Setting os.sequence.{YYYY}`.
  - Lacunas: regras específicas de categoria de usuário/técnico; reabertura (ativar) com reversões; envio de e‑mail na conclusão (só ao criar); campos/fluxos adicionais (autorizada, equipamento com auto‑criação e regras de série, horas, etc.).

- **Orçamento da OS**: [PARCIAL]
  - Presente: itens (produtos/serviços) com quantidade, preço e total via pivô; recálculo do total; exibição na UI.
    - Referências: pivôs `service_order_product`/`service_order_service` e telas de OS.
  - Lacunas: geração automática de itens (VISITA TÉCNICA/HORA TÉCNICA) baseada em Tipo/Forma/horas; consolidação específica na conclusão.

- **Comissões de Técnicos**: [FALTANDO]
  - Inexistente: cálculo, persistência e reprocessamentos; ausência de `empregado_comissao` ou equivalente.

- **Integração Financeira — Contas a Receber**: [FALTANDO]
  - Não há geração/atualização de faturas/notas (`contasreceber_*`) nem chave de configuração equivalente.

- **Notificações por E‑mail na Conclusão**: [PARCIAL]
  - Presente: e‑mail enviado ao criar OS.
    - Referências: `backend/app/Mail/ServiceOrderCreated.php`, disparo em `ServiceOrderController@store`.
  - Lacunas: envio automático na conclusão com conteúdo exigido; parametrização completa (host/porta/usuário/senha/diretório PHPMailer) — hoje usa stack de e‑mail do Laravel e Mailpit em dev.

- **Relatórios e Impressão**: [OK]
  - Presente: endpoints de relatórios (sumário, top, séries temporais, séries por status, KPIs) e PDF da OS; UI para relatórios.
    - Referências: `backend/app/Http/Controllers/Api/ReportController.php`, rotas `reports/*`, PDF `resources/views/pdf/service_order.blade.php`, UI `frontend/src/views/ReportsView.vue`.
  - Observação: escopo de relatórios é básico em relação ao legado; pode exigir ampliações específicas.

- **Pontuação por Fase (Gamificação/Controle)**: [FALTANDO]
  - Inexistente: `fsa_ponto`, `empregado_fase` e eventos de acúmulo.

- **Regras de Validação (datas, obrigatórios, máscaras)**: [FALTANDO]
  - Não há validações de sequência temporal específicas na conclusão; UI não utiliza formato DD/MM/AAAA; não há validações de CPF/CNPJ com DV; regras de autorizada/equipamento não existem.

### Requisitos Não Funcionais

- **Arquitetura e Plataforma**: [N/A — substituído]
  - V2 adota Laravel 11 + Vue 3 (SPA), Eloquent ORM, UTF‑8. Os requisitos legacy (PHP procedural, `mysql_*`, ISO‑8859‑1, compatibilidade IE) não se aplicam e foram superados por tecnologia moderna.

- **Segurança**: [PARCIAL]
  - Presente: Sanctum, validações em FormRequest/Controller, ORM evita concatenação SQL.
  - Lacunas: RBAC por recurso/ação; políticas/guards granulares; ausência de auditoria de ações.

- **Auditoria e Concorrência**: [FALTANDO]
  - Não há trilhas de auditoria nem semáforo de edição.

- **Desempenho e Escalabilidade**: [OK]
  - Padrões Laravel, paginação e consultas ORM; sem dependências de longas execuções na UI.

- **Internacionalização/Localização**: [PARCIAL]
  - UI em português; formatação financeira em `pt‑BR` na UI. Datas seguem ISO (yyyy‑mm‑dd) na API/UI; não há máscaras brasileiras por padrão.

- **Manutenibilidade e Qualidade**: [PARCIAL]
  - Código em camadas, recursos REST, alguns testes feature para APIs; ainda com baixa cobertura de testes e sem módulos críticos (equipamentos, comissões, etc.).

- **Parâmetros de Configuração**: [PARCIAL]
  - Presente: chave/valor com cache (`Setting`), seeds básicos de empresa.
    - Referências: `backend/app/Models/Setting.php`, `SettingController`, `SettingSeeder`.
  - Lacunas: parâmetros específicos do legado (numeração OS `cnf_ordemservico`, valores de serviço/hora por contrato/avulso, comissões, contas a receber, e‑mail legado) não mapeados.

### Ações recomendadas para 100% de cobertura (priorização)
1) Implementar módulo de `Equipamentos` com vínculo obrigatório à OS, séries empresa/fabricante, histórico, e serviço de busca.
2) Implementar entidades `Tipo/Forma/Fase/Consulta` com regras (garantia/comissão/pontos) e defaults.
3) Estender OS para numeração `N/YY`, reabertura/ativação e envio de e‑mail na conclusão; regras de técnico.
4) Implementar orçamento automático por regras (VISITA/HORA) e consolidação na conclusão.
5) Implementar `Comissões` (cálculo, limites, persistência) e `Contas a Receber` integradas por flag.
6) Regras de validação de dados (datas, CPF/CNPJ, obrigatórios) na API e UI; máscaras brasileiras.
7) Auditoria de operações e semáforo de concorrência.
8) RBAC por perfil/ação com políticas/gates e perfis atribuíveis a usuários.
9) Ampliar relatórios para cobrir estatísticas do legado e incluir filtros equivalentes.

### Evidências (trechos relevantes)
- Rotas protegidas e recursos:
  - `backend/routes/api.php` (grupos com `auth:sanctum` e recursos `clients`, `products`, `services`, `service-orders`, `settings`, `reports/*`).
- Modelos e pivôs da OS:
  - `backend/app/Models/ServiceOrder.php` (relacionamentos `products`, `services`, `statusHistories`).
  - Migrações: `create_service_orders_table`, `create_service_order_product_table`, `create_service_order_service_table`.
- Histórico de status:
  - `backend/app/Models/ServiceOrderStatusHistory.php`, migração `create_service_order_status_histories` e gravação em `ServiceOrderController@update`.
- PDF e e‑mail de OS:
  - `resources/views/pdf/service_order.blade.php`, método `pdf()` no `ServiceOrderController`.
  - `backend/app/Mail/ServiceOrderCreated.php` e envio em `store()`.
- UI de OS e relatórios:
  - `frontend/src/views/orders/*`, `frontend/src/views/ReportsView.vue`.

Data da validação: 2025‑08‑12.
