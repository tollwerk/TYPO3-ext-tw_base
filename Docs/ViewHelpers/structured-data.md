# TYPO3 extension: tw_base

> Collection of building blocks and viewhelpers for TYPO3 projects by tollwerk

## `structuredData` viewhelpers

The `structuredData` viewhelpers provide a simple interface for adding JSON-LD snippets to a page. They use the [http://schema.org](http://schema.org) vocabulary.

Data objects can be added in any Fluid template during the rendering process. The **output** of the JSON-LD representation, however, is intended to happen only once, preferably at the end of the rendering process / at the end of the page in one single JSON-LD script block. The `StructuredDataManager` singleton is taking care of aggregating all the registered data objects.

## `StructuredDataManager`

The Structured Data Manager acts as a singleton in the background and provides 3 important and 2 lesser important public methods. While it's possible to call these directly from within PHP code, they're usually utilized via the viewhelpers (see below).

1. **`register(string $type, string $id, array $data): array`** for registering a new **top-level data object**. `$type` is a schema.org data type (e.g. `'Person'`). `$id` is a unique URI referencing the data object (if an anchor-style URI is given, like `#john-doe`, the current page's base URI will be prepended automatically, resolving to e.g. `https://mysite.com#john-doe`). The 3rd parameter `$data` is an array of object properties, e.g. `['givenName' => 'John', 'familyName' => 'Doe']`, adhering to the schema.org vocabulary. The created data object / node will be returned.
2. **`public function set(string $id, $key, $value): void`** to set a single property for a previously registered object. The object is referenced by it's URI. Example: `set('#john-doe', 'additionalName', 'Jack')`.
3. **`createNode(string $type, string $id, array $data): array`** for registering a new **non-top-level data object**. It works the same as `register()` except that the object / node will just be returned without being added to the object graph. Useful for adding nested objects (see example below).
4. **`getGraph(): stdClass`** to return the complete object graph (used internally).
5. **`getBaseUri(): string`** to return the current page's base URI.

## Initialization

It should be common practice to add **at least one data object to each page** denoting the main type of the page, usually using the [WebPage](https://schema.org/WebPage) object. This can easily be achieved by "priming" the `StructuredDataManager` using an initialization hook, as shown in the following example.

### `ext_localconf.php` of your extension

```php
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['structuredData']['initialize'][] = \Your\Extension\Utility\StructuredDataUtility::class.'->initialize';
```

### `Classes/Utility/StructuredDataUtility.php`

```php
class StructuredDataUtility
{
    /**
     * Custom Structured Data Initialization
     *
     * @param array $params                                Parameters (empty)
     * @param StructuredDataManager $structuredDataManager Structured data manager
     */
    public function initialize(array $params, StructuredDataManager $structuredDataManager): void
    {
        // Register a top-level WebPage data object
        $structuredDataManager->register('WebPage', $structuredDataManager->getBaseUri(), []);
    }
}
```
You can also add additional default data objects, e.g. the publisher (example also adds a nested image object using `createNode()`): 

```php
$webPage = $structuredDataManager->register('WebPage', $structuredDataManager->getBaseUri(), []);
$structuredDataManager->register(
    'Organization',
    'https://tollwerk.de/#organization',
    [
        '@reverse' => [
            'publisher' => [
                ['@id' => $webPage['@id']],
            ]
        ],
        'name'     => 'tollwerk GmbH',
        'url'      => 'https://tollwerk.de',
        'logo'     => $structuredDataManager->createNode(
            'ImageObject',
            'https://tollwerk.de/#logo',
            ['url' => 'https://tollwerk.de/t.png']
        )
    ]
);
```

## `<base:structuredData.register>`

Use this viewhelper to register a new data object:

```html
<base:structuredData.register type="Person" id="#john-doe" data="{givenName: 'John', familyName: 'Doe'}"/>
```

## `<base:structuredData.set>`

Use this viewhelper to set an additional property to an already existing data object:

```html
<base:structuredData.set id="#john-doe" key="additionalName" value="Jack"/>
```

## `<base:structuredData.add>`

Use this viewhelper to add an additional value to an already existing data object property. Non-list properties will automatically be converted to a list, keeping the current value as the first list item. If the property doesn't exist yet, it will be created.

```html
<base:structuredData.add id="#john-doe" key="sameAs" value="https://johndoe.com"/>
```

## `<base:structuredData.idref>`

Resolves and returns an ID reference to a data object (as full URI). The return value is either an ID string or a JSON object referencing a data object by ID string. 

```html
<!-- Return the current page's base URI --> 
<base:structuredData.idref id=""/>

<!-- Return the normalized ID of the john-doe object (based on the current page URL as) --> 
<base:structuredData.idref id="#john-doe"/>

<!-- Return the ID of the john-doe object as top-level object (based on the current site URL) --> 
<base:structuredData.idref id="#john-doe" global="1"/>

<!-- Return a JSON reference to the john-doe object ({@id: '...#john-doe'}) --> 
<base:structuredData.idref id="#john-doe" object="1"/>
```

## `<base:structuredData.entityContext.wrap>` / `<base:structuredData.entityContext.get>`

Enables you to register a particular data object as "main entity" for nested fluid operations. `<base:structuredData.entityContext.get>` will return the ID of the current main entity, also within nested partials.

```html
<base:structuredData.entityContext.wrap id="#john-doe">
    <!-- Inside here the john-doe object is considered the "main entity" -->
    
    <f:variable name="mainEntity" value="{base:structuredData.entityContext.get()}"/>
    <base:structuredData.set id="{$mainEntity}" key="additionalName" value="Jack"/>
</base:structuredData.entityContext.wrap>
```

## `<base:structuredData>`

Use this viewhelper to output the aggregated structured data objects. This can be placed in the footer of a page, e.g. in a Fluid layout. The boolean `pretty` parameter can be used to pretty-print the output (just for readabilty).

```html
<base:structuredData pretty="true"/>
```

This will e.g. output:

```html
<script type="application/ld+json">{
    "@context": "http:\/\/schema.org",
    "@graph": [
        {
            "@type": "WebPage",
            "@id": "https:\/\/stage.tollwerk.de\/"
        },
        {
            "@type": "Organization",
            "@id": "https:\/\/tollwerk.de\/#organization",
            "@reverse": {
                "publisher": [
                    {
                        "@id": "https:\/\/stage.tollwerk.de\/"
                    }
                ]
            },
            "name": "tollwerk GmbH",
            "url": "https:\/\/tollwerk.de",
            "logo": {
                "@type": "ImageObject",
                "@id": "https:\/\/tollwerk.de\/#logo",
                "url": "https:\/\/tollwerk.de\/t.png"
            }
        }
    ]
}</script>
```


