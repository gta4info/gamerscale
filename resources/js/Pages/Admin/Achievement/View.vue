<template>
    <AdminLayout>
        <template #title>Основная информация</template>

        <form @submit.prevent="submit">
            <v-text-field
                v-model="achievement.title"
                label="Название"
            ></v-text-field>

            <v-text-field
                v-model="achievement.description"
                label="Описание"
            ></v-text-field>

            <v-select
                v-model="achievement.type"
                :items="available_types"
                label="Тип ачивки"
            ></v-select>

            <v-select
                v-model="achievement.is_active"
                :items="[{title: 'Неактивна', value: false}, {title: 'Активна', value: true}]"
                label="Статус отображения у пользователей"
            ></v-select>

            <v-row class="mb-3">
                <v-col cols="2">
                    <v-img :src="iconPreviewUrl"></v-img>
                </v-col>
                <v-col cols="10">
                    <v-file-input
                        accept="image/png, image/jpeg"
                        label="Иконка"
                        placeholder="Выбрать изображение"
                        prepend-icon="mdi-camera"
                        v-model="icon"
                        @change="iconPreview"
                        @click:clear="iconPreviewUrl = null"
                        ref="icon"
                    ></v-file-input>
                </v-col>
            </v-row>
            <v-btn
                class="me-4"
                type="submit"
                v-show="changed"
            >
                Сохранить
            </v-btn>

            <v-btn
                class="me-4"
                @click="deleteAchievement"
                color="red"
            >
                Удалить
            </v-btn>
        </form>

        <v-card class="mt-6">
            <v-card-title>Управление уровнями</v-card-title>
            <v-card-text>
                <v-data-table
                    :headers="tableLevelsHeaders"
                    :items="levels"
                    :sort-by="[{ key: 'level', order: 'asc' }]"
                    items-per-page="-1"
                    no-data-text="Пока что тут нет добавленных уровней"
                >
                    <template v-slot:bottom></template>
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
                                        Добавить уровень
                                    </v-btn>
                                </template>
                                <v-card>
                                    <v-card-title>{{ formTitle }}</v-card-title>

                                    <v-card-text>
                                        <v-text-field
                                            v-model="editedItem.value"
                                            label="Значение"
                                            type="number"
                                            min="1"
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
                                            @click="saveLevel"
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
                                @click="updateLevels"
                                v-show="levelsChanged"
                            >
                                Сохранить изменения
                            </v-btn>
                            <v-dialog v-model="dialogDelete" max-width="500px">
                                <v-card>
                                    <v-card-title>Подтвердить удаление уровня</v-card-title>
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn color="blue-darken-1" variant="text" @click="closeDelete">Отменить</v-btn>
                                        <v-btn color="blue-darken-1" variant="text" @click="deleteLevelConfirm">OK</v-btn>
                                        <v-spacer></v-spacer>
                                    </v-card-actions>
                                </v-card>
                            </v-dialog>
                        </div>
                    </template>
                    <template v-slot:item.prizes="{ item }">
                        <div class="d-flex">
                            <div v-for="p in item.prizes">
                                <v-dialog v-model="prizePreviewDialogs[item.level + '-' + p.prize_id]" max-width="500px">
                                    <template v-slot:activator="{ props }">
                                        <v-img
                                            class="cursor-pointer"
                                            :src="p.prize.icon ?? '/img/no-image.jpg'"
                                            width="40"
                                            height="40"
                                            v-bind="props"
                                            v-ripple
                                        ></v-img>
                                    </template>
                                    <v-card>
                                        <v-card-title>Название: {{prizes.filter(pr => pr.id === p.prize_id)[0].title}}</v-card-title>
                                        <v-card-text>
                                            <v-text-field disabled v-model="p.prize.value" label="Значение"></v-text-field>
                                            <v-text-field disabled v-model="prizes.filter(pr => pr.id === p.prize_id)[0].type" label="Тип приза"></v-text-field>
                                        </v-card-text>
                                        <v-card-actions>
                                            <v-btn color="blue-darken-1" variant="text" @click="prizePreviewDialogs[item.level + '-' + p.prize_id] = false">Закрыть</v-btn>
                                            <v-dialog v-model="prizeConfirmDeleteDialogs[item.level + '-' + p.prize_id]" max-width="500px">
                                                <template v-slot:activator="{ props }">
                                                    <v-btn color="red" v-bind="props" class="ml-2">Отвязать приз от уровня</v-btn>
                                                </template>
                                                <v-card>
                                                    <v-card-title>Подтвердить отвязку приза от уровня: {{item.level}}</v-card-title>
                                                    <v-card-actions>
                                                        <v-spacer></v-spacer>
                                                        <v-btn color="blue-darken-1" variant="text"
                                                               @click="prizeConfirmDeleteDialogs[item.level + '-' + p.prize_id] = false">
                                                            Отменить
                                                        </v-btn>
                                                        <v-btn color="blue-darken-1" variant="text" @click="removePrizeFromLevel(item.level, p.prize_id)">OK</v-btn>
                                                        <v-spacer></v-spacer>
                                                    </v-card-actions>
                                                </v-card>
                                            </v-dialog>
                                        </v-card-actions>
                                    </v-card>
                                </v-dialog>
                            </div>
                            <v-dialog v-model="addPrizeDialog[item.level]" max-width="500px">
                                <template v-slot:activator="{props}">
                                    <v-btn icon flat size="small" class="ml-2" v-bind="props">
                                        <v-icon color="red">mdi-plus</v-icon>
                                    </v-btn>
                                </template>
                                <v-card>
                                    <v-card-title>Добавить приз к уровню: {{item.level}}</v-card-title>
                                    <v-card-text>
                                        <v-select
                                            v-model="addPrizeId"
                                            :items="prizesNotAssignedToLevel(item.level)"
                                            :item-title="(item)=>`${item.title} (${item.value} ${item.type})`"
                                            item-value="id"
                                            placeholder="Выберите приз из списка"
                                        ></v-select>
                                    </v-card-text>
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn color="blue-darken-1" variant="text"
                                               @click="addPrizeDialog[item.level] = false">
                                            Отменить
                                        </v-btn>
                                        <v-btn color="blue-darken-1" variant="text" @click="addPrize(item.level)">OK</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-dialog>
                        </div>
                    </template>
                    <template v-slot:item.actions="{ item }">
                        <v-icon
                            class="me-2"
                            size="small"
                            @click="editLevel(item)"
                        >
                            mdi-pencil
                        </v-icon>
                        <v-icon
                            size="small"
                            @click="deleteLevel(item)"
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
        achievement: Object,
        levels: Array,
        available_types: Array,
        prizes: Array
    },
    data: () => ({
        changed: false,
        iconPreviewUrl: null,
        icon: null,
        achievementCache: {},
        tableLevelsHeaders: [
            {title: 'Уровень', key: 'level'},
            {title: 'Значение', key: 'value'},
            {title: 'Призы', key: 'prizes'},
            {title: '', key: 'actions', sortable: false, align: 'end'},
        ],
        dialog: false,
        dialogDelete: false,
        editedIndex: -1,
        editedItem: {
            value: null,
        },
        defaultItem: {
            value: null,
        },
        addPrizeId: null,
        levelsChanged: false,
        prizePreviewDialogs: [],
        prizeConfirmDeleteDialogs: [],
        addPrizeDialog: [],
    }),
    methods: {
        removePrizeFromLevel(level, prizeId) {
            const levelItem = this.levels.find(p => p.level === level);
            const prizeItem = levelItem.prizes.find(p => p.prize_id === prizeId);
            const prizeItemIndex = levelItem.prizes.indexOf(prizeItem);

            levelItem.prizes.splice(prizeItemIndex, 1);
        },
        updateLevels() {
            axios.post(`/achievement/update-levels/${this.achievement.id}`, {levels: this.levels})
                .then(res => {
                    this.levelsChanged = false;
                    alert(res.data.message)
                })
        },
        submit() {
            let data = new FormData();
            Object.keys(this.achievement).forEach((key, i) => {
                if (!['id', 'icon', 'created_at', 'updated_at', 'is_deleted', 'levels'].includes(key)) {
                    data.append(key, this.achievement[key]);
                }
            })

            if (this.$refs.icon.files[0]) {
                data.append('icon', this.$refs.icon.files[0]);
            }

            axios.post(`/achievement/update/${this.achievement.id}`,
                data,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(res => {
                    this.achievementCache = res.data.achievement;
                    this.changed = false;
                    alert(res.data.message)
                })
                .catch(err => alert(err.response.data.message))
        },
        deleteAchievement() {
            axios.post(`/achievement/delete/${this.achievement.id}`)
                .then(res => {
                    window.location = '/achievements/list';
                })
        },
        iconPreview() {
            if (!this.achievement.icon) return;
            this.iconPreviewUrl = URL.createObjectURL(this.achievement.icon[0]);
        },
        blobToFile(theBlob, fileName) {
            theBlob.lastModifiedDate = new Date();
            theBlob.name = fileName;
            return new File([theBlob], fileName, {lastModified: new Date().getTime(), type: 'image/png'})
        },
        editLevel(item) {
            this.editedIndex = this.levels.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialog = true
        },
        deleteLevel(item) {
            this.editedIndex = this.levels.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialogDelete = true
        },
        deleteLevelConfirm() {
            const level = this.editedIndex+1;
            this.levels.splice(this.editedIndex, 1)
            this.levels.map(l => {
                if(l.level > level) {
                    l.level--;
                }
            })
            this.closeDelete()
        },
        close() {
            this.dialog = false
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },
        closeDelete() {
            this.dialogDelete = false
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },
        saveLevel() {
            this.editedItem.value = parseInt(this.editedItem.value)

            if (this.editedIndex > -1) {
                Object.assign(this.levels[this.editedIndex], this.editedItem)
            } else {
                this.levels.push({
                    level: (this.levels.length ? Math.max(...this.levels.map(({ level }) => level)) : 0) + 1,
                    value: this.editedItem.value,
                    prizes: []
                })
            }
            this.close()
        },
        addPrize(level) {
            const l = this.levels.find(l => l.level === level)
            const prize = this.prizes.find(p => p.id === this.addPrizeId);

            l.prizes.push({
                id: null,
                prize_id: prize.id,
                prize: prize
            });

            this.addPrizeId = null;
            this.addPrizeDialog[level] = false;
        },
        prizesNotAssignedToLevel(level) {
            const existed = this.levels.find(l => l.level === level).prizes.map(p => p.prize_id)

            return this.prizes.filter(p => !existed.includes(p.id));
        }
    },
    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'Добавить уровень' : 'Изменить уровень'
        },
    },
    watch: {
        levels: {
            handler() {
                this.levelsChanged = true;
            },
            deep: true
        },
        'achievement.is_active'(newVal, oldVal) {
            this.changed = newVal !== this.achievementCache.is_active;
        },
        'achievement.title'(newVal, oldVal) {
            this.changed = newVal !== this.achievementCache.title;
        },
        'achievement.type'(newVal, oldVal) {
            this.changed = newVal !== this.achievementCache.type;
        },
        'achievement.description'(newVal, oldVal) {
            this.changed = newVal !== this.achievementCache.description;
        },
        icon() {
            this.achievement.icon = this.icon;
            this.changed = true;
        },
        dialog(val) {
            val || this.close()
        },
        dialogDelete(val) {
            val || this.closeDelete()
        },
    },
    beforeMount() {
        if (this.achievement.icon) {
            const iconBlob = new Blob();
            this.iconPreviewUrl = this.achievement.icon
            this.icon = [this.blobToFile(iconBlob, this.achievement.icon)];
        }

        this.levels.map(l => {
            this.addPrizeDialog[l.level] = false;

            l.prizes.map(p => {
                this.prizeConfirmDeleteDialogs[l.level + '-' + p.prize_id] = false;
                this.prizePreviewDialogs[l.level + '-' + p.prize_id] = false;
            })
        })
    },
    mounted() {
        this.changed = false;
        this.achievementCache = {...this.achievement};
    }
}
</script>
