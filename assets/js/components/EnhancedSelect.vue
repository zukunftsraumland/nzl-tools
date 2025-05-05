<template>
  <div class="enhanced-select" v-click-outside="closeDropdown">
    <div class="enhanced-select-header" @click="toggleDropdown">
      <div class="enhanced-select-value" :class="{ placeholder: alwaysShowPlaceholder || !selectedOption }">
        {{ alwaysShowPlaceholder ? placeholder : (selectedOption ? selectedOption.name : placeholder) }}
      </div>
      <div class="enhanced-select-arrow" :class="{ open: isOpen }"></div>
    </div>

    <div v-if="isOpen" class="enhanced-select-dropdown">
      <input
        v-if="searchable"
        type="text"
        class="enhanced-select-search"
        v-model="searchTerm"
        @click.stop
        placeholder="Suchen..."
        ref="searchInput"
      />
      
      <div class="enhanced-select-options">
        <div
          v-if="showPlaceholder"
          class="enhanced-select-option placeholder"
          @click="selectOption(null)"
        >
          {{ placeholder }}
        </div>
        <div
          v-for="option in filteredOptions"
          :key="option.id"
          class="enhanced-select-option"
          :class="{ selected: isSelected(option) }"
          @click="selectOption(option)"
        >
          {{ option.name }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EnhancedSelect',
  props: {
    modelValue: {
      type: [Object, null],
      default: null
    },
    options: {
      type: Array,
      default: () => []
    },
    placeholder: {
      type: String,
      default: 'Bitte wählen'
    },
    searchable: {
      type: Boolean,
      default: true
    },
    showPlaceholder: {
      type: Boolean,
      default: true
    },
    disabled: {
      type: Boolean,
      default: false
    },
    alwaysShowPlaceholder: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isOpen: false,
      searchTerm: '',
      selectedOption: null
    }
  },
  computed: {
    filteredOptions() {
      if (!this.searchTerm) return this.options
      
      const searchTermLower = this.searchTerm.toLowerCase()
      return this.options.filter(option => 
        option.name.toLowerCase().includes(searchTermLower)
      )
    }
  },
  watch: {
    modelValue: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          const option = this.options.find(opt => opt.id === newVal.id);
          if (option) {
            this.selectedOption = option;
          }
        } else {
          this.selectedOption = null;
        }
      }
    },
    options: {
      immediate: true,
      handler(newOptions) {
        if (newOptions.length > 0 && this.modelValue) {
          const option = newOptions.find(opt => opt.id === this.modelValue.id);
          if (option) {
            this.selectedOption = option;
          }
        }
      }
    },
    isOpen(newVal) {
      if (newVal && this.$refs.searchInput) {
        this.$nextTick(() => {
          this.$refs.searchInput.focus()
        })
      }
    }
  },
  methods: {
    toggleDropdown() {
      if (this.disabled) return
      this.isOpen = !this.isOpen
      if (!this.isOpen) {
        this.searchTerm = ''
      }
    },
    closeDropdown() {
      this.isOpen = false
      this.searchTerm = ''
    },
    selectOption(option) {
      this.selectedOption = option;
      this.$emit('update:modelValue', option);
      this.$emit('change', option);
      this.closeDropdown();
    },
    isSelected(option) {
      return this.selectedOption && this.selectedOption.id === option.id
    }
  },
  directives: {
    'click-outside': {
      bind(el, binding, vnode) {
        el.clickOutsideEvent = function(event) {
          if (!(el === event.target || el.contains(event.target))) {
            vnode.context[binding.expression](event)
          }
        }
        document.addEventListener('click', el.clickOutsideEvent)
      },
      unbind(el) {
        document.removeEventListener('click', el.clickOutsideEvent)
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.enhanced-select {
  position: relative;
  width: 100%;
  font-size: 14px;
  margin-bottom: 10px;

  &-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    background: white;
    border: 1px solid #5077b2;
    border-radius: 4px;
    cursor: pointer;
    min-height: 38px;

    &:hover {
      border-color: #3d5d8f;
    }
  }

  &-value {
    flex: 1;
    white-space: normal;
    line-height: 1.4;
    overflow: hidden;
    
    &.placeholder {
      color: #6c757d;
    }
  }

  &-arrow {
    width: 0;
    height: 0;
    margin-left: 8px;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #5077b2;
    transition: transform 0.2s ease;

    &.open {
      transform: rotate(180deg);
    }
  }

  &-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 4px;
    background: white;
    border: 1px solid #5077b2;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 1000;
    max-height: 300px;
    display: flex;
    flex-direction: column;
  }

  &-search {
    padding: 8px 12px;
    border: none;
    border-bottom: 1px solid #e9ecef;
    outline: none;
    font-size: 14px;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 4px;
    margin: 8px;

    &:focus {
      border-color: #5077b2;
      box-shadow: 0 0 0 0.2rem rgba(80, 119, 178, 0.25);
    }
  }

  &-options {
    overflow-y: auto;
    max-height: 250px;
  }

  &-option {
    padding: 8px 12px;
    cursor: pointer;
    white-space: normal;
    line-height: 1.4;
    border-bottom: 1px solid #f8f9fa;

    &:last-child {
      border-bottom: none;
    }

    &:hover {
      background-color: #f8f9fa;
    }

    &.selected {
      background-color: #e3f2fd;
      color: #5077b2;
    }

    &.placeholder {
      color: #6c757d;
      font-style: italic;
    }
  }
}

// Disabled state
.enhanced-select.disabled {
  opacity: 0.6;
  pointer-events: none;
}
</style> 