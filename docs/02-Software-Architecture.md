# Software Architecture

## 1. Tech Stack
This project follows a modern Monolith SPA (Single Page Application) architecture utilizing the TALL/VILT ecosystem.

- **Backend Framework**: Laravel 13
- **Language**: PHP 8.3
- **Frontend Framework**: Vue 3 (using `<script setup>` Composition API)
- **Routing & Data Bridge**: Inertia.js v2
- **State Management**: Pinia (Client-side)
- **CSS Framework**: Tailwind CSS v4 (Utility-first)
- **Icons**: `lucide-vue-next`

## 2. Structural Principles
- **No Blade Front-end**: Pure Livewire or Blade solutions are strictly forbidden for user-facing views. This project is 100% SPA using Inertia + Vue.
- **Inertia Rendering**: Controllers must exclusively return views using `Inertia::render()`.
- **Navigation**: Avoid full page reloads. Always use the `<Link>` component provided by `@inertiajs/vue3` for client-side routing.
- **Form Handling**: Utilize Inertia's `useForm` helper for managing form state, validation errors, and file uploads.

## 3. Server Environment & CI/CD
- **Deployment**: Deployed on a VPS using Git and GitHub Actions via SSH (`ssh root@101.50.1.12 -p 50008`).
- **Build Process**: All frontend assets must be built using `npm run build` safely via CI/CD. Avoid configurations that could cause memory leaks or timeouts during the automated build process in GitHub Actions (`.github/workflows`).
- **Branching**:
  - `master` branch targets the Demo server (`https://demo.researchavenir.com/`).
  - `live` branch targets the Production server (`https://researchavenir.com/`).
