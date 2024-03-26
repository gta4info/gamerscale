<template>
    <AdminLayout>
        <template #title>Страница выдачи приза</template>

        <v-list style="font-size: 16px;">
            <v-list-item>
                <div class="d-flex gap-3">
                    <span>Пользователь:</span>
                    <a :href="`/user/view/${prize.user_id}`" class="text-white" target="_blank">{{prize.user.name}}</a>
                </div>
            </v-list-item>
            <v-list-item>
                <div class="d-flex gap-3">
                    <span>Название приза:</span>
                    <a :href="`/prize/view/${prize.prize_id}`" class="text-white" target="_blank">{{prize.prize.title}}</a>
                </div>
            </v-list-item>
            <v-list-item>
                <div class="d-flex gap-3">
                    <span>За что выдан:</span>
                    <span v-html="prize.cardText"></span>
                </div>
            </v-list-item>
            <v-list-item>
                <div class="d-flex align-items-center gap-3">
                    <span>Статус выдачи:</span>
                    <div style="width: 300px;">
                        <v-select
                            v-model="selectedStatus"
                            :items="statuses"
                            hide-details
                        ></v-select>
                    </div>
                </div>
            </v-list-item>
            <v-list-item class="mt-6">
                <v-textarea v-model="prize.data.comment" no-resize label="Комментарий/заметка к призу"></v-textarea>
            </v-list-item>
            <v-btn color="red" @click="updatePrize" v-show="changed">Сохранить изменения</v-btn>
        </v-list>
    </AdminLayout>
</template>

<script>
import AdminLayout from "../../../../views/layouts/Admin.vue";

export default {
    components: {AdminLayout},
    props: {
        prize: Object,
        statuses: Array
    },
    data: () => ({
        selectedStatus: null,
        changed: false,
        comment: ''
    }),
    watch: {
        selectedStatus() {
            this.changed = this.selectedStatus !== this.prize.status.value;
        },
        'prize.data.comment'() {
            this.changed = true;
        }
    },
    methods: {
        updatePrize() {
            axios.post(`/prizes/history/update/${this.prize.id}`, {
                    status: this.selectedStatus,
                    comment: this.prize.data.comment
                })
                .then(res => {
                    this.changed = false;
                    alert(res.data.message)
                })
                .catch(err => alert(err.response.data.message));
        }
    },
    mounted() {
        this.selectedStatus = this.prize.status.value;
        this.changed = false;
    }
}
</script>
