<template>
    <ul class="nutrition-items">
        <RecipeNutritionInfoItem
            v-if="
                'servingSize' in nutrition &&
                !isNullOrEmpty(nutrition['servingSize'])
            "
            :title="t('cookbook', 'Serving Size')"
            :data="nutrition['servingSize']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'calories' in nutrition && !isNullOrEmpty(nutrition['calories'])
            "
            :title="t('cookbook', 'Energy')"
            :data="nutrition['calories']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'sugarContent' in nutrition &&
                !isNullOrEmpty(nutrition['sugarContent'])
            "
            :title="t('cookbook', 'Sugar')"
            :data="nutrition['sugarContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'carbohydrateContent' in nutrition &&
                !isNullOrEmpty(nutrition['carbohydrateContent'])
            "
            :title="t('cookbook', 'Carbohydrate')"
            :data="nutrition['carbohydrateContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'cholesterolContent' in nutrition &&
                !isNullOrEmpty(nutrition['cholesterolContent'])
            "
            :title="t('cookbook', 'Cholesterol')"
            :data="nutrition['cholesterolContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'fiberContent' in nutrition &&
                !isNullOrEmpty(nutrition['fiberContent'])
            "
            :title="t('cookbook', 'Fiber')"
            :data="nutrition['fiberContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'proteinContent' in nutrition &&
                !isNullOrEmpty(nutrition['proteinContent'])
            "
            :title="t('cookbook', 'Protein')"
            :data="nutrition['proteinContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'sodiumContent' in nutrition &&
                !isNullOrEmpty(nutrition['sodiumContent'])
            "
            :title="t('cookbook', 'Sodium')"
            :data="nutrition['sodiumContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'fatContent' in nutrition &&
                !isNullOrEmpty(nutrition['fatContent'])
            "
            :title="t('cookbook', 'Fat total')"
            :data="nutrition['fatContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'saturatedFatContent' in nutrition &&
                !isNullOrEmpty(nutrition['saturatedFatContent'])
            "
            :title="t('cookbook', 'Saturated Fat')"
            :data="nutrition['saturatedFatContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'unsaturatedFatContent' in nutrition &&
                !isNullOrEmpty(nutrition['unsaturatedFatContent'])
            "
            :title="t('cookbook', 'Unsaturated Fat')"
            :data="nutrition['unsaturatedFatContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'transFatContent' in nutrition &&
                !isNullOrEmpty(nutrition['transFatContent'])
            "
            :title="t('cookbook', 'Trans Fat')"
            :data="nutrition['transFatContent']"
        />
    </ul>
</template>

<script setup>
import RecipeNutritionInfoItem from 'cookbook/components/RecipeView/NutritionInformation/RecipeNutritionInfoItem.vue';

defineProps({
    nutrition: {
        type: Object,
        default: () => {},
    },
});

const isNullOrEmpty = (str) =>
    !str || (typeof str === 'string' && str.trim().length === 0);
</script>

<style scoped lang="scss">
.nutrition-items {
    list-style-type: none;
}
</style>
