# TYPO3 extension: tw_base

> Collection of building blocks and viewhelpers for TYPO3 projects by tollwerk

## Cta view helper

```html
<base:cta cta-type="link" cta-style="opaque" cta-invert="0" cta-theme="default"/>
```

The cta view helper renders a `<button>` or `<a>` tag with certain standardized CSS classes. The following
attributes are available. The ViewHelper inherits from AbstractTagBasedViewHelper, so all tag based attributes are available as well.

| Attribute | Type    | Mandatory | Description                                                                                       | Default |
|:----------|:--------|:---------:|:--------------------------------------------------------------------------------------------------|:--------|
| `cta-type` | Text    |    No    | Call To Action type (one of "link" or "button")                                                   |    `link`    |
| `cta-style`   | Text |    No     | Call To Action style (one of "opaque", "outline" or "inline") | `opaque`  |
| `cta-invert`   | Boolean |    No     | Invert the CTA colors for bright/dark backgrounds | `1`  |
| `cta-theme`   | Text |    No     | Call To Action theme  | `default`  |
| `href`   | Text |    No     | The `<a>` attribute `href`. Only necessary when `cta-type="link"`  |   |
| `value`   | Text |    No     | The `<button>` attribute `value`. Only necessary when `cta-type="button"`  |   |
| `type`   | Text |    No     | The `<button>` attribute `type`. Only necessary when `cta-type="button"`  |   |
| `name`   | Text |    No     | The `<button>` attribute `name`. Only necessary when `cta-type="button"`  |   |

