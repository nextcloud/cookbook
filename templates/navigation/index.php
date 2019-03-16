<form id="add-recipe" class="app-navigation-new">
    <input name="url" placeholder="Recipe URL">
    <button class="icon-download" type="submit" title="Download recipe"></button>
</form>

<form id="find-recipes" class="app-navigation-new">
    <input list="list-keywords" name="keywords" placeholder="Search" multiple>
    <datalist id="list-keywords">
        <?php foreach($_['all_keywords'] as $keyword) { ?>
            <option value="<?php echo $keyword['name']; ?>">
        <?php } ?>
    </datalist>
    <button class="icon-category-search" type="submit"></button>
</form>

<ul id="recipes"></ul>
