<?php
// Prepare prep, cook and total time values
if(isset($_['prepTime']) && $_['prepTime']) {
    $prep_interval = new DateInterval($_['prepTime']);
    $_['timePrep'] = [];
    $_['timePrep']['minutes'] =  $prep_interval->format('%I');
    $_['timePrep']['hours'] = $prep_interval->format('%h');
}
if(isset($_['cookTime']) && $_['cookTime']) {
    $cook_interval = new DateInterval($_['cookTime']);
    $_['timeCook'] = [];
    $_['timeCook']['minutes'] = $cook_interval->format('%I');
    $_['timeCook']['hours'] = $cook_interval->format('%h');
}
if(isset($_['totalTime']) && $_['totalTime']) {
    $total_interval = new DateInterval($_['totalTime']);
    $_['timeTotal'] = [];
    $_['timeTotal']['minutes'] = $total_interval->format('%I');
    $_['timeTotal']['hours'] = $total_interval->format('%h');
}
?>

<div id="app-recipe-content"></div>
<div id="app-recipe-data" style="display:none"><?php echo str_replace("'", "\\'", json_encode($_)); ?></div>
