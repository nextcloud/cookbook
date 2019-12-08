<header>
    <?php if(isset($_['image']) && $_['image']) { ?>
        <img src="<?php echo $_['imageURL']; ?>&t=<?php echo time(); ?>">
    <?php } ?>
    
    <div class="recipe-toolbar">
        <a id="edit-recipe" href="#<?php echo $_['id']; ?>|edit" class="button svg action icon-rename" title="<?php p($l->t('Edit recipe')); ?>"></a>
        <button id="print-recipe" class="button svg action icon-category-office" title="<?php p($l->t('Print recipe')); ?>"></button>
        <button id="delete-recipe" class="button svg action icon-delete" data-id="<?php echo $_['id']; ?>" title="<?php p($l->t('Delete recipe')); ?>"></button>
    </div>

    <h2><?php echo $_['name']; ?></h2>
    
    <div class="recipe-details">
        <?php if(isset($_['url']) && $_['url']) { ?>
            <p><strong><?php p($l->t('Source')); ?>: </strong><a target="_blank" href="<?php echo $_['url']; ?>"><?php echo $_['url']; ?></a></p>
        <?php } ?>

        <p><?php echo $_['description']; ?></p>

        <?php if(isset($_['prepTime']) && $_['prepTime']) {
            $prep_interval = new DateInterval($_['prepTime']);
            $prep_mins = $prep_interval->format('%I');
            $prep_hours = $prep_interval->format('%h');
        ?>
            <p><strong><?php p($l->t('Preparation time')); ?>: </strong><?php echo $prep_hours . ':' . $prep_mins; ?></p>
        <?php } ?>

        <?php if(isset($_['cookTime']) && $_['cookTime']) {
            $cook_interval = new DateInterval($_['cookTime']);
            $cook_mins = $cook_interval->format('%I');
            $cook_hours = $cook_interval->format('%h');
        ?>
            <p><strong><?php p($l->t('Cooking time')); ?>: </strong><?php echo $cook_hours . ':' . $cook_mins; ?></p>
        <?php } ?>

        <?php if(isset($_['totalTime']) && $_['totalTime']) {
            $total_interval = new DateInterval($_['totalTime']);
            $total_mins = $total_interval->format('%I');
            $total_hours = $total_interval->format('%h');
        ?>
            <p><strong><?php p($l->t('Total time')); ?>: </strong><?php echo $total_hours . ':' . $total_mins; ?></p>
        <?php } ?>

        <p><strong><?php p($l->t('Servings')); ?>: </strong><?php echo $_['recipeYield']; ?></p>
    </div>
</header>

<aside>
    <ul>
        <h3><?php p($l->t('Tools')); ?></h3>
        <?php if(isset($_['tool']) && $_['tool']) { ?>
        <?php foreach($_['tool'] as $tool) {  ?>
            <li><?php echo $tool; ?></li>
        <?php }} ?>
    </ul>
    <ul>
        <h3><?php p($l->t('Ingredients')); ?></h3>
        <?php if(isset($_['recipeIngredient']) && $_['recipeIngredient']) { ?>
        <?php foreach($_['recipeIngredient'] as $ingredient) {  ?>
            <li><?php echo $ingredient; ?></li>   
        <?php }} ?>
    </ul>    
</aside>

<main>
    <ol>
        <h3><?php p($l->t('Instructions')); ?></h3>
        <?php if(isset($_['recipeInstructions']) && $_['recipeInstructions']) { ?>
        <?php foreach($_['recipeInstructions'] as $step) {  ?>
            <li><?php echo nl2br($step); ?></li>   
        <?php }} ?>
    </ol>
</main>

