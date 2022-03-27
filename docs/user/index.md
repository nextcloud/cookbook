# Usage Documentation

## Starting with Cookbook

Before creating your first recipe, you should decide where your recipes and pictures will be stored. All files will be accessible in your Nextcloud file manager.

- Recipe's directory : check in the *Parameters* of Cookbook, in the low left corner, in which directory they will be stored on Nextcloud. 
- Pictures directory : it's also a good idea to create a directory to gather all Cookbook pictures in one place.

## Adding a recipe

Click on the button *Create a recipe* and add a title and any useful information. If a block of information is empty, it won't be displayed in Cookbook.
  
An image may be added to the recipe : the image must be previously stored on your Nextcloud instance. Click on the icon on the right of the image field, and select the image.

Links may be added to other recipes in *Description*, *Tools*, *Ingredients* or *Instructions* :

- Type a `#` and select, in the pop up list, the recipe to be linked.

## Using keywords and categories

The use of keywords and categories is entirely up to you.

The primary difference between the two is that a recipe can only have a single category,
but may have many keywords.
In other words,
categories are a 1:N relationship while keywords are an N:M relationship.

Categories can be accessed more directly than keywords,
as they appear in the sidebar.
By clicking a category in the sidebar,
you can quickly narrow down recipes to a certain class, like "Main dishes" or "Deserts".
Then, keywords can be used to further narrow down the selection
with tags like "vegetarian" or "easy".
In this way, categories are used for rough filtering and keywords are used for fine filtering.

![Example workflow using categories for rough filtering and keywords for fine filtering](assets/keywords-and-categories.png)

## Importing Recipes

### Importing from a Website

Recipes can be imported by entering the URL to a recipe in the text-input field in the top right area of the Cookbook app.

<img src="assets/create_import.png" alt="Recipe-import field" width="200px" />

However, the Cookbook app requires the recipe site to support the [schema.org recipe standard](http://www.schema.org/Recipe)’s JSON+LD meta data. Those websites which don't provide the required data are currently not supported.

## Sharing Recipes

### Sharing with another Nextcloud User

Currently, the only way to share recipes is by sharing the Nextcloud folder that contains all recipes with another Nextcloud user. To this end, the folder first needs to be shared from the Nextcloud Files app. Afterwards it can be set as the recipe directory in the Cookbook app’s `Settings` section

<img src="assets/settings.png" alt="Recipe-import field" width="200px" />

### Public Sharing

At the moment it is not possible to share a public link to a recipe.
