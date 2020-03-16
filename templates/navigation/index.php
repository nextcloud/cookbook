<div class="app-navigation-create">
	<a href="#recipes/create" title="<?php p($l->t('Create recipe')); ?>" class="button icon-add"><?php p($l->t('Create recipe')); ?></a>
</div>

<form id="import-recipe" class="app-navigation-new" method="POST">
    <input name="url" placeholder="<?php p($l->t('Recipe URL')); ?>">
    <button type="submit" title="<?php p($l->t('Download recipe')); ?>">
        <div class="icon-download"></div>
        <div class="icon-loading float-spinner"></div>
    </button>
</form>

<ul id="categories">

</ul>
