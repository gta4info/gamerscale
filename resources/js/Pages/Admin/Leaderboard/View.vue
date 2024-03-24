<template>
    <AdminLayout>
        <template #title>Лидерборд #{{leaderboard.id}}</template>

        <form @submit.prevent="submit">
            <v-select
                v-model="leaderboard.status"
                :items="available_statuses"
                label="Статус"
            ></v-select>

            <v-btn
                class="me-4"
                type="submit"
                v-show="changed"
            >
                Сохранить
            </v-btn>

            <v-btn
                class="me-4"
                @click="deleteLeaderboard"
                color="red"
            >
                Удалить
            </v-btn>
        </form>

        <v-card class="mt-6">
            <v-card-title>Таблица лидерборда</v-card-title>
            <v-card-text>
                <v-data-table
                    :items="leaderboard.users.sort((a,b) => b.points - a.points)"
                    :headers="headers"
                    no-data-text="Еще нет добавленных игроков..."
                >
                    <template v-slot:top>
                        <div class="d-flex mb-2">
                            <v-dialog
                                v-model="dialog"
                                max-width="500px"
                            >
                                <template v-slot:activator="{ props }">
                                    <v-btn
                                        class="mb-2 align-self-start"
                                        color="red"
                                        prepend-icon="mdi-plus"
                                        dark
                                        v-bind="props"
                                    >
                                        Добавить игрока
                                    </v-btn>
                                </template>
                                <v-card>
                                    <v-card-title>{{ formTitle }}</v-card-title>

                                    <v-card-text>
                                        <v-autocomplete
                                            v-model="editedItem.user"
                                            v-model:search="searchUsersQuery"
                                            :items="searchedUsers"
                                            label="Выберите игрока"
                                            :loading="loadingSearching"
                                            menu-icon=""
                                            item-value="id"
                                            item-title="name"
                                            hide-no-data
                                            hide-details
                                            class="mb-3"
                                            return-object
                                            v-show="editedIndex === -1"
                                        ></v-autocomplete>
                                        <v-text-field
                                            v-model="editedItem.points"
                                            label="Кол-во очков"
                                            type="number"
                                            min="0"
                                        ></v-text-field>
                                    </v-card-text>

                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn
                                            color="blue-darken-1"
                                            variant="text"
                                            @click="close"
                                        >
                                            Закрыть
                                        </v-btn>
                                        <v-btn
                                            color="blue-darken-1"
                                            variant="text"
                                            @click="saveUser"
                                        >
                                            Сохранить
                                        </v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-dialog>
                            <v-btn
                                class="align-self-start ml-2"
                                color="green"
                                dark
                                @click="updateUsers"
                                v-show="usersChanged"
                            >
                                Сохранить изменения
                            </v-btn>
                            <v-dialog v-model="dialogDelete" max-width="500px">
                                <v-card>
                                    <v-card-title>Подтвердить удаление игрока</v-card-title>
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn color="blue-darken-1" variant="text" @click="closeDelete">Отменить</v-btn>
                                        <v-btn color="blue-darken-1" variant="text" @click="deleteUserConfirm">OK</v-btn>
                                        <v-spacer></v-spacer>
                                    </v-card-actions>
                                </v-card>
                            </v-dialog>
                        </div>
                    </template>

                    <template v-slot:item.place="{ item }">
                        {{leaderboard.users.indexOf(item) + 1}}
                    </template>

                    <template v-slot:item.actions="{ item }">
                        <v-icon
                            class="me-2"
                            size="small"
                            @click="editUser(item)"
                        >
                            mdi-pencil
                        </v-icon>
                        <v-icon
                            size="small"
                            @click="deleteUser(item)"
                        >
                            mdi-delete
                        </v-icon>
                    </template>
                </v-data-table>
            </v-card-text>
        </v-card>
    </AdminLayout>
</template>

<script>

import AdminLayout from "../../../../views/layouts/Admin.vue";

