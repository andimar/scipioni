# API MVP

## Stato

Questa e' una prima ossatura API utile per iniziare integrazione mobile e test manuali.

Gli endpoint sono definiti in [routes/api.php](C:/dev/scipioni/backend/routes/api.php).

## Base Path

`/api/v1`

## Autenticazione

Il backend usa `Laravel Sanctum` con bearer token per il client mobile.

Header richiesto sulle route protette:

`Authorization: Bearer <token>`

## Endpoint Iniziali

### Auth

- `POST /auth/register`
- `POST /auth/login`
- `GET /auth/me`
- `POST /auth/logout`

### Eventi

- `GET /events`
- `GET /events/{slug}`

### Profilo protetto

- `GET /profile`
- `PUT /profile`

### Prenotazioni protette

- `GET /bookings`
- `POST /bookings`

## Stato Attuale

- auth mobile con token bearer attiva
- route `profile` e `bookings` protette con `auth:sanctum`
- `FormRequest` per validazione input
- `JsonResource` per output piu' coerente
- login, `auth/me` e booking autenticato gia' verificati sullo stack Docker

## Payload Esempio Registrazione

```json
{
  "first_name": "Mario",
  "last_name": "Rossi",
  "email": "mario@example.com",
  "phone": "+39 333 1234567",
  "password": "password123",
  "device_name": "iphone-di-mario",
  "gender": "uomo",
  "age_range": "35-44",
  "origin_city": "Roma",
  "rome_area": "Prati",
  "food_preferences": ["vino rosso"],
  "event_preferences": ["degustazioni guidate"],
  "privacy_consent": true,
  "marketing_consent": true
}
```

## Payload Esempio Login

```json
{
  "email": "cliente@example.com",
  "password": "password",
  "device_name": "pixel-test"
}
```

## Payload Esempio Booking

```json
{
  "event_id": 1,
  "seats_reserved": 2,
  "customer_notes": "Tavolo per due"
}
```

## Prossimi Step API

- refresh e revoke multi-device token
- endpoint `user_devices` per push notification
- CRUD admin per eventi e segmenti
- filtri eventi per data, tag, featured
- test feature Laravel sugli endpoint principali
