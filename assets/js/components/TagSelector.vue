<template>

    <div class="tag-selector-component">
        <div class="tag-selector-component-selection">
            <div class="tag-selector-component-selection-tag" v-for="option in model.map(option => options.find(o => o.id === option.id))"
                 @click="removeOption(option)">
                <span v-if="option && option[label]">{{ option[label] }}</span>
            </div>
            <template v-if="!readonly">
                <input class="tag-selector-component-selection-search" type="text" v-model="term" v-if="searchType === 'text'">
                <div class="select-wrapper" v-if="searchType === 'select'">
                    <select class="form-control" @change="changeSelect()" v-model="selectValue">
                        <option v-for="option in filterOptions(options)" :value="option">{{ option[label] }}</option>
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
        },
        data() {
            return {
                term: '',
                selectValue: null,
            };
        },
        methods: {
            selectOption(option) {
                if(this.readonly) {
                    return false;
                }
                if(this.getOptionById(option.id)) {
                    return false;
                }
                this.term = '';
                this.model.push(option);
            },
            removeOption(option) {
                if(this.readonly) {
                    return false;
                }
                let remove = this.getOptionById(option.id);
                if(remove) {
                    this.model.splice(this.model.indexOf(remove), 1);
                }
            },
            getOptionById(id) {
                return this.model.find((option) => {
                    if(id === option.id) return true;
                });
            },
            filterOptions(options, term = '') {
                if(!options) {
                    return [];
                }
                if(this.searchType === 'select') {
                    return options.filter((option) => {
                        return !this.getOptionById(option.id);
                    }).sort((a, b) => {
                        return a[this.label].localeCompare(b[this.label]);
                    });
                }
                return options.filter((option) => {
                    if(!term) {
                        return false;
                    }
                    if(option[this.label] && option[this.label].toLowerCase().includes(term.trim().toLowerCase())) {
                        return true;
                    }
                    return false;
                });
            },
            changeSelect() {
                this.selectOption(this.selectValue);
                this.selectValue = null;
            },
        },
    }
</script>