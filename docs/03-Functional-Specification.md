# Functional Specification

## 1. Content Management Strategy
The platform manages complex equity research reports which have evolved over time.

### 1.1 Legacy Content
- Much of the older research data was migrated from a previous Vercel-based iteration of the platform.
- These legacy reports often contain highly complex static HTML structures with specific CSS classes (e.g., `.dcf-box`).
- **CRITICAL**: Developers (and AI) must NEVER alter or break these legacy CSS classes when editing content, as doing so will corrupt the layout of critical financial tables (like DCF models) in production.

### 1.2 New Content (Admin Panel)
- New research is authored and edited via the Admin Panel using **QuillEditor**.
- The `CreateEdit.vue` component handles this rich-text authoring flow.

## 2. File and Asset Management
- **PDF Reports**: Every research publication is fundamentally tied to a downloadable PDF document.
- **Cover Images**: Reports require associated cover imagery.
- Any modifications to the research form logic must always account for and preserve the multipart form data logic required for uploading PDFs and images via Inertia's `useForm`.

## 3. UI Consistency Standard
- When implementing new features or pages based on client PRDs or UI/UX mockups, ensure the global layout wrapper is used.
- Headers and footers must be standardized into a single global component.
- The `max-width` of content containers must remain uniform across the entire platform to prevent disjointed user experiences.
