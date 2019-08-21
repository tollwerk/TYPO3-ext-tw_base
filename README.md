# TYPO3 extension: tw_base

> Collection of building blocks and viewhelpers for TYPO3 projects by tollwerk

## Domain objects

### Models

* Custom `Country` model extending `SJBR\StaticInfoTables\Domain\Model\Country` (when the `static_info_tables` extension is installed)
* `UnsubmittableFormDefinition` extending `TYPO3\CMS\Form\Domain\Model\FormDefinition` (Form Framework). Use for form definitions in combination with a custom hook to prevent advancing beyond the final form step (see class for hook example)

### Repositories

* Custom `CountryRepository` providing the method `findByIntlPhoneNumber()` to find all countries using a country code starting with particular digits. Extends `SJBR\StaticInfoTables\Domain\Repository\CountryRepository` when the `static_info_tables` extension is installed.

#### Traits

* `DebuggableRepositoryTrait` — add this to a repository and use the `debugQuery()` method for debugging SQL queries.
* `StoragePidsIgnoringRepositoryTrait` — add this to a repository as a quick and easy way to make it ignore the storage PIDs.

## Link handlers

- [Telecommunication link handler](Docs/LinkHandler/tel.md) adding support for phone calls etc.

## Services

- Custom `ImageService` extending `\TYPO3\CMS\Extbase\Service\ImageService` adding format conversion capabilities
- Image compression services (mozjpeg, SVGO)
- Image converters (WebP)
- Primitive LQIP service, creating SVGO previews of raster images

These services don't work out of the box and require particular software to be available on the server:

* For optimizing JPEG images using the **mozjpeg encoder**, install the [Mozilla JPEG Encoder Project](https://github.com/mozilla/mozjpeg) and create a `mozjpeg` alias to `jpegtran`.
* For creating WebP image variants, install the [WebP](https://developers.google.com/speed/webp/download) on the server and make sure the [cwebp](https://developers.google.com/speed/webp/docs/cwebp) encoder tool is available as `cwebp` on the command line.
* For compressing SVG images on the fly, install the [Node.js](https://nodejs.org/en/) based [SVGO](https://github.com/svg/svgo) tool and make sure it's available as `svgo` on the command line.
* For creating SVG previews for images with Primitive, install the [Go language](https://golang.org/) and [Primitive](https://github.com/fogleman/primitive) on your server and make sure `primitive` is available on the command line.

## Viewhelpers

* [`heading` viewhelper](Docs/ViewHelpers/heading.md) for semantic document structuring
* [`render` viewhelper](Docs/ViewHelpers/render.md) for rendering partials & sections with heading context awareness
* [`image` viewhelper](Docs/ViewHelpers/image.md) for rendering compressed images
* [`media` viewhelper](Docs/ViewHelpers/media.md) for responsive images
* [`uniqid` viewhelper](Docs/ViewHelpers/uniqid.md)
* Viewhelpers for preparing / refining lists of HTML element attributes (empty / non-empty, data attributes, binary attributes)
* Form element viewhelper (returns a [Form Framework](https://docs.typo3.org/typo3cms/extensions/form/) element by its name)
* `form.page.elementsByIdentifier` viewhelper (returns an array of all renderable elements of a form page by their identifier)
* Link info viewhelper (returns detailed information about a link target)
* SVG icon & icon sprite viewhelpers (returns detailed information about a link target)

## Utilities

* [Array utility](Classes/Utility/ArrayUtility.php) for advanced array operations
* [cURL utility](Classes/Utility/CurlUtility.php) for making HTTP requests
* [Email utility](Classes/Utility/EmailUtility.php) for sending out mixed HTML / plaintext emails
* [TCA utility](Classes/Utility/TcaUtility.php) for easier TCA configuration and manipulation
* [Localization utility](Classes/Utility/LocalizationUtility.php) for enhanced localization with fallback to given translation key

## Validators

* [Unique object validator](Classes/Domain/Validator/UniqueObjectValidator.php) for testing whether a unique value is already taken (e.g. for use with the Form Framework)

## Console Commands

* `cleanup:processedfiles` for truncating the table of processed files and deleting the corresponding files from the file system
* `cleanup:convertedfiles` for deleting the file variants generated by the image converters (WebP)

## Miscellaneous

* Image lazyloading with automatic SVG based preview images (like [SQIP](https://github.com/technopagan/sqip); requires particular software on the server)
* Fluid Standalone template renderer
* Email notification tool (HTML emails & plaintext)
* Helper traits for repositories (for debugging SQL queries and for generally ignoring storage PIDs)

## To-do

* [ ] Test inline images in emails
* [x] Install simplexml for wyrihaximus/html-compress
