(function (OC, window, $, undefined) {
'use strict';

$(document).ready(function () {

/**
 * The API helper
 */
var Cookbook = function (baseUrl) {
    this._baseUrl = baseUrl;
};

Cookbook.prototype = {
    reindex: function () {
        var deferred = $.Deferred();
        var self = this;
        $.ajax({
            url: this._baseUrl + '/reindex',
            method: 'POST'
        }).done(function () {
            deferred.resolve();
        }).fail(function () {
            deferred.reject();
        });
        return deferred.promise();
    },
    update: function(json) {
        var deferred = $.Deferred();
        var self = this;
        $.ajax({
            url: this._baseUrl + '/update',
            method: 'POST',
            data: json
        }).done(function () {
            deferred.resolve();
        }).fail(function () {
            deferred.reject();
        });
        return deferred.promise();
    },
    load: function (id) {
        location.hash = id;
    },
    getActiveId: function () {
        return parseInt(location.hash.replace( /[^0-9]/g, ''));
    },
    add: function (url) {
        var deferred = $.Deferred();
        var self = this;

        $('#add-recipe .icon-download').hide();
        $('#add-recipe .icon-loading').show();

        $.ajax({
            url: this._baseUrl + '/add',
            method: 'POST',
            data: 'url=' + url
        }).done(function (recipe) {
            $('#add-recipe .icon-download').show();
            $('#add-recipe .icon-loading').hide();
            
            location.hash = recipe.id;
            deferred.resolve();
        }).fail(function () {
            $('#add-recipe .icon-download').show();
            $('#add-recipe .icon-loading').hide();
            
            deferred.reject();
        });
        return deferred.promise();
    },
    getAll: function () {
        return this._recipes;
    },
    loadAll: function () {
        var deferred = $.Deferred();
        var self = this;
        $.get(this._baseUrl + '/all').done(function (recipes) {
            self._recipes = recipes;
            deferred.resolve();
        }).fail(function () {
            deferred.reject();
        });
        
        return deferred.promise();
    },
    setFolder: function(cb) {
        var self = this;

        OC.dialogs.filepicker(
            'Path to your recipe collection',
            function (path) {
                $.ajax({
                    url: self._baseUrl + '/config',
                    method: 'POST',
                    data: { 'folder': path },
                }).done(function () {
                    self.loadAll()
                    .then(function() {
                        self._activeRecipe = null;
                        location.hash = '';

                        cb(path);
                    });
                }).fail(function () {
                    alert('Could not set recipe folder to ' + path);
                    cb(null);
                });
            },
            false,
            'httpd/unix-directory',
            true
        );
    }
};

/**
 * The content view
 */
var Content = function (cookbook) {
    var self = this;

    /**
     * Render
     */
    self.render = function () {
        var recipeId = cookbook.getActiveId();
        var isEditor = location.hash.indexOf('|edit') > -1;

        if(!recipeId) {
            $('#app-content-wrapper').html('Please pick a recipe');
        } else {
            $.ajax({
                url: cookbook._baseUrl + '/' + (isEditor ? 'edit' : 'recipe') + '?id=' + recipeId,
                method: 'GET',
            })
            .done(function (html) {
                $('#app-content-wrapper').html(html);
                
                $('#app-content-wrapper form .icon-add').off('click');
                $('#app-content-wrapper form .icon-add').click(self.onAddListItem);

                $('#app-content-wrapper form').off('submit');
                $('#app-content-wrapper form').submit(self.onUpdateRecipe);

                self.updateListItems();
            })
            .fail(function (e) {
                alert('Could not load recipe');

                if(e && e instanceof Error) { throw e; }
            });
        }
    };

    /**
     * Updates all lists items with click events
     */
    self.updateListItems = function(e) {
        $('#app-content-wrapper form .icon-delete').off('click');
        $('#app-content-wrapper form .icon-delete').click(self.onDeleteListItem);
    }

    /**
     * Event: Click delete list element
     */
    self.onDeleteListItem = function(e) {
        e.preventDefault();

        e.currentTarget.parentElement.parentElement.removeChild(e.currentTarget.parentElement);
    };

    /**
     * Event: Click add list item
     */
    self.onAddListItem = function(e) {
        e.preventDefault();

        var html = $(e.currentTarget).parents('ul').find('template').html();

        $(html).insertBefore($(e.currentTarget));

        self.updateListItems();
    };

    /**
     * Event: Update recipe
     */
    self.onUpdateRecipe = function(e) {
        e.preventDefault();

        var data = $(e.currentTarget).serialize();

        cookbook.update(data)
        .then(function() {
            location.hash = location.hash.replace(/[^0-9]+/g, '');

            self.render();
        })
        .catch(function(e) {
            alert('Could not update recipe');
            
            if(e && e instanceof Error) { throw e; }
        });
    };
};

/**
 * The navigation view
 */
var Nav = function (cookbook) {
    var self = this;
    
    /**
     * Event: Change recipe folder
     */
    self.onChangeRecipeFolder = function(e) {
        cookbook.setFolder(function(path) {
            e.currentTarget.value = path;
            
            self.render();
        });
    };
    
    /**
     * Event: Submit new recipe
     */
    self.onAddNewRecipe = function(e) {
        e.preventDefault();
        
        var url = e.currentTarget.url.value;

        cookbook.add(url)
        .done(function() {
            self.render();
        })
        .fail(function () {
            alert('Could not add recipe');
        });
    };

    /**
     * Event: Submit new search query
     */
    self.onFindRecipes = function(e) {
        e.preventDefault();

        self.render();
    };

    /**
     * Event: Reindex database
     */
    self.onReindexRecipes = function(e) {
        cookbook.reindex()
        .done(function () {
            self.render();
        })
        .fail(function (e) {
            alert('Could not rebuild recipe index.');
        });
    };

    /**
     * Event: Edit recipe
     */
    self.onEditRecipe = function(e) {
        location.hash = e.currentTarget.dataset.id + '|edit';
    };

    /**
     * Event: Delete recipe
     */
    self.onDeleteRecipe = function(e) {
        if(!confirm('Are you sure you want to delete this recipe?')) { return; }

        var id = e.currentTarget.dataset.id;
        
        $.ajax({
            url: cookbook._baseUrl + '/delete?id=' + id,
            method: 'DELETE',
        })
        .done(function(html) {
            if(cookbook.getActiveId() == id) {
                location.hash = '';        
            }

            self.render();
        })
        .fail(function(e) {
            alert('Failed to delete recipe');

            if(e && e instanceof Error) { throw e; }
        });
    };

    /**
     * Get the current input keywords
     *
     * @return {String} Keywords
     */
    self.getKeywords = function() {
        return $('#find-recipes input').val();
    }

    /**
     * Render the view
     */
    self.render = function () {
        $.ajax({
            url: cookbook._baseUrl + '/recipes?keywords=' + self.getKeywords(),
            method: 'GET',
        })
        .done(function(html) {
            $('#app-navigation #recipes').html(html);

            $('#app-navigation #recipes .button-edit button').click(self.onEditRecipe);
            
            $('#app-navigation #recipes .button-delete button').click(self.onDeleteRecipe);
        })
        .fail(function(e) {
            alert('Failed to fetch recipes');

            if(e && e instanceof Error) { throw e; }
        });

        // Change recipe folder
        $('#recipe-folder').off('click');
        $('#recipe-folder').click(self.onChangeRecipeFolder);

        // Add a new recipe
        $('#add-recipe').off('submit');
        $('#add-recipe').submit(self.onAddNewRecipe);

        // Find recipes
        $('#find-recipes').off('submit');
        $('#find-recipes').submit(self.onFindRecipes);

        // Reindex recipes
        $('#reindex-recipes').off('click');
        $('#reindex-recipes').click(self.onReindexRecipes);
    };
}

var cookbook = new Cookbook(OC.generateUrl('/apps/cookbook'));
var nav = new Nav(cookbook);
var content = new Content(cookbook);

// Render the views
nav.render();
content.render();

// Render content view on hash change
window.addEventListener('hashchange', content.render); 

});

})(OC, window, jQuery);
