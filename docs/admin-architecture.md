# Admin Architecture

## Obiettivo

Introdurre un backoffice separato dal backend Blade, costruito come applicazione Nuxt 3 dedicata e collegata ad endpoint JSON Laravel admin.

## Struttura proposta

- [backend/](/C:/dev/scipioni/backend): Laravel, auth admin, API, policy, business logic
- [admin/](/C:/dev/scipioni/admin): Nuxt 3 admin app
- [infra/docker/admin/Dockerfile](/C:/dev/scipioni/infra/docker/admin/Dockerfile): build e runtime del servizio Nuxt

## Responsabilita'

### Laravel

- autenticazione admin
- sessione/cookie oppure token
- autorizzazione e permessi
- CRUD reali
- validazione, logging e business logic

### Nuxt

- shell applicativa staff
- experience di navigazione
- tabelle, filtri, form e dashboard
- stato client e middleware di routing

## Endpoint iniziali suggeriti

- `POST /api/admin/auth/login`
- `POST /api/admin/auth/logout`
- `GET /api/admin/me`
- `GET /api/admin/dashboard`
- `GET /api/admin/events`
- `POST /api/admin/events`
- `GET /api/admin/events/{id}`
- `PUT /api/admin/events/{id}`
- `GET /api/admin/bookings`
- `GET /api/admin/bookings/{id}`

## Auth consigliata

Scelta preferita:

- login gestito da Laravel
- sessione cookie HttpOnly
- richieste Nuxt con `credentials: include`
- middleware Nuxt che interroga `/api/admin/me`

Questo evita di duplicare la logica auth nel frontend e mantiene il controllo lato backend.

## Base URL admin API

Per il container Nuxt servono due URL distinti:

- `NUXT_ADMIN_API_BASE`: URL interno Docker, usato nelle chiamate SSR dal container Nuxt verso Laravel
- `NUXT_PUBLIC_ADMIN_API_BASE`: URL pubblico usato dal browser

Esempio locale:

- `NUXT_ADMIN_API_BASE=http://nginx/api/admin`
- `NUXT_PUBLIC_ADMIN_API_BASE=http://localhost:8080/api/admin`

## Stato dello scaffold

Lo scaffold iniziale contiene:

- pagina login
- layout staff
- dashboard placeholder
- modulo eventi placeholder
- modulo prenotazioni placeholder
- store auth Pinia
- composable API e middleware auth

Non contiene ancora:

- integrazione reale con login Laravel
- CRUD reali
- filtri server-side
- gestione errori API definitiva

## Passi successivi

1. aggiungere endpoint Laravel `api/admin`
2. collegare login/logout/me
3. spostare `events` su API reali
4. spostare `bookings` su API reali
5. dismettere gradualmente l'admin Blade
