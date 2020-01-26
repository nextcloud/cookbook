<div id="controls">
    <div class="breadcrumb">
        <div class="crumb svg crumbmenu hidden">
            <a class="icon-more menutoggle" aria-expanded="false"></a>
            <div class="popovermenu menu-center menu">
                <ul>
                    <li class="crumblist ui-droppable in-breadcrumb">
                        <a href="#">
                            <span class="icon-folder"></span>
                            <span><?php p($l->t('Home')); ?></span>
                        </a>
                    </li>
                    <li class="crumblist in-breadcrumb">
                        <a href="#<?php echo $_['id']; ?>">
                            <span class="icon-folder"></span>
                            <span><?php echo $_['name']; ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="crumb svg crumbhome ui-droppable">
            <a href="#" class="icon-home"><?php p($l->t('Home')); ?></a>
        </div>
        <div class="crumb svg">
            <a href="#<?php echo $_['id']; ?>"><?php echo $_['name']; ?></a>
        </div>
    </div>
    <div class="actions">
        <a id="edit-recipe" href="#<?php echo $_['id']; ?>|edit" class="button svg action" title="<?php p($l->t('Edit recipe')); ?>">
            <span class="icon icon-rename"></span>
            <span class="hidden-visually"><?php p($l->t('Edit recipe')); ?></span>
        </a>
    </div>
    <div class="actions">
        <button id="print-recipe" class="button svg action" title="<?php p($l->t('Print recipe')); ?>">
            <span class="icon icon-category-office"></span>
            <span class="hidden-visually"><?php p($l->t('Print recipe')); ?></span>
        </button>
    </div>
    <div class="actions">
        <button id="delete-recipe" class="button svg action" data-id="<?php echo $_['id']; ?>" title="<?php p($l->t('Delete recipe')); ?>">
            <span class="icon icon-delete"></span>
            <span class="hidden-visually"><?php p($l->t('Delete recipe')); ?></span>
        </button>
    </div>
</div>

<?php if(isset($_['image']) && $_['image']) { ?>
    <header>
        <img src="<?php echo $_['imageURL']; ?>&t=<?php echo time(); ?>">
    </header>
<?php } ?>

<div class="recipe-content">
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
    <?php if(!empty($_['tools'])) { ?>
        <ul>
            <h3><?php p($l->t('Tools')); ?></h3>

            <?php foreach($_['tool'] as $tools) {  ?>
                <li><?php echo $tools; ?></li>
            <?php } ?>
        </ul>
    <?php } ?>
    <?php if(!empty($_['recipeIngredient'])) { ?>
    <ul>
        <h3><?php p($l->t('Ingredients')); ?></h3>

        <?php foreach($_['recipeIngredient'] as $ingredient) {  ?>
            <li><?php echo $ingredient; ?></li>
        <?php } ?>
    </ul>
    <?php } ?>
</aside>

<?php if(!empty($_['recipeInstructions'])) { ?>
    <main>
        <ol>
            <h3><?php p($l->t('Instructions')); ?></h3>

            <?php foreach($_['recipeInstructions'] as $step) {  ?>
                <li><?php echo nl2br($step); ?></li>
            <?php } ?>
        </ol>
    </main>
<?php } ?>

