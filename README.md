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
- Text compression services (gzip, brotli)

These services don't work out of the box and require particular software to be available on the server:

* For optimizing JPEG images using the **mozjpeg encoder**, install the [Mozilla JPEG Encoder Project](https://github.com/mozilla/mozjpeg) and create a `mozjpeg` alias to `jpegtran`.
* For creating WebP image variants, install the [WebP](https://developers.google.com/speed/webp/download) converter on the server and make sure the [cwebp](https://developers.google.com/speed/webp/docs/cwebp) encoder tool is available as `cwebp` on the command line.
* For creating AVIV image variants, install the [AVIF](hhttps://github.com/AOMediaCodec/libavif) converter on the server and make sure the encoder tool is available as `avifenc` on the command line.
* For compressing SVG images on the fly, install the [Node.js](https://nodejs.org/en/) based [SVGO](https://github.com/svg/svgo) tool and make sure it's available as `svgo` on the command line.
* For creating SVG previews for images with Primitive, install the [Go language](https://golang.org/) and [Primitive](https://github.com/fogleman/primitive) on your server and make sure `primitive` is available on the command line.
* For creating gzipped versions of merged CSS and JavaScript files you need to have the [GNU Gzip](https://www.gnu.org/software/gzip/) command line utility installed (standard on any Linux system), globally available as `gzip` binary.
* For creating Brotli compressed versions of merged CSS and JavaScript files you need to have the [Brotli](https://github.com/google/brotli) command line utility installed, globally available as `brotli` binary.

## Viewhelpers

* [`cta` viewhelper](Docs/ViewHelpers/cta.md) for rendering CallToAction `<a>` or `<button>` tags
* [`heading` viewhelper](Docs/ViewHelpers/heading.md) for semantic document structuring
* [`render` viewhelper](Docs/ViewHelpers/render.md) for rendering partials & sections with heading context awareness
* [`image` viewhelper](Docs/ViewHelpers/image.md) for rendering compressed images
* [`media` viewhelper](Docs/ViewHelpers/media.md) for responsive images
* [`uniqid` viewhelper](Docs/ViewHelpers/uniqid.md)
* [`page.title` viewhelper](Docs/ViewHelpers/Page/title.md)
* [`structuredData.*` viewhelpers](Docs/ViewHelpers/structured-data.md)
* Viewhelpers for preparing / refining lists of HTML element attributes (empty / non-empty, data attributes, binary attributes)
* `language` viewhelper to translate a 2-character ISO 639-1 language identifier into a readable label (internal languages only)
* Form element viewhelper (returns a [Form Framework](https://docs.typo3.org/typo3cms/extensions/form/) element by its name)
* `form.page.elementsByIdentifier` viewhelper (returns an array of all renderable elements of a form page by their identifier)
* Link info viewhelper (returns detailed information about a link target)
* SVG icon & icon sprite viewhelpers (returns detailed information about a link target)
* `format.age` viewhelper for returning a human readable age string
* `format.leadingZeroes` viewhelper for returning a formatted number string

## Utilities

* [Array utility](Classes/Utility/ArrayUtility.php) for advanced array operations
* [cURL utility](Classes/Utility/CurlUtility.php) for making HTTP requests
* [Email utility](Classes/Utility/EmailUtility.php) for sending out mixed HTML / plaintext emails
* [TCA utility](Classes/Utility/TcaUtility.php) for easier TCA configuration and manipulation
* [Localization utility](Classes/Utility/LocalizationUtility.php) for enhanced localization with fallback to given translation key

## Title Providers

* `FlexPageTitleProvider` for altering the default title of a page.
* `SeoPageTitleProvider` for applying a dedicated page title for the `<title>` element only.

See the [title provider documentation](Docs/title-providers.md) for details.

## Validators

* [Unique object validator](Classes/Domain/Validator/UniqueObjectValidator.php) for testing whether a unique value is already taken (e.g. for use with the Form Framework)

## TCA field evaluations

* [NumberEvaluation](Classes/Evaluation/NumberEvaluation.php) for numbers with any number of decimals positions. Removes any non-numeric character and converts `,` to `.`

## Console Commands

* `cleanup:processedfiles` for truncating the table of processed files and deleting the corresponding files from the file system
* `cleanup:convertedfiles` for deleting the file variants generated by the image converters (WebP)
* `cleanup:nbsp` for replacing non-breaking spaces with regular spaces in RTE fields

## Content Elements

* Custom video content element with multiple sources, poster image and subtitles / captions

## AjaxController
A ready-to-use controller and plugin for handling AJAX requests via `?type=4000`. See [AjaxController::dispatchAction()](Classes/Controller/AjaxController.php#L134) for documentation.

## Miscellaneous

* Image lazyloading with automatic SVG based preview images (like [SQIP](https://github.com/technopagan/sqip); requires particular software on the server)
* Fluid Standalone template renderer
* Email notification tool (HTML emails & plaintext)
* Helper traits for repositories (for debugging SQL queries and for generally ignoring storage PIDs)

## To-do

* [ ] Test inline images in emails
* [x] Install simplexml for wyrihaximus/html-compress
