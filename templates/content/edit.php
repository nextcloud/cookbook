<?php $recipe = json_decode($_['current_node']->getContent(), true); ?>

<form action="/index.php/apps/cookbook/update" method="POST">
    <fieldset>
        <label>Name</label>
        <input required type="text" name="name" value="<?php echo $recipe['name']; ?>"></h2>
    </fieldset>

    <fieldset>
        <label>Image URL</label>
        <input type="text" name="image" value="<?php if(isset($recipe['image'])) { echo $recipe['image']; } ?>">
    </fieldset>

    <fieldset>
        <label>Servings</label>
        <input type="number" name="recipeYield" value="<?php if(isset($recipe['recipeYield'])) { echo $recipe['recipeYield']; } ?>">
    </fieldset>
    
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

    <fieldset>
        <label>Daily dozen</label>

        <ul>
            <?php foreach($daily_dozen as $id => $ingredient) { ?>
                <?php $has_ingredient = strpos($recipe['dailyDozen'], $id) !== false; ?>

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
            <?php foreach($recipe['recipeIngredient'] as $i => $ingredient) {  ?>
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
            <?php foreach($recipe['recipeInstructions'] as $i => $step) {  ?>
                <?php if(is_array($step) && isset($step['text'])) { $step = $step['text']; } ?>

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
