# Issue Intake and Smart Summary System (Laravel + React)

This project demonstrates a practical full-stack issue intake workflow for support/operations teams:
- submit and store issues
- filter and review issues by status/category/priority
- auto-generate short summary + next action (LLM when available, rules fallback otherwise)
- auto-flag escalation for high/critical or overdue issues

## Why this stack

- **Backend: Laravel (PHP)**: clear MVC/service structure, strong validation, expressive routing.
- **Database: SQLite (relational)**: fast local setup, stable schema constraints, good fit for structured ticket fields and filtering.
- **Frontend: React + Vite**: lightweight interface to create/filter issues and validate API behavior quickly.

## Project structure

- `backend/` Laravel API code (models, controller, service, validation, migration, seeders)
- `frontend/` React UI
- `docs/api-demo.http` request samples for manual endpoint testing

## Key backend design choices

1. **Validation-first requests**
   - `StoreIssueRequest` and `UpdateIssueRequest` enforce required fields, enums, and constraints.
2. **Encapsulated AI/automation**
   - `IssueInsightService` attempts OpenAI first (if `OPENAI_API_KEY` is configured).
   - Falls back to deterministic rules for summary and next-action generation.
3. **Simple business escalation logic**
   - `is_escalated = true` for high/critical issues.
   - Also escalates when due date is overdue.
4. **Filterable listing**
   - model scope `Issue::filter()` supports `status`, `category`, `priority`.

## API endpoints

- `POST /api/issues` create issue
- `GET /api/issues` list issues (supports query filters)
- `GET /api/issues/{id}` view issue
- `PATCH /api/issues/{id}` update issue

## Setup steps

### Backend (Laravel API — Laravel 13, PHP 8.4+)

1. **Install PHP (Windows example)**  
   - `winget install --id PHP.PHP.8.4 -e`  
   - PHP ships without a loaded `php.ini`. Copy `php.ini-development` to `php.ini` next to `php.exe`, then enable at least: `extension_dir = "ext"`, `extension=openssl`, `extension=curl`, `extension=mbstring`, `extension=fileinfo`, `extension=pdo_sqlite`.  
   - OpenSSL is required for Composer HTTPS downloads.

2. **Install dependencies**  
   From `backend/` (after cloning), either:
   - `composer install` if Composer is on your PATH, or  
   - `php composer.phar install` if you keep a local [`composer.phar`](https://getcomposer.org/download/).

3. **Environment and database**  
   - Copy `.env.example` to `.env`.  
   - Ensure SQLite exists (Laravel’s installer usually creates `database/database.sqlite`; if not: `New-Item database/database.sqlite` or `touch database/database.sqlite`).  
   - `php artisan key:generate` (if `.env` has no `APP_KEY`).  
   - `php artisan migrate --seed`

4. **Run the API**  
   From the **`backend/`** directory (not the repo root):  
   - `php artisan serve` (default `http://127.0.0.1:8000`, routes under `/api/...`).

   **Windows: `php` is not recognized** after installing with winget: close and reopen the terminal, or refresh PATH in the current PowerShell session:

   ```powershell
   $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
   php -v
   ```

   Then: `cd backend` and run `php artisan serve` again.

5. **Optional: OpenAI**  
   Set `OPENAI_API_KEY` and optionally `OPENAI_MODEL` in `backend/.env`. Without a key, summaries use the rules-based fallback.

**Note:** If you see a leftover `backend_new/` folder from migration, close any processes using it and delete the folder; the canonical app root is `backend/`.

### Frontend (React)

From `frontend/`:
- `npm install`
- `npm run dev`

Optional env:
- `VITE_API_BASE_URL=http://localhost:8000/api`

## Seed/sample data

- Seeders: `backend/database/seeders/IssueSeeder.php`
- API examples: `docs/api-demo.http`

## What works now

- Issue creation with validation
- Persistent storage schema for issue fields + generated insights
- List endpoint with filters by `status`, `category`, `priority`
- View/update endpoints
- Summary and next action generation with LLM + fallback rules
- Escalation flag logic
- React UI to submit and review/filter issues

## Improvements with more time

1. Add authentication/authorization (team roles and ownership).
2. Add retry/circuit-breaker metrics around LLM calls.
3. Add API tests + feature tests for validation and escalation scenarios.
4. Add background job queue for async summarization on heavy load.
5. Add richer triage workflow (comments, SLA timers, assignment).
