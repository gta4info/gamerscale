<template>
    <AdminLayout>
        <template #title>Список призов</template>

        <v-data-table :items="prizes" :headers="headers" :search="search"
                      no-data-text="Добавленных призов не найдено">
            <template v-slot:top>
                <v-text-field placeholder="Поиск по названию" v-model="search"/>
            </template>

            <template v-slot:item.icon="{ value }">
                <v-img :src="value ?? '/img/no-image.jpg'" height="40" max-width="40"></v-img>
            </template>

            <template v-slot:item.type="{ value }">
                {{available_types[value].title}}
            </template>

            <template v-slot:item.actions="{ item }">
                <inertia-link :href="`/prize/view/${item.id}`">
                    <v-icon
                        class="me-2"
                        size="small"
                    >
                        mdi-eye
                    </v-icon>
                </inertia-link>
            </template>
        </v-data-table>
    </AdminLayout>
</template>

<script>
import AdminLayout from "../../../../views/layouts/Admin.vue";

export default {
    components: {AdminLayout},
    props: {
        prizes: Array,
        available_types: Array
    },
    data: () => ({
        headers: [
            { title: 'ID', key: 'id'},
            { title: 'Иконка', key: 'icon', value: 'icon', sortable: false, filterable: false },
            { title: 'Название', value: 'title', key: 'title' },
            { title: 'Тип', key: 'type' },
            { title: 'Значение', value: 'value', key: 'value' },
            { title: '', key: 'actions', sortable: false },
        ],
        search: ''
    })
}
</script>
