<div id="app-settings">
    <div id="app-settings-header">
        <button class="settings-button" data-apps-slide-toggle="#app-settings-content"><?php p($l->t('Settings')); ?></button>
    </div>
    <div id="app-settings-content">
        <fieldset class="settings-fieldset">
            <ul class="settings-fieldset-interior">
                <li class="settings-fieldset-interior-item">
                    <button class="button icon-history" id="reindex-recipes"><?php p($l->t('Rescan library')); ?></button>
                </li>
                <li class="settings-fieldset-interior-item">
                    <label class="settings-input"><?php p($l->t('Recipe folder')); ?></label>
                    <input id="recipe-folder" type="text" class="input settings-input" value="<?php echo $_['folder']; ?>" placeholder="<?php p($l->t('Please pick a folder')); ?>">
                </li>
                <li class="settings-fieldset-interior-item">
                    <label class="settings-input">
                        <?php p($l->t('Update interval in minutes')); ?>
                        <br>
                        (<?php p($l->t('Last update')); ?>: <?php echo date('Y-m-d H:i', $_['last_update']); ?>)
                    </label>
                    <input id="recipe-update-interval" type="number" class="input settings-input" value="<?php echo $_['update_interval']; ?>" placeholder="<?php echo $_['update_interval']; ?>">
                </li>
            </ul>
        </fieldset>
    </div>
</div>
