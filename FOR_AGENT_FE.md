---
name: Artisanal Dashboard
colors:
  surface: '#fff8f6'
  surface-dim: '#ecd5cd'
  surface-bright: '#fff8f6'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#fff1ec'
  surface-container: '#ffe9e2'
  surface-container-high: '#fbe3db'
  surface-container-highest: '#f5ded5'
  on-surface: '#251914'
  on-surface-variant: '#55423e'
  inverse-surface: '#3b2d28'
  inverse-on-surface: '#ffede7'
  outline: '#89726d'
  outline-variant: '#dcc1ba'
  surface-tint: '#9b432d'
  primary: '#792a16'
  on-primary: '#ffffff'
  primary-container: '#98412b'
  on-primary-container: '#ffc8bb'
  inverse-primary: '#ffb4a2'
  secondary: '#72594f'
  on-secondary: '#ffffff'
  secondary-container: '#fbd9cc'
  on-secondary-container: '#765d53'
  tertiary: '#324a37'
  on-tertiary: '#ffffff'
  tertiary-container: '#49624e'
  on-tertiary-container: '#c0dcc3'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdad2'
  primary-fixed-dim: '#ffb4a2'
  on-primary-fixed: '#3c0700'
  on-primary-fixed-variant: '#7c2d18'
  secondary-fixed: '#fddbce'
  secondary-fixed-dim: '#e0c0b3'
  on-secondary-fixed: '#291710'
  on-secondary-fixed-variant: '#584238'
  tertiary-fixed: '#cdead0'
  tertiary-fixed-dim: '#b2ceb5'
  on-tertiary-fixed: '#082010'
  on-tertiary-fixed-variant: '#344c39'
  background: '#fff8f6'
  on-background: '#251914'
  surface-variant: '#f5ded5'
  success-sage: '#49624e'
  warning-gold: '#c49b4d'
  surface-ivory: '#fff8f6'
  border-muted: '#dcc1ba'
  table-gray: '#f1f1f1'
typography:
  headline-lg:
    fontFamily: Nunito Sans
    fontSize: 32px
    fontWeight: '800'
    lineHeight: 40px
  headline-md:
    fontFamily: Nunito Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-sm:
    fontFamily: Nunito Sans
    fontSize: 20px
    fontWeight: '700'
    lineHeight: 28px
  body-lg:
    fontFamily: Nunito Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Nunito Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Nunito Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-lg:
    fontFamily: Nunito Sans
    fontSize: 14px
    fontWeight: '700'
    lineHeight: 20px
    letterSpacing: 0.02em
  label-md:
    fontFamily: Nunito Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.04em
  mono-data:
    fontFamily: JetBrains Mono
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  container-margin: 32px
  gutter: 20px
  card-padding: 24px
  table-cell-padding: 12px 16px
---

## Brand & Style

This design system translates a "handmade boutique soul" into a high-efficiency management environment. The brand personality is **Professional Artisanal**: it marries the warmth of a craft-focused brand with the precision required for internal operations. The goal is to evoke a sense of calm, focused productivity—moving away from the cold, clinical nature of traditional SaaS dashboards toward a space that feels like a well-organized physical workshop.

The visual style is **Modern Corporate with Tactile Warmth**, utilizing standard card-based layouts for immediate usability (the "Safe" visual identity) while softening the experience through a palette of terracotta, ivory, and sage. It prioritizes data density and information hierarchy without sacrificing the organic, approachable character of the parent brand.

## Colors

The color palette balances brand expression with functional utility. The **Primary Terracotta** is reserved for high-priority actions and brand-defining moments. **Secondary Mocha** provides a grounded anchor for navigation and secondary controls.

- **Background & Surfaces:** The dashboard uses a tiered surface strategy. The main canvas is **Surface Ivory**, while individual workspace modules and cards use a pure white to maximize contrast.
- **Functional Accents:** Success states utilize **Sage Green**, a calming and professional alternative to standard bright greens. Warnings use a **Muted Gold**, providing clear signaling without harshness. 
- **Data Grays:** A specific range of cool-toned functional grays is introduced for UI borders and table row zebra-striping, ensuring that the "warmth" of the brand does not interfere with the clarity of dense numerical data.

