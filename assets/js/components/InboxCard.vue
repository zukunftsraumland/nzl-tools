<template>

    <div class="inbox-card-component clickable"
         :class="{error: item.status === 'deleted'}">
        <div class="inbox-card-component-content">
            <div class="inbox-card-component-content-title">
                <template v-if="item.type === 'project' && item.status !== 'deleted'">
                    {{ getTitle(item.normalizedData) }}
                </template>
                <template v-else>
                    {{ item.title }}
                </template>
                <a @click="clickDismiss()" @click.stop class="inbox-card-component-content-title-dismiss">
                    <span class="material-icons">cancel</span>
                </a>
                <div class="date">{{ formatDate(item.createdAt) }}</div>
            </div>
            <div class="inbox-card-component-content-info">
                <div class="source regiosuisse" v-if="item.source === 'regiosuisse'">REGIOSUISSE</div>
                <div class="source chmos" v-if="item.source === 'chmos'">CHMOS</div>
                <div class="source blw" v-if="item.source === 'blw'">BLW</div>
                <div class="source xls" v-if="item.source === 'xls'">XLS</div>
                <div class="status success" v-if="item.status === 'new'">Neu</div>
                <div class="status warning" v-if="item.status === 'update'">Update</div>
                <div class="status error" v-if="item.status === 'deleted'">Gelöscht</div>
            </div>
        </div>
    </div>

</template>

<script>
    import moment from 'moment';

    export default {
        props: ['item'],
        methods: {
            getTitle(project) {
                if(project.title) {
                    return project.title;
                }
                if(project.translations && project.translations['fr'] && project.translations['fr'].title) {
                    return project.translations['fr'].title;
                }
                if(project.translations && project.translations['it'] && project.translations['it'].title) {
                    return project.translations['it'].title;
                }
                return '';
            },
            formatDate(date) {
                if(date && moment(date)) {
                    return moment(date).format('DD.MM.YYYY, HH:mm:ss') + ' Uhr';
                }
            },
            clickDismiss() {
                this.$emit('onDismiss', this.item);
            },
        }
    }
</script>