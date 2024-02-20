# %INSTANCE_ID%Jobs

## Setup

### Import CSS (optional)

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/jobs-latest/embed-jobs-latest.css">
```

### Import JavaScript

```html
<script src="%HOST%/embed/jobs-latest/embed-jobs-latest.js"></script>
```

## Basic Usage

```html
<div id="%INSTANCE_ID%-jobs"></div>
```

```javascript
%INSTANCE_ID%Jobs('#%INSTANCE_ID%-jobs', {
    // options
});
```

## Customization

### Options

| Option              | Type      | Description                                                                              | Example                                                    |
|---------------------|-----------|------------------------------------------------------------------------------------------|------------------------------------------------------------|
| `locale`            | `String`  | Set the locale.                                                                          | `"de"`                                                     |
| `fixedFilters`      | `Array`   | Set fixed filter options. These aren't visible to the user.                              | `[{ type: "stint", entity: { id: 2 } }]`                   |
| `defaultFilters`    | `Array`   | Preselect specific filter options.                                                       | `[{ type: "location", entity: { id: 1 } }]`                |
| `responsive`        | `Boolean` | Whether responsive media queries should be enabled.                                      | `true`                                                     |
| `middleware`        | `Object`  | (Experimental) Middleware options allow you to define custom methods to manipulate data. | `{ filterLocations: location => location.name !== "Foo" }` |
| `disableTelemetry`  | `Boolean` | Disable collection of telemetry data.                                                    | `true`                                                     |
| `history`           | `Object`  | Enable browser history support.                                                          | `{ mode: 'hash', base: 'http://localhost/jobs' }`          |
| `disableThumbnails` | `Boolean` | Disable list item thumbnails.                                                            | `true`                                                     |

### Styling

Basic styles can be overwritten using CSS variables:

```css
.embed-jobs {
    --%INSTANCE_ID%-max-width: none;
    --%INSTANCE_ID%-gutter-width: 1em;
    --%INSTANCE_ID%-primary-color: #0059be;
    --%INSTANCE_ID%-secondary-color: #92bae2;
    --%INSTANCE_ID%-border-radius-1: 0;
    --%INSTANCE_ID%-border-radius-2: 0;
}
```

> For more available variables have a look at %HOST%/embed/jobs-latest/embed-jobs-latest.css

## Full Example

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/jobs-latest/embed-jobs-latest.css">

<script src="%HOST%/embed/jobs-latest/embed-jobs-latest.js"></script>

<div id="%INSTANCE_ID%-jobs"></div>

<style>
    .embed-jobs {
        --%INSTANCE_ID%-primary-color: #0059be;
        --%INSTANCE_ID%-secondary-color: #92bae2;
    }
</style>

<script>
    %INSTANCE_ID%Jobs('#%INSTANCE_ID%-jobs', {
        locale: 'fr',
        responsive: true,
        fixedFilters: [
            { 
                type: 'stint', 
                entity: { 
                    id: 1,
                } 
            }
        ],
        defaultFilters: [
            { 
                type: 'location', 
                entity: { 
                    id: 1,
                } 
            }
        ],
        middleware: {
            mapLocations: location => ({ ...location, name: location.name === 'Foo' ? 'Bar' : location.name }),
            filterLocations: location => location.id !== 1,
            sortLocations: (a, b) => a.name.localeCompare(b.name),
            // mapStints
            // filterStints
            // sortStints
        },
    });
</script>
```