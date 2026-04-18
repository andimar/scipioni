# Scipioni Club Backend

Backend API e pannello amministrativo del progetto Magazzino Scipioni.

## Stack

- Laravel 12
- MySQL
- sessioni, cache e queue su database nella configurazione iniziale

## Avvio locale

1. configurare `backend/.env` con credenziali MySQL reali
2. creare il database `scipioni_club`
3. eseguire:

```bash
php artisan migrate
php artisan serve
```

## Avvio con Docker

L'infrastruttura Docker del progetto vive a livello root del repository.

Comandi principali:

```bash
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Persistenza dati MySQL su host:

`infra/data/mysql`

## Dominio Applicativo Iniziale

- utenti cliente con profilazione
- utenti amministrativi separati
- eventi, categorie e tag
- segmentazione target per evento
- prenotazioni
- notifiche push e relativi destinatari
- device token per mobile push

## Note

- il progetto e' stato bootstrapato, ma non e' ancora collegato a un server MySQL locale
- la migrazione iniziale Laravel e' stata adattata al dominio dell'app
- finche' `flutter` non e' installato, il frontend resta documentato ma non bootstrapato
