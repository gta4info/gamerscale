<template>
    <AdminLayout>
        <template #title>Список призов</template>

        <v-data-table
            :items="prizes.filter(i => selectedStatuses.includes(i.status) || selectedStatuses.includes(-1))"
            :headers="headers"
            :search="search"
            filter-keys="user.name"
            no-data-text="Призов не найдено"
        >
            <template v-slot:top>
                <v-row>
                    <v-col cols="12" md="6">
                        <v-text-field placeholder="Поиск по нику дискорда" v-model="search"/>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="selectedStatuses"
                            :items="statuses"
                            multiple
                            label="Показывать статусы"
                        ></v-select>
                    </v-col>
                </v-row>
            </template>

            <template v-slot:item.status="{ item }">
                {{statuses.find(s => s.value === item.status).title}}
            </template>

            <template v-slot:item.updated_at="{ item }">
                {{$moment(item.updated_at).format('YYYY-MM-DD HH:mm')}}
            </template>

            <template v-slot:item.actions="{ item }">
                <inertia-link :href="`/prizes/history/view/${item.id}`">
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
        statuses: Array
    },
    data: () => ({
        headers: [
            { title: 'Игрок', key: 'user.name' },
            { title: 'Название', value: 'prize.title', key: 'title' },
            { title: 'Категория', key: 'category.title' },
            { title: 'Статус', key: 'status' },
            { title: 'Дата обновления', key: 'updated_at' },
            { title: '', key: 'actions', sortable: false },
        ],
        search: '',
        selectedStatuses: [-1]
    }),
    watch: {
        selectedStatuses(newVal, oldVal) {
            if(newVal.includes(-1) && !oldVal.includes(-1) || newVal.length === 0) {
                this.selectedStatuses = [-1];
                return;
            }
            if(newVal.length > 1 && this.selectedStatuses.find(s => s === -1)) {
                this.selectedStatuses.splice(this.selectedStatuses.indexOf(this.selectedStatuses.find(s => s === -1)), 1);
            }
        }
    },
    mounted() {
        this.statuses.unshift({value: -1, title: 'Все статусы'})
    }
}
</script>
