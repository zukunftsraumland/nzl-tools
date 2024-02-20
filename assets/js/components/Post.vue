<template>

    <div class="post-component">

        <div class="post-component-form">

            <div class="post-component-form-header">

                <h3>Eintrag erstellen</h3>

                <div class="post-component-form-header-actions">
                    <a class="button" @click="showPreview = true">Vorschau</a>
                    <a class="button warning" @click="post.isPublic = true" v-if="!post.isPublic">Entwurf</a>
                    <a class="button success" @click="post.isPublic = false" v-if="post.isPublic">Öffentlich</a>
                    <a @click="locale = 'de'" class="button" :class="{primary: locale === 'de'}">DE</a>
                    <a @click="locale = 'fr'" class="button" :class="{primary: locale === 'fr'}">FR</a>
                    <a @click="locale = 'it'" class="button" :class="{primary: locale === 'it'}">IT</a>
                    <a class="button error" @click="clickDelete()" v-if="post.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="post-component-form-section">

                <div class="row">
                    <div class="col-md-6" v-if="locale === 'de'">
                        <label for="title">Bezeichnung</label>
                        <input id="title" type="text" class="form-control" v-model="post.title" :placeholder="translate('title', post)">
                    </div>
                    <div class="col-md-6" v-else>
                        <label for="title">Bezeichnung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="title" type="text" class="form-control" v-model="post.translations[locale].title" :placeholder="translate('title', post)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="description">Teaser</label>
                        <textarea name="description" id="description" class="form-control" rows="2" v-model="post.description" :placeholder="translate('description', post)"></textarea>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="description">Teaser (Übersetzung {{ locale.toUpperCase() }})</label>
                        <textarea name="description" id="description" class="form-control" rows="2" v-model="post.translations[locale].description" :placeholder="translate('description', post)"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="text">Text</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="post.text" :placeholder="translate('text', post)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="text">Text (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="post.translations[locale].text" :placeholder="translate('text', post)"></ckeditor>
                    </div>
                </div>

                <div class="post-component-form-section-group">

                    <div class="post-component-form-section-group-headline">Kategorisierung</div>

                    <div class="row">
                        <div class="col-md-3">
                            <label for="date">Datum</label>
                            <date-picker mode="date" v-model="post.date" :locale="'de'">
                                <template v-slot="{ inputValue, inputEvents }">
                                    <input type="text" class="form-control"
                                           :value="inputValue"
                                           v-on="inputEvents"
                                           id="startDate">
                                </template>
                            </date-picker>
                        </div>
                        <div class="col-md-3">
                            <label for="locations">Thema</label>
                            <tag-selector id="topics" :model="post.topics"
                                          :options="topics.filter(topic => !topic.context || topic.context === 'post')" :searchType="'select'"></tag-selector>
                        </div>
                    </div>

                </div>

                <div class="post-component-form-section-group">

                    <div class="post-component-form-section-group-headline">Weiterführende Informationen</div>

                    <div class="row">
                        <div class="col-md-8">
                            <label v-if="locale === 'de'">Links</label>
                            <label v-else>Links (Übersetzung {{ locale.toUpperCase() }})</label>
                            <div class="row" v-for="(link, index) in (locale === 'de' ? post.links : post.translations[locale].links)">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="link.label" placeholder="Bezeichnung">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="link.value" placeholder="URL">
                                </div>
                                <div class="col-md-3">
                                    <button class="button error" @click="clickRemoveLink(index)">Link entfernen</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <button class="button success" @click="clickAddLink()">Link hinzufügen</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <label v-if="locale === 'de'">Videos</label>
                            <label v-else>Videos (Übersetzung {{ locale.toUpperCase() }})</label>
                            <div class="row" v-for="(video, index) in (locale === 'de' ? post.videos : post.translations[locale].videos)">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="video.label" placeholder="Bezeichnung">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="video.value" placeholder="URL">
                                </div>
                                <div class="col-md-3">
                                    <button class="button error" @click="clickRemoveVideo(index)">Video entfernen</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <button class="button success" @click="clickAddVideo()">Video hinzufügen</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="images">Bilder</label>
                            <image-selector id="images" :items="post.images" :locale="locale" @changed="updateImages"></image-selector>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="files">Dokumente</label>
                            <file-selector id="files" :items="post.files" :locale="locale" @changed="updateFiles"></file-selector>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="post-component-overlay" v-if="showPreview" @click="showPreview = false">

            <EmbedPostsView @click.stop @clickClose="showPreview = false"
                               :post="post" :locale="locale"></EmbedPostsView>

        </div>


        <transition name="fade">
            <Modal v-if="modal" :config="modal"></Modal>
        </transition>

    </div>

