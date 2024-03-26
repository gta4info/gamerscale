<template>
    <v-app>
        <v-container class="container">
            <header class="header">
                <a href="/profile" class="header__logo">
                    <img src="/img/logo.svg" alt="GamerScale">
                </a>
<!--                <ul class="header__nav">-->
<!--                    <li class="header__nav-item">-->
<!--                        <a href="#">Подписка</a>-->
<!--                    </li>-->
<!--                </ul>-->
                <div class="header__actions">
                    <div class="d-flex align-items-center">
                        <span style="margin-right: 1rem;">{{ $page.props.auth.user.name }}</span>
                        <v-btn icon="mdi-logout" href="/logout"></v-btn>
                    </div>
                </div>
            </header>
            <main class="main">
                <div class="profile">
                    <a
                        class="epicGamesIntegration"
                        href="/integrate-epic-games"
                        v-if="!$page.props.auth.user.epic_id"
                    >
                        Привязать Epic Games
                        <v-img src="/img/epic-games.svg" width="20" height="20"/>
                    </a>
                    <div class="profile__info">
                        <div class="profile__info-avatar">
                            <v-img :src="profileUser.avatar_url" width="98px" height="98px"></v-img>
                        </div>
                        <div class="profile__info-name">{{profileUser.name}}</div>
                        <div class="profile__info-joined text-grey">Дата регистрации {{$moment(profileUser.created_at).format('MM.DD.YYYY')}}</div>
                    </div>
                    <div class="profile__tabs">
                        <inertia-link
                            :href="item.url"
                            class="profile__tabs-link"
                            :class="{active: $page.url.match(item.url)}"
                            v-for="item in tabs.filter(i => {
                                if(i.is_personal) {
                                    return profileUser.id === $page.props.auth.user.id
                                }
                                return true;
                            })"
                        >
                            {{item.title}}
                        </inertia-link>
                    </div>
                </div>
                <slot/>
            </main>
        </v-container>
    </v-app>
</template>

<script>

export default {
    name: "Admin",
    props: {
        title: {
            type: String
        }
    },
    data: () => ({
        tabs: [
            {url: '/profile', title: 'Профиль', is_personal: false},
            {url: '/achievements', title: 'Ачивки', is_personal: true},
            {url: '/leaderboard', title: 'Лидерборд', is_personal: true},
            {url: '/quests', title: 'Квесты', is_personal: true},
            {url: '/tournaments', title: 'Турниры', is_personal: true},
        ],
        profileUser: {
            id: null,
            avatar_url: '',
            name: '',
            created_at: '',
        }
    }),
    created() {
        this.profileUser.id = this.$page.props.auth.user.id;
        this.profileUser.avatar_url = this.$page.props.auth.user.avatar_url;
        this.profileUser.name = this.$page.props.auth.user.name;
        this.profileUser.created_at = this.$page.props.auth.user.created_at;
    },
    mounted() {
        this.emitter.on('share-user-object-to-layout', (data) => {
            this.profileUser.id = data.id;
            this.profileUser.avatar_url = data.avatar_url;
            this.profileUser.name = data.name;
            this.profileUser.created_at = data.created_at;
        })
    }
}
</script>

<style lang="scss" scoped>

.header {
    padding: 1rem 0;
    display: flex;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, .1)
}

.header__nav {
    margin: 0 2.4rem
}

.header__nav-item {
    color: #68889c;
    font-size: .875rem;
    font-weight: 600
}

.header__nav-item:hover {
    color: #fff
}

.header__actions {
    margin-left: auto
}

.profile {
    margin-top: 60px;
    display: flex;
    flex-direction: column;

    &__info {
        display: flex;
        flex-direction: column;
        align-items: center;

        &-avatar {
            margin-bottom: 8px;
            border: 2px solid #DE8F33;
            padding: 4px;
            border-radius: 10px;
            position: relative;

            &:before {
                content: '';
                position: absolute;
                margin: 0 auto;
                width: 20px;
                height: 21px;
                top: -23px;
                left: 0;
                right: 0;
                background: url(img/crown.svg);
            }

            .v-img {
                border-radius: 8px;
            }
        }

        &-name {
            font-weight: 700;
            font-size: 28px;
        }
    }

    &__tabs {
        margin-top: 56px;
        border-bottom: 1px solid #222837;
        display: flex;
        width: 100%;
        margin-bottom: 40px;

        &-link {
            width: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 16px;
            color: #68889c;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            border-bottom: 3px solid transparent;
            transition: .3s;
            margin-bottom: -1px;

            &:hover, &.active {
                color: #08D9D6;
            }

            &.active {
                border-bottom-color: #08D9D6;
            }
        }
    }
}

.epicGamesIntegration {
    display: inline-flex;
    padding: 0.6875rem 1.875rem;
    justify-content: center;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
    border-radius: 0.5rem;
    border: 1px solid #36566A;
    color: #F6F6F6;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 700;
    line-height: 130%;
    text-decoration: none;
    transition: .3s;
    align-self: flex-end;

    &:hover {
        border-radius: 0.5rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
        background: #00A19E;
        box-shadow: 0 4px 8px 0 rgba(8, 217, 214, 0.08), 0 13px 25px 0 rgba(8, 217, 214, 0.25);
    }
}

@media (max-width: 600px) {
    body {
        background: url(/img/bg-sm.png) #010818;
        background-size: contain, cover
    }

    .main {
        padding-top: 2rem;
        align-items: center
    }

    .header__nav {
        margin: 0 1.8rem
    }

    .header__nav-item {
        font-size: .825rem
    }
}
</style>
