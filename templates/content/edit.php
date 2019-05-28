<form action="/index.php/apps/cookbook/update" method="POST">
    <fieldset>
        <label>Name</label>
        <input required type="text" name="name" value="<?php echo $_['name']; ?>"></h2>
    </fieldset>

    <fieldset>
        <label>Image URL</label>
        <input type="text" name="image" value="<?php echo $_['image']; ?>">
    </fieldset>
    
    <fieldset>
        <label>Keywords (comma-separated)</label>
        <input type="text" name="keywords" value="<?php echo $_['keywords']; ?>">
    </fieldset>

    <fieldset>
        <label>Servings</label>
        <input type="number" name="recipeYield" value="<?php echo $_['recipeYield']; ?>">
    </fieldset>
    
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

    <fieldset>
        <label>Daily dozen</label>

        <?php echo 'DUDE: ' . $_['dailyDozen']; ?>

        <ul>
            <?php foreach($daily_dozen as $id => $ingredient) { ?>
                <?php $has_ingredient = strpos($_['dailyDozen'], $id) !== false; ?>

                <li>    
                    <label title="<?php echo $ingredient['name']; ?>">
                        <?php echo $ingredient['name'] . ' ' . $ingredient['icon']; ?>
                        <input type="checkbox" name="dailyDozen[<?php echo $id; ?>]" <?php if($has_ingredient) { echo 'checked'; } ?>>
                    </label>
                </li>
            <?php } ?>
        </ul>
    </fieldset>

    <fieldset>
        <label>Ingredients</label>

        <ul>
            <?php foreach($_['recipeIngredient'] as $i => $ingredient) {  ?>
                <li>
                    <input type="text" name="recipeIngredient[]" value="<?php echo $ingredient; ?>">
                    <button class="icon-delete"></button>
                </li>   
            <?php } ?>
            <button class="icon-add"></button>
        </ul>    
    </fieldset>

    <fieldset>
        <label>Instructions</label>

        <ul>
           <?php foreach($_['recipeInstructions'] as $i => $step) {  ?>
                <li>
                    <textarea name="recipeInstructions[]"><?php echo $step; ?></textarea>
                    <button class="icon-delete"></button>
                </li>   
            <?php } ?>
            <button class="icon-add"></button>
        </ul>
    </fieldset>

    <button type="submit">Save</button>
</form>
