<template>
    <AdminLayout>
        <template #title>Добавление ачивки</template>

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
                        v-model="achievement.icon"
                        @change="iconPreview"
                        @click:clear="iconPreviewUrl = null"
                        ref="icon"
                    ></v-file-input>
                </v-col>
            </v-row>
            <v-btn
                class="me-4"
                type="submit"
                color="green"
                v-show="filled"
            >
                Добавить
            </v-btn>
        </form>
    </AdminLayout>
</template>

<script>
import AdminLayout from "../../../../views/layouts/Admin.vue";

export default {
    name: "View",
    components: {AdminLayout},
    props: {
        available_types: Array
    },
    data: () => ({
        iconPreviewUrl: null,
        achievement: {
            title: '',
            description: '',
            type: 0,
            icon: null
        }
    }),
    methods: {
        submit() {
            let data = new FormData();
            Object.keys(this.achievement).forEach((key, i) => {
                if(key !== 'icon') {
                    data.append(key, this.achievement[key]);
                }
            })

            if(this.$refs.icon.files[0]) {
                data.append('icon', this.$refs.icon.files[0]);
            }

            axios.post('/achievements/create',
                data,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(res => {
                    window.location = '/achievement/view/' + res.data.id
                })
                .catch(err => alert(err.response.data.message))
        },
        iconPreview() {
            if (!this.achievement.icon) return;
            this.iconPreviewUrl = URL.createObjectURL(this.achievement.icon[0]);
        },
    },
    computed: {
        filled() {
            return this.achievement.title.length >= 4 && this.achievement.description.length >= 4;
        }
    }
}
</script>