</template>

<script>
import { mapState } from 'vuex';
import draggable from 'vuedraggable';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import TagSelector from './TagSelector';
import ImageSelector from './ImageSelector';
import FileSelector from './FileSelector';
import EmbedPostsView from "./EmbedPostsView";
import Modal from './Modal';
import {DatePicker} from 'v-calendar';
import moment from 'moment';

export default {
    data() {
        return {
            locale: 'de',
            post: {
                isPublic: false,
                title: '',
                description: '',
                text: '',
                date: moment().format('DD.MM.YYYY'),
                topics: [],
                links: [],
                videos: [],
                images: [],
                files: [],
                translations: {
                    fr: {
                        links: [],
                        videos: [],
                    },
                    it: {
                        links: [],
                        videos: [],
                    },
                },
            },
            showPreview: false,
            modal: null,
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
                        'insertTable',
                        '|',
                        'undo',
                        'redo',
                    ]
                }
            },
            simpleEditorConfig: {
                basicEntities: false,
                toolbar: {
                    items: [
                        'bold',
                        'italic',
                        'link',
                        '|',
                        'numberedList',
                        'bulletedList',
                        'insertTable',
                    ]
                }
            },
        };
    },
    components: {
        EmbedPostsView,
        TagSelector,
        ImageSelector,
        FileSelector,
        DatePicker,
        draggable,
        Modal,
    },
    computed: {
        ...mapState({
            selectedPost: state => state.posts.post,
            topics: state => state.topics.all,
        }),
    },
    methods: {
        clickDelete () {
            this.modal = {
                title: 'Eintrag löschen',
                description: 'Sind Sie sicher dass Sie diesen Eintrag unwiderruflich löschen möchten?',
                actions: [
                    {
                        label: 'Endgültig löschen',
                        class: 'error',
                        onClick: () => {
                            this.$store.dispatch('posts/delete', this.post.id).then(() => {
                                this.$router.push('/posts');
                            });
                        }
                    },
                    {
                        label: 'Abbrechen',
                        class: 'warning',
                        onClick: () => {
                            this.modal = null;
                        }
                    }
                ],
            };
        },
        clickCancel () {
            this.$router.push('/posts');
        },
        clickSave() {

            if(this.post.id) {
                return this.$store.dispatch('posts/update', this.post).then(() => {
                    this.$router.push('/posts');
                });
            }

            this.$store.dispatch('posts/create', this.post).then(() => {
                this.$router.push('/posts');
            });

        },
        reload() {
            if(this.$route.params.id) {
                this.$store.commit('posts/set', {});
                this.$store.dispatch('posts/load', this.$route.params.id).then(() => {
                    this.post = {...this.selectedPost};

                    if(!this.post.translations['fr'].videos) {
                        this.post.translations['fr'].videos = [];
                    }

                    if(!this.post.translations['it'].videos) {
                        this.post.translations['it'].videos = [];
                    }
                });
            }
        },
        clickAddLink() {
            (this.locale === 'de' ? this.post.links : this.post.translations[this.locale].links).push({
                value: '',
                label: '',
            });
        },
        clickRemoveLink(index) {
            let link = (this.locale === 'de' ? this.post.links : this.post.translations[this.locale].links).splice(index, 1)[0];
        },
        clickAddVideo() {
            (this.locale === 'de' ? this.post.videos : this.post.translations[this.locale].videos).push({
                value: '',
                label: '',
            });
        },
        clickRemoveVideo(index) {
            let video = (this.locale === 'de' ? this.post.videos : this.post.translations[this.locale].videos).splice(index, 1)[0];
        },
        updateImages(images) {
            this.post.images = images;
        },
        updateFiles(files) {
            this.post.files = files;
        },
        translate(property, context) {
            if(this.locale === 'de') {
                return context[property] || context.translations.fr[property] || context.translations.it[property];
            }
            if(this.locale === 'fr') {
                return context.translations.fr[property] || context[property] || context.translations.it[property];
            }
            if(this.locale === 'it') {
                return context.translations.it[property] || context.translations.fr[property] || context[property];
            }
            return context[property];
        },
        parseYoutubeId(url) {
            const result = (url || '').split(/(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/)/);
            return (result[2] !== undefined) ? result[2].split(/[^0-9a-z_\-]/i)[0] : false;
        },
    },
    created () {
        this.reload();
    }
}
</script>