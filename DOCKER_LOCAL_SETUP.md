# Docker deployment

## 1. Prepare production environment

Rename `.env.example` to `.env` and set production values for MySQL, chatbot secret, and any domain-specific settings.

## 2. Deploy the stack

```bash
docker compose up -d --build
```

## 3. Open the app

- Website: `http://<server-ip-or-domain>/Real-Estate-website-in-PHP-main/`
- Chatbot stays internal to the Docker network.

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
- Do not expose chatbot or database ports unless you explicitly need external access.