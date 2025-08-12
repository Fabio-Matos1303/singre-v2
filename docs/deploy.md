# Deploy do SINGRE (Frontend + Backend)

Este guia explica como executar o sistema com Docker Compose (dev/prod), rodar migrações e configurar o pipeline de build/publicação via GitHub Actions.

## Requisitos
- Docker 24+
- Docker Compose v2
- Conta no Docker Hub (opcional, para publicar imagens)

## Visão geral dos serviços (docker-compose.yml)
- `mysql`: MySQL 8
- `redis`: cache/queue
- `mailpit`: SMTP de desenvolvimento (UI em 8025)
- `backend`: PHP-FPM do Laravel (porta 9000 interna)
- `scheduler`: worker do Laravel Schedule
- `nginx`: Nginx do backend (expõe por padrão 8080)
- `frontend`: Nginx servindo SPA (expõe por padrão 5173)
- `legacy-mysql`: opcional, para leitura de dados legados

Portas padrão (personalizáveis via env):
- Backend (Nginx): 8080
- Frontend (Nginx): 5173
- MySQL: 3307 (host) -> 3306 (container)
- Redis: 6379
- Mailpit: 1025 (SMTP) e 8025 (UI)

## Variáveis de ambiente úteis
Você pode criar um `.env` na raiz (onde está o `docker-compose.yml`) para ajustar:

```
MYSQL_HOST_PORT=3307
MYSQL_DATABASE=singre
MYSQL_USER=singre
MYSQL_PASSWORD=singre

NGINX_HOST_PORT=8080
FRONTEND_HOST_PORT=5173

MAILPIT_SMTP_PORT=1025
MAILPIT_UI_PORT=8025
```

## Subindo o ambiente

- Build e subir tudo:
```
docker compose up -d --build
```

- Verificar status:
```
docker compose ps
```

- Logs (ex.: nginx backend):
```
docker compose logs -f nginx
```

## Migrações do backend
Após subir os serviços:
```
docker compose exec backend php artisan migrate --force
```
Se precisar seed:
```
docker compose exec backend php artisan db:seed --force
```

## Acesso aos serviços
- Frontend: http://localhost:5173
- Backend (Nginx): http://localhost:8080
- Mailpit UI: http://localhost:8025

Observação: o frontend já está configurado para rotear `/api/*` para o Nginx do backend via `docker/nginx/frontend.conf`.

## Produção (build da SPA)
O `docker/frontend/Dockerfile` possui dois targets:
- `final-static`: copia o diretório `frontend/` (útil para dev)
- `final-built`: constrói a SPA com Vite e copia `dist/` para o Nginx (recomendado para prod/CI)

Na CI, usamos `target: final-built` para publicar a imagem otimizada.

## CI/CD (GitHub Actions)
Workflows adicionados:
- Frontend: `.github/workflows/frontend-ci.yml`
  - Passos: install, type-check, tests, build e build/push de imagem Docker
- Backend: `.github/workflows/backend-ci.yml`
  - Passos: lint PHP 8.3 e build/push de imagem Docker

Configure os secrets no repositório (Settings → Secrets and variables → Actions):
- `DOCKERHUB_USERNAME`
- `DOCKERHUB_TOKEN`

Com os secrets definidos, os jobs de build/push publicarão:
- Frontend: `${DOCKERHUB_USERNAME}/singre-frontend:latest`
- Backend: `${DOCKERHUB_USERNAME}/singre-backend:latest`

## Atualizando um servidor com Docker
No servidor, faça login no Docker Hub e puxe as novas imagens:
```
docker login -u <user>
docker compose pull

docker compose up -d
```

## Troubleshooting
- Verificar healthchecks: `docker compose ps`
- Checar logs do Nginx do frontend: `docker compose logs -f frontend`
- Verificar proxy `/api` no frontend: arquivo `docker/nginx/frontend.conf`
- Erros de PHP locais ao rodar `php -l`: use sempre o container (PHP 8.x) do `backend`/`scheduler`
