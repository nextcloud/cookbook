<button id="reindex-recipes">Reindex</button>

<form id="find-recipes">
    <input list="list-keywords" name="keywords" placeholder="Search" multiple>
    <datalist id="list-keywords">
        <?php foreach($_['all_keywords'] as $keyword) { ?>
            <option value="<?php echo $keyword['name']; ?>">
        <?php } ?>
    </datalist>
    <input type="submit" value="Search">
</form>

<ul></ul>

<script id="navigation-tpl" type="text/template">
    <li>
        <a href="#{{recipe_id}}" data-id="{{recipe_id}}">
            <img src="/apps/cookbook/image?recipe={{recipe_id}}&size=thumb">
            {{name}}
        </a>
    </li>
</script>
