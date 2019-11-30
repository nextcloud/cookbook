<header>
    <div class="recipe-toolbar">
        <a href="#<?php echo $_['id']; ?>" class="svg action icon-close"></a>
    </div>
</header>

<form id="editRecipeForm" action="#" method="POST">
    <fieldset>
        <label><?php /* TRANSLATORS The name of the recipe */
            echo p($l->t('Name')); ?></label>
        <input required type="text" name="name" value="<?php if(isset($_['name'])) { echo $_['name']; } ?>"></h2>
    </fieldset>

    <fieldset>
        <label><?php /* TRANSLATORS The description of the recipe */
            echo p($l->t('Description')); ?></label>
        <input type="text" name="description" value="<?php if(isset($_['description'])) { echo $_['description']; } ?>">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('URL')); ?></label>
        <input type="url" name="url" value="<?php if(isset($_['url'])) { echo $_['url']; } ?>">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Image')); ?></label>
        <input type="text" name="image" value="<?php if(isset($_['image'])) { echo $_['image']; } ?>"><button id="pick-image" title="<?php p($l->t('Pick a local image')) ?>"><span class="icon-category-multimedia"></span></button>
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Prep Time')); ?></label>
        <input type="text" name="prepTime" value="<?php if(isset($_['prepTime'])) {echo $_['prepTime']; } ?>" placeholder="PT0H15M">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Cook Time')); ?></label>
        <input type="text" name="cookTime" value="<?php if(isset($_['cookTime'])) { echo $_['cookTime']; } ?>" placeholder="PT1H30M">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Total Time')); ?></label>
        <input type="text" name="totalTime" value="<?php if(isset($_['totalTime'])) { echo $_['totalTime']; } ?>" placeholder="PT1H30M">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Keywords (comma-separated)')); ?></label>
        <input type="text" name="keywords" value="<?php if(isset($_['keywords'])) { echo $_['keywords']; } ?>">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Servings')); ?></label>
        <input type="number" name="recipeYield" value="<?php if(isset($_['recipeYield'])) { echo $_['recipeYield']; } ?>">
    </fieldset>

    <fieldset>
        <label><?php p($l->t('Tools')); ?></label>
        <ul>
            <template>
                <li>
                    <input type="text" name="tool[]" value="">
                    <button class="icon-delete"></button>
                </li>
            </template>
            <?php if(isset($_['tool']) && is_array($_['tool'])) { ?>
                <?php foreach ($_['tool'] as $i => $tool) { ?>
                    <li>
                        <input type="text" name="tool[]" value="<?php echo $tool; ?>">
                        <button class="icon-delete"></button>
                    </li>
                <?php } ?>
            <?php } ?>
            <button class="icon-add"></button>
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
            <?php if(isset($_['recipeIngredient']) && is_array($_['recipeIngredient'])) { ?>
                <?php foreach ($_['recipeIngredient'] as $i => $ingredient) { ?>
                    <li>
                        <input type="text" name="recipeIngredient[]" value="<?php echo $ingredient; ?>">
                        <button class="icon-delete"></button>
                    </li>
                <?php } ?>
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
            <?php if(isset($_['recipeInstructions']) && is_array($_['recipeInstructions'])) { ?>
                <?php foreach ($_['recipeInstructions'] as $i => $step) { ?>
                    <li>
                        <textarea name="recipeInstructions[]"><?php echo $step; ?></textarea>
                        <button class="icon-delete"></button>
                    </li>
                <?php } ?>
            <?php } ?>
            <button class="icon-add"></button>
        </ul>
    </fieldset>

    <button type="submit"><?php p($l->t('Save')); ?></button>
</form>
