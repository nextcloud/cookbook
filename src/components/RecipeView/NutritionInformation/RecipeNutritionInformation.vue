<template>
    <ul class="nutrition-items">
        <RecipeNutritionInfoItem
            v-if="
                'servingSize' in nutrition &&
                !isNullOrEmpty(nutrition['servingSize'])
            "
            :title="
                // TRANSLATORS Nutrition value
                t('cookbook', 'Serving Size')
            "
            :data="nutrition['servingSize']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'calories' in nutrition && !isNullOrEmpty(nutrition['calories'])
            "
            :title="
                // TRANSLATORS Nutrition value
                t('cookbook', 'Energy')
            "
            :data="nutrition['calories']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'sugarContent' in nutrition &&
                !isNullOrEmpty(nutrition['sugarContent'])
            "
            :title="
                // TRANSLATORS Nutrition value
                t('cookbook', 'Sugar')
            "
            :data="nutrition['sugarContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'carbohydrateContent' in nutrition &&
                !isNullOrEmpty(nutrition['carbohydrateContent'])
            "
            :title="
                // TRANSLATORS Nutrition value
                t('cookbook', 'Carbohydrate')
            "
            :data="nutrition['carbohydrateContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'cholesterolContent' in nutrition &&
                !isNullOrEmpty(nutrition['cholesterolContent'])
            "
            :title="
                // TRANSLATORS Nutrition value
                t('cookbook', 'Cholesterol')
            "
            :data="nutrition['cholesterolContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'fiberContent' in nutrition &&
                !isNullOrEmpty(nutrition['fiberContent'])
            "
            :title="
                // TRANSLATORS Nutrition
                t('cookbook', 'Fiber')
            "
            :data="nutrition['fiberContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'proteinContent' in nutrition &&
                !isNullOrEmpty(nutrition['proteinContent'])
            "
            :title="
                // TRANSLATORS Nutrition value
                t('cookbook', 'Protein')
            "
            :data="nutrition['proteinContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'sodiumContent' in nutrition &&
                !isNullOrEmpty(nutrition['sodiumContent'])
            "
            :title="
                // TRANSLATORS Nutrition value
                t('cookbook', 'Sodium')
            "
            :data="nutrition['sodiumContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'fatContent' in nutrition &&
                !isNullOrEmpty(nutrition['fatContent'])
            "
            :title="
                // TRANSLATORS Nutrition value
                t('cookbook', 'Fat total')
            "
            :data="nutrition['fatContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'saturatedFatContent' in nutrition &&
                !isNullOrEmpty(nutrition['saturatedFatContent'])
            "
            :title="
                // TRANSLATORS Nutrition value
                t('cookbook', 'Saturated Fat')
            "
            :data="nutrition['saturatedFatContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'unsaturatedFatContent' in nutrition &&
                !isNullOrEmpty(nutrition['unsaturatedFatContent'])
            "
            :title="
                // TRANSLATORS Nutrition value
                t('cookbook', 'Unsaturated Fat')
            "
            :data="nutrition['unsaturatedFatContent']"
        />
        <RecipeNutritionInfoItem
            v-if="
                'transFatContent' in nutrition &&
                !isNullOrEmpty(nutrition['transFatContent'])
            "
            :title="
                // TRANSLATORS Nutrition value
                t('cookbook', 'Trans Fat')
            "
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
    padding-top: 5px;
    list-style-type: none;
}
</style>
