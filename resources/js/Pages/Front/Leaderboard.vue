<template>
    <FrontLayout>
        <v-data-table
            :items="leaderboard.users"
            :headers="headers"
            no-data-text="Не найдено участников."
            :items-per-page-options="[10]"
            items-per-page-text=""
            :show-current-page="false"
            page-text=""
            class="table-hide-controls"
            :search="searchByName"
            filter-keys="user.name"
        >
            <template v-slot:item="props">
                <tr :class="{active: props.item.user.id === $page.props.auth.user.id}">
                    <td>{{leaderboard.users.indexOf(props.item) + 1}}</td>
                    <td>
                        <a :href="`/profile/${props.item.user.id}`" class="d-flex align-items-center" target="_blank">
                            <v-avatar>
                                <v-img :src="props.item.user.avatar_url"></v-img>
                            </v-avatar>
                            <span class="ml-3">{{props.item.user.name}}</span>
                        </a>
                    </td>
                    <td>{{props.item.points}}</td>
                    <td></td>
                </tr>
            </template>
            <template #footer.prepend>
                <div class="leaderboard__search">
                    <v-text-field
                        v-model="searchByName"
                        placeholder="Поиск по нику"
                        hide-details
                        rounded
                    ></v-text-field>
                </div>
            </template>
        </v-data-table>
    </FrontLayout>
</template>

<script>
import FrontLayout from "../../../views/layouts/Front.vue";
export default {
    components: {FrontLayout},
    props: {
        leaderboard: Object
    },
    data: () => ({
        headers: [
            {title: 'Место', key: 'place', sortable: false, filterable: false, width: '10%'},
            {title: 'Ник', key: 'user.name', sortable: false, width: '30%'},
            {title: 'Очки', key: 'points', sortable: false, filterable: false},
            {title: 'Призы', key: 'prizes', sortable: false, filterable: false},
        ],
        searchByName: ''
    }),
}
</script>

<style scoped>
    .leaderboard__search {
        width: 300px;
        margin-right: auto;
    }
</style>
