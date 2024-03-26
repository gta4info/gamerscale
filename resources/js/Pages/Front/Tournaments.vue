<template>
    <FrontLayout>
        Турниры
    </FrontLayout>
</template>

<script>
import FrontLayout from "../../../views/layouts/Front.vue";
export default {
    components: {FrontLayout},
    props: {
        tournaments: Array
    },
}
</script>

<style lang="scss" scoped>

</style>
