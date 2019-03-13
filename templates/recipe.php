<?php $recipe = json_decode($_['current_node']->getContent(), true); ?>

<header>
    <?php if(isset($recipe['image']) && $recipe['image']) { ?>
        <figure>
            <img src="/apps/cookbook/image?recipe=<?php echo $_['current_node']->getId(); ?>&size=full">
        </figure>
    <?php } ?>

    <h2><?php echo $recipe['name']; ?></h2>
    
    <p><?php echo $recipe['recipeYield']; ?> servings</p>
</header>

<aside>
    <ul>
        <h3>Ingredients</h3>

        <?php foreach($recipe['recipeIngredient'] as $ingredient) {  ?>
            <li><?php echo $ingredient; ?></li>   
        <?php } ?>
    </ul>
    
    <?php if(isset($recipe['dailyDozen'])) { ?>
        <ul>
            <h3>Daily dozen</h3>

            <?php 
    
            $daily_dozen = [
                'beans' => [ 'icon' => '🥛', 'name' => 'Beans and legumes' ],
                'berries' => [ 'icon' => '🍓', 'name' => 'Berries' ],
                'cruficerous_vegetables' => [ 'icon' => '🥦', 'name' => 'Cruciferous vegetables' ],
                'flaxseeds' => [ 'icon' => '🌱', 'name' => 'Flaxseeds' ],
                'greens' => [ 'icon' => '🥬', 'name' => 'Greens' ],
                'nuts' => [ 'icon' => '🌰', 'name' => 'Nuts and seeds' ],
                'other_fruits' => [ 'icon' => '🍌', 'name' => 'Other fruits' ],
                'other_vegetables' => [ 'icon' => '🥑', 'name' => 'Other vegetables' ],
                'spices' => [ 'icon' => '🌿', 'name' => 'Herbs and spices' ],
                'whole_grains' => [ 'icon' => '🍞', 'name' => 'Whole grains' ],
            ];
        
            ?>

            <?php foreach($daily_dozen as $id => $ingredient) { ?>
                <li>
                    <span title="<?php echo $ingredient['name']; ?>"><?php echo $ingredient['icon']; ?></span>
                    <input disabled title="<?php echo $ingredient['name']; ?>" name="dailyDozen[<?php echo $id; ?>]" type="checkbox" <?php if(strpos($recipe['dailyDozen'], $id) === false) { echo 'checked'; } ?>>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
</aside>

<main>
    <ul>
        <h3>Instructions</h3>

        <?php foreach($recipe['recipeInstructions'] as $step) {  ?>
            <li><?php echo $step['text']; ?></li>   
        <?php } ?>
    </ul>
</main>
