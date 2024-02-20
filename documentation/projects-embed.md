# %INSTANCE_ID%Projects

## Setup

### Import CSS (optional)

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/projects-latest/embed-projects-latest.css">
```

### Import JavaScript

```html
<script src="%HOST%/embed/projects-latest/embed-projects-latest.js"></script>
```

## Basic Usage

```html
<div id="%INSTANCE_ID%-projects"></div>
```

```javascript
%INSTANCE_ID%Projects('#%INSTANCE_ID%-projects', {
    // options
});
```

## Customization

### Options

| Option             | Type      | Description                                                                              | Example                                                                 |
|--------------------|-----------|------------------------------------------------------------------------------------------|-------------------------------------------------------------------------|
| `locale`           | `String`  | Set the locale.                                                                          | `"de"`                                                                  |
| `limit`            | `Number`  | Set the pagination limit.                                                                | `30`                                                                    |
| `fixedFilters`     | `Array`   | Set fixed filter options. These aren't visible to the user.                              | `[{ type: "topic", entity: { id: 2 } }]`                                |
| `defaultFilters`   | `Array`   | Preselect specific filter options.                                                       | `[{ type: "program", entity: { id: 1 } }]`                              |
| `responsive`       | `Boolean` | Whether responsive media queries should be enabled.                                      | `true`                                                                  |
| `middleware`       | `Object`  | (Experimental) Middleware options allow you to define custom methods to manipulate data. | `{ filterTopics: topic => topic.name !== "Tourism" }`                   |
| `disableTelemetry` | `Boolean` | Disable collection of telemetry data.                                                    | `true`                                                                  |
| `history`          | `Object`  | Enable browser history support.                                                          | `{ mode: 'hash', base: 'http://localhost/projects' }`                   |
| `mapboxApiToken`   | `String`  | Mapbox API Token.                                                                        | `"pk.eyJ1IjoiY..."`                                                     |
| `templateHooks`    | `Object`  | Insert html into template hooks.                                                         | `{ projectContentAfter: (instance, project) => '<p>Hello World!</p>' }` |

### Styling

Basic styles can be overwritten using CSS variables:

```css
.embed-projects {
    --%INSTANCE_ID%-max-width: none;
    --%INSTANCE_ID%-gutter-width: 1em;
    --%INSTANCE_ID%-primary-color: #0059be;
    --%INSTANCE_ID%-secondary-color: #92bae2;
    --%INSTANCE_ID%-border-radius-1: 0;
    --%INSTANCE_ID%-border-radius-2: 0;
}
```

> For more available variables have a look at %HOST%/embed/projects-latest/embed-projects-latest.css

## Full Example

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/projects-latest/embed-projects-latest.css">

<script src="%HOST%/embed/projects-latest/embed-projects-latest.js"></script>

<div id="%INSTANCE_ID%-projects"></div>

<style>
    .embed-projects {
        --%INSTANCE_ID%-primary-color: #0059be;
        --%INSTANCE_ID%-secondary-color: #92bae2;
    }
</style>

<script>
    %INSTANCE_ID%Projects('#%INSTANCE_ID%-projects', {
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
                type: 'program', 
                entity: { 
                    id: 1,
                } 
            }
        ],
        middleware: {
            mapTopics: topic => ({ ...topic, name: topic.name === 'Foo' ? 'Bar' : topic.name }),
            filterTopics: topic => topic.id !== 1,
            sortTopics: (a, b) => a.name.localeCompare(b.name),
            // mapStates
            // filterStates
            // sortStates
            // mapPrograms
            // filterPrograms
            // sortPrograms
            // mapInstruments
            // filterInstruments
            // sortInstruments
        },
        templateHooks: {
            projectContentAfter: (instance, project) => '<p>The project ID is: '+project.id+'</p>',
            // mapToggleAfter: function (instance) {}
            // filterSelectOptionsItemAfter: function (instance, context, item) {}
            // projectContentBefore: function (instance, project) {}
            // projectSidebarImage: function (instance, project) {}
        },
    });
</script>
```