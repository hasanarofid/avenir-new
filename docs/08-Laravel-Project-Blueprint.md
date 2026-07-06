# Laravel Project Blueprint

## 1. Directory Structure Conventions
- **Controllers**: `app/Http/Controllers/`
- **Models**: `app/Models/`
- **Frontend Pages (Inertia)**: `resources/js/Pages/`
- **Frontend Components**: `resources/js/Components/`
- **CSS**: `resources/css/app.css` (Tailwind entry point). Page-specific legacy CSS might reside in `public/css/`.

## 2. Framework Versioning
- This project specifically utilizes **Laravel 13** and **PHP 8.3**.
- Always refer to Laravel 13 features when writing modern PHP code (e.g., typed properties, readonly classes, match expressions, modern array spread operators).

## 3. Inertia.js Implementation Rules
- Always use `Inertia::render('PageName', ['prop' => $data])` from controllers.
- Use the `<Head>` component provided by `@inertiajs/vue3` to manage page titles and meta tags dynamically.
- Data passed to Inertia views should be minimized and properly mapped using Eloquent API Resources (`Illuminate\Http\Resources\Json\JsonResource`) to prevent leaking sensitive model attributes (like passwords or hidden tokens) to the client-side Vue inspection tools.

## 4. Styling (Tailwind CSS v4)
- Exclusively use Tailwind CSS v4 utility classes for all new component styling.
- Avoid writing custom CSS in `<style>` blocks within Vue components unless absolutely necessary for complex, highly specific overrides (such as manipulating third-party libraries or handling legacy graph integrations).
- When custom CSS is required, heavily prefer `<style scoped>` to prevent global namespace pollution.
