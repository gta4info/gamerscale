<template>
    <AdminLayout>
        <template #title>Список лидербордов</template>

        <v-data-table :items="leaderboards" :headers="headers" no-data-text="Добавленных лидербордов не найдено">

            <template v-slot:item.status="{ value }">
                {{available_statuses[value].title}}
            </template>

            <template v-slot:item.created_at="{ value }">
                {{$moment(value).format('YYYY-MM-DD HH:mm')}}
            </template>

            <template v-slot:item.actions="{ item }">
                <inertia-link :href="`/leaderboard/view/${item.id}`">
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
        leaderboards: Array,
        available_statuses: Array
    },
    data: () => ({
        headers: [
            { title: 'ID', key: 'id'},
            { title: 'Статус', key: 'status' },
            { title: 'Кол-во игроков', key: 'users_count' },
            { title: 'Дата создания', key: 'created_at' },
            { title: '', key: 'actions', sortable: false },
        ],
    })
}
</script>
