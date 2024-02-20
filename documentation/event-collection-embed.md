# %INSTANCE_ID%EventCollection

## Setup

### Import CSS (optional)

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/event-collection-latest/embed-event-collection-latest.css">
```

### Import JavaScript

```html
<script src="%HOST%/embed/event-collection-latest/embed-event-collection-latest.js"></script>
```

## Basic Usage

```html
<div id="%INSTANCE_ID%-event-collection"></div>
```

```javascript
%INSTANCE_ID%EventCollection('#%INSTANCE_ID%-event-collection', collectionId, {
    // options
});
```

## Customization

### Options

| Option             | Type      | Description                                                                              | Example                                               |
|--------------------|-----------|------------------------------------------------------------------------------------------|-------------------------------------------------------|
| `locale`           | `String`  | Set the locale.                                                                          | `"de"`                                                |

### Styling

Basic styles can be overwritten using CSS variables:

```css
.embed-event-collection {
    --%INSTANCE_ID%-max-width: none;
    --%INSTANCE_ID%-gutter-width: 1em;
    --%INSTANCE_ID%-primary-color: #0059be;
    --%INSTANCE_ID%-secondary-color: #92bae2;
    --%INSTANCE_ID%-border-radius-1: 0;
    --%INSTANCE_ID%-border-radius-2: 0;
}
```

> For more available variables have a look at %HOST%/embed/event-collection-latest/embed-event-collection-latest.css

## Full Example

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/event-collection-latest/embed-event-collection-latest.css">

<script src="%HOST%/embed/event-collection-latest/embed-event-collection-latest.js"></script>

<div id="%INSTANCE_ID%-event-collection"></div>

<style>
    .embed-event-collection {
        --%INSTANCE_ID%-primary-color: #0059be;
        --%INSTANCE_ID%-secondary-color: #92bae2;
    }
</style>

<script>
    %INSTANCE_ID%EventCollection('#%INSTANCE_ID%-event-collection', 1, {
        locale: 'fr',
    });
</script>
```