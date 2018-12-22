# TYPO3 extension: tw_base

> Collection of building blocks and view helpers for TYPO3 projects made by tollwerk

## View helpers

* [Heading view helper](Docs/ViewHelpers/heading.md) for semantic document structuring
* [Media view helper](Docs/ViewHelpers/media.md) for responsive images
* [Unique ID view helper](Docs/ViewHelpers/uniqid.md)
* View helpers for preparing / refining lists of HTML element attributes (empty / non-empty, data attributes, binary attributes)
* Form element view helper (returns a [Form Framework](https://docs.typo3.org/typo3cms/extensions/form/) element by its name)
* Link info view helper (returns detailed information about a link target)

## Link handlers

* [Telecommunication link handler](Docs/LinkHandler/tel.md)

## Services

* Image compression services (mozjpeg, SVGO)
* Image converters (WebP)

These services won't work out of the box and require particular software to be available on the server.

## Miscellaneous

* Image lazyloading with automatic SVG based preview images (like [SQIP](https://github.com/technopagan/sqip); requires particular software on the server)
