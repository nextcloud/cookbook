<?php $recipe = json_decode($_['current_node']->getContent(), true); ?>

<form action="/index.php/apps/cookbook/update" method="POST">
    <header>
        <?php if(isset($recipe['image']) && $recipe['image']) { ?>
            <input type="hidden" name="image" value="<?php echo $recipe['image']; ?>">

            <figure>
                <img src="/index.php/apps/cookbook/image?recipe=<?php echo $_['current_node']->getId(); ?>&size=full">
            </figure>
        <?php } ?>

        <h2><input type="text" name="name" value="<?php echo $recipe['name']; ?>"></h2>
        
        <p><input type="number" name="recipeYield" value="<?php echo $recipe['recipeYield']; ?>"> serving<?php if($recipe['recipeYield'] > 1) { echo 's'; } ?></p>
        
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
            <?php $has_ingredient = strpos($recipe['dailyDozen'], $id) !== false; ?>
            
            <label title="<?php echo $ingredient['name']; ?>">
                <?php echo $ingredient['icon']; ?>
                <input type="checkbox" name="dailyDozen[<?php echo $id; ?>]" <?php if($has_ingredient) { echo 'checked'; } ?>>
            </label>
        <?php } ?>
    </header>

    <aside>
        <ul>
            <h3>Ingredients</h3>

            <?php foreach($recipe['recipeIngredient'] as $i => $ingredient) {  ?>
                <li>
                    <input type="text" name="recipeIngredient[<?php echo $i; ?>]" value="<?php echo $ingredient; ?>">
                    <button class="icon-delete"></button>
                </li>   
            <?php } ?>
        </ul>    
    </aside>

    <main>
        <ul>
            <h3>Instructions</h3>

            <?php foreach($recipe['recipeInstructions'] as $i => $step) {  ?>
                <?php if(is_array($step) && isset($step['text'])) { $step = $step['text']; } ?>

                <li>
                    <input type="hidden" name="recipeInstructions[<?php echo $i; ?>][@type]" value="HowToStep">
                    <textarea name="recipeInstructions[<?php echo $i; ?>][text]"><?php echo $step; ?></textarea>
                    <button class="icon-delete"></button>
                </li>   
            <?php } ?>
        </ul>
    </main>

    <button type="submit">Save</button>
</form>