## Typography

The system relies on **Nunito Sans** to maintain a friendly, approachable tone. Its rounded terminals soften the dashboard's structured layout. 

- **Hierarchy:** Headlines are weighted heavily (Bold to Extra Bold) to provide clear landmarks within complex data views. 
- **Data Clarity:** For technical identifiers such as SKUs, Order IDs, and tracking numbers, **JetBrains Mono** is introduced. This monospaced font ensures that characters like '0' and 'O' or '1' and 'l' are easily distinguishable, reducing errors in staff workflows.
- **Labels:** Small UI labels use semi-bold weights and increased letter spacing to ensure readability at smaller scales, especially within compact data tables.

## Layout & Spacing

The dashboard employs a **Fixed Grid** system for internal management, providing a predictable environment for high-density data. 

- **Grid Model:** A standard 12-column grid is used for desktop views. Content is typically housed within a 1440px wide container to prevent excessive eye travel on ultra-wide monitors.
- **Rhythm:** An 8px base unit governs all spatial relationships. 
- **Reflow:** On tablet devices, the side navigation collapses into an icon-only rail, and the grid shifts to a 6-column model. For the rare mobile use case, cards stack vertically and margins reduce to 16px to conserve horizontal real estate for table data (which should horizontal-scroll within their parent containers).

## Elevation & Depth

To maintain a professional and "clean" appearance, this design system uses **Tonal Layering** and **Ambient Shadows**.

- **Shadow Character:** Shadows are extremely diffused and subtly tinted with the Primary color (`#98412b`) at low opacity (around 4-6%). This ensures the elevation feels "warm" and integrated rather than cold and gray.
- **Stacking:** The base layer is the Surface Ivory. Cards sit on this base with a "Low" elevation (4px blur). Modals and floating menus use a "High" elevation (16px blur) to clearly separate them from the workspace.
- **Outlines:** Low-contrast borders in **Border Muted** (`#dcc1ba`) are used alongside shadows to define card boundaries, providing a crisp architectural edge that aids in information parsing.

## Shapes

The shape language reinforces the "Rounded" aesthetic, using a consistent 8px (`0.5rem`) radius for standard UI elements.

- **Primary UI Elements:** Buttons, input fields, and search bars use the 8px radius.
- **Module Containers:** Dashboard cards and main content areas use a larger 12px-16px radius (`rounded-lg`) to create a softer, more distinctive silhouette that separates the dashboard from typical flat enterprise tools.
- **Data Tags:** Status chips and badges utilize a fully rounded (pill-shaped) geometry to distinguish them from actionable buttons.

## Components

### Buttons
- **Primary:** Solid Terracotta with white text. 8px radius. Uses a subtle bottom-heavy shadow to feel "pressed" into the surface.
- **Secondary:** Transparent with a 1.5px Mocha Brown border. 
- **Actionable Icons:** Small 8px rounded squares with subtle ivory backgrounds for row-level actions in tables.

### Data Tables
- **Style:** Clean, borderless rows with a 1px solid separator in `#f1f1f1`.
- **Highlights:** Subtle row hover state using a 50% opacity of the Ivory surface.
- **Typography:** Header cells use `label-lg` in Mocha Brown; body cells use `body-sm` or `mono-data`.

### Cards
- **Style:** White background, `rounded-lg` (12px) corners, and a soft 6% terracotta-tinted shadow.
- **Header:** Cards include a distinct header section with a subtle bottom border to separate titles from content.

### Inputs & Forms
- **Fields:** 8px radius, `#FFFFFF` background with a 1px border. Focus state uses a 2px Terracotta ring with 20% opacity.
- **Labels:** Always positioned above the input field using `label-md` for maximum clarity in dense forms.

### Status Indicators
- **Success:** Sage green background (low opacity) with deep sage text.
- **Warning:** Muted Gold background (low opacity) with deep amber text.
- **Imagery:** Profile and product thumbnails should always use a 4px rounded corner to maintain the soft visual theme.