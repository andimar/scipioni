---
name: Vinea Reserve
colors:
  surface: '#fbf9f5'
  surface-dim: '#dbdad6'
  surface-bright: '#fbf9f5'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f5f3ef'
  surface-container: '#efeeea'
  surface-container-high: '#eae8e4'
  surface-container-highest: '#e4e2de'
  on-surface: '#1b1c1a'
  on-surface-variant: '#544341'
  inverse-surface: '#30312e'
  inverse-on-surface: '#f2f0ed'
  outline: '#877270'
  outline-variant: '#dac1bf'
  surface-tint: '#954742'
  primary: '#2a0002'
  on-primary: '#ffffff'
  primary-container: '#4a0e0e'
  on-primary-container: '#cc726d'
  inverse-primary: '#ffb3ad'
  secondary: '#775a19'
  on-secondary: '#ffffff'
  secondary-container: '#fed488'
  on-secondary-container: '#785a1a'
  tertiary: '#120f0d'
  on-tertiary: '#ffffff'
  tertiary-container: '#282421'
  on-tertiary-container: '#918b86'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdad7'
  primary-fixed-dim: '#ffb3ad'
  on-primary-fixed: '#3d0506'
  on-primary-fixed-variant: '#77302d'
  secondary-fixed: '#ffdea5'
  secondary-fixed-dim: '#e9c176'
  on-secondary-fixed: '#261900'
  on-secondary-fixed-variant: '#5d4201'
  tertiary-fixed: '#e9e1dc'
  tertiary-fixed-dim: '#cdc5c0'
  on-tertiary-fixed: '#1e1b18'
  on-tertiary-fixed-variant: '#4b4642'
  background: '#fbf9f5'
  on-background: '#1b1c1a'
  surface-variant: '#e4e2de'
typography:
  display-xl:
    fontFamily: Noto Serif
    fontSize: 80px
    fontWeight: '400'
    lineHeight: 90px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Noto Serif
    fontSize: 48px
    fontWeight: '400'
    lineHeight: 56px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Noto Serif
    fontSize: 32px
    fontWeight: '400'
    lineHeight: 40px
  body-lg:
    fontFamily: Manrope
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Manrope
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-caps:
    fontFamily: Manrope
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.15em
spacing:
  unit: 8px
  container-max: 1280px
  gutter: 32px
  margin-edge: 64px
  section-gap: 120px
  overlap-offset: -40px
---

## Brand & Style

This design system embodies the quiet luxury of a contemporary Roman cellar. It targets an elite demographic of wine connoisseurs who value heritage as much as modern refinement. The personality is curated, authoritative, and intimate.

The design style is **Editorial Minimalism** blended with **Tactile layering**. It leverages generous negative space and high-contrast typography to mimic a bespoke coffee-table book. Visual interest is generated through asymmetrical layouts and elements that subtly overlap, creating a sense of physical depth and architectural structure. The emotional response should be one of "hushed exclusivity"—the digital equivalent of a private tasting in a candlelit vault.

## Colors

The palette is anchored by **Warm Antique Cream**, used as the primary surface color to provide a softer, more organic feel than pure white. **Deep Burgundy** serves as the primary brand mark and high-impact call-to-action color, evoking the richness of aged Cabernet. 

**Rich Charcoal Brown** is utilized for primary text and structural lines, offering a softer alternative to black that pairs harmoniously with the warm background. **Brass accents** are reserved for secondary interactive elements, subtle borders, and ornamentation, providing a metallic "flash" that signifies premium quality. Interaction states should use slight tonal shifts; for instance, brass elements deepen upon hover to simulate the patina of real metal.

## Typography

This design system utilizes **Noto Serif** for headlines to achieve a high-contrast, editorial aesthetic reminiscent of traditional viticulture journals. Large display sizes should be set with tight kerning to emphasize the elegant thick-to-thin transitions of the letterforms.

**Manrope** is used for body copy and functional UI elements. Its clean, geometric construction provides a necessary contemporary counterpoint to the traditional serif. For navigation and labels, use the `label-caps` style; the increased letter-spacing ensures legibility and adds a touch of modern architectural labeling. Paragraphs should be set with generous line heights to maintain a relaxed, premium reading pace.

## Layout & Spacing

The layout follows a **Fixed Editorial Grid** (12 columns) with exceptionally wide outer margins to focus the user’s eye. Unlike standard web layouts, this system encourages "asymmetric balance"—placing images and text blocks off-center to create a dynamic, magazine-like flow.

A key signature of this system is the **Overlapping Grid**. Images should frequently break the container or overlap with text blocks using the `overlap-offset` variable. This breaks the "boxed" feel of the web and creates a sense of tactile layers. Spacing between major sections is expansive (`section-gap`) to allow the brand story to breathe, ensuring the interface never feels crowded or transactional.

## Elevation & Depth

Depth is conveyed through **Tonal Layering** and **Ambient Shadows**. Instead of heavy dropshadows, use extremely soft, large-radius blurs with a low-opacity Deep Burgundy tint (#4A0E0E at 5-8% opacity). This creates a "glow" rather than a shadow, suggesting the soft, diffused lighting of a cellar.

Surfaces are tiered into three levels:
1.  **Base:** Antique Cream background.
2.  **Floating:** Elements like cards use a slightly lighter cream or a subtle brass border to lift them.
3.  **Overlays:** Modal components and menus use a high-blur backdrop filter (glassmorphism) to maintain a sense of space while focusing the user's attention.

## Shapes

The shape language is strictly **Sharp (0px)**. This design system avoids rounded corners to maintain a sophisticated, high-end architectural feel. The sharp edges echo the geometry of modern wine racks and stone-cut cellar walls. 

Borders, when used, should be hairline thin (1px). Horizontal rules used to separate editorial content should be rendered in the brass accent color, serving as a premium visual anchor. Buttons and input fields must maintain these hard edges to differentiate from mass-market, consumer-grade applications.

## Components

### Buttons
Primary buttons are solid Deep Burgundy with Antique Cream text, utilizing a sharp-edged rectangular shape. Secondary buttons use a transparent background with a 1px Brass border. Hover states should feature a subtle transition to a brass background.

### Input Fields
Inputs are minimalist: a single 1px Charcoal Brown bottom border with a label in `label-caps`. Focus states transition the bottom border to Brass.

### Cards & Containers
Cards for wine listings or event invites should have no visible borders. Depth is created via subtle ambient shadows and "floating" images that extend slightly beyond the card's vertical bounds.

### Navigation
The main navigation is minimalist and centered, using `label-caps` for links. A "Member Portal" CTA should be distinct, perhaps underlined with a 2px Brass stroke.

### Specialized Components
- **The Sommelier Badge:** A small, circular brass seal used to denote curated or rare vintages.
- **Tasting Note Tags:** Minimalist text-only chips in Deep Burgundy, separated by vertical lines (|) rather than boxes.
- **Vertical Typography:** Occasional use of vertical "sideways" text for date markers or category headers to reinforce the editorial aesthetic.