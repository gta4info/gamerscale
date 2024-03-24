<template>
    <AdminLayout>
        <template #title>Список пользователей</template>

        <v-data-table :items="users" :headers="headers" :search="search">
            <template v-slot:top>
                <v-text-field placeholder="Поиск по Дискорду/Эпику" v-model="search"/>
            </template>

            <template v-slot:item.epic_name="{ item }">
                <span v-if="item.epic_name">{{item.epic_name}}</span>
                <span class="text-red" v-else>Не привязан</span>
            </template>

            <template v-slot:item.created_at="{ item }">
                {{$moment(item.created_at).format('YYYY-MM-DD')}}
            </template>

            <template v-slot:item.actions="{ item }">
                <inertia-link :href="`/user/view/${item.id}`">
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
        users: Array
    },
    data: () => ({
        headers: [
            { title: 'ID', key: 'id'},
            { title: 'Epic', key: 'epic_name' },
            { title: 'Discord', key: 'name' },
            { title: 'Дата регистрации', key: 'created_at' },
            { title: '', key: 'actions', sortable: false },
        ],
        search: ''
    }),
    mounted() {

    }
}
</script>
