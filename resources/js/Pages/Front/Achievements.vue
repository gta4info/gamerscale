<template>
    <FrontLayout>
            <v-row>
                <v-col cols="12" md="3" v-for="achievement in achievements">
                    <div class="achievement">
                        <div class="achievement__icon">
                            <v-img :src="achievement.achievement.icon" max-width="90px" max-height="90px"></v-img>
                        </div>
                        <div class="achievement__title">
                            {{achievement.achievement.title}}
                            <span v-if="achievement.level > 0">{{achievement.level}}</span>
                        </div>
                        <div class="achievement__description">{{achievement.achievement.description}}</div>
                        <div class="achievement__progress">
                            <div class="achievement__progress-bar">
                                <div class="achievement__progress-bar--fill" :style="{width: calculatedProgress(achievement).percentOfComplete + '%'}"></div>
                            </div>
                            <div class="achievement__progress-bottom">
                                <span>Прогресс</span>
                                <span>{{achievement.progress}}/{{calculatedProgress(achievement).nextLevelValue}}</span>
                            </div>
                        </div>
                    </div>
                </v-col>
            </v-row>
    </FrontLayout>
</template>

<script>
import FrontLayout from "../../../views/layouts/Front.vue";
export default {
    components: {FrontLayout},
    props: {
        achievements: Array
    },
    methods: {
        calculatedProgress(item) {
            const levels = item.achievement.levels;
            const curLevel = levels.find(l => {
                return item.progress <= l
            })
            const curLevelIndex = levels.indexOf(curLevel);
            const percent = item.progress / levels[curLevelIndex] * 100;

            return {
                nextLevelValue: levels[curLevelIndex],
                percentOfComplete: percent.toFixed()
            }
        }
    }
}
</script>

<style lang="scss" scoped>
    .achievement {
        background: #0F1627;
        border-radius: 16px;
        border: 1px solid rgba(#fff, .05);
        padding: 12px 16px 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;

        &__icon {
            width: 90px;
            height: 90px;
            margin-bottom: 24px;
            margin-top: 24px;
        }

        &__title {
            color: #F6F6F6;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        &__description {
            font-size: 14px;
            color: #68889c;
            text-align: center;
            margin-bottom: 40px;
        }

        &__progress {
            display: flex;
            flex-direction: column;
            width: 100%;
            margin-top: auto;

            &-bar {
                height: 3px;
                border-radius: 16px;
                width: 100%;
                background: #08D9D64D;
                margin-bottom: 4px;
                position: relative;

                &--fill {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    background-color: #08D9D6;
                    box-shadow: 0 0 10px 0 rgba(8, 217, 214, 0.50);
                    border-radius: 16px;
                }
            }

            &-bottom {
                display: flex;
                justify-content: space-between;

                span {
                    font-size: 12px;
                    font-weight: 600;
                }

                span:first-of-type {
                    color: #36566a;
                }
            }
        }
    }
</style>
