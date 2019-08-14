<header>
    <?php if(isset($_['image']) && $_['image']) { ?>
        <figure>
            <img src="<?php echo $_['imageURL']; ?>">
        </figure>
    <?php } ?>

    <h2><?php echo $_['name']; ?></h2>

    <div class="recipe-details">
        <p><?php echo $_['description']; ?></p>

        <?php if(isset($_['prepTime']) && $_['prepTime']) {
            $prep_interval = new DateInterval($_['prepTime']);
            $prep_mins = $prep_interval->format('%i');
            $prep_hours = $prep_interval->format('%h');
            if($prep_hours > 0) {
                $prep_hour_string = $prep_hours.' '.($prep_hours > 1 ? 'Hours' : 'Hour').' ';
            } else {
                $prep_hour_string = "";
            }
            if($prep_mins > 0) {
                $prep_min_string = $prep_mins.' '.($prep_mins > 1 ? 'Mins' : 'Min');
            } else {
                $prep_min_string = "";
            }
            ?>
            <p><strong>Prep: </strong><?php echo $prep_hour_string.$prep_min_string; ?></p>
        <?php } ?>
        <?php if(isset($_['cookTime']) && $_['cookTime']) {
            $cook_interval = new DateInterval($_['cookTime']);
            $cook_mins = $cook_interval->format('%i');
            $cook_hours = $cook_interval->format('%h');
            if($cook_hours > 0) {
                $cook_hour_string = $cook_hours.' '.($cook_hours > 1 ? 'Hours' : 'Hour').' ';
            } else {
                $cook_hour_string = "";
            }
            if($cook_mins > 0) {
                $cook_min_string = $cook_mins.' '.($cook_mins > 1 ? 'Mins' : 'Min');
            } else {
                $cook_min_string = "";
            }
            ?>
            <p><strong>Cook: </strong><?php echo $cook_hour_string.$cook_min_string; ?></p>
        <?php } ?>

        <p><?php p($l->n('One serving', '%n servings', $_['recipeYield'])); ?></p>
    </div>

    <?php if(isset($_['dailyDozen'])) { ?>
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

        <?php foreach($daily_dozen as $id => $ingredient) { ?>
            <?php if(strpos($_['dailyDozen'], $id) === false) { continue; } ?>
            
            <span title="<?php echo $ingredient['name']; ?>"><?php echo $ingredient['icon']; ?></span>
        <?php } ?>
    <?php } ?>
</header>

<aside>
    <ul>
        <h3><?php p($l->t('Ingredients')); ?></h3>

        <?php foreach($_['recipeIngredient'] as $ingredient) {  ?>
            <li><?php echo $ingredient; ?></li>   
        <?php } ?>
    </ul>    
</aside>

<main>
    <ul>
        <h3><?php p($l->t('Instructions')); ?></h3>

        <?php foreach($_['recipeInstructions'] as $step) {  ?>
            <li><?php echo $step; ?></li>   
        <?php } ?>
    </ul>
</main>


<?php if(isset($_['url']) && $_['url']) { ?>
    <p><a href="<?php echo $_['url']; ?>">Original Source</a></p>
<?php } ?>

