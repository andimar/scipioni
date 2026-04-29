# Deploy Environments

## Obiettivo

Separare chiaramente sviluppo locale, staging e produzione per backend Laravel, demo/frontend web e nuovo backoffice admin Nuxt.

## File di configurazione

### Backend

- [backend/.env.docker](/C:/dev/scipioni/backend/.env.docker)
- [backend/.env.staging](/C:/dev/scipioni/backend/.env.staging)
- [backend/.env.production](/C:/dev/scipioni/backend/.env.production)

Qui vanno definiti:

- `APP_URL`
- `CORS_ALLOWED_ORIGINS`
- credenziali DB
- livello log
- chiavi applicative

### Docker Compose

- [docker-compose.yml](/C:/dev/scipioni/docker-compose.yml)
- [docker-compose.staging.yml](/C:/dev/scipioni/docker-compose.staging.yml)
- [docker-compose.production.yml](/C:/dev/scipioni/docker-compose.production.yml)

Il file base contiene i servizi comuni. Gli override di staging e production servono a:

- cambiare `env_file`
- agganciare config Nginx specifiche per dominio
- mantenere separazione tra ambienti

Per il deploy server-only sono disponibili anche:

- [docker-compose.server.staging.yml](/C:/dev/scipioni/docker-compose.server.staging.yml)
- [docker-compose.server.production.yml](/C:/dev/scipioni/docker-compose.server.production.yml)

Negli override server il servizio `frontend-demo` viene buildato dal sorgente tramite:

- [infra/docker/frontend-demo/Dockerfile](/C:/dev/scipioni/infra/docker/frontend-demo/Dockerfile)

### Nginx

Backend API:

- [default.conf](/C:/dev/scipioni/infra/docker/nginx/default.conf)
- [staging-api.conf](/C:/dev/scipioni/infra/docker/nginx/staging-api.conf)
- [production-api.conf](/C:/dev/scipioni/infra/docker/nginx/production-api.conf)

Frontend demo:

- [nginx.conf](/C:/dev/scipioni/infra/docker/frontend-demo/nginx.conf)
- [staging.conf](/C:/dev/scipioni/infra/docker/frontend-demo/staging.conf)
- [production.conf](/C:/dev/scipioni/infra/docker/frontend-demo/production.conf)

Admin Nuxt:

- [infra/docker/admin/Dockerfile](/C:/dev/scipioni/infra/docker/admin/Dockerfile)
- [docs/admin-architecture.md](/C:/dev/scipioni/docs/admin-architecture.md)

Nei file Nginx si definiscono soprattutto:

- `server_name`
- eventuali redirect
- comportamento frontend SPA

## Domini

Esempio staging:

- frontend: `staging.scipioni.brane.it`
- api: `api-staging.scipioni.brane.it`
- admin: `admin-staging.scipioni.brane.it`

Esempio produzione:

- frontend: `app.example.com`
- api: `api.example.com`
- admin: `admin.example.com`

Sostituisci questi placeholder con i domini reali del progetto.

## Avvio

Staging:

```bash
docker compose -f docker-compose.yml -f docker-compose.staging.yml up -d
```

Per includere anche il backoffice admin:

```bash
docker compose -f docker-compose.yml -f docker-compose.staging.yml up -d --build admin
```

Per il bootstrap staging completo con rebuild, migrazioni e dati demo:

```bash
./ops/staging-demo-setup.sh
```

Lo script crea anche il database `scipioni_club` se manca, riallinea `composer install` e `package:discover`, esegue le migrazioni, carica i dati demo e solo dopo pulisce la cache applicativa. Questo evita errori su `cache` quando il database o le tabelle non esistono ancora e riduce i problemi dovuti a provider/cache non riallineati dopo il deploy.

Da Windows e PowerShell e' disponibile anche:

```powershell
.\ops\staging-demo-setup.bat
```

Produzione:

```bash
docker compose -f docker-compose.yml -f docker-compose.production.yml up -d
```

Per includere anche il backoffice admin:

```bash
docker compose -f docker-compose.yml -f docker-compose.production.yml up -d --build admin
```

## Nota importante

Questa struttura non gestisce ancora automaticamente:

- la rimozione completa delle porte pubbliche dei servizi interni

Per Hetzner, il reverse proxy edge con TLS e' stato predisposto in:

- [docs/reverse-proxy-hetzner.md](/C:/dev/scipioni/docs/reverse-proxy-hetzner.md)
