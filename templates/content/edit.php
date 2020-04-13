<form id="editRecipeForm" action="#" method="<?php echo $_['id'] ? 'PUT' : 'POST' ?>">
    <div id="controls">
        <div class="breadcrumb">
            <div class="crumb svg crumbhome ui-droppable">
                <a href="#" class="icon-category-organization"></a>
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

        <fieldset class="duration">
            <label><?php p($l->t('Preparation time')); ?></label>
            <input type="number" min="0" name="prepTime[]" value="<?php if(isset($_['prepTime'])) { echo preg_replace_callback('/PT([0-9]+)H[0-9]+M/', function($m) { return $m[1]; }, $_['prepTime']); } ?>" placeholder="00">
            <span>:</span>
            <input type="number" min="0" max="59" name="prepTime[]" value="<?php if(isset($_['prepTime'])) { echo preg_replace_callback('/PT[0-9]+H([0-9]+)M/', function($m) { return $m[1]; }, $_['prepTime']); } ?>" placeholder="00">
        </fieldset>

        <fieldset class="duration">
            <label><?php p($l->t('Cooking time')); ?></label>
            <input type="number" min="0" name="cookTime[]" value="<?php if(isset($_['cookTime'])) { echo preg_replace_callback('/PT([0-9]+)H[0-9]+M/', function($m) { return $m[1]; }, $_['cookTime']); } ?>" placeholder="00">
            <span>:</span>
            <input type="number" min="0" max="59" name="cookTime[]" value="<?php if(isset($_['cookTime'])) { echo preg_replace_callback('/PT[0-9]+H([0-9]+)M/', function($m) { return $m[1]; }, $_['cookTime']); } ?>" placeholder="00">
        </fieldset>

        <fieldset class="duration">
            <label><?php p($l->t('Total time')); ?></label>
            <input type="number" min="0" name="totalTime[]" value="<?php if(isset($_['totalTime'])) { echo preg_replace_callback('/PT([0-9]+)H[0-9]+M/', function($m) { return $m[1]; }, $_['totalTime']); } ?>" placeholder="00">
            <span>:</span>
            <input type="number" min="0" max="59" name="totalTime[]" value="<?php if(isset($_['totalTime'])) { echo preg_replace_callback('/PT[0-9]+H([0-9]+)M/', function($m) { return $m[1]; }, $_['totalTime']); } ?>" placeholder="00">
        </fieldset>
        
        <fieldset>
            <label><?php p($l->t('Category')); ?></label>
            <input type="text" name="recipeCategory" value="<?php if(isset($_['recipeCategory'])) { echo $_['recipeCategory']; } ?>">
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
                    <li class="input-group">
                        <input type="text" name="tool[]" value="">
                        <div class="input-group-addon">
                            <button class="icon-arrow-up move-list-item-up"></button>
                            <button class="icon-arrow-down move-list-item-down"></button>
                            <button class="icon-delete right remove-list-item"></button>
                        </div>
                    </li>
                </template>
                <?php if(isset($_['tool']) && is_array($_['tool'])) { ?>
                    <?php foreach ($_['tool'] as $i => $tool) { ?>
                        <li class="input-group">
                            <input type="text" name="tool[]" value="<?php echo $tool; ?>">
                            <div class="input-group-addon">
                                <button class="icon-arrow-up move-list-item-up"></button>
                                <button class="icon-arrow-down move-list-item-down"></button>
                                <button class="icon-delete right remove-list-item"></button>
                            </div>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
            <button class="button add-list-item"><span class="icon-add"></span></button>
        </fieldset>

        <fieldset>
            <label><?php p($l->t('Ingredients')); ?></label>
            <ul>
                <template>
                    <li class="input-group">
                        <input type="text" name="recipeIngredient[]" value="">
                        <div class="input-group-addon">
                            <button class="icon-arrow-up move-list-item-up"></button>
                            <button class="icon-arrow-down move-list-item-down"></button>
                            <button class="icon-delete right remove-list-item"></button>
                        </div>
                    </li>
                </template>
                <?php if(isset($_['recipeIngredient']) && is_array($_['recipeIngredient'])) { ?>
                    <?php foreach ($_['recipeIngredient'] as $i => $ingredient) { ?>
                        <li class="input-group">
                            <input type="text" name="recipeIngredient[]" value="<?php echo $ingredient; ?>">
                            <div class="input-group-addon">
                                <button class="icon-arrow-up move-list-item-up"></button>
                                <button class="icon-arrow-down move-list-item-down"></button>
                                <button class="icon-delete right remove-list-item"></button>
                            </div>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
            <button class="button add-list-item"><span class="icon-add"></span></button>
        </fieldset>

        <fieldset>
            <label><?php p($l->t('Instructions')); ?></label>
            <ul>
                <template>
                    <li class="textarea-group">
                        <div class="step-number"></div>
                        <div class="textarea-group-addon">
                            <button class="icon-arrow-up move-list-item-up"></button>
                            <button class="icon-arrow-down move-list-item-down"></button>
                            <button class="icon-delete right remove-list-item"></button>
                        </div>
                        <textarea name="recipeInstructions[]"></textarea>
                    </li>
                </template>
                <?php if(isset($_['recipeInstructions']) && is_array($_['recipeInstructions'])) { ?>
                    <?php foreach ($_['recipeInstructions'] as $i => $step) { ?>
                        <li class="textarea-group">
                            <div class="step-number"><?php echo ($i + 1); ?>.</div>
                            <div class="textarea-group-addon">
                                <button class="icon-arrow-up move-list-item-up"></button>
                                <button class="icon-arrow-down move-list-item-down"></button>
                                <button class="icon-delete right remove-list-item"></button>
                            </div>
                            <textarea name="recipeInstructions[]"><?php echo $step; ?></textarea>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
            <button class="button add-list-item"><span class="icon-add"></span></button>
        </fieldset>
    </div>
</form>
