# REST API Specification

## 1. Internal API vs Inertia Responses
- Because the platform is built on Inertia.js, the majority of data fetching and form submissions do not require traditional REST API endpoints (`routes/api.php`).
- Controllers in `routes/web.php` respond to Inertia requests with JSON automatically when queried via Inertia's XHR, or with a full HTML document on the first page load.
- Avoid building parallel REST API endpoints for internal frontend consumption unless there is a specific technical requirement that bypasses Inertia (e.g., highly customized background fetching or third-party integrations).

## 2. External APIs
- Any actual REST endpoints intended for mobile apps, external partners, or third-party webhooks should be defined in `routes/api.php`.
- These endpoints must utilize Laravel Sanctum for API token authentication.
- All API responses must adhere to a consistent JSON structure (e.g., standardized `data`, `message`, and `status` keys).

## 3. Form Handling (Inertia)
- Use standard Laravel Request classes for validation.
- Return standard HTTP redirect responses (e.g., `return redirect()->back()->with(...)`). Inertia intercepts these redirects and automatically updates the client-side state without a full reload.
