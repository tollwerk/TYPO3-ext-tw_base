# TYPO3 extension: tw_base

> Collection of building blocks and viewhelpers for TYPO3 projects by tollwerk

Title providers are a fairly modern approach to manipulate the title of a page (as used for the `<title>` element). You can register an arbitrary number of title providers [via TypoScript](https://docs.typo3.org/m/typo3/reference-typoscript/master/en-us/Setup/Config/Index.html#pagetitleproviders) (with `record` being the name of the default TYPO3 page title provider):

```typo3_typoscript
config {
    pageTitleProviders {
        flex {
            provider = Tollwerk\TwBase\Domain\Provider\FlexPageTitleProvider
            before = record
        }

        seo {
            provider = Tollwerk\TwBase\Domain\Provider\SeoPageTitleProvider
            before = flex
        }
    }
}
```

## `FlexPageTitleProvider`

The `FlexPageTitleProvider` has a higher priority as the default title provider and enables you to alter the page title from within PHP or Fluid. templates.

### PHP example

```php
use Tollwerk\TwBase\Domain\Provider\FlexPageTitleProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

GeneralUtility::makeInstance(FlexPageTitleProvider::class)->setTitle($title);
```

### Fluid example

```html
<base:page.title title="Custom title"/>
```

For more details and advanced features please see the `base:page.title` [ViewHelper documentation](ViewHelpers/Page/title.md).

## `SeoPageTitleProvider`

In combination with the corresponding **SEO Page Title** field (`tx_twbase_seo_title`) in page records, the SEO page title provider makes up for an easy method to optimize the page titles for SEO purposes. 

* The **SEO Page Title** field utilizes the `websiteTitle` value given in the **Site Configuration** to calculate a maximum length for optimal SEO performance. The input field is restricted to this amount of characters. For SEO purposes, the title should not exceed 65 characters in length. So the length of the SEO title is restricted to
    * `65` minus
    * length of `websiteTitle` minus
    * length of `config.pageTitleSeparator` including optionall `noTrimWrap` settings
* The `websiteTitle` given in a particular language is taking priority over the general `websiteTitle` in the Site Configuration.

The SEO title provider itself takes a lot into account:

* It should be the title provider with the highest priority.
* It'll walk through all other registered providers and determine the **actual page title**.
* It will search for the original value of the page's `title` in the SEO title string. If found, it will replace the original value with the **actual title** and return it as the final title. Otherwise, the SEO title will be returned.

Example:

* `title`: `"Contact Us"`
* `tx_twbase_seo_title`: `"Contact Us · fancy · keywords"`
* Final `<title>`: `"Contact Us · fancy · keywords"`
* Or final `<title>`: `"3 Errors! Contact Us · fancy · keywords"` (assuming the contact form creates an error and adds this information to the title, e.g. via the `FlexPageTitleProvider`)

**Hint**: By altering the page title dynamically, it is possible but generally not recommended to exceed the 65 character limit. 
