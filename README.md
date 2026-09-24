# MSTIDE Database 2.0

**MSTIDE Database 2.0** is a web-based database for negative-ion tandem
mass spectrometry (MS/MS) data of natural nucleotides and their
synthetic derivatives.

The project provides an integrated interface for browsing compounds and
fragment ions, inspecting MS/MS spectra and peak lists, searching by
chemical and spectrometric properties, and comparing normalized spectra.
Authorized users can also create, edit, upload, and maintain database
records.

This repository contains both parts of the application:

``` text
mstide-db2/
├── frontend/    Vue 3 web application
└── backend/     Symfony REST API
```

## Features

-   Browse detailed compound records, including molecular structures,
    molecular formulas and masses, experimental conditions, MS/MS
    spectra, peak lists, and assigned fragments.
-   Search and filter compounds by name and abbreviation, molecular
    formula, compound ID, canonical SMILES, *m/z* values, modification
    type, and charge state.
-   Browse and search fragment ions by name, canonical SMILES, molecular
    mass, and related compounds.
-   Compare spectra of two or more compounds and calculate their Match
    Factor.
-   Download compound structures in SDF format when available.
-   Session-based user authentication.
-   Authenticated management of compounds and fragments.
-   Upload compound images, fragment images, and SDF structure files.
-   Responsive user interface with persistent light and dark themes.

## Technology Stack

### Frontend

-   Vue 3
-   TypeScript
-   Vite
-   Vuetify 3
-   Vue Router
-   Axios
-   Sass

### Backend

-   PHP 8.2+
-   Symfony 7.3
-   Doctrine ORM
-   Doctrine Migrations
-   MySQL 8 / MariaDB
-   Nelmio CORS Bundle

### Production

The application can be deployed using:

-   Nginx
-   PHP-FPM
-   MySQL or MariaDB
-   HTTPS

## Repository Structure

``` text
mstide-db2/
│
├── frontend/
│   ├── public/
│   ├── src/
│   │   ├── assets/
│   │   ├── components/
│   │   ├── db/
│   │   ├── interfaces/
│   │   ├── pages/
│   │   └── router/
│   ├── package.json
│   ├── package-lock.json
│   └── vite.config.ts
│
├── backend/
│   ├── config/
│   ├── migrations/
│   ├── public/
│   │   └── uploads/
│   ├── src/
│   │   ├── Controller/
│   │   ├── Entity/
│   │   ├── EventListener/
│   │   └── Repository/
│   ├── composer.json
│   └── composer.lock
│
└── README.md
```

## Requirements

### Frontend

-   Node.js `20.19+` or `22.12+`
-   npm

### Backend

-   PHP `>= 8.2`
-   Composer 2
-   PHP extensions: `ctype`, `iconv`, `pdo_mysql`
-   MySQL 8.x or a compatible MariaDB installation
-   Symfony CLI (recommended for local development)

## Local Installation

### 1. Clone the repository

``` bash
git clone <repository-url>
cd mstide-db2
```

### 2. Configure the backend

Enter the backend directory:

``` bash
cd backend
```

Install PHP dependencies:

``` bash
composer install
```

Create a local environment configuration. Production credentials and
secrets must not be committed to Git.

For example:

``` dotenv
APP_ENV=dev
APP_SECRET=replace-with-a-long-random-string
DATABASE_URL="mysql://DB_USER:DB_PASSWORD@127.0.0.1:3306/mstide_db?serverVersion=8.0&charset=utf8mb4"
BACKEND_URL="http://localhost:8000"
```

Keep `BACKEND_URL` without a trailing slash.

### 3. Prepare the database

For a complete project handoff, import a recent database dump:

``` bash
mysql -u DB_USER -p -e "CREATE DATABASE mstide_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u DB_USER -p mstide_db < mstide_db_backup.sql
```

Then check and apply pending migrations:

``` bash
php bin/console doctrine:migrations:status
php bin/console doctrine:migrations:migrate --no-interaction
```

The database dump is not included in this repository and should be
transferred separately.

For development with an empty database:

``` bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
```

An empty database will not contain the reference compounds, fragments,
types, or application users from the production database.

### 4. Start the backend

Using Symfony CLI:

``` bash
symfony server:start
```

By default, the API should then be available at:

``` text
http://127.0.0.1:8000
```

A simple authentication-state check can be performed using:

``` bash
curl http://127.0.0.1:8000/api/user/me
```

When no user is logged in, the expected response is:

``` json
{"loggedIn":false}
```

### 5. Configure and start the frontend

Open another terminal and enter the frontend directory:

``` bash
cd frontend
```

Install the exact dependency versions from the lockfile:

``` bash
npm ci
```

Start the Vite development server:

``` bash
npm run dev
```

The local frontend is normally available at:

``` text
http://localhost:5173
```

During development, Vite proxies `/api` requests to:

``` text
http://127.0.0.1:8000
```

This behavior is configured in `frontend/vite.config.ts`.

## Frontend Commands

Run these commands from the `frontend/` directory:

  Command                Description
  ---------------------- --------------------------------------------
  `npm run dev`          Start the Vite development server
  `npm run type-check`   Run TypeScript checks with `vue-tsc`
  `npm run build`        Type-check and create the production build
  `npm run build-only`   Build without a separate type-check step
  `npm run preview`      Preview the production build locally

The production frontend is generated in:

``` text
frontend/dist/
```

## Backend API

All API endpoints use the `/api` prefix.

### Authentication

Authentication is session-based. Browser clients must retain and send
the session cookie with authenticated requests.

