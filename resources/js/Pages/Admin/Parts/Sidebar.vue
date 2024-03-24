<template>
    <v-card
        class="mx-auto"
    >
        <v-card-title>Меню</v-card-title>
            <v-list v-model:opened="open">
                <v-list-item
                    prepend-icon="mdi-home"
                    @click="$refs.home.$el.click()"
                >
                    <template v-slot:title>
                        <inertia-link href="/" ref="home">Главная</inertia-link>
                    </template>
                </v-list-item>

                <v-list-group value="Users">
                    <template v-slot:activator="{ props }">
                        <v-list-item
                            v-bind="props"
                            prepend-icon="mdi-account-circle"
                            title="Пользователи"
                        ></v-list-item>
                    </template>

                    <v-list-item
                        v-for="([title, icon, link], i) in users"
                        :key="i"
                        :prepend-icon="icon"
                        :value="title"
                        @click="$refs[link][0].$el.click()"
                    >
                        <template v-slot:title>
                            <inertia-link :href="link" :ref="link">{{ title }}</inertia-link>
                        </template>
                    </v-list-item>
                </v-list-group>

                <v-list-group value="Prizes">
                    <template v-slot:activator="{ props }">
                        <v-list-item
                            v-bind="props"
                            prepend-icon="mdi-account-circle"
                            title="Призы"
                        ></v-list-item>
                    </template>

                    <v-list-item
                        v-for="([title, icon, link], i) in prizes"
                        :key="i"
                        :prepend-icon="icon"
                        :value="title"
                        @click="$refs[link][0].$el.click()"
                    >
                        <template v-slot:title>
                            <inertia-link :href="link" :ref="link">{{ title }}</inertia-link>
                        </template>
                    </v-list-item>
                </v-list-group>

                <v-list-group value="Achievements">
                    <template v-slot:activator="{ props }">
                        <v-list-item
                            v-bind="props"
                            prepend-icon="mdi-account-circle"
                            title="Ачивки"
                        ></v-list-item>
                    </template>

                    <v-list-item
                        v-for="([title, icon, link], i) in achievements"
                        :key="i"
                        :prepend-icon="icon"
                        :value="title"
                        @click="$refs[link][0].$el.click()"
                    >
                        <template v-slot:title>
                            <inertia-link :href="link" :ref="link">{{ title }}</inertia-link>
                        </template>
                    </v-list-item>
                </v-list-group>

                <v-list-group value="Leaderboards">
                    <template v-slot:activator="{ props }">
                        <v-list-item
                            v-bind="props"
                            prepend-icon="mdi-account-circle"
                            title="Лидерборды"
                        ></v-list-item>
                    </template>

                    <v-list-item
                        v-for="([title, icon, link], i) in leaderboards"
                        :key="i"
                        :prepend-icon="icon"
                        :value="title"
                        @click="$refs[link][0].$el.click()"
                    >
                        <template v-slot:title>
                            <inertia-link :href="link" :ref="link">{{ title }}</inertia-link>
                        </template>
                    </v-list-item>
                </v-list-group>
            </v-list>
    </v-card>
</template>

<script>
import {defineComponent} from 'vue'

export default defineComponent({
    name: "Sidebar",
    data: () => ({
        open: [],
        users: [
            ['Список всех', 'mdi-account-multiple-outline', '/users/list'],
        ],
        prizes: [
            ['Список всех', 'mdi-account-multiple-outline', '/prizes/list'],
            ['Добавить', 'mdi-plus', '/prizes/create'],
        ],
        achievements: [
            ['Список всех', 'mdi-account-multiple-outline', '/achievements/list'],
            ['Добавить', 'mdi-plus', '/achievements/create'],
        ],
        leaderboards: [
            ['Список всех', 'mdi-account-multiple-outline', '/leaderboards/list'],
            ['Добавить', 'mdi-plus', '/leaderboards/create'],
        ]
    })
})
</script>

<style lang="scss" scoped>
    .v-list-item-title {
        a {
            color: inherit;
            text-decoration: none;
        }
    }
</style>
