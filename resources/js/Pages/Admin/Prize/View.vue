<template>
    <AdminLayout>
        <template #title>Приз: "{{prize.title}}"</template>

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
                @click="deletePrize"
                color="red"
            >
                Удалить
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
        prize: Object,
        available_types: Array
    },
    data: () => ({
        changed: false,
        iconPreviewUrl: null,
        icon: null,
        prizeCache: {}
    }),
    methods: {
        submit() {
            let data = new FormData();
            Object.keys(this.prize).forEach((key, i) => {
                if(!['id', 'icon', 'created_at', 'updated_at', 'is_deleted'].includes(key)) {
                    data.append(key, this.prize[key]);
                }
            })

            if(this.$refs.icon.files[0]) {
                data.append('icon', this.$refs.icon.files[0]);
            }

            axios.post(`/prize/update/${this.prize.id}`,
                data,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(res => {
                    this.prizeCache = res.data.prize;
                    this.changed = false;
                    alert(res.data.message)
                })
                .catch(err => alert(err.response.data.message))
        },
        deletePrize() {
            axios.post(`/prize/delete/${this.prize.id}`)
                .then(res => {
                    window.location = '/prizes/list';
                })
        },
        iconPreview() {
            if (!this.prize.icon) return;
            this.iconPreviewUrl = URL.createObjectURL(this.prize.icon[0]);
        },
        blobToFile(theBlob, fileName){
            theBlob.lastModifiedDate = new Date();
            theBlob.name = fileName;
            return new File([theBlob], fileName, { lastModified: new Date().getTime(), type: 'image/png' })
        }
    },
    watch: {
        'prize.title'(newVal, oldVal) {
            this.changed = newVal !== this.prizeCache.title;
        },
        'prize.type'(newVal, oldVal) {
            this.changed = newVal !== this.prizeCache.type;
        },
        'prize.value'(newVal, oldVal) {
            this.changed = newVal !== this.prizeCache.value;
        },
        icon() {
            this.prize.icon = this.icon;
            this.changed = true;
        }
    },
    beforeMount() {
        if(this.prize.icon) {
            const iconBlob = new Blob();
            this.iconPreviewUrl = this.prize.icon
            this.icon = [this.blobToFile(iconBlob, this.prize.icon)];
        }
    },
    mounted() {
        this.changed = false;
        this.prizeCache = {...this.prize};
    }
}
</script>
