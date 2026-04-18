# Wireframe App

## Navigazione Principale

```mermaid
flowchart LR
    A["Home"] --> B["Eventi"]
    A --> C["Prenotazioni"]
    A --> D["Profilo"]
    A --> E["Notifiche"]
    B --> F["Dettaglio Evento"]
```

## Home

```text
+--------------------------------------------------+
| Logo / Profilo / Notifiche                       |
+--------------------------------------------------+
| HERO EVENTO IN EVIDENZA                          |
| Immagine grande                                  |
| Titolo                                           |
| CTA: Scopri l'evento                             |
+--------------------------------------------------+
| Consigliati per te                               |
| [Card] [Card] [Card]                             |
+--------------------------------------------------+
| Prossime prenotazioni                            |
| - Evento 1                                       |
| - Evento 2                                       |
+--------------------------------------------------+
| Nav: Home | Eventi | Prenotazioni | Profilo      |
+--------------------------------------------------+
```

## Lista Eventi

```text
+--------------------------------------------------+
| Cerca eventi                                     |
| Filtri: Data | Categoria | Disponibilita'        |
+--------------------------------------------------+
| [Card Evento]                                    |
| Immagine | Titolo | Data | Prezzo | Badge        |
+--------------------------------------------------+
| [Card Evento]                                    |
+--------------------------------------------------+
| [Card Evento]                                    |
+--------------------------------------------------+
```

## Dettaglio Evento

```text
+--------------------------------------------------+
| Cover image                                      |
+--------------------------------------------------+
| Titolo evento                                    |
| Sottotitolo                                      |
| Data, ora, luogo                                 |
| Prezzo | Posti disponibili                       |
+--------------------------------------------------+
| Descrizione editoriale                           |
| Testo lungo                                      |
+--------------------------------------------------+
| Note pratiche                                    |
+--------------------------------------------------+
| CTA: Prenota                                     |
+--------------------------------------------------+
```

## Registrazione

```text
+--------------------------------------------------+
| Crea il tuo account                              |
+--------------------------------------------------+
| Nome                                             |
| Cognome                                          |
| Email                                            |
| Telefono                                         |
| Password                                         |
| Sesso                                            |
| Fascia eta'                                      |
| Provenienza                                      |
| Zona di Roma                                     |
| Preferenze                                        |
| [ ] Privacy                                      |
| [ ] Marketing                                    |
+--------------------------------------------------+
| CTA: Crea account                                |
+--------------------------------------------------+
```

## Profilo

```text
+--------------------------------------------------+
| Dati personali                                   |
| Nome, Cognome, Email, Telefono                   |
+--------------------------------------------------+
| Profilazione                                     |
| Sesso, Eta', Provenienza, Zona                   |
+--------------------------------------------------+
| Preferenze                                       |
+--------------------------------------------------+
| Consensi                                         |
+--------------------------------------------------+
| CTA: Modifica profilo                            |
+--------------------------------------------------+
```
