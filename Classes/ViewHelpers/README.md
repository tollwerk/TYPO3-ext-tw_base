# ViewHelper XSD schema

Register the included XSD schema [tw_base_viewhelpers.xsd](tw_base_viewhelpers.xsd) with your IDE (e.g. PHPStorm) for the namespace `http://typo3.org/ns/Tollwerk/TwBase/ViewHelpers` in order to get autocompletion support for `tw_base` viewhelper names and arguments when authoring Fluid templates.

To recreate the XSD schema, please install and run the [typo3/fluid-schema-generator](https://packagist.org/packages/typo3/fluid-schema-generator) package:

```
composer require --dev typo3/fluid-schema-generator
vendor/bin/generateschema Tollwerk\\TwBase > public/typo3conf/ext/tw_base/Classes/ViewHelpers/tw_base_viewhelpers.xsd
```