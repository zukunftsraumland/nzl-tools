# %INSTANCE_ID%Educations

## Setup

### Import CSS (optional)

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/educations-latest/embed-educations-latest.css">
```

### Import JavaScript

```html
<script src="%HOST%/embed/educations-latest/embed-educations-latest.js"></script>
```

## Basic Usage

```html
<div id="%INSTANCE_ID%-educations"></div>
```

```javascript
%INSTANCE_ID%Educations('#%INSTANCE_ID%-educations', {
    // options
});
```

## Customization

### Options

| Option              | Type      | Description                                                                              | Example                                                                        |
|---------------------|-----------|------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------|
| `locale`            | `String`  | Set the locale.                                                                          | `"de"`                                                                         |
| `fixedFilters`      | `Array`   | Set fixed filter options. These aren't visible to the user.                              | `[{ type: "educationType", entity: { id: 2 } }]`                               |
| `defaultFilters`    | `Array`   | Preselect specific filter options.                                                       | `[{ type: "language", entity: { id: 1 } }]`                                    |
| `responsive`        | `Boolean` | Whether responsive media queries should be enabled.                                      | `true`                                                                         |
| `middleware`        | `Object`  | (Experimental) Middleware options allow you to define custom methods to manipulate data. | `{ filterEducationTypes: educationType => educationType.name !== "Bachelor" }` |
| `disableTelemetry`  | `Boolean` | Disable collection of telemetry data.                                                    | `true`                                                                         |
| `history`           | `Object`  | Enable browser history support.                                                          | `{ mode: 'hash', base: 'http://localhost/educations' }`                        |
| `disableThumbnails` | `Boolean` | Disable list item thumbnails.                                                            | `true`                                                                         |

### Styling

Basic styles can be overwritten using CSS variables:

```css
.embed-educations {
    --%INSTANCE_ID%-max-width: none;
    --%INSTANCE_ID%-gutter-width: 1em;
    --%INSTANCE_ID%-primary-color: #0059be;
    --%INSTANCE_ID%-secondary-color: #92bae2;
    --%INSTANCE_ID%-border-radius-1: 0;
    --%INSTANCE_ID%-border-radius-2: 0;
}
```

> For more available variables have a look at %HOST%/embed/educations-latest/embed-educations-latest.css

## Full Example

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/educations-latest/embed-educations-latest.css">

<script src="%HOST%/embed/educations-latest/embed-educations-latest.js"></script>

<div id="%INSTANCE_ID%-educations"></div>

<style>
    .embed-educations {
        --%INSTANCE_ID%-primary-color: #0059be;
        --%INSTANCE_ID%-secondary-color: #92bae2;
    }
</style>

<script>
    %INSTANCE_ID%Educations('#%INSTANCE_ID%-educations', {
        locale: 'fr',
        responsive: true,
        fixedFilters: [
            { 
                type: 'educationType', 
                entity: { 
                    id: 1,
                } 
            }
        ],
        defaultFilters: [
            { 
                type: 'language', 
                entity: { 
                    id: 1,
                } 
            }
        ],
        middleware: {
            mapEducationTypes: educationType => ({ ...educationType, name: educationType.name === 'Foo' ? 'Bar' : educationType.name }),
            filterEducationTypes: educationType => educationType.id !== 1,
            sortEducationType: (a, b) => a.name.localeCompare(b.name),
            // mapLanguages
            // filterLanguages
            // sortLanguages
            // mapLocations
            // filterLocations
            // sortLocations
        },
    });
</script>
```