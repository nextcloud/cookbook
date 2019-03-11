<?php

function parse_recipe_image($json) {
    if(!isset($json['image'])) { return ''; }

    $image = $json['image'];

    if(!$image) { return ''; }
    if(is_string($image)) { return $image; }
    if(isset($image['url'])) { return $image['url']; }
    if(isset($image[0]['url'])) { return $image[0]['url']; }
    if(isset($image[0])) { return $image[0]; }

    return '';
}

function parse_recipe_yield($json) {
    if(!isset($json['recipeYield'])) { return 1; }
    
    $yield = filter_var($json['recipeYield'], FILTER_SANITIZE_NUMBER_INT);

    if(!$yield) { return 1; }

    return (int) $yield;
}

function parse_recipe_instructions($json) {
    if(!isset($json['recipeInstructions'])) { return []; }

    $instructions = $json['recipeInstructions'];

    if(is_array($instructions)) { return $instructions; }

    $regex_matches = [];
    preg_match_all('/<p>(.*?)<\/p>/', htmlspecialchars_decode($instructions), $regex_matches, PREG_SET_ORDER); 

    $instructions = [];

    foreach($regex_matches as $regex_match) {
        if(!$regex_match || !isset($regex_match[1])) { continue; }

        $string = $regex_match[1];
        $string = strip_tags($string);
        $string = str_replace(["\r", "\n", "\t"], '', $string);
            
        if(!$string) { continue; }

        array_push($instructions, [ '@type' => 'HowToStep', 'text' => $string ]);
    }

    return $instructions;
}

function parse_recipe_ingredients($json) {
    if(!isset($json['recipeIngredient']) || !is_array($json['recipeIngredient'])) { return []; }

    $ingredients = [];

    foreach($json['recipeIngredient'] as $i => $ingredient) {
        $ingredient = strip_tags($ingredient);
        $ingredient = str_replace(["\r", "\n", "\t", "\\"], '', $ingredient);

        if(!$ingredient) { continue; }

        array_push($ingredients, $ingredient);
    }

    return $ingredients;
}

function parse_recipe_keywords($json) {
    if(!isset($json['keywords'])) { return ''; }

    $keywords = $json['keywords'];
    $keywords = strip_tags($keywords);
    $keywords = str_replace(' and', '', $keywords);
    $keywords = str_replace(' ', ',', $keywords);
    $keywords = str_replace(',,', ',', $keywords);

    return $keywords;
}

function parse_recipe($url) {
    $host = parse_url($url);

    if(!$host) { throw new Exception('Could not parse URL'); }
    
    $html = file_get_contents($url);

    if(!$html) { throw new Exception('Could not fetch site'); }

    $html = str_replace(["\r", "\n"], '', $html);
    
    $regex_matches = [];
    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $regex_matches, PREG_SET_ORDER);

    foreach($regex_matches as $regex_match) {
        if(!$regex_match || !isset($regex_match[1])) { continue; }

        $string = $regex_match[1];
        
        if(!$string) { continue; }
            
        $json = json_decode($string, true);

        if(!$json || $json['@type'] !== 'Recipe') { continue; }

        $recipe = [
            '@context' => 'http://schema.org',
            '@type' => 'Recipe',
            'name' => isset($json['name']) ? $json['name'] : '',
            'image' => parse_recipe_image($json),
            'recipeYield' => parse_recipe_yield($json),
            'keywords' => parse_recipe_keywords($json),
            'recipeIngredient' => parse_recipe_ingredients($json),
            'recipeInstructions' => parse_recipe_instructions($json),
        ];

        return $recipe;
    }

    throw new Exception('No recipe data found');
}

function change_recipe_yield($recipe, $yield) {
    if(!$recipe || !$yield) { return $recipe; }

    if($recipe['recipeYield'] == $yield) { return $recipe; }

    $default_yield = (float) $recipe['recipeYield'];
    $new_yield = (float) $yield;
    
    foreach($recipe['recipeIngredient'] as $i => $ingredient) {
        $ingredient = preg_replace_callback('/[0-9+]/', function($match) use ($default_yield, $new_yield) {
            $amount = (float) $match[0];

            return round(($amount / $default_yield) * $new_yield, 2);
        }, $ingredient);

        $recipe['recipeIngredient'][$i] = $ingredient;
    }

    $recipe['recipeYield'] = $yield;

    return $recipe;
}

function render_recipe_json($recipe) {
    if(!$recipe) { return; }

    echo '<pre>' . json_encode($recipe, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES) . '</pre>';
}

?>

<form method="POST">
    <input name="url" type="text" value="<?php echo isset($_POST['url']) ? $_POST['url'] : '' ?>" placeholder="Recipe URL">
    <input name="yield" type="number" value="<?php echo isset($_POST['yield']) ? $_POST['yield'] : '' ?>" placeholder="Yield">
    <input type="submit" value="Fetch">
</form>

<?php

if(isset($_POST['url'])) {
    $recipe = parse_recipe($_POST['url']);

    if(isset($_POST['yield'])) {
        $recipe = change_recipe_yield($recipe, $_POST['yield']);
    }
    
    render_recipe_json($recipe);
}

?>
