<template>
    <AdminLayout>
        <template #title>Добавление лидерборда</template>

        <form @submit.prevent="submit">
            <v-btn
                class="me-4"
                type="submit"
                color="green"
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
    data: () => ({
        leaderboard: {}
    }),
    methods: {
        submit() {
            axios.post('/leaderboards/create', {
                    leaderboard: this.leaderboard
                })
                .then(res => {
                    window.location = '/leaderboard/view/' + res.data.id
                })
                .catch(err => alert(err.response.data.message))
        },
    },
}
</script>
