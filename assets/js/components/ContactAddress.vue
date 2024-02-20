<template>

    <div>

        <template v-if="company">
            <strong>{{ company.companyName }}</strong><br>
        </template>

        <template v-if="person">
            {{ person.title }} {{ person.firstName }} {{ person.lastName }}<br>
        </template>

        <template v-if="employment">
            {{ employment?.role }}<br>
        </template>

        <template v-if="company?.street">
            {{ company.street }}<br>
            {{ company.zipCode }} {{ company.city }}<br>
        </template>

        <template v-else-if="person?.street">
            {{ person.street }}<br>
            {{ person.zipCode }} {{ person.city }}<br>
        </template>

        <template v-if="person?.email">
            <a :href="'mailto:'+person.email">{{ person.email }}</a><br>
        </template>

        <template v-else-if="company?.email">
            <a :href="'mailto:'+company.email">{{ company.email }}</a><br>
        </template>

        <template v-if="company?.website">
            <a :href="company.website">{{ company.website }}</a><br>
        </template>

        <template v-else-if="person?.website">
            <a :href="person.website">{{ person.website }}</a><br>
        </template>

    </div>

</template>

<script>

import {translateField} from '../utils/filters';

export default {

    props: {
        locale: {
            type: String,
            required: true,
        },
        person: {
            type: Object,
            required: false,
            default: null,
        },
        company: {
            type: Object,
            required: false,
            default: null,
        },
        employment: {
            type: Object,
            required: false,
            default: null,
        },
    },

    methods: {
        translateField,
    },

}

</script>