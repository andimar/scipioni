# Reverse Proxy Hetzner

## Obiettivo

Esporre frontend demo e API su domini veri con HTTPS automatico, usando un reverse proxy edge separato dai container applicativi.

## Scelta tecnica

Per Hetzner la soluzione piu semplice e robusta, in questa fase, e `Caddy`:

- ottiene e rinnova automaticamente i certificati TLS
- semplifica la gestione dei virtual host
- resta separato da Nginx applicativo

## File principali

- [Caddyfile.staging](/C:/dev/scipioni/infra/docker/caddy/Caddyfile.staging)
- [Caddyfile.production](/C:/dev/scipioni/infra/docker/caddy/Caddyfile.production)
- [docker-compose.edge.staging.yml](/C:/dev/scipioni/docker-compose.edge.staging.yml)
- [docker-compose.edge.production.yml](/C:/dev/scipioni/docker-compose.edge.production.yml)

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

In questo assetto, lato pubblico restano esposte solo:

- `80`
- `443`
