<template>

    <div class="single-selector-component">

        <div class="single-selector-component-selection">
            <input class="single-selector-component-selection-search" type="text" v-model="term"
                   @input="onInput" @change="onChange" :class="{ success: selectValue && !visibleOptions, warning: selectValue && selectValue.isPublic === false && !visibleOptions }">
        </div>
        <div class="single-selector-component-options" v-if="visibleOptions && filterOptions(options, term).length">
            <div class="single-selector-component-options-option" @mouseenter="checkTermIsDisabled = true" @mouseleave="checkTermIsDisabled = false"
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
        'value': Object,
        'options': Array,
        'label': {
            type: String,
            default: 'name'
        },
    },
    data() {
        return {
            term: '',
            selectValue: null,
            visibleOptions: false,
            checkTermIsDisabled: false,
        };
    },
    watch: {
        value(curVal, oldVal) {
            if(curVal && curVal !== oldVal) {
                this.term = curVal[this.label];
                this.selectOption(curVal);
                this.visibleOptions = false;
            }
        }
    },
    methods: {
        onInput() {
            this.visibleOptions = true;
            this.checkTermIsDisabled = false;
        },
        onChange() {
            if(!this.checkTermIsDisabled) {
                if(this.term === '') {
                    return this.selectOption(null);
                }

                if(this.selectValue && this.term !== this.selectValue[this.label]) {
                    this.term = this.selectValue[this.label];
                    this.visibleOptions = false;
                }
            }
        },
        selectOption(option) {
            this.checkTermIsDisabled = true;
            this.selectValue = option;

            if(!this.selectValue) {
                this.selectValue = {};
            }

            this.$emit('update', this.selectValue);
        },
        filterOptions(options, term = '') {
            if(!options) {
                return [];
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
    },
    created() {
        if(this.value) {
            this.selectOption(this.value);
            this.term = this.value[this.label];
        }
    }
}
</script>