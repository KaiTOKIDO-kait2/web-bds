# Docker local setup

## 1. Copy environment file

Rename `.env.example` to `.env` and keep the default values unless you want to change the database credentials.

## 2. Start the stack

```bash
docker compose up -d --build
```

## 3. Open the app

- Website: `http://localhost:8080/Real-Estate-website-in-PHP-main/`
- Chatbot health: `http://localhost:8000/health`

## 4. Check the database

The first startup imports:

- `DATABASE FILE/realestatephp_new.sql`
- `chatbot-service/ai_schema.sql`

If the MySQL volume already exists, the init scripts will not run again. Remove the volume if you need a clean re-import.

## 5. Useful commands

```bash
docker compose logs -f web
docker compose logs -f chatbot
docker compose logs -f db
docker compose down
```

## 6. Notes

- The PHP app still uses the project subpath in `BASEURL`, so keep the site inside `/Real-Estate-website-in-PHP-main` for now.
- The chatbot service uses the shared MySQL database through the `db` service name.