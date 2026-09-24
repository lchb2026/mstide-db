# MSTIDE Database 2.0 — Frontend

The web interface for **MSTIDE Database 2.0**, a repository of negative-ion tandem mass spectrometry data for natural nucleotides and their synthetic derivatives.

The application lets researchers browse compounds and fragment ions, inspect MS/MS spectra and peak lists, search the database by chemical properties, and compare normalized spectra. Authorized users can also maintain database records through the same interface.

> This repository contains the frontend only. A compatible backend API is required for data access and authentication.

## Features

- Browse detailed compound records, including structures, molecular data, experimental conditions, spectra, peak lists, and assigned fragments
- Search and filter compounds by charge state, name, molecular formula, catalog ID, canonical SMILES, *m/z* values, and modification type
- Browse and search fragment ions by abbreviation, canonical SMILES, and *m/z*
- Compare two or more compound spectra and calculate their Match Factor
- Download compound structures in SDF format when available
- Authenticated create, edit, file-upload, and delete workflows for compounds and fragments
- Responsive Vuetify interface with persistent light and dark themes

## Technology Stack

- [Vue 3](https://vuejs.org/) with the Composition API
- [TypeScript](https://www.typescriptlang.org/)
- [Vite](https://vite.dev/)
- [Vuetify 3](https://vuetifyjs.com/)
- [Vue Router](https://router.vuejs.org/)
- [Axios](https://axios-http.com/)
- Sass

## Requirements

- Node.js `20.19+` or `22.12+`
- npm
- A running MSTIDE Database backend API

The development server expects the backend at `http://127.0.0.1:8000` and proxies requests from `/api` to it. This setting is defined in [`vite.config.ts`](./vite.config.ts).

## Getting Started

1. Clone the repository and enter the project directory:

   ```bash
   git clone <repository-url>
   cd mstide-db-frontend
   ```

2. Install the exact dependency versions from the lockfile:

   ```bash
   npm ci
   ```

3. Start the backend API on `http://127.0.0.1:8000`.

4. Start the frontend development server:

   ```bash
   npm run dev
   ```

5. Open the local URL printed by Vite, normally `http://localhost:5173`.

## Available Commands

| Command | Description |
| --- | --- |
| `npm run dev` | Start the Vite development server with hot module replacement |
| `npm run type-check` | Run TypeScript checks with `vue-tsc` |
| `npm run build` | Type-check and create a production build in `dist/` |
| `npm run build-only` | Create a production build without a separate type-check step |
| `npm run preview` | Preview the production build locally |

No automated test suite is currently configured.

## Backend Integration

All application requests use relative `/api/...` URLs. Authentication is session-based and sends cookies with requests, so the backend must support credentials and the deployed frontend must be able to reach the API through the same origin or a correctly configured reverse proxy.

The frontend uses API areas for:

- user login, logout, and session checks;
- compound listing, filtering, comparison, CRUD operations, and file uploads;
- fragment listing, filtering, CRUD operations, and image uploads;
- compound types and compound–fragment relationships.

Compound and fragment searches use the custom HTTP method `QUERY`. Make sure the backend and any reverse proxy allow and forward this method.

## Production Deployment

Create the optimized build:

```bash
npm ci
npm run build
```

Deploy the generated `dist/` directory with any static web server. The server configuration must:

1. Serve `index.html` as the fallback for unknown paths because the application uses HTML5 history routing.
2. Proxy `/api` to the backend service.
3. Preserve cookies and forward all HTTP methods used by the API, including `QUERY`.
4. Use HTTPS in production to protect authenticated sessions.

The proxy in `vite.config.ts` is used only by the development server and does not configure production hosting.

## Project Structure

```text
public/                 Static files served as-is
src/
├── assets/             Global styles, fonts, and images
├── components/
│   ├── atoms/          Reusable basic UI controls
│   ├── molecules/      Composite controls, charts, and spectra tables
│   ├── organisms/      Feature-level UI and data-loading logic
│   └── templates/      Page layouts
├── db/                 Session and authentication state
├── interfaces/         TypeScript domain interfaces
├── pages/              Route-level components
├── router/             Routes, page titles, and authentication guards
├── App.vue             Root component
└── main.ts             Application and Vuetify setup
```

The UI follows an Atomic Design-inspired component organization.

## Scientific Context

MSTIDE Database 2.0 supports analysis of fragmentation patterns in biologically and therapeutically relevant di- and trinucleotide analogues. It accompanies research by D. Zhilyaev, D. Strzelecka, S. Golojuch, A. Pandiurin, J. Jemielity, and J. Kowalska. More scientific background and usage guidance are available on the application's **About** page.

## License

No license is currently included in this repository. Add an appropriate license before distributing or accepting third-party contributions.
