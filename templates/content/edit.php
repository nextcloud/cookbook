<form action="/index.php/apps/cookbook/update" method="POST">
    <fieldset>
        <label><?php /* TRANSLATORS The name of the recipe */echo p($l->t('Name')); ?></label>
        <input required type="text" name="name" value="<?php echo $_['name']; ?>"></h2>
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Image URL')); ?></label>
        <input type="text" name="image" value="<?php echo $_['image']; ?>">
    </fieldset>
    
    <fieldset>
        <label><?php p($l->t('Keywords (comma-separated)')); ?></label>
        <input type="text" name="keywords" value="<?php echo $_['keywords']; ?>">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Servings')); ?></label>
        <input type="number" name="recipeYield" value="<?php echo $_['recipeYield']; ?>">
    </fieldset>
    
    <?php 

    $daily_dozen = [
        'beansAndLegumes' => [ 'icon' => '🥛', 'name' => $l->t('Beans and legumes') ],
        'berries' => [ 'icon' => '🍓', 'name' => $l->t('Berries') ],
        'cruciferousVegetables' => [ 'icon' => '🥦', 'name' => $l->t('Cruciferous vegetables') ],
        'flaxseeds' => [ 'icon' => '🌱', 'name' => $l->t('Flaxseeds') ],
        'greens' => [ 'icon' => '🥬', 'name' => $l->t('Greens') ],
        'nutsAndSeeds' => [ 'icon' => '🌰', 'name' => $l->t('Nuts and seeds') ],
        'otherFruits' => [ 'icon' => '🍌', 'name' => $l->t('Other fruits') ],
        'otherVegetables' => [ 'icon' => '🥑', 'name' => $l->t('Other vegetables') ],
        'herbsAndSpices' => [ 'icon' => '🌿', 'name' => $l->t('Herbs and spices') ],
        'wholeGrains' => [ 'icon' => '🍞', 'name' => $l->t('Whole grains') ],
    ];

    ?>

    <fieldset>
        <label><?php p($l->t('Daily dozen')); ?></label>

        <ul>
            <?php foreach($daily_dozen as $id => $ingredient) { ?>
                <?php $has_ingredient = strpos($_['dailyDozen'], $id) !== false; ?>

                <li>    
                    <label title="<?php echo $ingredient['name']; ?>">
                        <?php echo $ingredient['name'] . ' ' . $ingredient['icon']; ?>
                        <input type="checkbox" name="dailyDozen[<?php echo $id; ?>]" <?php if($has_ingredient) { echo 'checked'; } ?>>
                    </label>
                </li>
            <?php } ?>
        </ul>
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Ingredients')); ?></label>

        <ul>
            <template>
                <li>
                    <input type="text" name="recipeIngredient[]" value="">
                    <button class="icon-delete"></button>
                </li>   
            </template>
            <?php foreach($_['recipeIngredient'] as $i => $ingredient) {  ?>
                <li>
                    <input type="text" name="recipeIngredient[]" value="<?php echo $ingredient; ?>">
                    <button class="icon-delete"></button>
                </li>   
            <?php } ?>
            <button class="icon-add"></button>
        </ul>    
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Instructions')); ?></label>

        <ul>
            <template>
                <li>
                    <textarea name="recipeInstructions[]"></textarea>
                    <button class="icon-delete"></button>
                </li>   
            </template>
            <?php foreach($_['recipeInstructions'] as $i => $step) {  ?>
                <li>
                    <textarea name="recipeInstructions[]"><?php echo $step; ?></textarea>
                    <button class="icon-delete"></button>
                </li>   
            <?php } ?>
            <button class="icon-add"></button>
        </ul>
    </fieldset>

    <button type="submit"><?php p($l->t('Save')); ?></button>
</form>
