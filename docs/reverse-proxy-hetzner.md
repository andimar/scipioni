# Reverse Proxy Hetzner

## Obiettivo

Esporre frontend demo e API su domini veri con HTTPS automatico, usando un reverse proxy edge separato dai container applicativi.

## Scelta tecnica

Per Hetzner la soluzione piu semplice e robusta, in questa fase, e `Caddy`:

- ottiene e rinnova automaticamente i certificati TLS
- semplifica la gestione dei virtual host
- resta separato da Nginx applicativo

Per il frontend demo, nei compose server viene usata una build Docker autonoma:

- il server non ha bisogno di Flutter installato
- l'immagine `frontend-demo` compila Flutter Web durante la `docker build`
- il risultato viene servito poi da Nginx nello stage finale

## File principali

- [Caddyfile.staging](/C:/dev/scipioni/infra/docker/caddy/Caddyfile.staging)
- [Caddyfile.production](/C:/dev/scipioni/infra/docker/caddy/Caddyfile.production)
- [docker-compose.edge.staging.yml](/C:/dev/scipioni/docker-compose.edge.staging.yml)
- [docker-compose.edge.production.yml](/C:/dev/scipioni/docker-compose.edge.production.yml)
- [frontend Dockerfile](/C:/dev/scipioni/infra/docker/frontend-demo/Dockerfile)

## Routing

Staging:

- `staging.scipioni.brane.it` -> `frontend-demo`
- `api-staging.scipioni.brane.it` -> `nginx`

Produzione:

- `app.example.com` -> `frontend-demo`
- `api.example.com` -> `nginx`

## Cosa devi sostituire

Prima del deploy reale sostituisci i placeholder:

- `devops@example.com`
- `staging.scipioni.brane.it`
- `api-staging.scipioni.brane.it`
- `app.example.com`
- `api.example.com`

## Avvio

Staging:

```bash
docker compose \
  -f docker-compose.yml \
  -f docker-compose.staging.yml \
  -f docker-compose.server.staging.yml \
  -f docker-compose.edge.staging.yml \
  up -d
```

Oppure, per staging con migrazioni, seed demo e check finale:

```bash
./ops/staging-demo-setup.sh
```

Lo script forza anche la creazione del database applicativo se il volume MySQL e' gia' presente ma il database non esiste ancora.

Produzione:

```bash
docker compose \
  -f docker-compose.yml \
  -f docker-compose.production.yml \
  -f docker-compose.server.production.yml \
  -f docker-compose.edge.production.yml \
  up -d
```

## DNS

Per funzionare, i record DNS devono puntare all'IP pubblico del server Hetzner:

- record `A` per dominio frontend
- record `A` per dominio API

## Persistenza certificati

I dati TLS di Caddy sono salvati su disco host in:

- `infra/data/caddy/staging/data`
- `infra/data/caddy/staging/config`
- `infra/data/caddy/production/data`
- `infra/data/caddy/production/config`

Questo evita di perdere certificati e stato del proxy quando i container vengono ricreati.

## Hardening server

Per il deploy server-only sono disponibili anche:

- [docker-compose.server.staging.yml](/C:/dev/scipioni/docker-compose.server.staging.yml)
- [docker-compose.server.production.yml](/C:/dev/scipioni/docker-compose.server.production.yml)

Questi override:

- rimuovono l'esposizione pubblica di `nginx`
- rimuovono l'esposizione pubblica di `frontend-demo`
- rimuovono l'esposizione pubblica di `mysql`
- escludono `adminer` dall'uso server normale tramite profilo `local-only`
- fanno buildare il frontend demo direttamente sul server via Docker

In questo assetto, lato pubblico restano esposte solo:

- `80`
- `443`
