# TYPO3 extension: tw_base

> Collection of building blocks and viewhelpers for TYPO3 projects by tollwerk

## `render` viewhelper

```html
<base:render partial="SomePartial" contentAs="content" shiftHeadingLevel="1">
    <base:heading content="Shifted heading">
        <p>Hello world!</p>
    </base:heading>
</base:render>
```

The `render` viewhelper is an extension of the standard Fluid `render` viewhelper with the capability to affect the heading context inside the viewhelper body. It is useful in combination with the `contentAs` argument.

Consider the following template:

```html
<f:render section="SomeSection" contentAs="content">
    <base:heading content="Sub heading">
        <p>Hello world</p>
    </base:heading>
</base:render>
        
<f:section name="SomeSection">
    <base:heading content="Main heading">
        <f:format.raw>{content}</f:format.raw>
    </base:heading>
</f:section>
```

By default, this would render as:

```html
<h2>Main heading</h2>
<h1>Sub heading</h1>
<p>Hello world</p>
```

The contents of the `<f:render>` element are created **before** the `<f:section>` contents are rendered. Therefore, the `"Sub heading"` gets the main heading level (`<h1>`) even although it's wrapped inside the `<f:section><base:heading>` call later on.

By using the extended `render` viewhelper you can temporarily shift the heading level using the `shiftHeadingLevel` attribute. A new heading context with the appropriate heading level will automatically be used for rendering the contents:

```html
<base:render section="SomeSection" contentAs="content" shiftHeadingLevel="1">
    <base:heading content="Sub heading">
        <p>Hello world</p>
    </base:heading>
</base:render>
        
<f:section name="SomeSection">
    <base:heading content="Main heading">
        <f:format.raw>{content}</f:format.raw>
    </base:heading>
</f:section>
```

Shifting the heading level by 1 creates the result you would expect:

```html
<h1>Main heading</h1>
<h2>Sub heading</h2>
<p>Hello world</p>
```

