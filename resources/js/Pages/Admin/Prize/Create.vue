<template>
    <AdminLayout>
        <template #title>Добавление приза</template>

        <form @submit.prevent="submit">
            <v-text-field
                v-model="prize.title"
                label="Название"
            ></v-text-field>

            <v-text-field
                v-model="prize.value"
                label="Значение"
            ></v-text-field>

            <v-select
                v-model="prize.type"
                :items="available_types"
                label="Тип приза"
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
                        v-model="prize.icon"
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
        prize: {
            title: '',
            value: '',
            type: 0,
            icon: null
        }
    }),
    methods: {
        submit() {
            let data = new FormData();
            Object.keys(this.prize).forEach((key, i) => {
                if(key !== 'icon') {
                    data.append(key, this.prize[key]);
                }
            })

            if(this.$refs.icon.files[0]) {
                data.append('icon', this.$refs.icon.files[0]);
            }

            axios.post('/prizes/create',
                data,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(res => {
                    window.location = '/prize/view/' + res.data.id
                })
                .catch(err => alert(err.response.data.message))
        },
        iconPreview() {
            if (!this.prize.icon) return;
            this.iconPreviewUrl = URL.createObjectURL(this.prize.icon[0]);
        },
    },
    computed: {
        filled() {
            return this.prize.title.length >= 4 && this.prize.value.length >= 1;
        }
    }
}
</script>
