<header>
    <?php if(isset($_['image']) && $_['image']) { ?>
        <figure>
            <img src="<?php echo $_['imageURL']; ?>">
        </figure>
    <?php } ?>

    <h2><?php echo $_['name']; ?></h2>
    
    <p><?php echo $_['recipeYield']; ?> serving<?php if($_['recipeYield'] > 1) { echo 's'; } ?></p>
    
    <?php if(isset($_['dailyDozen'])) { ?>
        <?php 

        $daily_dozen = [
            'beansAndLegumes' => [ 'icon' => '🥛', 'name' => 'Beans and legumes' ],
            'berries' => [ 'icon' => '🍓', 'name' => 'Berries' ],
            'cruciferousVegetables' => [ 'icon' => '🥦', 'name' => 'Cruciferous vegetables' ],
            'flaxseeds' => [ 'icon' => '🌱', 'name' => 'Flaxseeds' ],
            'greens' => [ 'icon' => '🥬', 'name' => 'Greens' ],
            'nutsAndSeeds' => [ 'icon' => '🌰', 'name' => 'Nuts and seeds' ],
            'otherFruits' => [ 'icon' => '🍌', 'name' => 'Other fruits' ],
            'otherVegetables' => [ 'icon' => '🥑', 'name' => 'Other vegetables' ],
            'herbsAndSpices' => [ 'icon' => '🌿', 'name' => 'Herbs and spices' ],
            'wholeGrains' => [ 'icon' => '🍞', 'name' => 'Whole grains' ],
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
        <h3>Ingredients</h3>

        <?php foreach($_['recipeIngredient'] as $ingredient) {  ?>
            <li><?php echo $ingredient; ?></li>   
        <?php } ?>
    </ul>    
</aside>

<main>
    <ul>
        <h3>Instructions</h3>

        <?php foreach($_['recipeInstructions'] as $step) {  ?>
            <li><?php echo $step; ?></li>   
        <?php } ?>
    </ul>
</main>
