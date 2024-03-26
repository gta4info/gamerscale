<template>
    <AdminLayout>
        <template #title>Профиль #{{user.id}} {{$page.props.auth.user.id === user.id ? '(Твой профиль)' : ''}}</template>

        <v-tabs v-model="tab">
            <v-tab value="profile">Профиль</v-tab>
            <v-tab value="achievements">Ачивки</v-tab>
            <v-tab value="balance">Управление балансом</v-tab>
            <v-tab value="balanceHistory">История баланса</v-tab>
        </v-tabs>
        <v-window v-model="tab" class="mt-6">
            <v-window-item value="profile">
                <v-list>
                    <v-list-item>
                        Ник Discord: {{user.name}}
                    </v-list-item>
                    <v-list-item>
                        Ник Epic: {{user.epic_name ?? 'Не подключен'}}
                    </v-list-item>
                    <v-list-item>
                        Email: {{user.email ?? 'Не указан'}}
                    </v-list-item>
                    <v-list-item>
                        Дата регистрации: {{$moment(user.created_at).format('YYYY-MM-DD HH:mm')}}
                    </v-list-item>
                    <v-list-item v-if="$page.props.auth.user.id !== user.id">
                        <v-btn color="red" @click="setAdmin" v-if="!user.is_admin">Выдать админку</v-btn>
                        <v-btn color="red" @click="unsetAdmin" v-else>Снять админку</v-btn>
                    </v-list-item>
                </v-list>
            </v-window-item>
            <v-window-item value="achievements">
                <v-dialog v-model="dialogEditAchievement" max-width="500px">
                    <v-card>
                        <v-card-title>Изменение прогресса ачивки</v-card-title>
                        <v-card-text>
                            <v-select
                                v-model="editedAchievementItem.level"
                                :items="getAvailableLevelsForAchievement"
                                label="Уровень"
                                type="number"
                                min="1"
                            ></v-select>
                            <v-text-field
                                v-model="editedAchievementItem.progress"
                                label="Текущий прогресс"
                                type="number"
                                min="1"
                            ></v-text-field>
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                                color="blue-darken-1"
                                variant="text"
                                @click="closeAchievementEdit"
                            >
                                Закрыть
                            </v-btn>
                            <v-btn
                                color="blue-darken-1"
                                variant="text"
                                @click="saveAchievement"
                            >
                                Сохранить
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
                <v-data-table
                    :items="user.achievements"
                    :headers="achievementsTableHeaders"
                    no-data-text="У пользователя нет активных ачивок"
                >
                    <template v-slot:top>
                        <div class="d-flex align-items-start">
                            <v-btn
                                class="me-4"
                                @click="updateUserAchievements"
                                v-show="changedAchievements"
                            >
                                Сохранить
                            </v-btn>
                        </div>
                    </template>
                    <template v-slot:item.actions="{ item }">
                        <v-icon
                            class="me-2"
                            size="small"
                            @click="editAchievement(item)"
                        >
                            mdi-pencil
                        </v-icon>
                    </template>
                </v-data-table>
            </v-window-item>
            <v-window-item value="balance">
                <h5>Текущие балансы пользователя:</h5>
                <v-list class="d-flex">
                    <v-list-item v-for="balance in current_balances">
                        {{balance_types.find(bt => bt.value === balance.type).name}}: {{balance.value}}
                    </v-list-item>
                </v-list>
                <v-row>
                    <v-col md="4" cols="12">
                        <v-select
                            :items="balance_types"
                            v-model="selectedBalanceType"
                            item-value="value"
                            item-title="name"
                            label="Выберите баланс"
                        ></v-select>
                    </v-col>
                    <v-col md="4" cols="12">
                        <v-select
                            :items="[{title: 'Пополнить', value: 'give'}, {title: 'Списать', value: 'take'}]"
                            v-model="selectedBalanceAction"
                            label="Выберите тип операции"
                        ></v-select>
                    </v-col>
                    <v-col md="4" cols="12">
                        <v-text-field
                            v-model="selectedBalanceValue"
                            label="Введите сумму"
                            type="number"
                            min="1"
                        ></v-text-field>
                    </v-col>
                </v-row>
                <v-btn @click="updateUserBalance" color="red" v-show="checkFieldsForBalanceUpdate">Применить операцию</v-btn>
            </v-window-item>
            <v-window-item value="balanceHistory">
                <v-data-table
                    :items="filteredBalances"
                    :headers="balanceTableHeaders"
                    no-data-text="У пользователя нет истории баланса"
                >
                    <template v-slot:top>
                        <div class="d-flex">
                            <v-checkbox
                                v-for="type in balance_types"
                                :key="type.value"
                                :label="type.name.toLowerCase()"
                                v-model="showBalancesByType.find(i => i.type === type.value).active"
                                @change="toggleShowBalanceType(type.value)"
                                hide-details
                            ></v-checkbox>
                        </div>
                    </template>
                    <template v-slot:item.created_at={item}>
                        {{$moment(item.created_at).format('YYYY-MM-DD HH:mm:ss')}}
                    </template>
                    <template v-slot:item.type={item}>
                        {{balance_types.find(bt => bt.value === item.type).name}}
                    </template>
                </v-data-table>
            </v-window-item>
        </v-window>
    </AdminLayout>
