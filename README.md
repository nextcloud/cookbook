# NextCloud Cookbook

A library for all your recipes. It uses JSON files following the schema.org recipe format. To add a recipe to the collection, you can paste in the URL of the recipe, and the provided web page will be parsed and downloaded to whichever folder you specify in the app settings.

## Fork purpose
Branch addfields
Add description, url and other fields to the json and viewer.

## F.A.Q.

#### "Could not load recipe" when trying to download recipes?
A lot of websites are unfortunately not following the schema.org/Recipe standard, which makes their recipes impossible to read by this app.

#### A website using correct schema.org markup is not being read correctly
The parser is far from perfect. If you can help out in any way, please [have a look at the parseRecipeHtml() method](https://github.com/mrzapp/nextcloud-cookbook/blob/master/lib/Service/RecipeService.php) and create a pull request with your changes.
