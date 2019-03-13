<button id="reindex-recipes">Reindex</button>

<ul>
    <?php foreach($_['all_recipes'] as $recipe) { ?>
        <li>
            <img src="/apps/cookbook/image?recipe=<? echo $recipe['recipe_id']; ?>&size=thumb">
            <a href="?recipe=<?php echo $recipe['recipe_id']; ?>"><?php echo $recipe['name']; ?></a>
        </li>
    <?php } ?>
</ul>
