# Scipioni App Demo

Demo Flutter Web collegata al backend Laravel del progetto.

## Comandi utili

Da [frontend/app](/C:/dev/scipioni/frontend/app):

```powershell
flutter pub get
flutter analyze
flutter build web --dart-define=SCIPIONI_API_BASE_URL=http://localhost:8080/api/v1
```

Preview locale:

```powershell
flutter run -d chrome --dart-define=SCIPIONI_API_BASE_URL=http://localhost:8080/api/v1
```

## Credenziali demo

- email: `cliente@example.com`
- password: `password`

## Note

- gli eventi sono caricati dall'endpoint reale `GET /api/v1/events`
- profilo e prenotazioni vengono caricati dopo login con bearer token
- per la demo browser il backend deve consentire CORS verso `http://localhost:8090`
