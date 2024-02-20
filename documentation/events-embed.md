# %INSTANCE_ID%Events

## Setup

### Import CSS (optional)

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/events-latest/embed-events-latest.css">
```

### Import JavaScript

```html
<script src="%HOST%/embed/events-latest/embed-events-latest.js"></script>
```

## Basic Usage

```html
<div id="%INSTANCE_ID%-events"></div>
```

```javascript
%INSTANCE_ID%Events('#%INSTANCE_ID%-events', {
    // options
});
```

## Customization

### Options

| Option             | Type      | Description                                                                              | Example                                               |
|--------------------|-----------|------------------------------------------------------------------------------------------|-------------------------------------------------------|
| `locale`           | `String`  | Set the locale.                                                                          | `"de"`                                                |
| `limit`            | `Number`  | Set the pagination limit.                                                                | `30`                                                  |
| `fixedFilters`     | `Array`   | Set fixed filter options. These aren't visible to the user.                              | `[{ type: "topic", entity: { id: 2 } }]`              |
| `defaultFilters`   | `Array`   | Preselect specific filter options.                                                       | `[{ type: "program", entity: { id: 1 } }]`            |
| `responsive`       | `Boolean` | Whether responsive media queries should be enabled.                                      | `true`                                                |
| `middleware`       | `Object`  | (Experimental) Middleware options allow you to define custom methods to manipulate data. | `{ filterTopics: topic => topic.name !== "Tourism" }` |
| `disableTelemetry` | `Boolean` | Disable collection of telemetry data.                                                    | `true`                                                |
| `history`          | `Object`  | Enable browser history support.                                                          | `{ mode: 'hash', base: 'http://localhost/events' }` |

### Styling

Basic styles can be overwritten using CSS variables:

```css
.embed-events {
    --%INSTANCE_ID%-max-width: none;
    --%INSTANCE_ID%-gutter-width: 1em;
    --%INSTANCE_ID%-primary-color: #0059be;
    --%INSTANCE_ID%-secondary-color: #92bae2;
    --%INSTANCE_ID%-border-radius-1: 0;
    --%INSTANCE_ID%-border-radius-2: 0;
}
```

> For more available variables have a look at %HOST%/embed/events-latest/embed-events-latest.css

## Full Example

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/events-latest/embed-events-latest.css">

<script src="%HOST%/embed/events-latest/embed-events-latest.js"></script>

<div id="%INSTANCE_ID%-events"></div>

<style>
    .embed-events {
        --%INSTANCE_ID%-primary-color: #0059be;
        --%INSTANCE_ID%-secondary-color: #92bae2;
    }
</style>

<script>
    %INSTANCE_ID%Events('#%INSTANCE_ID%-events', {
        locale: 'fr',
        limit: 6,
        responsive: true,
        fixedFilters: [
            { 
                type: 'topic', 
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
            mapTopics: topic => ({ ...topic, name: topic.name === 'Foo' ? 'Bar' : topic.name }),
            filterTopics: topic => topic.id !== 1,
            sortTopics: (a, b) => a.name.localeCompare(b.name),
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