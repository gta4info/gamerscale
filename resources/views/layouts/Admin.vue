<template>
    <v-app>
        <v-container style="position: relative">
            <v-app-bar :elevation="1" rounded absolute style="position: relative">

                <v-app-bar-title>Админка</v-app-bar-title>

                <template v-slot:append>
                    <v-btn icon="mdi-logout" :href="`${url}/logout`"></v-btn>
                </template>
            </v-app-bar>
            <v-main style="--v-layout-top: 30px">
                <v-row
                    class="mb-6"
                >
                    <v-col cols="3">
                        <Sidebar/>
                    </v-col>
                    <v-col cols="9">
                        <v-card>
                            <v-card-title><slot name="title"/></v-card-title>
                            <v-card-text>
                                <slot/>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-main>
        </v-container>
    </v-app>
</template>

<script>
import {defineComponent} from 'vue'
import Sidebar from "@/Pages/Admin/Parts/Sidebar.vue";

export default defineComponent({
    name: "Admin",
    components: {Sidebar},
    props: {
        title: {
            type: String
        }
    },
    data: () => ({
        url: import.meta.env.VITE_APP_URL,
    })
})
</script>

<style scoped>

</style>
