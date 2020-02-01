<div class="app-navigation-create">
	<a href="#recipes/create" title="<?php p($l->t('Create recipe')); ?>" class="button icon-add"><?php p($l->t('Create recipe')); ?></a>
</div>

<form action="import" class="app-navigation-new" method="POST">
    <input name="url" placeholder="<?php p($l->t('Recipe URL')); ?>">
    <button type="submit" title="<?php p($l->t('Download recipe')); ?>">
        <div class="icon-download"></div>
        <div class="icon-loading float-spinner"></div>
    </button>
</form>

<ul id="categories">
	<li><a href="#all"><?php echo p($l->t('All recipes')); ?></a></li>
	
	<?php foreach($_['all_keywords'] as $keyword) { ?>
		<?php if(!isset($keyword['name']) || empty($keyword['name'])) { continue; } ?>
		
		<li><a href="#tag/<?php echo urlencode($keyword['name']); ?>"><?php echo $keyword['name']; ?></a></li>
	<?php } ?>
</ul>
