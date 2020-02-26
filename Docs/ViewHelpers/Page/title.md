b# TYPO3 extension: tw_base

> Collection of building blocks and viewhelpers for TYPO3 projects by tollwerk

## Page title viewhelper

The page title viewhelper utilizes the `FlexPageTitleProvider` to set a custom page title from within a Fluid template:

```html
<base:page.title title="Custom title"/>
```

For the custom page title to be used you need to register it as title provider in TypoScript:

```typoscript
config.pageTitleProviders {
    flex {
        provider = Tollwerk\TwBase\Domain\Provider\FlexPageTitleProvider
        before = record
    }
}
```

The `title` string provided to the viewhelper may also contain a placeholder `"%s"`. In that case you also have to provide the `replace` argument which lists one ore more keys of registered title providers that will be evaluated in the given order. The first title provider returning a non-empty value will be used to replace the placeholder.viewhelper

```html
<base:page.title title="Custom title | %s" replace="blog record"/>

<!-- equivalent to: -->

<base:page.title title="Custom title | %s" replace="{0: 'blog', 1: 'record'}"/>
```

Hint: `"record"` is the key of the one default page title provider used by TYPO3.