Main authentication endpoints:

  Method   Endpoint             Purpose
  -------- -------------------- ----------------------------------
  `POST`   `/api/user/login`    Log in
  `POST`   `/api/user/logout`   Log out
  `GET`    `/api/user/me`       Get current authentication state

### Compounds

Main compound endpoints include:

  ----------------------------------------------------------------------------------
  Method                  Endpoint                           Purpose
  ----------------------- ---------------------------------- -----------------------
  `QUERY`                 `/api/compound/list`               Search compounds

  `GET`                   `/api/compound/{id}`               Get a compound

  `GET`                   `/api/compound/fragments`          List fragments

  `GET`                   `/api/compound/types`              List compound types

  `POST`                  `/api/compound/compare`            Compare compounds

  `POST`                  `/api/compound/save`               Create a compound

  `PUT` / `PATCH`         `/api/compound/{id}/save`          Update a compound

  `DELETE`                `/api/compound/{id}`               Delete a compound

  `POST`                  `/api/compound/{id}/upload-file`   Upload a compound image
                                                             or SDF file
  ----------------------------------------------------------------------------------

### Fragments

Main fragment endpoints include:

  -------------------------------------------------------------------------------------
  Method                  Endpoint                              Purpose
  ----------------------- ------------------------------------- -----------------------
  `QUERY`                 `/api/fragment/list`                  Search fragments

  `GET`                   `/api/fragment/{id}`                  Get a fragment

  `GET`                   `/api/fragment/compounds`             List related compounds

  `POST`                  `/api/fragment/save`                  Create a fragment

  `PUT` / `PATCH`         `/api/fragment/{id}/save`             Update a fragment

  `DELETE`                `/api/fragment/{id}`                  Delete a fragment

  `POST`                  `/api/fragment/{id}/upload-picture`   Upload a fragment image
  -------------------------------------------------------------------------------------

Compound and fragment searches intentionally use the custom HTTP method
`QUERY`. Development and production reverse proxies must allow and
forward this method.

## Uploaded Files

Uploaded assets are stored by the backend under:

``` text
backend/public/uploads/
├── compounds/
│   ├── images/
│   └── sdf/
└── fragments/
    └── images/
```

Database records store relative paths to these files.

The database and `backend/public/uploads/` should therefore be backed up
and restored together as a consistent set.

## Authentication and User Accounts

The application uses Symfony session-based authentication.

To generate a password hash for an application user:

``` bash
cd backend
php bin/console security:hash-password
```

A user can then be created in the database using the generated password
hash.

For example:

``` sql
INSERT INTO users (login, password, active)
VALUES ('admin', 'PASTE_THE_PASSWORD_HASH_HERE', 1);
```

Application users receive `ROLE_USER`, which permits authenticated
data-management operations.

## Production Deployment

### Backend

Install optimized production dependencies:

``` bash
cd backend
composer install --no-dev --optimize-autoloader
```

Configure production environment variables, including:

``` text
APP_ENV
APP_SECRET
DATABASE_URL
BACKEND_URL
```

Production credentials must not be committed to this repository.

Clear the Symfony production cache:

``` bash
APP_ENV=prod php bin/console cache:clear
```

The web server document root for Symfony must point to:

``` text
backend/public/
```

The web-server user must have appropriate write permissions for:

``` text
backend/var/
backend/public/uploads/
```

### Frontend

Create the optimized frontend build:

``` bash
cd frontend
npm ci
npm run build
```

The resulting application is located in:

``` text
frontend/dist/
```

The production web server should:

1.  serve the compiled frontend;
2.  fall back to `index.html` for Vue Router routes;
3.  proxy `/api` requests to the Symfony backend;
4.  expose the required uploaded assets;
5.  preserve session cookies;
6.  forward all required HTTP methods, including `QUERY`;
7.  serve the application over HTTPS.

The proxy configured in `vite.config.ts` applies only to local
development and does not configure the production web server.

## CORS

When the frontend and backend are served from different origins,
configure the permitted frontend origin in:

``` text
backend/config/packages/nelmio_cors.yaml
```

If cross-origin requests use custom methods such as `QUERY` or `PATCH`,
those methods must also be included in the permitted CORS methods.

When both frontend and API are served through the same origin and
reverse proxy, browser CORS requirements are significantly simpler.

## Verification

Useful backend checks after installation or deployment:

``` bash
cd backend

php bin/console about
php bin/console lint:yaml config
php bin/console doctrine:schema:validate
php bin/console debug:router
```

There is currently no automated test suite configured for the project.

## Scientific Context

MSTIDE Database 2.0 supports the analysis of fragmentation patterns in
biologically and therapeutically relevant natural nucleotides and
synthetic nucleotide derivatives.

The application provides access to tandem mass spectrometry data,
molecular information, assigned fragment ions, and tools for comparing
normalized spectra.

The project accompanies research by:

-   D. Zhilyaev
-   D. Strzelecka
-   S. Golojuch
-   A. Pandiurin
-   J. Jemielity
-   J. Kowalska

Additional scientific background and database usage information are
available through the application's **About** page.

## Security

Do not commit production secrets or credentials to this repository.

In particular, keep files containing values such as the following
outside version control:

``` text
.env.local
.env.local.php
```

Production database passwords, `APP_SECRET`, server credentials, and
other sensitive configuration values should be provided separately
through environment configuration.

Before making this repository public, review both the current files and
Git history for accidentally committed credentials or other sensitive
information.

## License

No open-source license is currently included.

Unless a license is added by the project owners, the source code should
be treated as private/proprietary and should not be redistributed or
reused without permission.
