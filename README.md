# TYPO3 extension: tw_base

> Collection of building blocks and viewhelpers for TYPO3 projects made by tollwerk

## Viewhelpers

* [`heading` viewhelper](Docs/ViewHelpers/heading.md) for semantic document structuring
* [`render` viewhelper](Docs/ViewHelpers/render.md) for rendering partials & sections with heading context awareness
* [`media` viewhelper](Docs/ViewHelpers/media.md) for responsive images
* [`uniqid` viewhelper](Docs/ViewHelpers/uniqid.md)
* Viewhelpers for preparing / refining lists of HTML element attributes (empty / non-empty, data attributes, binary attributes)
* Form element viewhelper (returns a [Form Framework](https://docs.typo3.org/typo3cms/extensions/form/) element by its name)
* Link info viewhelper (returns detailed information about a link target)
* SVG icon & icon sprite viewhelpers (returns detailed information about a link target)

## Link handlers

* [Telecommunication link handler](Docs/LinkHandler/tel.md)

## Utilities

* [Array utility](Classes/Utility/ArrayUtility.php) for advanced array operations
* [cURL utility](Classes/Utility/CurlUtility.php) for making HTTP requests
* [Email utility](Classes/Utility/EmailUtility.php) for sending out mixed HTML / plaintext emails
* [TCA utility](Classes/Utility/TcaUtility.php) for easier TCA configuration and manipulation
* [Localization utility](Classes/Utility/LocalizationUtility.php) for enhanced localization with fallback to given translation key

## Validators

* [Unique object validator](Classes/Domain/Validator/UniqueObjectValidator.php) for testing whether a unique value is already taken (e.g. for use with the Form Framework)

## Services

* Image compression services (mozjpeg, SVGO)
* Image converters (WebP)

These services won't work out of the box and require particular software to be available on the server.

## Miscellaneous

* Image lazyloading with automatic SVG based preview images (like [SQIP](https://github.com/technopagan/sqip); requires particular software on the server)
* Fluid Standalone template renderer
* Email notification tool (HTML emails & plaintext)
* Helper traits for repositories (for debugging SQL queries and for generally ignoring storage PIDs)
