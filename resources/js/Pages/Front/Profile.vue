<template>
    <FrontLayout>
        <h3>Выигранные призы</h3>
        <div class="prizes">
            <v-row>
                <v-col cols="12" md="3">
                    <div class="prize gspoints">
                        <div class="prize__head">
                            <div class="prize__head-badge">Учитывается за все время</div>
                        </div>
                        <div class="prize__title">GSPOINTS</div>
                        <div class="prize__text">
                            <div class="d-flex flex-column">
                                <span>Получено: <strong>{{ prizes.gspoints.amountReceived }} GS поинтов</strong></span>
                                <span>В ожидании: <strong>{{ prizes.gspoints.amountAwaiting }} GS поинтов</strong></span>
                            </div>
                        </div>
                    </div>
                </v-col>

                <v-col cols="12" md="3" v-for="prize in prizes.others">
                    <div class="prize">
                        <div class="prize__head">
                            <div class="prize__head-badge" :class="prize.status.class">{{prize.status.text}}</div>
                            <v-img :src="prize.prize.icon" class="prize__head-icon"></v-img>
                        </div>
                        <div class="prize__title">{{prize.prize.title}}</div>
                        <div class="prize__text" v-html="prize.cardText"></div>
                    </div>
                </v-col>
            </v-row>
        </div>
    </FrontLayout>
</template>

<script>
import FrontLayout from "../../../views/layouts/Front.vue";
export default {
    components: {FrontLayout},
    props: {
        user: Object,
        prizes: Array
    },
    mounted() {
        this.emitter.emit('share-user-object-to-layout', this.user)
    }
}
</script>

<style lang="scss" scoped>
    .prizes {
        margin-top: 24px;
    }

    .prize {
        display: flex;
        flex-direction: column;
        border-radius: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
        background: #0F1627;
        position: relative;
        overflow: hidden;
        height: 100%;

        &:before {
            content: '';
            position: absolute;
            top: 50%;
            height: 50%;
            left: 0;
            right: 0;
            background: url(/img/prize-color-manual-bottom.png) bottom right no-repeat;
        }

        &.gspoints {

            .prize__head {
                background: url(/img/prize-color-gspoints-top.png) top left no-repeat, url(/img/prize-bg.png) no-repeat;
                background-size: contain, cover;
            }

            &:before {
                background: url(/img/prize-color-gspoints-bottom.png) bottom right no-repeat;
            }
        }

        &__head {
            padding: 8px;
            height: 137px;
            background: url(/img/prize-color-manual-top.png) top left no-repeat, url(/img/prize-bg.png) no-repeat;
            background-size: contain, cover;
            position: relative;
            z-index: 1;
            display: flex;

            &-badge {
                padding: 4px 10px;
                background: rgba(#2C355D, .6);
                border: 2px solid rgba(255, 255, 255, 0.05);
                align-self: flex-start;
                font-size: 14px;
                font-weight: 700;
                border-radius: 8px;
                position: absolute;
                top: 8px;
                left: 8px;
                z-index: 1;

                &.completed {
                    background: rgba(green, .6);
                }

                &.pending {
                    background: rgba(grey, .6);
                }

                &.in-progress {
                    background: rgba(orange, .6);
                }
            }
        }

        &__title {
            background: #131E33;
            color: #ffffff;
            font-weight: 600;
            font-size: 22px;
            text-align: center;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        &__text {
            padding: 24px 16px 40px;
            font-size: 14px;
            color: #f6f6f6;
            text-align: center;
            position: relative;
            z-index: 1;
        }
    }
</style>
