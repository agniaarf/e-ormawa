---
name: E-ORMAWA Tactical SaaS
colors:
  surface: '#f8f9fa'
  surface-dim: '#d9dadb'
  surface-bright: '#f8f9fa'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f5'
  surface-container: '#edeeef'
  surface-container-high: '#e7e8e9'
  surface-container-highest: '#e1e3e4'
  on-surface: '#191c1d'
  on-surface-variant: '#414845'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#f0f1f2'
  outline: '#717974'
  outline-variant: '#c1c8c3'
  surface-tint: '#436558'
  primary: '#244539'
  on-primary: '#ffffff'
  primary-container: '#3b5d50'
  on-primary-container: '#afd4c4'
  inverse-primary: '#aacfbe'
  secondary: '#3b6756'
  on-secondary: '#ffffff'
  secondary-container: '#bdedd7'
  on-secondary-container: '#416d5b'
  tertiary: '#513b15'
  on-tertiary: '#ffffff'
  tertiary-container: '#6a522a'
  on-tertiary-container: '#e8c794'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#c5ebda'
  primary-fixed-dim: '#aacfbe'
  on-primary-fixed: '#002117'
  on-primary-fixed-variant: '#2b4d41'
  secondary-fixed: '#bdedd7'
  secondary-fixed-dim: '#a2d1bc'
  on-secondary-fixed: '#002116'
  on-secondary-fixed-variant: '#234f3f'
  tertiary-fixed: '#ffdeac'
  tertiary-fixed-dim: '#e3c290'
  on-tertiary-fixed: '#281900'
  on-tertiary-fixed-variant: '#59431c'
  background: '#f8f9fa'
  on-background: '#191c1d'
  surface-variant: '#e1e3e4'
typography:
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '800'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.3'
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: '1.3'
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.4'
  title-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: '1.5'
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.6'
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  container-padding-desktop: 32px
  container-padding-mobile: 16px
  gutter: 24px
  sidebar-width: 280px
---

## Brand & Style

The design system is engineered for **E-ORMAWA**, a student organization management platform. The brand personality is disciplined yet accessible—merging the authoritative nature of military aesthetics with the efficiency of a high-end SaaS product. The target audience includes student leaders and administrators who require a structured, professional environment to manage complex organizational workflows.

The design style is **Modern Corporate SaaS** with a focus on high legibility and organizational clarity. It utilizes a refined color palette of deep greens and warm accents to move away from generic "tech blue" identities, establishing a unique institutional presence. Visuals are defined by generous whitespace, soft depth, and a commitment to Bootstrap 5's structural logic while elevating the aesthetic through custom elevation and sophisticated typography.

## Colors

The palette is anchored by **Deep Army Green (#3B5D50)**, used for primary actions, navigation backgrounds, and key branding elements to project stability. **Muted Sage Green (#4E7A68)** acts as a tonal secondary for hover states, secondary buttons, and active indicators, ensuring a harmonious monochromatic foundation.

The **Warm Sand/Gold (#D4B483)** serves as a high-contrast accent color, reserved for critical call-to-actions, notifications, and "premium" feature highlights. Surfaces utilize a range of soft neutrals (from White to Light Gray) to maintain the clean SaaS aesthetic and ensure that the green-heavy palette remains professional rather than overwhelming.

## Typography

This design system exclusively employs **Plus Jakarta Sans**, a modern geometric sans-serif that provides excellent readability and a friendly, open feel. 

- **Headlines:** Use heavy weights (Bold/ExtraBold) with slight negative letter-spacing to create a "locked-in" professional look for dashboard titles.
- **Body Text:** Set at 16px for primary reading and 14px for density-focused data views. Line heights are kept generous (1.6) to prevent eye fatigue during long administrative sessions.
- **Labels:** Small caps and increased letter-spacing are applied to micro-copy and table headers to distinguish them from interactive data.

## Layout & Spacing

The layout follows a **Fixed-Fluid Hybrid** model optimized for dashboard workflows. 
- **Sidebar:** A fixed 280px navigation rail on the left on desktop, transitioning to a collapsible drawer on mobile.
- **Content Area:** A fluid container with a maximum width of 1440px to prevent excessive line lengths on ultra-wide monitors.
- **Grid:** A standard 12-column system (Bootstrap 5 compliant) with a 24px gutter.
- **Spacing Rhythm:** Based on an 8px scale. Use 16px (2x) for internal card padding and 32px (4x) for section spacing to maintain an airy, modern feel.

## Elevation & Depth

To achieve the "Soft Shadow" SaaS look, this design system avoids harsh black shadows. Instead, it uses **Tinted Ambient Shadows**. 

1.  **Level 0 (Floor):** Background color (#F8F9FA). No shadow.
2.  **Level 1 (Cards/Sidebar):** Surface White. Shadow: `0px 4px 20px rgba(59, 93, 80, 0.05)`. This subtle green tint in the shadow creates a cohesive color bleed.
3.  **Level 2 (Dropdowns/Modals):** Surface White. Shadow: `0px 12px 32px rgba(59, 93, 80, 0.12)`.
4.  **Borders:** All cards and inputs feature a subtle 1px border (`#E9ECEF`) to maintain structural definition even in high-brightness environments.

## Shapes

The shape language is defined by "Large Radius" geometry to soften the professional tone. 
- **Small Elements:** Buttons and Input fields use a `0.5rem` (8px) radius.
- **Standard Elements:** Dashboard cards and containers use a `1rem` (16px) radius.
- **Large Elements:** Modals and feature hero cards use a `1.5rem` (24px) radius.
- **Pill Shapes:** Status badges and tags always use a full pill radius for immediate visual distinction from buttons.

## Components

### Buttons
- **Primary:** Deep Army Green background, white text. Soft shadow on hover.
- **Secondary:** Transparent with a Sage Green border and text.
- **Accent:** Warm Sand background with Deep Army Green text for high visibility.

### Sidebar
- Dark variant: Deep Army Green background.
- Active state: Muted Sage Green background with a 4px left-accent border in Warm Sand.
- Icons: Linear, 24px, with 60% opacity for inactive states.

### Cards
- White background, 1px border (#E9ECEF), 16px border-radius.
- Title area: 14px Bold labels with a 1px bottom divider.

### Data Tables
- Header: Light neutral background (#F8F9FA) with uppercase labels.
- Rows: Clean, no vertical borders. 1px horizontal dividers only.
- Hover state: Very light sage tint (#F1F4F2).

### Status Badges
- High-vibrancy fills with 10% opacity backgrounds of the same color for a "glowing" effect.
- Text: Bold, matching the primary color of the status (e.g., Dark Green text on light green background).

### Input Fields
- Height: 44px for a modern, touch-friendly feel.
- Border: 1px Gray, turning Deep Army Green with a 3px soft shadow ring on focus.