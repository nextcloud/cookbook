<div id="controls">
    <div class="breadcrumb">
        <div class="crumb svg crumbhome ui-droppable">
            <a href="#" class="icon-category-organization"></a>
        </div>
        <div class="crumb svg">
            <a href="#recipes/<?php echo $_['id']; ?>"><?php echo $_['name']; ?></a>
        </div>
    </div>
    <div class="actions">
        <a id="edit-recipe" href="#recipes/<?php echo $_['id']; ?>/edit" class="button svg action" title="<?php p($l->t('Edit recipe')); ?>">
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
    <header class="collapsed<?php if($_['print_image']) echo ' printable'; ?>">
        <img src="<?php echo $_['image_url']; ?>">
    </header>
<?php } ?>

<div class="recipe-content">
	<h2><?php echo $_['name']; ?></h2>
    
    <div class="recipe-details">
        <p><?php echo $_['description']; ?></p>

        <?php if(isset($_['url']) && $_['url']) { ?>
            <p><strong><?php p($l->t('Source')); ?>: </strong><a target="_blank" href="<?php echo $_['url']; ?>"><?php echo $_['url']; ?></a></p>
        <?php } ?>

        <p><strong><?php p($l->t('Servings')); ?>: </strong><?php echo $_['recipeYield']; ?></p>
		
		<div class="times">
			<?php if(isset($_['prepTime']) && $_['prepTime']) {
				$prep_interval = new DateInterval($_['prepTime']);
				$prep_mins = $prep_interval->format('%I');
				$prep_hours = $prep_interval->format('%h');
                if ($prep_hours > 0 || $prep_mins > 0) {
			?>
				<div class="time" data-raw="<?php echo $_['prepTime']; ?>">
		            <h4><?php p($l->t('Preparation time')); ?></h4>
					<p><?php echo $prep_hours . ':' . $prep_mins; ?></p>
				</div>
            <?php }} ?>

			<?php if(isset($_['cookTime']) && $_['cookTime']) {
				$cook_interval = new DateInterval($_['cookTime']);
				$cook_mins = $cook_interval->format('%I');
				$cook_hours = $cook_interval->format('%h');
                if ($cook_hours > 0 || $cook_mins > 0) {
			?>
                <div class="time" data-raw="<?php echo $_['cookTime']; ?>">
					<button type="button" class="icon-play" data-hours="<?php echo $cook_hours ?>" data-minutes="<?php echo $cook_mins ?>"></button>
		            <h4><?php p($l->t('Cooking time')); ?></h4>
					<p><?php echo $cook_hours . ':' . $cook_mins; ?></p>
				</div>
            <?php }} ?>
			
			<?php
			if(isset($_['totalTime']) && $_['totalTime']) {
				$total_interval = new DateInterval($_['totalTime']);
				$total_mins = $total_interval->format('%I');
				$total_hours = $total_interval->format('%h');
                if ($total_hours > 0 || $total_mins > 0) {
			?>
				<div class="time" data-raw="<?php echo $_['totalTime']; ?>">
		            <h4><?php p($l->t('Total time')); ?></h4>
					<p><?php echo $total_hours . ':' . $total_mins; ?></p>
				</div>
            <?php }} ?>
		</div>
    </div>
</div>

<section>
	<aside>
		<section>
		    <?php if(!empty($_['recipeIngredient'])) { ?>
			<h3><?php p($l->t('Ingredients')); ?></h3>
		    <ul>
		        <?php foreach($_['recipeIngredient'] as $ingredient) {  ?>
		            <li><?php echo $ingredient; ?></li>
		        <?php } ?>
		    </ul>
		    <?php } ?>
		</section>
			
		<section>
			<?php if(!empty($_['tool'])) { ?>
				<h3><?php p($l->t('Tools')); ?></h3>
				<ul>
					<?php foreach($_['tool'] as $tools) {  ?>
						<li><?php echo $tools; ?></li>
					<?php } ?>
				</ul>
			<?php } ?>
		</section>
	</aside>

	<?php if(!empty($_['recipeInstructions'])) { ?>
	    <main>
			<h3><?php p($l->t('Instructions')); ?></h3>
	        <ol class="instructions">
	            <?php foreach($_['recipeInstructions'] as $step) {  ?>
	                <li class="instruction"><?php echo nl2br($step); ?></li>
	            <?php } ?>
	        </ol>
	    </main>
	<?php } ?>
</section>
