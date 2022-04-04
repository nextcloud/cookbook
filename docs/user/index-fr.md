# Documentation d'utilisation
*Attention , cette documentation en français peut ne pas être à jour. Seule la [documentation en anglais](index.md) fait foi !*

## Démarrer avec Cookbook

Avant de créer votre première recette, vous devriez décider où seront stockées les recettes et images.

Tous les fichiers seront accessibles dans votre gestionnaire de fichiers Nextcloud.
Cela vous permet d'accéder à ces fichiers depuis une application tierce, en utilisant les applications/clients de synchronisation habituels de Nextcloud.

Par défaut, un répertoire *Recettes* sera situé dans votre répertoire principal.
Vous pouvez simplement vérifier, dans les *Paramètres* de Cookbook, en bas à gauche, dans quel répertoire les recettes seront stockées sur Nextcloud.

## Ajouter une recette

Cliquer sur le bouton *Créer une recette* et ajouter un titre et toute information utile. Si un bloc est vide, il ne sera pas affiché dans Cookbook.

Une image peut être ajoutée à la recette. Il y a plusieurs façons de faire cela :

- L'image peut être auparavant stockée sur votre instance Nextcloud. Cliquez sur l'icône à droite du champ image, et sélectionnez l'image. Cette dernière sera alors recopiée dans le répertoire de la recette.
- L'image peut être chargée depuis une URL. Saisissez ou copiez l'URL dans le champ. L'application Cookbook téléchargera l'image et l'utilisera.

Des liens vers d'autres recettes peuvent être ajoutés dans les champs *Description*, *Ustensiles*, *Ingrédients*, et *Instructions* :

- Tapez un `#` et sélectionnez, dans la liste déroulante, la recette à lier.

## Utiliser les mots-clés et catégories

L'utilisation des mots-clés et catégories est totalement à votre choix.

La différence principale entre les deux est qu'une recette ne peut avoir qu'une seule catégorie, mais plusieurs mots-clés. En d'autres mots, les catégories sont des relations 1:N, alors que les mots-clés sont des relations N:M.

On peut accéder aux catégories de façon plus directe qu'aux mots-clés car elles sont affichées dans le panneau latéral.

En cliquant sur une catégorie dans le panneau latéral, vous pouvez filtrer rapidement des recettes comme "Plat principal" ou "Dessert". Les mots-clés peuvent ensuite être utilisés pour réduire la sélection avec des étiquettes comme "végétarien" ou "facile". De cette façon, les catégories opérent un filtrage large, et les mots-clés vont permettre d'affiner ce filtrage.


![Exemple utilisant les catégories pour un filtrage large, et les mots-clés pour affiner ce filtrage](assets/keywords-and-categories.png)


## Importer des Recettes

### Importer depuis un site web

Les recettes peuvent être importées en entrant une URL de recette dans le champ texte en haut à gauche de l'écran.

<img src="assets/create_import.png" alt="Recipe-import field" width="200px" />

Cependant l'application Cookbook nécessite que le site de recettes respecte les méta données JSON+LD du [standard des recettes schema.org](https://www.schema.org/Recipe). Les sites ne respectant pas ce standard ne sont à l'heure actuelle pas supportés.


## Partager des recettes

### Partager avec un autre utilisateur Nextcloud

Pour l'instant, la seule façon de partager des recettes est de partager, avec un autre utilisateur Nextcloud, le répertoire Nextcloud qui contient les recettes. À cette fin, le répertoire doit d'abord être partagé depuis le gestionnaire de fichiers de Nextcloud.Ensuite, il peut être indiqué comme répertoire de recette dans les *Paramètres* de l'application Cookbook.

<img src="assets/settings.png" alt="Recipe-import field" width="200px" />


### Partage public

Pour l'instant, il n'est pas possible de partager un lien public vers une recette.
