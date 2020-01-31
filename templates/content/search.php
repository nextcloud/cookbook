<div class="home">
	<h2>
		<?php if (isset($_['query'])) { ?>
			<?php p($l->t('Search')); ?> <small><?php echo $_['query']; ?></small>
		<?php } elseif (isset($_['tag'])) { ?>
			<?php p($l->t('Tag')); ?> <small><?php echo $_['tag']; ?></small>
		<?php } elseif (isset($_['category'])) { ?>
			<?php p($l->t('Category')); ?> <small><?php echo $_['category']; ?></small>
		<?php } else { ?>
			<?php p($l->t('All recipes')); ?>
		<?php } ?>
	</h2>
	<?php if (empty($_['recipes'])) { ?>
		<p>
			<em><?php p($l->t('No results')); ?></em>
		</p>
	<?php } else { ?>
		<ul>
			<?php foreach ($_['recipes'] as $i => $recipe) { ?>
			    <li>
			        <a href="#recipes/<?php echo $recipe['recipe_id']; ?>"><?php echo $recipe['name']; ?></a>
			    </li>
			<?php } ?>
		</ul>
	<?php } ?>
</div>
