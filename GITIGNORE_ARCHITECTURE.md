# Arquitetura dos Arquivos .gitignore - SINGRE V2

Este documento explica a estrutura e organização dos arquivos `.gitignore` no projeto SINGRE V2.

## 📁 Estrutura dos Arquivos .gitignore

```
singre-v2/
├── .gitignore                    # Principal - configurações gerais
├── backend/.gitignore           # Laravel - backend específico
├── frontend/.gitignore          # Vue.js - frontend específico
├── docker/.gitignore            # Docker - containers e volumes
├── scripts/.gitignore           # Scripts - automação e DevOps
└── GITIGNORE_ARCHITECTURE.md    # Este arquivo de documentação
```

## 🎯 Propósito de Cada Arquivo

### 1. `.gitignore` (Raiz)
- **Escopo**: Configurações gerais do projeto
- **Funcionalidade**: 
  - Arquivos do sistema operacional
  - Editores e IDEs
  - Configurações Docker gerais
  - Logs e arquivos temporários
  - Arquivos específicos do projeto

### 2. `backend/.gitignore`
- **Escopo**: Backend Laravel
- **Funcionalidade**:
  - Dependências Composer (`/vendor/`)
  - Arquivos de configuração (`.env`)
  - Storage e cache do Laravel
  - Build e compilação
  - Testes e cobertura
  - Segurança (chaves, certificados)

### 3. `frontend/.gitignore`
- **Escopo**: Frontend Vue.js
- **Funcionalidade**:
  - Dependências Node.js (`node_modules/`)
  - Build Vite (`dist/`, `dist-ssr/`)
  - Cache e temporários
  - Testes e cobertura
  - Configurações de ambiente
  - Arquivos de sistema

### 4. `docker/.gitignore`
- **Escopo**: Containers e volumes Docker
- **Funcionalidade**:
  - Volumes de dados (`data/`, `volumes/`)
  - Configurações locais
  - Logs dos containers
  - Backups e snapshots
  - Chaves e certificados

### 5. `scripts/.gitignore`
- **Escopo**: Scripts de automação
- **Funcionalidade**:
  - Logs de execução
  - Arquivos temporários
  - Configurações locais
  - Resultados de execução

## 🔧 Configurações Específicas

### Backend Laravel
```bash
# Ignora dependências
/vendor/
composer.phar

# Ignora arquivos de configuração
.env
.env.*
!.env.example

# Ignora storage mas mantém estrutura
/storage/*.key
/storage/logs/*.log
!/storage/framework/cache/.gitkeep
```

### Frontend Vue.js
```bash
# Ignora dependências
node_modules/
npm-debug.log*

# Ignora build
dist/
dist-ssr/

# Ignora cache
.vite/
.vite-cache/
```

### Docker
```bash
# Ignora volumes
data/
volumes/
mysql/

# Ignora configurações locais
.env.docker
docker-compose.override.yml
```

## 🚀 Benefícios da Arquitetura

### 1. **Organização Clara**
- Cada diretório tem seu próprio `.gitignore`
- Fácil manutenção e atualização
- Responsabilidades bem definidas

### 2. **Flexibilidade**
- Configurações específicas por tecnologia
- Fácil adaptação para novos diretórios
- Manutenção independente

### 3. **Segurança**
- Proteção de arquivos sensíveis
- Exclusão de chaves e certificados
- Controle de variáveis de ambiente

### 4. **Performance**
- Exclusão de arquivos desnecessários
- Redução do tamanho do repositório
- Builds mais rápidos

## 📋 Boas Práticas Implementadas

### 1. **Comentários Organizados**
- Seções claramente definidas
- Explicações para cada categoria
- Fácil navegação e manutenção

### 2. **Exclusões Inteligentes**
- Mantém estrutura de diretórios
- Usa `.gitkeep` para diretórios vazios
- Exclui conteúdo mas preserva organização

### 3. **Segurança**
- Protege arquivos `.env`
- Exclui chaves e certificados
- Controle de acesso a dados sensíveis

### 4. **Manutenibilidade**
- Arquivos modulares
- Fácil atualização
- Documentação clara

## 🔄 Atualização e Manutenção

### 1. **Adicionar Nova Tecnologia**
```bash
# Criar novo .gitignore específico
mkdir nova-tecnologia/
touch nova-tecnologia/.gitignore

# Adicionar ao .gitignore principal se necessário
echo "# Nova tecnologia" >> .gitignore
echo "nova-tecnologia/temp/" >> .gitignore
```

### 2. **Atualizar Configurações**
```bash
# Verificar arquivos ignorados
git status --ignored

# Testar exclusões
git check-ignore arquivo-teste.txt
```

### 3. **Sincronizar Mudanças**
```bash
# Adicionar alterações
git add .gitignore
git add */gitignore

# Commit das mudanças
git commit -m "chore: atualiza configurações .gitignore"
```

## 📚 Referências

- [Laravel .gitignore](https://github.com/laravel/laravel/blob/master/.gitignore)
- [Vue.js .gitignore](https://github.com/vuejs/vue/blob/main/.gitignore)
- [Docker .gitignore](https://github.com/docker/docker/blob/master/.gitignore)
- [Git .gitignore](https://git-scm.com/docs/gitignore)

## 🤝 Contribuição

Para contribuir com melhorias nos arquivos `.gitignore`:

1. Identifique a área que precisa de ajustes
2. Proponha mudanças específicas
3. Teste as exclusões antes de submeter
4. Documente as mudanças neste arquivo

---

**Última atualização**: $(date)
**Versão**: 1.0.0
**Responsável**: Equipe de Desenvolvimento SINGRE V2
