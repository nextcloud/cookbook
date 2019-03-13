(function (OC, window, $, undefined) {
'use strict';

$(document).ready(function () {

var translations = {
    newRecipe: $('#new-recipe-string').text()
};

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
        return deferred.promise()
        .then(this.loadAll);
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
    removeActive: function () {
        var index;
        var deferred = $.Deferred();
        var id = this._activeRecipe.id;
        this._recipes.forEach(function (recipe, counter) {
            if (recipe.recipe_id === id) {
                index = counter;
            }
        });

        if (index !== undefined) {
            // delete cached active recipe if necessary
            if (this._activeRecipe === this._recipes[index]) {
                delete this._activeRecipe;
            }

            this._recipes.splice(index, 1);

            $.ajax({
                url: this._baseUrl + '/' + id,
                method: 'DELETE'
            }).done(function () {
                deferred.resolve();
            }).fail(function () {
                deferred.reject();
            });
        } else {
            deferred.reject();
        }
        return deferred.promise();
    },
    create: function (recipe) {
        var deferred = $.Deferred();
        var self = this;
        $.ajax({
            url: this._baseUrl,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(recipe)
        }).done(function (recipe) {
            self._recipes.push(recipe);
            self._activeRecipe = recipe;
            self.load(recipe.id);
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
    updateActive: function (title, content) {
        var recipe = this.getActive();
        recipe.title = title;
        recipe.content = content;

        return $.ajax({
            url: this._baseUrl + '/' + recipe.id,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(recipe)
        });
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
       
        // create a new recipe
        var self = this;
        $('#new-recipe').click(function () {
            var recipe = {
                title: translations.newRecipe,
                content: ''
            };

            self._cookbook.create(recipe).done(function() {
                self.render();
                $('#editor textarea').focus();
            }).fail(function () {
                alert('Could not create recipe');
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
                self.render();
            }).fail(function (e) {
                alert('Could not rebuild recipe index.');
            });
        });

        // show app menu
        $('#app-navigation .app-navigation-entry-utils-menu-button').click(function () {
            var entry = $(this).closest('.recipe');
            entry.find('.app-navigation-entry-menu').toggleClass('open');
        });

        // delete a recipe
        $('#app-navigation .recipe .delete').click(function () {
            var entry = $(this).closest('.recipe');
            entry.find('.app-navigation-entry-menu').removeClass('open');

            self._cookbook.removeActive().done(function () {
                self.render();
            }).fail(function () {
                alert('Could not delete recipe, not found');
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
    view.render();
}).fail(function () {
    alert('Could not load recipes');
});


});

})(OC, window, jQuery);
