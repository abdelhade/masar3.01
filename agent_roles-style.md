You are designing the full UI/UX Design System for a modern, enterprise-level ERP Web Application.

Project Context:
- Brand color: Mint Green (#34D3A3 or generate 2–3 mint variations)
- Industry: Multi-industry ERP (HR, POS, Accounting, Inventory, CRM, Projects…)
- Tech stack: Laravel + Blade + TailwindCSS

Your task:
Create a complete, production-ready Design System that I can apply directly in Laravel Blade templates.

Deliverables you MUST generate:

1. **Design Philosophy**
   - Describe the tone of the ERP UI (professional, minimalistic, enterprise-focused).
   - Define the UX principles for multi-industry business users.
   - Define roles-based layout logic (Admin, HR, POS operator, Accountant).

2. **Color System (Light + Dark)**
   - Full semantic palette:
     - primary (mint green variants)
     - secondary
     - neutral/gray scale
     - info, success, warning, danger
   - Provide design tokens in this structure:
     --color-primary
     --color-primary-light
     --color-primary-dark
     --color-gray-100 … --color-gray-900
   - Provide Tailwind config extensions for colors.

3. **Typography System**
   - Font pairing recommendation (Google Fonts preferred)
   - Text styles for:
     - Page title
     - Module title
     - Section title
     - Body
     - Table cells
   - Provide Tailwind tokens (text-xl, font-semibold…)
   - Responsive scale rules.

4. **Spacing & Layout System**
   - 8px spacing scale
   - Containers for:
     - Dashboards
     - Tables
     - Forms
   - Grid rules (2-col, 3-col, 4-col templates)

5. **Components (Enterprise-grade)**
   For each component: describe structure + states + sample Tailwind classes:
   - Buttons (primary / secondary / outline / ghost)
   - Inputs, selects, textareas
   - Cards (KPIs, stats, module shortcuts)
   - Tables (dense mode, wide mode, sticky header, inline editing)
   - Sidebar navigation (collapsed + expanded)
   - Topbar
   - Tabs, Modals, Drawers, Toasts
   - Badges, Chips, Tags
   - Pagination component

6. **Micro-interactions**
   - Hover states
   - Active states
   - Focus ring (A11y-friendly)
   - Subtle enterprise motion (100–150ms)
   - Skeleton loading + shimmer effects

7. **Role-based Layout System**
   - Admin layout (full sidebar)
   - POS layout (large buttons + fast actions)
   - HR layout (profiles, tabs)
   - Accounting layout (dense tables + filters)
   - Warehouse layout (barcode-friendly + big fonts)

8. **Blade Integration Examples**
   Provide ready-to-use Blade component sample code:
   - <x-button>  
   - <x-card>  
   - <x-table>  
   - <x-badge>  
   - <x-modal>  
   - Layout file: layouts/app.blade.php

9. **Tailwind Configuration**
   - Extend colors using mint green palette
   - Add shadows, radiuses, animations as tokens

Design direction:
- Minimalistic enterprise UI
- High readability
- Mint green as primary identity
- Card-based dashboard
- Light neumorphism on key surfaces
- Strong data density management
- Clean borders, soft shadows, subtle gradients

Final Output Format:
- Structured sections
- Code blocks for Blade + Tailwind
- Design tokens clearly listed
- Realistic UI examples

Goal:
Deliver a complete, modern, enterprise-grade ERP design system that can be implemented directly using Laravel Blade + Tailwind.

