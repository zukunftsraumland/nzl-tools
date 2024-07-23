<template>

    <div class="interactive-graphic-editor-component">

        <div v-html="getSvgStyle()"></div>

        <div class="interactive-graphic-editor-component-svg" v-html="svg" ref="svg" @click="clickSvg"></div>

        <div class="interactive-graphic-editor-component-content">

            <template v-if="selectedElementIdentifier">

                <div class="form-group">
                    <label for="type">Typ</label>
                    <div class="select-wrapper">
                        <select id="type" class="form-control" @change="onChangeType($event.target.value)">
                            <option value="text" :selected="['string', 'undefined'].includes(typeof config[selectedElementIdentifier])">Text</option>
                            <option value="project" :selected="['object'].includes(typeof config[selectedElementIdentifier]) && config[selectedElementIdentifier].type === 'project'">Projekt</option>
                        </select>
                    </div>
                </div>

                <div v-if="['string', 'undefined'].includes(typeof config[selectedElementIdentifier])">
                    <ckeditor :editor="editor" :config="editorConfig"
                              v-model="config[selectedElementIdentifier]" @blur="onChangeConfig()"></ckeditor>
                </div>

                <div v-if="['object'].includes(typeof config[selectedElementIdentifier]) && config[selectedElementIdentifier].type === 'project'">
                    <div class="form-group">
                        <label>Projekt-ID</label>
                        <input type="text" class="form-control" v-model="config[selectedElementIdentifier].id" @change="onChangeConfig()">
                    </div>
                </div>

            </template>

        </div>

    </div>

</template>

<script>
    import draggable from 'vuedraggable';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    export default {
        data() {
            return {
                selectedElement: null,
                selectedElementIdentifier: null,
                editor: ClassicEditor,
                editorConfig: {
                    basicEntities: false,
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'link',
                            '|',
                            'numberedList',
                            'bulletedList',
                            '|',
                            'undo',
                            'redo',
                        ]
                    }
                },
            };
        },
        components: {
            draggable,
        },
        props: {
            svg: {
                type: String,
                required: false,
            },
            selector: {
                type: String,
                required: false,
            },
            config: {
                type: Object,
                required: false,
            },
        },
        computed: {
        },
        methods: {
            clickSvg (event) {

                let target = event.target;

                if(!target.matches(this.selector) && !target.matches(this.selector+' *')) {
                    return;
                }

                if(!target.matches(this.selector)) {
                    target = event.target.closest(this.selector);
                }

                this.clickSvgElement(target);

            },
            clickSvgElement (target) {
                this.$refs.svg.querySelectorAll('svg '+this.selector+', svg '+this.selector+' *').forEach((e) => {
                    e.classList.remove('active');
                });

                let svgClone = this.$refs.svg.querySelector('svg').cloneNode(false);

                svgClone.append(target.cloneNode(true));

                this.selectedElement = target;
                this.selectedElementIdentifier = svgClone.innerHTML;

                this.selectedElement.classList.add('active');
            },
            getSvgStyle () {
                let style = '';

                if(!this.selector) {
                    return style;
                }

                style += '.interactive-graphic-editor-component svg *' + ' ' +
                    '{' +
                        'pointer-events: none;' +
                    '}';

                style += '.interactive-graphic-editor-component svg ' + this.selector + ' ' +
                    '{' +
                        'opacity: .25;' +
                        'cursor: pointer;' +
                        'pointer-events: auto;' +
                        'transition: all .25s;' +
                    '}';

                style += '.interactive-graphic-editor-component svg ' + this.selector + ' * ' +
                    '{' +
                        'pointer-events: auto;' +
                    '}';

                style += '.interactive-graphic-editor-component svg ' + this.selector + ':hover ' +
                    '{' +
                        'opacity: 1;' +
                    '}';

                style += '.interactive-graphic-editor-component svg ' + this.selector + '.active ' +
                    '{' +
                        'opacity: 1;' +
                    '}';

                return '<style>'+style+'</style>';
            },
            onChangeConfig () {
                this.$emit('onChangeConfig', this.config);
            },
            onChangeType (type) {
                this.config[this.selectedElementIdentifier] = '';

                if(type === 'project') {
                    this.config[this.selectedElementIdentifier] = {
                        type: type,
                        id: '',
                    };
                }

                this.onChangeConfig();
            },
        },
    }
</script>