</template>

<script>
import AdminLayout from "../../../../views/layouts/Admin.vue";

export default {
    name: "Profile",
    components: {AdminLayout},
    props: {
        user: Object,
        balance_types: Array,
        current_balances: Array,
    },
    data: () => ({
        tab: null,
        balanceTableHeaders: [
            {title: 'Дата', key: 'created_at'},
            {title: 'Новая сумма', key: 'amount'},
            {title: 'Тип валюты', key: 'type'},
            {title: 'Коментарий', key: 'comment'},
        ],
        achievementsTableHeaders: [
            {title: 'Название', key: 'achievement.title'},
            {title: 'Уровень', key: 'level'},
            {title: 'Текущий прогресс', key: 'progress'},
            {title: '', key: 'actions', sortable: false, align: 'end'},
        ],
        showBalancesByType: [],
        dialogEditAchievement: false,
        editedAchievementItem: {
            level: null,
            progress: null,
            title: '',
            countLevels: null,
        },
        changedAchievements: false,
        selectedBalanceType: null,
        selectedBalanceValue: null,
        selectedBalanceAction: null,
    }),
    methods: {
        setAdmin() {
            axios.put(`/user/set-admin/${this.user.id}`)
                .then(res => {
                    this.user.is_admin = true;
                    alert(res.data.message)
                })
                .catch(err => alert(err.response.data.message))
        },
        unsetAdmin() {
            axios.put(`/user/unset-admin/${this.user.id}`)
                .then(res => {
                    this.user.is_admin = false;
                    alert(res.data.message)
                })
                .catch(err => alert(err.response.data.message))
        },
        toggleShowBalanceType(type) {
            const index = this.showBalancesByType.indexOf(type);
            if(index !== -1) {
                this.showBalancesByType.splice(index, 1);
            } else {
                this.showBalancesByType.push(type)
            }
        },
        editAchievement(item) {
            this.editedAchievementIndex = this.user.achievements.indexOf(item);
            const current = this.user.achievements[this.editedAchievementIndex];
            this.editedAchievementItem = {
                level: current.level,
                progress: current.progress,
                title: current.achievement.title,
                countLevels: current.achievement.levels.length,
            }
            this.dialogEditAchievement = true;
        },
        closeAchievementEdit() {
            this.dialogEditAchievement = false;
            this.$nextTick(() => {
                this.editedAchievementIndex = null;
                this.editedAchievementItem = {
                    level: null,
                    progress: null,
                    title: '',
                    countLevels: null,
                };
            })
        },
        saveAchievement() {
            this.editedAchievementItem.level = parseInt(this.editedAchievementItem.level)
            this.editedAchievementItem.progress = parseInt(this.editedAchievementItem.progress)

            Object.assign(this.user.achievements[this.editedAchievementIndex], this.editedAchievementItem)

            this.closeAchievementEdit()
        },
        updateUserAchievements() {
            axios.post(`/user/achievements-stats-update/${this.user.id}`,
                {
                    achievements: this.user.achievements
                })
                .then(res => {
                    this.changedAchievements = false;
                    alert(res.data.message)
                })
                .catch(err => alert(err.response.data.message))
        },
        updateUserBalance() {
            axios.post(`/user/balance/${this.user.id}`, {
                    currency_type: this.selectedBalanceType,
                    amount: this.selectedBalanceValue,
                    action: this.selectedBalanceAction,
                })
                .then(res => {
                    res.data.current_balances.forEach((item, index) => {
                        this.current_balances[index] = item;
                    })
                    this.selectedBalanceType = null;
                    this.selectedBalanceAction = null;
                    this.selectedBalanceValue = null;
                    alert(res.data.message)
                })
                .catch(err => alert(err.response.data.message))
        }
    },
    computed: {
        filteredBalances() {
            const activeTypes = [];
            this.showBalancesByType.map(i => {
                if(i.active) {
                    activeTypes.push(i.type);
                }
            })

            return this.user.balance
                .filter(i => activeTypes.includes(i.type))
                .sort((a,b) => b.id - a.id)
        },
        getAvailableLevelsForAchievement() {
            let arr = [];

            for(let i = 1; i <= this.editedAchievementItem.countLevels; i++) {
                arr.push({title: `Уровень ${i}`, value: i})
            }

            return arr;
        },
        checkFieldsForBalanceUpdate() {
            return this.selectedBalanceType !== null && this.selectedBalanceValue !== null && this.selectedBalanceAction !== null;
        }
    },
    watch: {
        'user.achievements': {
            handler() {
                this.changedAchievements = true;
            },
            deep: true
        }
    },
    beforeMount() {
        this.balance_types.map(bt => {
            this.showBalancesByType.push({
                type: bt.value,
                active: true
            })
        })
    }
}
</script>

<style scoped>

</style>
