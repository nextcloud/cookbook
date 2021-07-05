# Recipes import using schema.org metadata

## Introduction

Websites are in general made for human readers. There are fancy and nice-looking pages out there in the wild that are designed to be well readable and quick to grasp. Styling with color, indentation, tables, animations, etc make it easy for a human being to get the structure of a page quickly and to understand what is presented.

A machine on the other hand does not have the ability to get the main point of a website easily. Of course, there are huge databases in the world (called search engines) that try to make sense out of websites. However, these are working mainly on keywords and structure. The better the structure of a page the better a computer can guess the _meaning_ of a website and filter out the relevant information.

An example would be writing a text document in any writer software. The human could mark all headings with a bigger font, make it bold, and center it. A typical human will understand this is a headline. The computer can only guess so. Most writer programs allow on the other hand to **define** a certain line to be a heading of level 1, 2, ... Now, the computer can trivially detect the headlines. If formatted correctly, the difference will be invisible to the (human) viewer.

Such additional information (like if the line is a heading or not) are called metadata. They are typically not directly visible. Depending on their purpose, they might manifest in the styling or not.

## Recipes need annotations for automatic parsing

The number of possibilities when trying to express a cooking recipe are quite large. So a recipe (in HTML) is in general a bigger beast where somewhere the relevant information like the ingredients, instructions, but also simple information like the name or the amount of time to cook are buried deep the page. Without the help of metadata, a machine has a had job to extract all relevant information from such a website.

Just putting some invisible data into the webpage will do no good. In fact it will depend in the heuristics and software _reading_ the page if these are detected and correctly associated (like a title is a title and not by chance the name of the author). Therefore a standardized approch needs to be realized. Many very intelligent people have thought about that problem and the result is the so called _Semantic Web_. You can read on this topic on your own, if you like.

