<header>
    <div class="recipe-toolbar">
        <a href="#<?php echo $_['id']; ?>" class="svg action icon-close"></a>
    </div>
</header>

<form action="/index.php/apps/cookbook/update" method="POST">
    <fieldset>
        <label><?php /* TRANSLATORS The name of the recipe */echo p($l->t('Name')); ?></label>
        <input required type="text" name="name" value="<?php echo $_['name']; ?>"></h2>
    </fieldset>

    <fieldset>
        <label><?php /* TRANSLATORS The description of the recipe */echo p($l->t('Description')); ?></label>
        <input type="text" name="description" value="<?php echo $_['description']; ?>">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('URL')); ?></label>
        <input type="url" name="url" value="<?php echo $_['url']; ?>">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Image')); ?></label>
        <input type="text" name="image" value="<?php echo $_['image']; ?>"><button id="pick-image" title="<?php p($l->t('Pick a local image')) ?>"><span class="icon-category-multimedia"></span></button>
    </fieldset>
    
    <fieldset>
        <label><?php p($l->t('Prep Time')); ?></label>
        <input type="text" name="prepTime" value="<?php echo $_['prepTime']; ?>" placeholder="PT0H15M">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Cook Time')); ?></label>
        <input type="text" name="cookTime" value="<?php echo $_['cookTime']; ?>" placeholder="PT1H30M">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Keywords (comma-separated)')); ?></label>
        <input type="text" name="keywords" value="<?php echo $_['keywords']; ?>">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Servings')); ?></label>
        <input type="number" name="recipeYield" value="<?php echo $_['recipeYield']; ?>">
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
