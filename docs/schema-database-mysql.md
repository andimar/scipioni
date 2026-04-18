# Schema Database MySQL

## Obiettivo

Lo schema iniziale deve coprire autenticazione cliente, profilazione, eventi, prenotazioni, notifiche e utenti amministrativi.

## Entita' Principali

- `users`
- `user_profiles`
- `event_categories`
- `events`
- `event_tags`
- `event_tag_event`
- `bookings`
- `user_devices`
- `notifications`
- `notification_recipients`
- `admin_users`
- `event_audiences`

## Diagramma ER

```mermaid
erDiagram
    USERS ||--|| USER_PROFILES : has
    USERS ||--o{ BOOKINGS : makes
    EVENTS ||--o{ BOOKINGS : receives
    USERS ||--o{ USER_DEVICES : owns
    EVENT_CATEGORIES ||--o{ EVENTS : classifies
    EVENTS ||--o{ EVENT_TAG_EVENT : tagged
    EVENT_TAGS ||--o{ EVENT_TAG_EVENT : links
    EVENTS ||--o{ EVENT_AUDIENCES : targets
    NOTIFICATIONS ||--o{ NOTIFICATION_RECIPIENTS : has
    USERS ||--o{ NOTIFICATION_RECIPIENTS : receives

    USERS {
        bigint id PK
        varchar first_name
        varchar last_name
        varchar email
        varchar phone
        varchar password_hash
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    USER_PROFILES {
        bigint id PK
        bigint user_id FK
        varchar gender
        varchar age_range
        varchar origin_city
        varchar rome_area
        text food_preferences
        text event_preferences
        boolean privacy_consent
        boolean marketing_consent
        datetime created_at
        datetime updated_at
    }

    EVENT_CATEGORIES {
        bigint id PK
        varchar name
        varchar slug
    }

    EVENTS {
        bigint id PK
        bigint category_id FK
        varchar title
        varchar slug
        text short_description
        text full_description
        varchar cover_image_url
        datetime starts_at
        int capacity
        decimal price
        varchar booking_status
        boolean is_featured
        varchar status
        datetime created_at
        datetime updated_at
    }

    EVENT_TAGS {
        bigint id PK
        varchar name
        varchar slug
    }

    EVENT_TAG_EVENT {
        bigint event_id FK
        bigint tag_id FK
    }

    BOOKINGS {
        bigint id PK
        bigint user_id FK
        bigint event_id FK
        varchar status
        int seats_reserved
        text internal_notes
        datetime created_at
        datetime updated_at
    }

    USER_DEVICES {
        bigint id PK
        bigint user_id FK
        varchar platform
        varchar device_token
        boolean is_active
        datetime last_seen_at
        datetime created_at
        datetime updated_at
    }

    NOTIFICATIONS {
        bigint id PK
        varchar title
        text body
        varchar audience_type
        bigint created_by_admin_id
        datetime sent_at
        datetime created_at
    }

    NOTIFICATION_RECIPIENTS {
        bigint id PK
        bigint notification_id FK
        bigint user_id FK
        varchar delivery_status
        datetime delivered_at
    }

    EVENT_AUDIENCES {
        bigint id PK
        bigint event_id FK
        json filters
        boolean is_enabled
        datetime created_at
        datetime updated_at
    }
```

## Tabelle e Funzioni

## `users`

Contiene le credenziali e i dati anagrafici base del cliente.

Campi principali:
- `id`
- `first_name`
- `last_name`
- `email`
- `phone`
- `password_hash`
- `is_active`
- `created_at`
- `updated_at`

Vincoli consigliati:
- `email` univoca
- `phone` indicizzato

## `user_profiles`

Contiene i dati di profilazione e i consensi.

Campi principali:
- `user_id`
- `gender`
- `age_range`
- `origin_city`
- `rome_area`
- `food_preferences`
- `event_preferences`
- `privacy_consent`
- `marketing_consent`

Vincoli consigliati:
- relazione 1:1 con `users`

## `event_categories`

Classifica gli eventi in macro categorie come degustazione, cena evento, masterclass.

## `events`

Contiene il catalogo eventi pubblicato nel backend.

Campi principali:
- `category_id`
- `title`
- `slug`
- `short_description`
- `full_description`
- `cover_image_url`
- `starts_at`
- `capacity`
- `price`
- `booking_status`
- `is_featured`
- `status`

Valori utili:
- `booking_status`: `open`, `closed`, `waitlist`
- `status`: `draft`, `published`, `archived`

## `event_tags`

Usata per temi e interessi: bollicine, rossi, jazz, vini francesi.

## `event_tag_event`

Tabella pivot tra eventi e tag.

## `bookings`

Gestisce la prenotazione dell'utente a un evento.

Campi principali:
- `user_id`
- `event_id`
- `status`
- `seats_reserved`
- `internal_notes`

Valori utili:
- `status`: `requested`, `confirmed`, `cancelled`, `waitlist`

## `notifications`

Traccia le comunicazioni push create dal backend.

## `user_devices`

Registra i device token per le notifiche push iOS e Android.

## `notification_recipients`

Registra lo stato di invio per utente.

## `admin_users`

Tabella consigliata per autenticazione separata del backend amministrativo.

Campi suggeriti:
- `id`
- `name`
- `email`
- `password_hash`
- `role`
- `is_active`
- `created_at`
- `updated_at`

## Relazioni Chiave

```mermaid
flowchart LR
    A["users"] --> B["user_profiles"]
    A --> C["bookings"]
    A --> D["user_devices"]
    E["events"] --> C
    E --> F["event_tag_event"]
    G["event_tags"] --> F
    E --> H["event_audiences"]
    I["notifications"] --> L["notification_recipients"]
    A --> L
```

## Evoluzioni Future

- tabella `payments`
- tabella `checkins`
- tabella `membership_levels`
- motore campagne con template email/push