To have a uniform language throughout the web, there was a standard formalized. One very common standard is [schema.org](http://schema.org). They try to create a very basic but general description of all types of metadata. As a byproduct, their standard became very popular and many websites are providing information in their _language_. The cookbook app also bases its data files on the standard [schema.org/Recipe](http://schema.org/Recipe).

## Formats of metadata

The question is: How are these metadata now formated to allow for easy parsing but without affecting the visual representation at all?

The answer is not unique. There are different approaches wich have their individual benefits and drawbacks. Here the most common two should be presented.

### JSON+LD

One HTML element that does not show directly is any sort of (javascript) scripts. Of course the effects might well be visible but the script itself is hidden.

The idea behind JSON+LD is to store the relevant data in such a script tag. The script contains valid javascript (so the browser will not complain) but this code will not do anything. It just there. A parser however looking for such metadata will spot it right away.

Here is an example of a JSON+LD metadata block as it might be found on an importable site:
```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Recipe",
  "author": "John Smith",
  "cookTime": "PT1H",
  "datePublished": "2009-05-08",
  "description": "This classic banana bread recipe comes from my mom -- the walnuts add a nice texture and flavor to the banana bread.",
  "image": "bananabread.jpg",
  "recipeIngredient": [
    "3 or 4 ripe bananas, smashed",
    "1 egg",
    "3/4 cup of sugar"
  ],
  "interactionStatistic": {
    "@type": "InteractionCounter",
    "interactionType": "https://schema.org/Comment",
    "userInteractionCount": "140"
  },
  "name": "Mom's World Famous Banana Bread",
  "nutrition": {
    "@type": "NutritionInformation",
    "calories": "240 calories",
    "fatContent": "9 grams fat"
  },
  "prepTime": "PT15M",
  "recipeInstructions": "Preheat the oven to 350 degrees. Mix in the ingredients in a bowl. Add the flour last. Pour the mixture into a loaf pan and bake for one hour.",
  "recipeYield": "1 loaf",
  "suitableForDiet": "https://schema.org/LowFatDiet"
}
</script>
```

### Microdata

The JSON+LD format has the drawback that the data needs to be present twice: Once for the human reader and once for the parser in the script tag. To overcome this, microdata was introduced. It recycles the human readable data and gives hints where in the big HTML file which type if information can be found/parsed. This is more a guided heuristics that parses the visible data similar to colors guide the human eye.

The key are attributes `itemscope`, `itemtype`, and `itemprop` that are attached to the HTML elements. With these the parser knows what data to expect and where to put them (Remember: is is a title or the author's name?).

Here comes the same example as above with the JSON+LD with microdata attached:
```html
<div itemscope itemtype="https://schema.org/Recipe">
  <span itemprop="name">Mom's World Famous Banana Bread</span>
  By <span itemprop="author">John Smith</span>,
  <meta itemprop="datePublished" content="2009-05-08">May 8, 2009
  <img itemprop="image" src="bananabread.jpg"
       alt="Banana bread on a plate" />

  <span itemprop="description">This classic banana bread recipe comes
  from my mom -- the walnuts add a nice texture and flavor to the banana
  bread.</span>

  Prep Time: <meta itemprop="prepTime" content="PT15M">15 minutes
  Cook time: <meta itemprop="cookTime" content="PT1H">1 hour
  Yield: <span itemprop="recipeYield">1 loaf</span>
  Tags: <link itemprop="suitableForDiet" href="https://schema.org/LowFatDiet" />Low fat

  <div itemprop="nutrition"
    itemscope itemtype="https://schema.org/NutritionInformation">
    Nutrition facts:
    <span itemprop="calories">240 calories</span>,
    <span itemprop="fatContent">9 grams fat</span>
  </div>

  Ingredients:
  - <span itemprop="recipeIngredient">3 or 4 ripe bananas, smashed</span>
  - <span itemprop="recipeIngredient">1 egg</span>
  - <span itemprop="recipeIngredient">3/4 cup of sugar</span>
  ...

  Instructions:
  <span itemprop="recipeInstructions">
  Preheat the oven to 350 degrees. Mix in the ingredients in a bowl. Add
  the flour last. Pour the mixture into a loaf pan and bake for one hour.
  </span>

  140 comments:
  <div itemprop="interactionStatistic" itemscope itemtype="https://schema.org/InteractionCounter">
    <meta itemprop="interactionType" content="https://schema.org/CommentAction" />
    <meta itemprop="userInteractionCount" content="140" />
  </div>
  From Janel, May 5 -- thank you, great recipe!
  ...
</div>
```

## Manually checking websites for metadata

By far not all websites are publishing metadata to help parsing/interpreting the content of the website. This can have different reasons:

- The owner of the website is not able to provide the data (e.g. user provided content)
- The owner is not aware of the metadata and its benefits
- The owner wants to avoid parsing from third parties (protection of own knowlegde/web content)

In order to check a website for schema.org metadata, one has to view the source code of the website. How to do this depends on your browser in use. For Firefox and Chrome you can for example press `Ctrl`+`U`.

First, you should check if the page contains anything related to schema.org. Just search in the code (typically with `Ctrl`+`F`) for the string `schema.org`. If it is not found, sorry, this website is not supported by the import feature of the cookbook app.

Now, the funny part comes. If a page supports schema.org typically multiple different information are give. For example a page could provide the recipe, some information on the author, maybe some information about the company hosting the site etc. Now, you have to check if the page _really_ uses sche,a.org for the recipe description and not only somewhere. 

First you have to check if JSON+LD or microdata is in use. It is pretty obvious as you just have to look for the `itemtype` before the address. If it is there, you have microdata. If not, JSON+LD it is. Of course, if a page provides different classes of metadata (e.g. recipe and information about the company), these can be different formats (one in microdata and one in JSON+LD).

If you are in **microdata**, you have to search for an entry `itemprop` of `schema.org/Recipe`. You can jsut search for `schema.org/Recipe` in the HTML source code. If you find it, the page is should be importable, otherwise, you are out of luck.

Last but not least, there is the **JSON+LD** type of data. This is more complex as this format allows different ways to express the data. First, you should look for `"@context": "https://schema.org"`. Every JSON+LD block needs to define to use the schema.org standard to be recognized. So searching for `schema.org` will you bring to the interesting places. Typically there will be a `"@type": "..."` entry. If that is `Recipe`, you just found a recipe entry. If it is something else, sorry, that entry ist something else.

There are more complex structures with JSON+LD possible. In fact a complete network of interconnected information can be provided. The syntax of this is not trivial and one has to have a closer look. When in doubt, you can still open an issue. In the worst case, you will just get a short answer and refusal if the page is not supporting JSON+LD
























