<form id="create-recipe" class="app-navigation-create">
    <button type="submit" title="<?php p($l->t('Create recipe')); ?>" class="button icon-add"><?php p($l->t('Create recipe')); ?></button>
</form>

<form id="add-recipe" class="app-navigation-new">
    <input name="url" placeholder="<?php p($l->t('Recipe URL')); ?>">
    <button type="submit" title="<?php p($l->t('Download recipe')); ?>">
        <div class="icon-download"></div>
        <div class="icon-loading float-spinner"></div>
    </button>
</form>

<form id="find-recipes" class="app-navigation-new">
    <input list="list-keywords" name="keywords" placeholder="<?php p($l->t('Search')); ?>" multiple>
    <datalist id="list-keywords">
        <?php foreach($_['all_keywords'] as $keyword) { ?>
            <option value="<?php echo $keyword['name']; ?>">
        <?php } ?>
    </datalist>
    <a id="clear-recipe-search" class="icon-close"></a>
    <button class="icon-category-search" type="submit"></button>
</form>

<form id="categorize-recipes" class="app-navigation-new">
    <select name="keywords">
        <option selected value=""><?php p($l->t('All')); ?></option>

        <?php foreach($_['all_keywords'] as $keyword) { ?>
            <option value="<?php echo $keyword['name']; ?>"><?php echo $keyword['name']; ?></option>
        <?php } ?>
    </select>
</form>

<ul id="recipes"></ul>
