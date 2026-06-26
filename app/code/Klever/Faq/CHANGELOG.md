# Changelog

## [1.2.1] - 2026-06-20
### Added
- Meta Title and Meta Description fields for FAQ Groups and FAQ items (SEO)
- SEO fieldset in admin forms for both FAQ Groups and FAQ items
- Controllers use custom meta values with fallback to auto-generated defaults
- Plugin to prevent MGS Blog router from hijacking FAQ URLs
- Main FAQ page redesigned as group card grid linking to detail pages
- Group detail page shows question list as links (no accordion)

## [1.2.0] - 2026-06-19
### Added
- URL key (slug) support for FAQ Groups and FAQ items
- Dedicated group page: `/faqs/{group-slug}` (e.g. `/faqs/tyres`)
- Dedicated single FAQ page: `/faqs/{group-slug}/{faq-slug}` (e.g. `/faqs/tyres/what-should-i-consider-when-choosing-new-tyres`)
- Custom router to handle multi-segment FAQ URLs
- Auto-generation of URL keys from group name / FAQ title on save
- URL Key admin fields in both FAQ Group and FAQ item forms
- Breadcrumb navigation on group and FAQ detail pages
- Related FAQs sidebar on single FAQ detail page
- JSON-LD FAQPage schema on group and single FAQ pages
- Hyva-compatible templates for both new page types

## [1.1.0]
### Added
- Footer FAQ display based on page URL slugs
- `footer_slugs` column on FAQ Group for page-specific footer display
- Plugin to exclude footer-only groups from main FAQ page
- JSON-LD FAQPage schema for footer FAQs

## [1.0.0]
### Added
- Initial module setup extending Mageprince_Faq
