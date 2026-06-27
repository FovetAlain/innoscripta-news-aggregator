# News Aggregator API

A Laravel backend that aggregates articles from several news providers, stores them
locally and exposes a REST API for searching, filtering and a per-user personalized feed.

Built for the innoscripta backend take-home challenge.

## Stack

- PHP 8.3 / Laravel 13
- MySQL 8 + Redis (via Laravel Sail / Docker)
- Sanctum token authentication
- Pest for the test suite

## Data sources

Three providers are integrated. Each one only needs a free API key:

| Provider | Where to get a key |
|----------|--------------------|
| NewsAPI | https://newsapi.org |
| The Guardian | https://open-platform.theguardian.com/access |
| New York Times | https://developer.nytimes.com |

A provider with no key configured is simply skipped at fetch time, so you can run the
app with one, two or all three.

## Getting started

```bash
git clone <repo> && cd innoscripta-news-aggregator
cp .env.example .env
composer install

# add your provider keys to .env
# NEWSAPI_KEY=...
# GUARDIAN_KEY=...
# NYT_KEY=...

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

The API is then available at `http://localhost/api`.

> Prefer running without Docker? Point the `DB_*` variables at any MySQL instance (or
> `DB_CONNECTION=sqlite`) and use `php artisan` instead of `./vendor/bin/sail artisan`.

## Fetching articles

Pull the latest articles from every configured provider:

```bash
./vendor/bin/sail artisan news:fetch --sync   # run inline, handy for a first import
./vendor/bin/sail artisan news:fetch          # dispatch one queued job per provider
```

Fetching is wired into the scheduler (hourly). In production run the scheduler and a
queue worker:

```bash
./vendor/bin/sail artisan schedule:work
./vendor/bin/sail artisan queue:work
```

Articles are upserted on `(provider, external_id)`, so re-running a fetch updates
existing rows instead of creating duplicates.

## Tests

```bash
./vendor/bin/sail artisan test     # or ./vendor/bin/pest
```

The suite runs on an in-memory SQLite database and fakes every outbound HTTP call, so
no provider keys or network access are required.

## API reference

All responses are JSON. Authenticated routes expect a `Authorization: Bearer <token>`
header obtained from `/register` or `/login`.

### Authentication

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/register` | – | Create an account, returns a token |
| POST | `/api/login` | – | Log in, returns a token |
| POST | `/api/logout` | ✓ | Revoke the current token |
| GET | `/api/me` | ✓ | Current user |

### Articles

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/articles` | – | Paginated list with search & filters |
| GET | `/api/articles/{id}` | – | Single article |
| GET | `/api/feed` | ✓ | Articles matching the user's preferences |

Supported query parameters on `/api/articles`:

| Param | Example | Notes |
|-------|---------|-------|
| `q` | `?q=climate` | Free-text on title & description |
| `sources` | `?sources=1,2` | Source ids (array or comma-separated) |
| `categories` | `?categories=3` | Category ids |
| `authors` | `?authors=5` | Author ids |
| `date_from` | `?date_from=2026-01-01` | Published on/after |
| `date_to` | `?date_to=2026-06-30` | Published on/before |
| `per_page` | `?per_page=30` | 1–100, defaults to 15 |

### Reference data

Used by a frontend to build filter controls:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/sources` | All sources |
| GET | `/api/categories` | All categories |
| GET | `/api/authors` | All authors |

### Preferences

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/preferences` | ✓ | Read the user's preferences |
| PUT | `/api/preferences` | ✓ | Update preferred sources / categories / authors |

```jsonc
// PUT /api/preferences
{
  "preferred_sources": [1, 4],
  "preferred_categories": [2],
  "preferred_authors": [7]
}
```

## Architecture notes

- **Providers** — every source implements `App\Services\News\Contracts\NewsProvider`
  and maps its payload into a shared `ArticleData` DTO. Adding a source is one class
  plus a line in `NewsServiceProvider`; nothing else changes (open/closed).
- **Importer** — `ArticleImporter` resolves sources/categories/authors and upserts the
  articles. It is provider-agnostic and runs inside a queued job per provider.
- **Filtering** — query constraints live as Eloquent scopes on the `Article` model and
  are applied conditionally in the controller, keeping the same building blocks reused
  between the public listing and the personalized feed (DRY).
