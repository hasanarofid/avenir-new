# Engineering Guidelines

## 1. Code Quality & Clean Code
- **Simplicity**: Code should be "Clean & Maintainable". Do not over-engineer solutions.
- **DRY Principle**: Avoid code duplication (Don't Repeat Yourself).
- **Comments**: Write brief comments to explain complex logic or non-obvious business rules. Do not write comments for code that is self-explanatory.

## 2. PHP Standards
- **Strict Typing**: Always use strict typing and type-hinting for function arguments and return types.
- **Naming Conventions**:
  - `PascalCase` for Classes, Traits, and Interfaces.
  - `camelCase` for methods, variables, and properties.
  - `snake_case` for database columns and configuration keys.

## 3. Vue & JavaScript Standards
- **Component Style**: Use Vue 3 `<script setup>` syntax exclusively.
- **Naming Conventions**:
  - `PascalCase` for Vue Component filenames (e.g., `CreateEdit.vue`) and when importing them.
  - `camelCase` for variables, refs, and composables (e.g., `useOwnershipLogic.js`).

## 4. AI Assistant Guidelines
When acting as an AI Developer on this project:
- Provide concise, dense, and direct answers (focus on the code).
- When fixing bugs, briefly explain the root cause before providing the solution.
- Only show the relevant snippets or diffs of code being changed, rather than dumping entire files.
- Communicate in a professional yet relaxed Indonesian language.
- ALWAYS include a "State of Project" summary when asked for a status update.

## 5. Deployment Rules
- The code is deployed to a VPS via Git and SSH/GitHub Actions.
- Ensure that any new NPM packages or Webpack/Vite configurations do not cause memory spikes or timeouts during the `npm run build` process on GitHub Actions runners.
