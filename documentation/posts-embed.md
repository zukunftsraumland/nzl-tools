# %INSTANCE_ID%Posts

## Setup

### Import CSS (optional)

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/posts-latest/embed-posts-latest.css">
```

### Import JavaScript

```html
<script src="%HOST%/embed/posts-latest/embed-posts-latest.js"></script>
```

## Basic Usage

```html
<div id="%INSTANCE_ID%-posts"></div>
```

```javascript
%INSTANCE_ID%Posts('#%INSTANCE_ID%-posts', {
    // options
});
```

## Customization

### Options

| Option              | Type      | Description                                                                              | Example                                                                        |
|---------------------|-----------|------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------|
| `locale`            | `String`  | Set the locale.                                                                          | `"de"`                                                                         |
| `fixedFilters`      | `Array`   | Set fixed filter options. These aren't visible to the user.                              | `[{ type: "postType", entity: { id: 2 } }]`                               |
| `defaultFilters`    | `Array`   | Preselect specific filter options.                                                       | `[{ type: "language", entity: { id: 1 } }]`                                    |
| `responsive`        | `Boolean` | Whether responsive media queries should be enabled.                                      | `true`                                                                         |
| `middleware`        | `Object`  | (Experimental) Middleware options allow you to define custom methods to manipulate data. | `{ filterPostTypes: postType => postType.name !== "Bachelor" }` |
| `disableTelemetry`  | `Boolean` | Disable collection of telemetry data.                                                    | `true`                                                                         |
| `history`           | `Object`  | Enable browser history support.                                                          | `{ mode: 'hash', base: 'http://localhost/posts' }`                        |
| `disableThumbnails` | `Boolean` | Disable list item thumbnails.                                                            | `true`                                                                         |

### Styling

Basic styles can be overwritten using CSS variables:

```css
.embed-posts {
    --%INSTANCE_ID%-max-width: none;
    --%INSTANCE_ID%-gutter-width: 1em;
    --%INSTANCE_ID%-primary-color: #0059be;
    --%INSTANCE_ID%-secondary-color: #92bae2;
    --%INSTANCE_ID%-border-radius-1: 0;
    --%INSTANCE_ID%-border-radius-2: 0;
}
```

> For more available variables have a look at %HOST%/embed/posts-latest/embed-posts-latest.css

## Full Example

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/posts-latest/embed-posts-latest.css">

<script src="%HOST%/embed/posts-latest/embed-posts-latest.js"></script>

<div id="%INSTANCE_ID%-posts"></div>

<style>
    .embed-posts {
        --%INSTANCE_ID%-primary-color: #0059be;
        --%INSTANCE_ID%-secondary-color: #92bae2;
    }
</style>

<script>
    %INSTANCE_ID%Posts('#%INSTANCE_ID%-posts', {
        locale: 'fr',
        responsive: true,
        fixedFilters: [
            { 
                type: 'postType', 
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
            mapPostTypes: postType => ({ ...postType, name: postType.name === 'Foo' ? 'Bar' : postType.name }),
            filterPostTypes: postType => postType.id !== 1,
            sortPostType: (a, b) => a.name.localeCompare(b.name),
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