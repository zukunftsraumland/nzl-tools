<template>

    <div class="file-selector-component">
        <div class="file-selector-component-item" v-for="(item, key) in items">
            <input type="text" class="form-control" v-model="item.name" v-if="!item.loading">
            <input type="text" class="form-control" value="Bitte warten..." readonly v-else>
            <div class="file-selector-component-item-remove" @click="items.splice(key, 1)" v-if="!item.loading && !readonly">
                <span class="material-icons error">cancel</span>
            </div>
            <div class="file-selector-component-item-move" v-if="!readonly">
                <span class="material-icons" v-if="key !== 0" @click="clickMoveLeft(key)">keyboard_arrow_left</span>
                <span class="material-icons" v-if="key + 1 !== items.length" @click="clickMoveRight(key)">keyboard_arrow_right</span>
            </div>
        </div>
        <label class="file-selector-component-add" :for="'upload-' + rand" v-if="!readonly">
            <span class="material-icons">add</span>
        </label>
        <input type="file" :id="'upload-' + rand" ref="upload" @change="addFile()" multiple :accept="allowedTypes">
    </div>

</template>

<script>
    export default {
        props: {
            items: {
                type: Array,
                default: [],
            },
            allowedTypes: {
                type: String,
                default: '.pdf',
            },
            readonly: {
                type: Boolean,
                default: false,
            },
        },
        data () {
            return {
                rand: Math.random(),
            };
        },
        methods: {
            addFile () {
                let files = this.$refs.upload.files;
                for(let file of files) {
                    let reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = () => {

                        if(file.size > 40000000) {
                            return alert('Die ausgewählte Datei "'+file.name+'" überschreitet das Uploadlimit von 30 MB.');
                        }

                        let item = {
                            name: file.name,
                            data: reader.result,
                            mimeType: file.type,
                            extension: file.name.split('.')[1] ? file.name.split('.')[file.name.split('.').length-1] : '',
                            loading: true,
                        };
                        item.extension = item.extension.toLowerCase();
                        this.items.push(item);

                        this.$store.dispatch('files/create', item).then((file) => {
                            this.items[this.items.indexOf(item)] = {...file};
                            this.$emit('changed', this.items);
                        });

                    };
                }
                this.$refs.upload.value = null;
            },
            clickMoveLeft(index) {
                [this.items[index], this.items[index-1]] = [this.items[index-1], this.items[index]]
            },
            clickMoveRight(index) {
                [this.items[index], this.items[index+1]] = [this.items[index+1], this.items[index]];
            },
        },
        created () {
            this.rand = Math.random();
        },
    }
</script>