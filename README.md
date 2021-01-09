| ⚠️ **IMPORTANT** ⚠️ |
| --- |
| Users of this app are practically testers. We're limited on resources, and still working out how to make this app function the best it can. There will be regressions and bugs. And we of course appreciate constructive feedback whenever users run into them. |

![CI-tests](https://github.com/nextcloud/cookbook/workflows/CI-tests/badge.svg)

# NextCloud Cookbook

A library for all your recipes. It uses JSON files following the schema.org recipe format. To add a recipe to the collection, you can paste in the URL of the recipe, and the provided web page will be parsed and downloaded to whichever folder you specify in the app settings.

## Clients

### Android
The currently available clients are

- Nextcloud Cookbook (by MicMun) ([Google Play](https://play.google.com/store/apps/details?id=de.micmun.android.nextcloudcookbook&hl=en_US&gl=US), [FDroid](https://f-droid.org/en/packages/de.micmun.android.nextcloudcookbook/), [homepage](https://micmun.de/nextcloud-cookbook-english/))
- Nextcloud Cookbook (by Teifun2) ([Google Play](https://play.google.com/store/apps/details?id=com.nextcloud_cookbook_flutter&hl=en_US&gl=US))

## Join the discussion

* [#nextcloud-cookbook:matrix.org](https://matrix.to/#/#nextcloud-cookbook:matrix.org)

## F.A.Q.

#### I can't see my recipes
They probably haven't been indexed. Try clicking the Settings > Rescan library button.

#### "Could not load recipe" when trying to download recipes?
A lot of websites are unfortunately not following the schema.org/Recipe standard, which makes their recipes impossible to read by this app.

#### A website using correct schema.org markup is not being read correctly
The parser is far from perfect. If you can help out in any way, please [have a look at the parseRecipeHtml() method](https://github.com/mrzapp/nextcloud-cookbook/blob/master/lib/Service/RecipeService.php) and create a pull request with your changes.

#### All of the text is in English?
This app uses the [Transifex](https://www.transifex.com/nextcloud/nextcloud/cookbook/) translation system.
You might want to register there to help translating the app to new languages or report errors in existing translations.
