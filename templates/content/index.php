<?php if(!$_['current_node']) { ?>
    Please pick a recipe

<?php } else { ?>
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
        
        <ul>
            <h3>Daily dozen</h3>

            <?php foreach(['beans', 'berries', 'otherFruits', 'cruciferousVegetables', 'greens', 'otherVegetables', 'flaxseeds', 'nutsAndSeeds', 'herbsAndSpices', 'wholeGrains', 'beverages'] as $element) { ?>
                <li><?php echo $element; ?><?php if(strpos($recipe['dailyDozen'], $element) !== false) { echo ' ✔'; } ?></li>
            <?php } ?>
        </ul>
    </aside>

    <main>
        <ul>
            <h3>Instructions</h3>

            <?php foreach($recipe['recipeInstructions'] as $step) {  ?>
                <li><?php echo $step['text']; ?></li>   
            <?php } ?>
        </ul>
    </main>
<?php } ?>
