<?php
namespace OCA\Cookbook\Entity;

class RecipeEntity
{
    
    /**
     * The JSON representation of the recipe
     * @var string
     */
    private $json;
    
    /**
     * Indicates if the entity has any unsaved changes
     * @var bool
     */
    private $changed;
    
    /**
     * Get the JSON representing the recipe
     * @return string A string representing the internal structure
     */
    public function getJSON() : string
    {
        return json_encode($this->json);
    }
    
    
    //// ************************** Getters and Setters of basic JSON data
    
    /**
     * Get the name of the recipe
     * @return string The name of the recipe
     */
    public function getName() : string
    {
        return $this->json['name'];
    }
    
    /**
     * Set the name of the recipe
     * @param string The new name of the recipe
     */
    public function setName(string $name) : void
    {
        $this->json['name'] = $name;
        $this->changed = true;
    }
    
    /**
     * Get the description of the recipe
     * @return string The description
     */
    public function getDescription() : string
    {
        return $this->json['description'];
    }
    
    /**
     * Set the description of the recipe
     * @param string The new description
     */
    public function setDescription(string $description) : void
    {
        $this->json['description'] = $description;
        $this->changed = true;
    }
    
    /**
     * Get the URL of the recipe
     * @return string The URL of the recipe
     */
    public function getUrl() : string
    {
        return $this->json['url'];
    }
    
    /**
     * Set the URL of the recipe
     * @param string The new URL
     */
    public function setUrl(string $url) : void
    {
        $this->json['url'] = $url;
        $this->changed = true;
    }
    
    /**
     * Get the amount of servings the recipe yields
     * @return int The amount of servings
     */
    public function getYield() : int
    {
        return $this->json['recipeYield'];
    }
    
    /**
     * Set the amount of portions teh recipe yields
     * @param int The amount of portions
     */
    public function setYield(int $yield) : void
    {
        $this->json['recipeYield'] = (int) $yield;
        $this->changed = true;
    }
    
    /**
     * Get the preparation time for the recipe
     * @return string The preparation time
     */
    public function getPrepTime() : string
    {
        return $this->json['prepTime'];
    }
    
    /**
     * Set the preparation time of a recipe
     * @param string The new preparation time
     */
    public function setPrepTime(string $prepTime) : void
    {
        $this->json['prepTime'] = $prepTime;
        $this->changed = true;
    }
    /**
     * Get the cooking time for the recipe
     * @return string The cooking time
     */
    public function getCookTime() : string
    {
        return $this->json['cookTime'];
    }
    
    /**
     * Set the cooking time of a recipe
     * @param string The new cooking time
     */
    public function setPrepTime(string $cookTime) : void
    {
        $this->json['prepTime'] = $cookTime;
        $this->changed = true;
    }
    /**
     * Get the total time for the recipe
     * @return string The total time
     */
    public function getTotalTime() : string
    {
        return $this->json['totalTime'];
    }
    
    /**
     * Set the total time of a recipe
     * @param string The new total time
     */
    public function setTotalTime(string $totalTime) : void
    {
        $this->json['prepTime'] = $totalTime;
        $this->changed = true;
    }
    
    /**
     * Get the ingredients of the recipe
     * @return array The ingredients
     */
    public function getIngredients() : array
    {
        return $this->json['recipeIngredient'];
    }
    
    /**
     * Set the ingredients of a recipe
     * @param array The list of ingredients of the recipe
     */
    public function setIngredients(array $ingredients) : void
    {
        $this->json['recipeIngredient'] = $ingredients;
        $this->changed = true;
    }
    
    /**
     * Get the tools of the recipe
     * @return array The tools
     */
    public function getTools() : array
    {
        return $this->json['tool'];
    }
    
    /**
     * Set the tools of a recipe
     * @param array The list of tools of the recipe
     */
    public function setIngredients(array $tools) : void
    {
        $this->json['tool'] = $tools;
        $this->changed = true;
    }
    
    /**
     * Get the instructions of the recipe
     * @return array The instructions
     */
    public function getInstructions() : array
    {
        return $this->json['recipeInstructions'];
    }
    
    /**
     * Set the instructions of a recipe
     * @param array The list of instructions of the recipe
     */
    public function setIngredients(array $instructions) : void
    {
        $this->json['recipeInstructions'] = $instructions;
        $this->changed = true;
    }
    
    //// ****************************** Getters and setters for the category and keywords
    
    /**
     * Get the category of a recipe
     * @todo Use a category class instead of strings
     * @return string The category of the recipe
     */
    public function getCategory() : string
    {
        return $this->json['recipeCategory'];
    }
    
    /**
     * Set the category of a recipe
     * @todo Use category class instead of string
     * @param string The new category
     */
    public function setCategory(string $category) : void
    {
        $this->json['recipeCategory'] = $category;
        $this->changed = false;
    }
    
    /**
     * Get the keywords of the recipe
     * @todo Use keyword call instead of strings
     * @return array The keywords of the recipe
     */
    public function getKeywords() : array
    {
        $keywords = $this->json['keywords'];
        
        if(strlen(trim($keywords)) == 0)
            return array();
        
        return explode(',', $keywords);
    }
    
    /**
     * Set the keywords of the recipe
     * @todo Use Keyword call instead of strings
     * @param array The keywords as an array of strings
     */
    public function setKeywords(array $keywords) : void
    {
        $this->json['keywords'] = implode(',', $keywords);
        $this->changed = true;
    }
}
