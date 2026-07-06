# Product Requirements Document (PRD)

## 1. Domain Platform
- **Name**: Avenir
- **Type**: Research Equity (Katalog Riset Saham)
- **Environments**:
  - Demo: `https://demo.researchavenir.com/` (branch `master`)
  - Production: `https://researchavenir.com/` (branch `live`)

## 2. Core Modules
### 2.1. Research Catalog
A platform for displaying and managing deep-dive equity research reports, market analysis, and daily updates.

### 2.2. Desk Brief
A daily market briefing and snapshot containing macro/micro overviews. Includes the **Ownership Intelligence** sub-module, which provides linked network mapping of issuers and investors (proxy UBO, holding changes, control concentration).

## 3. User Roles & Access
1. **Admin (Internal)**
   - Full control over content management and posting research.
   - Currently, all research publishing workflows are handled through the Admin panel.
2. **Mitra (External)**
   - External contributors.
   - The infrastructure is being prepared for a future release to allow external partners to manage their own research submissions.

## 4. UI/UX Principles
- **Consistency**: Features designed from client PRDs or UI/UX mockups must maintain a standardized header, footer, and `max-width` across all pages.
- **Brand Colors**: The primary brand color is `#4c940c`.

## 5. Domain Knowledge for Developers
Developers (and AI) working on this project must act as **Equity Research Analysts**, understanding:
- Capital market terminology and IDX (Bursa Efek Indonesia) mechanics.
- Valuation metrics (DCF, target price, margin of safety).
- Risk analysis methodologies (Bull/Base/Bear scenarios).
- The business workflow of publishing institutional stock research reports.
