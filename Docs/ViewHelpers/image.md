# TYPO3 extension: tw_base

> Collection of building blocks and viewhelpers for TYPO3 projects by tollwerk

## Image view helper

```html
<base:image src="path/to/image.jpg" width="500"/>
```

The extended image viewhelper works just like the [standard Fluid image viewhelper](https://docs.typo3.org/other/typo3/view-helper-reference/9.5/en-us/typo3/fluid/latest/Image.html) (arguments are the same) and additionally file format specific compressors:

* [mozjpeg](https://github.com/mozilla/mozjpeg) for JPEG images
* [SVGO](https://github.com/svg/svgo) for SVG graphics

Depending on an images's optimization potential, it tends to become a bit smaller when processed with the `<base:image>` view helper.
