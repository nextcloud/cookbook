<form id="editRecipeForm" action="#" method="<?php echo $_['id'] ? 'PUT' : 'POST' ?>">
    <div id="controls">
        <div class="breadcrumb">
            <div class="crumb svg crumbhome ui-droppable">
                <a href="#" class="icon-home"><?php p($l->t('Home')); ?></a>
            </div>
            <div class="crumb svg">
                <a href="#recipes/<?php echo $_['id']; ?>"><?php echo $_['id'] ? $_['name'] : p($l->t('New recipe')); ?></a>
            </div>
			<?php if($_['id']) { ?>
				<div class="crumb svg">
					<a href="javascript:;"><?php echo p($l->t('Edit')); ?></a>
				</div>
			<?php } ?>
        </div>
        <div class="actions">
            <button type="submit">
                <span class="icon icon-checkmark"></span>
                <span class="hidden-visually"><?php p($l->t('Save changes')); ?></span>
            </button>
        </div>

        <div class="actions pull-right">
            <a id="edit-recipe" href="#recipes/<?php echo $_['id']; ?>" class="button svg action" title="<?php p($l->t('Cancel')); ?>">
                <span class="icon icon-close"></span>
                <span class="hidden-visually"><?php p($l->t('Cancel')); ?></span>
            </a>
        </div>
    </div>

    <div class="recipe-edit">
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
            <input type="text" name="image" value="<?php if(isset($_['image'])) { echo $_['image']; } ?>"><button type="button" id="pick-image" title="<?php p($l->t('Pick a local image')) ?>"><span class="icon-category-multimedia"></span></button>
        </fieldset>

        <fieldset>
            <label><?php p($l->t('Preparation time')); ?></label>
            <input type="text" name="prepTime" value="<?php if(isset($_['prepTime'])) {echo $_['prepTime']; } ?>" placeholder="PT0H15M">
        </fieldset>

        <fieldset>
            <label><?php p($l->t('Cooking time')); ?></label>
            <input type="text" name="cookTime" value="<?php if(isset($_['cookTime'])) { echo $_['cookTime']; } ?>" placeholder="PT1H30M">
        </fieldset>

        <fieldset>
            <label><?php p($l->t('Total time')); ?></label>
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
            <button class="icon-add right"></button>
            <label><?php p($l->t('Tools')); ?></label>
            <ul>
                <template>
                    <li>
                        <input type="text" name="tool[]" value="">
                        <button class="icon-delete right"></button>
                    </li>
                </template>
                <?php if(isset($_['tool']) && is_array($_['tool'])) { ?>
                    <?php foreach ($_['tool'] as $i => $tool) { ?>
                        <li>
                            <input type="text" name="tool[]" value="<?php echo $tool; ?>">
                            <button class="icon-delete right"></button>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </fieldset>

        <fieldset>
            <button class="icon-add right"></button>
            <label><?php p($l->t('Ingredients')); ?></label>
            <ul>
                <template>
                    <li>
                        <input type="text" name="recipeIngredient[]" value="">
                        <button class="icon-delete right"></button>
                    </li>
                </template>
                <?php if(isset($_['recipeIngredient']) && is_array($_['recipeIngredient'])) { ?>
                    <?php foreach ($_['recipeIngredient'] as $i => $ingredient) { ?>
                        <li>
                            <input type="text" name="recipeIngredient[]" value="<?php echo $ingredient; ?>">
                            <button class="icon-delete right"></button>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </fieldset>

        <fieldset>
            <button class="icon-add right"></button>
            <label><?php p($l->t('Instructions')); ?></label>
            <ul>
                <template>
                    <li>
                        <textarea name="recipeInstructions[]"></textarea>
                        <button class="icon-delete right"></button>
                    </li>
                </template>
                <?php if(isset($_['recipeInstructions']) && is_array($_['recipeInstructions'])) { ?>
                    <?php foreach ($_['recipeInstructions'] as $i => $step) { ?>
                        <li>
                            <textarea name="recipeInstructions[]"><?php echo $step; ?></textarea>
                            <button class="icon-delete right"></button>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </fieldset>
    </div>
</form>
