# MSTIDE Database 2.0 — Backend

Backend API for the MSTIDE Database 2.0 application. It stores and serves chemical compounds, mass-spectrometry fragments, compound classifications, molecular structure files, and associated images. The API also provides session-based authentication for data-management operations.

> This repository contains the backend only. The frontend application and a production database dump are not included.

## Features

- Search compounds by name, abbreviation, molecular formula, compound ID, canonical SMILES, fragment mass, type, and charge
- Search fragments by name, molecular mass, and the canonical SMILES of related compounds
- Compare selected compounds
- Manage compounds and fragments through authenticated endpoints
- Upload compound images, SDF structure files, and fragment images
- Organize compounds into grouped types
- JSON login with a server-side session cookie

## Technology stack

- PHP 8.2 or newer
- Symfony 7.3
- Doctrine ORM and Doctrine Migrations
- MySQL 8
- Nelmio CORS Bundle

## Requirements

- PHP `>= 8.2` with `ctype`, `iconv`, and `pdo_mysql`
- [Composer 2](https://getcomposer.org/)
- MySQL 8.x
- [Symfony CLI](https://symfony.com/download) for the recommended local web server

## Local setup

### 1. Clone the repository and install dependencies

```bash
git clone https://github.com/devpandapp/mstide-db-backend.git
cd mstide-db-backend
composer install
```

### 2. Configure the environment

Create `.env` in the project root. This file is ignored by Git in this repository:

```dotenv
APP_ENV=dev
APP_SECRET=replace-with-a-long-random-string
DATABASE_URL="mysql://DB_USER:DB_PASSWORD@127.0.0.1:3306/mstide_db?serverVersion=8.0&charset=utf8mb4"
BACKEND_URL="http://localhost:8000"
```

Keep `BACKEND_URL` without a trailing slash. It is prepended to stored `/uploads/...` paths when the API returns image URLs.

Do not commit `.env`, `.env.local`, or production credentials. If the frontend runs anywhere other than `http://localhost:5173`, update the allowed origin in `config/packages/nelmio_cors.yaml`. Cross-origin clients that use the `QUERY` or `PATCH` endpoints must also add those methods to `allow_methods` in that file.

### 3. Prepare the database

For a project handoff, import a recent MySQL dump so that both the schema and the MSTIDE Database 2.0 reference data are preserved:

```bash
mysql -u DB_USER -p -e "CREATE DATABASE mstide_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u DB_USER -p mstide_db < mstide_db_backup.sql
php bin/console doctrine:migrations:status
php bin/console doctrine:migrations:migrate --no-interaction
```

The dump is not stored in this repository and must be transferred separately.

For development with an empty database, Doctrine can create the current schema from entity metadata:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
```

An empty schema contains no users, compounds, fragments, or type reference data. The checked-in migrations are incremental and do not contain the complete initial schema, so they are not a replacement for the production database dump.

### 4. Create an editor account if needed

Generate a Symfony-compatible password hash:

```bash
php bin/console security:hash-password
```

Insert the resulting hash into MySQL:

```sql
INSERT INTO users (login, password, active)
VALUES ('admin', 'PASTE_THE_PASSWORD_HASH_HERE', 1);
```

All application users receive `ROLE_USER`, which permits create, update, delete, and upload operations.

### 5. Start the API

```bash
symfony server:start
```

The API is available at `http://localhost:8000`. Confirm it is running with:

```bash
curl http://localhost:8000/api/user/me
```

Expected response when no user is logged in:

```json
{"loggedIn":false}
```

## Authentication

Authentication uses a session cookie. API clients and browser frontends must retain the cookie between requests. Browser requests should use `credentials: "include"`.

Log in and save the cookie with cURL:

```bash
curl -i -c mstide.cookies \
  -X POST http://localhost:8000/api/user/login \
  -H "Content-Type: application/json" \
  -d '{"login":"admin","password":"your-password"}'
```

Use the saved cookie for protected requests:

```bash
curl -b mstide.cookies \
  -X DELETE http://localhost:8000/api/compound/123
```

Log out:

```bash
curl -b mstide.cookies -X POST http://localhost:8000/api/user/logout
```

## API overview

All endpoints return JSON unless they serve an uploaded static file.

### User endpoints

| Method | Endpoint | Authentication | Purpose |
| --- | --- | --- | --- |
| `POST` | `/api/user/login` | Public | Log in with `login` and `password` |
| `POST` | `/api/user/logout` | Session | End the current session |
| `GET` | `/api/user/me` | Public | Return the current authentication state |

### Compound endpoints

| Method | Endpoint | Authentication | Purpose |
| --- | --- | --- | --- |
| `GET` | `/api/compound/fragments` | Public | List fragments for compound editing/search |
| `GET` | `/api/compound/maxSecondDigit?mass=...` | Public | Find the highest suffix used by matching compound IDs |
| `POST` | `/api/compound/compare` | Public | Return comparison data for an `ids` array |
| `GET` | `/api/compound/types` | Public | List compound types and their groups |
| `QUERY` | `/api/compound/list` | Public | Search compounds using a JSON request body |
| `GET` | `/api/compound/{id}` | Public | Get one compound |
| `POST` | `/api/compound/save` | `ROLE_USER` | Create a compound |
| `PUT` / `PATCH` | `/api/compound/{id}/save` | `ROLE_USER` | Update a compound |
| `DELETE` | `/api/compound/{id}` | `ROLE_USER` | Delete a compound |
| `POST` | `/api/compound/{id}/upload-file` | `ROLE_USER` | Upload a `picture` or `sdf` file |

Compound search accepts these JSON fields:

```json
{
  "logic": "AND",
  "name": "m7G",
  "molecularFormula": "C",
  "compoundId": "123",
  "canonicalSmiles": "OC",
  "mz": "152, 168",
  "types": [1, 2],
  "charge": ["1", "2"]
}
```

`logic` may be `AND` or `OR`. The search endpoint intentionally uses the custom HTTP method `QUERY`, so clients and reverse proxies must allow that method.

Example:

```bash
curl -X QUERY http://localhost:8000/api/compound/list \
  -H "Content-Type: application/json" \
  -d '{"logic":"AND","name":"m7G","mz":"152,168"}'
```

To upload a compound asset, send multipart form data with a `file` field and a `type` value of either `picture` or `sdf`:

```bash
curl -b mstide.cookies \
  -X POST http://localhost:8000/api/compound/123/upload-file \
  -F "type=picture" \
  -F "file=@compound.png"
```

### Fragment endpoints

| Method | Endpoint | Authentication | Purpose |
| --- | --- | --- | --- |
| `GET` | `/api/fragment/compounds` | Public | List compounds for fragment editing/search |
| `QUERY` | `/api/fragment/list` | Public | Search fragments using a JSON request body |
| `GET` | `/api/fragment/{id}` | Public | Get one fragment |
| `POST` | `/api/fragment/save` | `ROLE_USER` | Create a fragment |
| `PUT` / `PATCH` | `/api/fragment/{id}/save` | `ROLE_USER` | Update a fragment |
| `DELETE` | `/api/fragment/{id}` | `ROLE_USER` | Delete a fragment |
| `POST` | `/api/fragment/{id}/upload-picture` | `ROLE_USER` | Upload an image in the `picture` form field |

Fragment search accepts `logic`, `name`, `compoundCanonicalSmiles`, and `molecularMass`. Multiple masses can be separated by commas or spaces.

```bash
curl -X QUERY http://localhost:8000/api/fragment/list \
  -H "Content-Type: application/json" \
  -d '{"logic":"OR","name":"Gpp","molecularMass":"152,168"}'
```

## Uploaded files

Uploaded assets are stored under:

```text
public/uploads/
|-- compounds/
|   |-- images/
|   `-- sdf/
`-- fragments/
    `-- images/
```

Existing assets in these directories are versioned in the repository. In production, make the upload directories writable by the web-server user and include them in backups together with the MySQL database. Database rows store relative paths, so the database and upload directory should be restored as one consistent backup set.

## Project structure

```text
config/       Symfony, Doctrine, security, routes, and CORS configuration
migrations/   Incremental Doctrine database migrations
public/       Web root and uploaded assets
src/
|-- Controller/     JSON API controllers
|-- Entity/         Doctrine entities
|-- EventListener/  JSON logout response handler
`-- Repository/     Doctrine repositories
```

The main data relationships are:

- compounds and fragments: many-to-many
- compounds and types: many-to-many
- type groups and types: one-to-many

## Production deployment checklist

1. Configure the web server document root as `public/`.
2. Create the required `.env` file and provide `APP_ENV=prod`, a strong `APP_SECRET`, `DATABASE_URL`, and the public `BACKEND_URL`. Real production values can instead override the file through server environment variables.
3. Run `composer install --no-dev --optimize-autoloader`.
4. Import the database backup and apply any pending migrations.
5. Run `APP_ENV=prod php bin/console cache:clear`.
6. Grant the web-server user write access to `var/` and `public/uploads/`.
7. Set the production frontend origin in `config/packages/nelmio_cors.yaml`.
8. Serve the application over HTTPS and back up both MySQL and `public/uploads/`.

The included `compose.yaml` is Symfony's default PostgreSQL service definition. The current application configuration and migrations target MySQL, so do not use that Compose service unchanged without first adapting and regenerating the database migrations.

## Verification

Useful checks after installation:

```bash
php bin/console about
php bin/console lint:yaml config
php bin/console doctrine:schema:validate
php bin/console debug:router
```

There is currently no automated test suite in this repository.

## License

No open-source license is included. Treat this project as private/proprietary unless the owner provides different licensing terms.