export default {
    name: "View",
    components: {AdminLayout},
    props: {
        leaderboard: Object,
        available_statuses: Array,
    },
    data: () => ({
        changed: false,
        usersChanged: false,
        leaderboardCache: {},
        headers: [
            {title: 'Место', key: 'place', sortable: false},
            {title: 'Игрок', key: 'user.name'},
            {title: 'Кол-во очков', key: 'points'},
            {title: '', key: 'actions', sortable: false, align: 'end'},
        ],
        editedItem: {},
        defaultItem: {
            userId: null,
            points: 0
        },
        editedIndex: -1,
        dialog: false,
        dialogDelete: false,
        searchUsersQuery: '',
        loadingSearching: false,
        searchedUsers: [],
        usersToDelete: []
    }),
    methods: {
        submit() {
            axios.post(`/leaderboard/update/${this.leaderboard.id}`, {
                    leaderboard: this.leaderboard
                })
                .then(res => {
                    this.leaderboardCache = res.data.leaderboard;
                    this.changed = false;
                    alert(res.data.message)
                })
                .catch(err => alert(err.response.data.message))
        },
        deleteLeaderboard() {
            axios.post(`/leaderboard/delete/${this.leaderboard.id}`)
                .then(res => {
                    window.location = '/leaderboards/list';
                })
        },
        editUser(item) {
            this.editedIndex = this.leaderboard.users.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialog = true
        },
        deleteUser(item) {
            this.editedIndex = this.leaderboard.users.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialogDelete = true
        },
        close() {
            this.dialog = false
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },
        saveUser() {
            this.editedItem.points = parseInt(this.editedItem.points)
            this.editedItem.user.id = parseInt(this.editedItem.user.id)

            if (this.editedIndex > -1) {
                Object.assign(this.leaderboard.users[this.editedIndex], this.editedItem)
            } else {
                this.leaderboard.users.push({
                    user: {
                        id: this.editedItem.user.id,
                        name: this.editedItem.user.name
                    },
                    points: this.editedItem.points
                })
            }
            this.close()
        },
        closeDelete() {
            this.dialogDelete = false
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },
        deleteUserConfirm() {
            this.usersToDelete.push(this.leaderboard.users[this.editedIndex].user_id);
            this.leaderboard.users.splice(this.editedIndex, 1)
            this.closeDelete()
        },
        updateUsers() {
            axios.post(`/leaderboard/update-users/${this.leaderboard.id}`, {
                    users: this.leaderboard.users,
                    usersToDelete: this.usersToDelete
                })
                .then(res => {
                    this.leaderboardCache = this.leaderboard;
                    this.usersChanged = false;
                    this.usersToDelete = [];
                    alert(res.data.message)
                })
                .catch(err => alert(err.response.data.message))
        },
        querySelections (v) {
            this.loadingSearching = true
            this.searchedUsers = [];
            const existedIds = this.leaderboard.users.map(u => u.user.id);

            axios.get(`/users/search-by-name/${v}`, {
                params: {
                    existedIds: existedIds.toString()
                }
            })
                .then(res => {
                    this.searchedUsers = res.data.users;
                })

            this.loadingSearching = false
        },
        handleSelectedUserChange() {
            this.searchedUsers = [];
            this.searchUsersQuery = '';
        }
    },
    watch: {
        'leaderboard.status'(newVal, oldVal) {
            this.changed = newVal !== this.leaderboardCache.status;
        },
        dialog(val) {
            val || this.close()
        },
        dialogDelete(val) {
            val || this.closeDelete()
        },
        'leaderboard.users': {
            handler() {
                this.usersChanged = true;
            },
            deep: true
        },
        searchUsersQuery (val) {
            val && val !== this.searchedUsers && this.querySelections(val)
        },
    },
    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'Добавить игрока' : 'Изменить информацию'
        },
    },
    mounted() {
        this.leaderboardCache = {...this.leaderboard};
    }
}
</script>

<style scoped>

</style>
