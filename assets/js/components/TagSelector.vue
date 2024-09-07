<template>
    <div class="tag-selector-component">
        <div class="tag-selector-component-selection">
            <template v-if="isOptGroup">
                <div class="tag-selector-component-selection-tag" v-for="option in groupOptions"
                    @click="removeOption(option)" v-if="showSelection">
                    <span v-if="option && option[label]">{{ option[label] }}</span>
                </div>
            </template>

            <template v-else>
                <div class="tag-selector-component-selection-tag" v-for="option in model.map(option => options.find(o => o.id === option.id))"
                    @click="removeOption(option)" v-if="showSelection">
                    <span v-if="option && option[label]">{{ option[label] }}</span>
                </div>
            </template>

            <template v-if="!readonly">
                <input class="tag-selector-component-selection-search" type="text" v-model="term" v-if="searchType === 'text'">

                <div class="select-wrapper" v-if="searchType === 'select'">
                    <select class="form-control" @change="changeSelect()" v-model="selectValue">
                        <option v-if="labelSelectAll.length > 0" value="selectAll">{{ labelSelectAll }}</option>
                        <template v-if="isOptGroup">
                            <optgroup :label="optGroup[label]" v-for="optGroup in options">
                                <option v-for="option in filterOptions(optGroup.children)" :value="option">{{ option[label] }}</option>
                            </optgroup>
                        </template>

                        <option v-else v-for="option in filterOptions(options)" :value="option">{{ option[label] }}</option>
                    </select>
                </div>

            </template>
        </div>

        <div class="tag-selector-component-options" v-if="searchType === 'text' && filterOptions(options, term).length">
            <div class="tag-selector-component-options-option"
                v-for="option in filterOptions(options, term)"
                @click="selectOption(option)">
                {{ option[label] }}
            </div>
        </div>
    </div>
</template>

<script>
export default {
    emits: ['change'],
    props: {
        'model': Array,
        'options': Array,
        'label': {
            type: String,
            default: 'name'
        },
        'readonly': Boolean,
        'searchType': {
            type: String,
            default: 'text'
        },
        'showSelection': {
            type: Boolean,
            default: true
        },
        'isOptGroup': {
            type: Boolean,
            default: false,
        },
        'labelSelectAll': {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            term: '',
            selectValue: null,
        };
    },
    methods: {
        selectOption(option) {
            if(this.readonly) return false;
            if(option === 'selectAll') {
                this.toggleSelectAll();
                return;
            }
            if(this.getOptionById(option.id)) return false;
            this.term = '';
            this.model.push(option);
            this.$emit('change', this.model);
        },
        removeOption(option) {
            if(this.readonly) return false;
            let remove = this.getOptionById(option.id);
            if(remove) {
                this.model.splice(this.model.indexOf(remove), 1);
                this.$emit('change', this.model);
            }
        },
        getOptionById(id) {
            return this.model.find(option => option.id === id);
        },
        filterOptions(options, term = '') {
            if(!options) return [];
            if(this.searchType === 'select') {
                return options.filter(option => !this.getOptionById(option.id)).sort((a, b) => a[this.label].localeCompare(b[this.label]));
            }
            return options.filter(option => option[this.label]?.toLowerCase().includes(term.trim().toLowerCase()));
        },
        changeSelect() {
            this.selectOption(this.selectValue);
            this.selectValue = null;
        },
        toggleSelectAll() {
            if(this.isAllSelected) {
                this.model.splice(0);
            } else {
                this.model.splice(0, this.model.length, ...this.options);
            }
            this.$emit('change', this.model);
        },
    },
    computed: {
        isAllSelected() {
            return this.model.length === this.options.length;
        },
        groupOptions() {
            if(!this.model) return [];
            let groupOptions = [];
            for(let option of this.model) {
                for(let optGroup of this.options) {
                    for(let opt of optGroup.children) {
                        if(opt.id === option.id) {
                            groupOptions.push({
                                ...opt, [this.label]: `${optGroup[this.label]}: ${opt[this.label]}`
                            });
                        }
                    }
                }
            }
            return groupOptions;
        }
    },
}
</script>
