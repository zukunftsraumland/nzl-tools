# %INSTANCE_ID%Contacts

## Setup

### Import CSS (optional)

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/contacts-latest/embed-contacts-latest.css">
```

### Import JavaScript

```html
<script src="%HOST%/embed/contacts-latest/embed-contacts-latest.js"></script>
```

## Basic Usage

```html
<div id="%INSTANCE_ID%-contacts"></div>
```

```javascript
%INSTANCE_ID%Contacts('#%INSTANCE_ID%-contacts', {
    // options
});
```

## Customization

### Options

| Option              | Type      | Description                                                                              | Example                                               |
|---------------------|-----------|------------------------------------------------------------------------------------------|-------------------------------------------------------|
| `locale`            | `String`  | Set the locale.                                                                          | `"de"`                                                |
| `fixedFilters`      | `Array`   | Set fixed filter options. These aren't visible to the user.                              | `[{ type: "country", entity: { id: 2 } }]`            |
| `defaultFilters`    | `Array`   | Preselect specific filter options.                                                       | `[{ type: "state", entity: { id: 1 } }]`              |
| `responsive`        | `Boolean` | Whether responsive media queries should be enabled.                                      | `true`                                                |
| `middleware`        | `Object`  | (Experimental) Middleware options allow you to define custom methods to manipulate data. | `{ filter: country => country.name !== "Schweiz" }` |
| `disableTelemetry`  | `Boolean` | Disable collection of telemetry data.                                                    | `true`                                                |
| `history`           | `Object`  | Enable browser history support.                                                          | `{ mode: 'hash', base: 'http://localhost/contacts' }` |
| `disableThumbnails` | `Boolean` | Disable list item thumbnails.                                                            | `true`                                                |

### Styling

Basic styles can be overwritten using CSS variables:

```css
.embed-contacts {
    --%INSTANCE_ID%-max-width: none;
    --%INSTANCE_ID%-gutter-width: 1em;
    --%INSTANCE_ID%-primary-color: #0059be;
    --%INSTANCE_ID%-secondary-color: #92bae2;
    --%INSTANCE_ID%-border-radius-1: 0;
    --%INSTANCE_ID%-border-radius-2: 0;
}
```

> For more available variables have a look at %HOST%/embed/contacts-latest/embed-contacts-latest.css

## Full Example

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/contacts-latest/embed-contacts-latest.css">

<script src="%HOST%/embed/contacts-latest/embed-contacts-latest.js"></script>

<div id="%INSTANCE_ID%-contacts"></div>

<style>
    .embed-contacts {
        --%INSTANCE_ID%-primary-color: #0059be;
        --%INSTANCE_ID%-secondary-color: #92bae2;
    }
</style>

<script>
    %INSTANCE_ID%Contacts('#%INSTANCE_ID%-contacts', {
        locale: 'fr',
        responsive: true,
        fixedFilters: [
            { 
                type: 'country', 
                entity: { 
                    id: 1,
                } 
            }
        ],
        defaultFilters: [
            { 
                type: 'state', 
                entity: { 
                    id: 1,
                } 
            }
        ],
        middleware: {
            mapStates: state => ({ ...state, name: state.name === 'Foo' ? 'Bar' : state.name }),
            filterStates: state => state.id !== 1,
            sortStates: (a, b) => a.name.localeCompare(b.name),
            // mapContactGroups
            // filterContactGroups
            // sortContactGroups
        },
    });
</script>
```