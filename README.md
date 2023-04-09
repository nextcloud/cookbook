> ⚠️ **IMPORTANT** ⚠️  
>  Users of this app are practically testers. We're limited on resources, and still working out how to make this app function the best it can. There will be regressions and bugs. And we of course appreciate constructive feedback whenever users run into them.

<a href="https://matrix.to/#/#nextcloud-cookbook:matrix.org" >
    <img src="https://img.shields.io/matrix/nextcloud-cookbook:matrix.org?logo=matrix&label=Join%20the%20discussion&style=flat" alt="Join us on Matrix" >
</a>

![CI-tests](https://github.com/nextcloud/cookbook/workflows/CI-tests/badge.svg)
[![codecov](https://codecov.io/gh/nextcloud/cookbook/branch/master/graph/badge.svg?token=J1DI0KGEX3)](https://codecov.io/gh/nextcloud/cookbook)
![GitHub](https://img.shields.io/github/license/nextcloud/cookbook)
![GitHub language count](https://img.shields.io/github/languages/count/nextcloud/cookbook)
![GitHub Repo stars](https://img.shields.io/github/stars/nextcloud/cookbook?logo=github)
![GitHub all releases](https://img.shields.io/github/downloads/nextcloud/cookbook/total?logo=github)

# NextCloud Cookbook
<p align=center>
<img alt="A screenshot of how the app looks" src="./docs/assets/screenshot.png">
</p>
A library for all your recipes. It uses JSON files following the schema.org recipe format. To add a recipe to the collection, you can paste in the URL of the recipe, and the provided web page will be parsed and downloaded to whichever folder you specify in the app settings.

Further documentation (also internal ones) are published on the [documentation pages of the project](http://nextcloud.github.io/cookbook/).

## Clients

The app works generally in any modern browser. Additionally, there are some more specialized clients listed here.

### Mobile
The currently available clients are:

[Nextcloud Cookbook](https://micmun.de/nextcloud-cookbook-english/) (by MicMun)  
[<img src="https://fdroid.gitlab.io/artwork/badge/get-it-on.png" alt="Get it on F-Droid" height="80">](https://f-droid.org/en/packages/de.micmun.android.nextcloudcookbook/) [<img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png" alt="Get it on Google Play" height="80">](https://play.google.com/store/apps/details?id=de.micmun.android.nextcloudcookbook&hl=en_US&gl=US&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1/)

[Nextcloud Cookbook](https://github.com/Teifun2/nextcloud-cookbook-flutter) (by Teifun2)  
[<img src="https://fdroid.gitlab.io/artwork/badge/get-it-on.png" alt="Get it on F-Droid" height="80">](https://f-droid.org/en/packages/com.nextcloud_cookbook_flutter/) [<img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png" alt="Get it on Google Play" height="80">](https://play.google.com/store/apps/details?id=com.nextcloud_cookbook_flutter&hl=en_US&gl=US) [<img src="https://tools.applemediaservices.com/api/badges/download-on-the-app-store/black/en-us" alt="Download on the App Store" height="80" width="160">](https://apps.apple.com/us/app/nextcloud-cookbook/id1619926634?itsct=apps_box_badge&amp;itscg=30200)

Nook  
[<img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png" alt="Get it on Google Play" height="80">](https://play.google.com/store/apps/details?id=org.capacitor.cookbook.app)

### Browser plugins/scripts

- [add-nextcloud-cookbook](https://github.com/qutebrowser/qutebrowser/blob/master/misc/userscripts/add-nextcloud-cookbook) - qutebrowser userscript that allows users to easily add new recipes

## F.A.Q.

#### I can't see my recipes
Recipes are only shown in the UI if they are present in the database. It is likely you have recipes that haven't been indexed/added to the database yet. Try clicking the Settings > Rescan library button to compare the database with what is in your recipes folder and apply any differences to the database. 

If this still doesnt work, a full, non-incremental resync might help. This can be done by setting your recipes folder to a different (ideally empty) folder to clear the database. Setting the recipes folder back to what it was before should cause all your recipes to sync again, effectively refreshing the database

#### "Could not load recipe" when trying to download recipes?
A lot of websites are unfortunately not following the schema.org/Recipe standard, which makes their recipes impossible to read by this app.

#### A website using correct schema.org markup is not being read correctly
The parser is far from perfect. If you can help out in any way, please [have a look at the parseRecipeHtml() method](https://github.com/nextcloud/nextcloud-cookbook/blob/master/lib/Service/RecipeService.php) and create a pull request with your changes.

#### All of the text is in English?
This app uses the [Transifex](https://app.transifex.com/nextcloud/nextcloud/cookbook/) translation system.
You might want to register there to help translating the app to new languages or report errors in existing translations.
