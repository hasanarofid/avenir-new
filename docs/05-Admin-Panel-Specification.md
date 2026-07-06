# Admin Panel Specification

## 1. Scope and Authority
- The Admin Panel is the sole point of content creation, editing, and publishing for the platform at this time.
- It is accessed exclusively by internal internal personnel (`admin` role).

## 2. Research Catalog Management
- **Primary Component**: `CreateEdit.vue`
- **Rich Text Editing**: Content bodies for research reports are authored using **QuillEditor**. Custom integrations or formatting handlers within Quill must be preserved during any refactoring.
- **Attachments**: The admin form requires mandatory handling for:
  - Uploading PDF files (the core research document).
  - Uploading Cover Images (for display cards and metadata).

## 3. Legacy Content Preservation
- Older reports migrated from the previous Vercel application have raw, complex HTML injected into their content fields.
- This HTML relies on highly specific legacy CSS classes (e.g., `.dcf-box` for Discounted Cash Flow tables, specific layout grids).
- When modifying the admin panel, the display and editing capability for this legacy HTML must remain intact. Automated formatting or sanitization that strips these legacy classes is strictly prohibited.
