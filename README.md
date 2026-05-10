## Avvio

Serve solo Docker Desktop oppure Docker Engine con il plugin Compose.

Il progetto funziona anche senza file `.env`, usando i valori predefiniti del `docker-compose.yaml`.
Se vuoi personalizzare porte o credenziali del database, parti dall'esempio:

```bash
cp .env.example .env
```

Poi modifica `.env` e avvia i container:

```bash
docker compose up --build
```

Poi apri:

- Frontend React: http://localhost:5173
- API PHP/Slim: http://localhost:8080/alunni

## phpMyAdmin

phpMyAdmin e' opzionale. Avvialo solo quando serve:

```bash
docker compose --profile tools up --build
```

Poi apri http://localhost:8081

Credenziali database didattiche:

- Server: `db`
- Utente: valore di `DB_USERNAME`
- Password: valore di `DB_PASSWORD`
- Database: valore di `DB_DATABASE`

## Reset completo

Per cancellare database e dipendenze installate nei volumi Docker:

```bash
docker compose down -v
```

## Struttura

- `app`: frontend React con Vite
- `php`: API PHP con Slim
- `build/init.sql`: schema e dati iniziali del database

Database, `node_modules` e `vendor` vivono in volumi Docker, quindi non sporcano il repository.
