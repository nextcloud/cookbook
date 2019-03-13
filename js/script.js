(function (OC, window, $, undefined) {
'use strict';

$(document).ready(function () {

var Cookbook = function (baseUrl) {
    this._baseUrl = baseUrl;
    this._recipes = [];
    this._activeRecipe = undefined;
};

Cookbook.prototype = {
    reindex: function () {
        var deferred = $.Deferred();
        var self = this;
        $.ajax({
            url: this._baseUrl + '/reindex',
            method: 'POST',
        }).done(function () {
            deferred.resolve();
        }).fail(function () {
            deferred.reject();
        });
        return deferred.promise();
    },
    find: function (keywords) {
        var deferred = $.Deferred();
        var self = this;
        $.ajax({
            url: this._baseUrl + '/find?keywords=' + keywords,
            method: 'GET',
        }).done(function (recipes) {
            self._recipes = recipes;
            deferred.resolve();
        }).fail(function () {
            deferred.reject();
        });
        return deferred.promise();
    },
    loadHtml: function (id) {
        var deferred = $.Deferred();
        var self = this;
        $.ajax({
            url: this._baseUrl + '/recipe?id=' + id,
            method: 'GET',
        }).done(function (html) {
            deferred.resolve(html);
        }).fail(function () {
            deferred.reject();
        });
        return deferred.promise();
    },
    load: function (id) {
        var self = this;
        this._recipes.forEach(function (recipe) {
            if (recipe.recipe_id === id) {
                recipe.active = true;
                self._activeRecipe = recipe;
            } else {
                recipe.active = false;
            }
        });
    },
    getActive: function () {
        return this._activeRecipe;
    },
    add: function (url) {
        var deferred = $.Deferred();
        var self = this;
        $.ajax({
            url: this._baseUrl + '/add',
            method: 'POST',
            data: 'url=' + url
        }).done(function (recipe) {
            self._recipes.push(recipe);
            self._activeRecipe = recipe;
            self.load(recipe.recipe_id);
            deferred.resolve();
        }).fail(function () {
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
    updateActive: function() {
        var self = this;       
        var activeId = location.hash.replace('#', '');

        if(activeId) {
            this._recipes.forEach(function(recipe) {
                if(recipe.recipe_id == activeId) {
                    self._activeRecipe = recipe;
                }
            });
        } else {
            this._activeRecipe = undefined;
        }
    }
};

// this will be the view that is used to update the html
var View = function (cookbook) {
    this._cookbook = cookbook;
};

View.prototype = {
    renderContent: function () {
        var recipe = this._cookbook.getActive();

        if(!recipe) {
            $('#app-content-wrapper').html('Please pick a recipe');
        } else {
            this._cookbook.loadHtml(recipe.recipe_id)
            .then(function(html) {
                $('#app-content-wrapper').html(html);
            });
        }

    },
    renderNavigation: function () {
        var source = $('#navigation-tpl').html();
        var html = '';
        
        this._cookbook.getAll().forEach(function(recipe) {
            html += source
                .replace(/{{recipe_id}}/g, recipe.recipe_id)
                .replace(/{{name}}/g, recipe.name);
        });

        $('#app-navigation ul').html(html);
       
        // add a new recipe
        var self = this;
        $('#add-recipe').submit(function (e) {
            e.preventDefault();
            
            var url = e.currentTarget.url.value;

            self._cookbook.add(url).done(function() {
                self.render();
            }).fail(function () {
                alert('Could not add recipe');
            });
        });

        // find recipes
        $('#find-recipes').submit(function (e) {
            e.preventDefault();

            self._cookbook.find(e.currentTarget.keywords.value).done(function () {
                self.render();
            }).fail(function (e) {
                alert('Could not search for recipes.');
            });
        });

        // reindex recipes
        $('#reindex-recipes').click(function () {
            self._cookbook.reindex().done(function () {
                self._cookbook.loadAll().done(function() {
                    self.render();
                });
            }).fail(function (e) {
                alert('Could not rebuild recipe index.');
            });
        });

        // load a recipe
        $('#app-navigation ul a').click(function (e) {
            var id = parseInt($(this).data('id'));
            self._cookbook.load(id);
            self.render();
        });
    },
    render: function () {
        this.renderNavigation();
        this.renderContent();
    }
};

var cookbook = new Cookbook(OC.generateUrl('/apps/cookbook'));
var view = new View(cookbook);
cookbook.loadAll().done(function () {
    cookbook.updateActive();
    view.render();
}).fail(function () {
    alert('Could not load recipes');
});


});

})(OC, window, jQuery);
