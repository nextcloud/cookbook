(function (OC, window, $, undefined) {
'use strict';

var appName = 'cookbook';

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
    sendForm: function(form) {
        var url = location.hash.substr(1);
        var data = $(form).serialize();
        var deferred = $.Deferred();
        $.ajax({
            url: this._baseUrl + '/' + url,
            method: form.getAttribute('method'),
            data: data
        }).done(function (response) {
            deferred.resolve(response);
        }).fail(function () {
            deferred.reject();
        });
        return deferred.promise();
    },
    put: function(url, data) {
		var deferred = $.Deferred();
		$.ajax({
			url: this._baseUrl + url,
			method: 'PUT',
			data: data
		}).done(function (response) {
			deferred.resolve(response);
		}).fail(function () {
			deferred.reject();
		});
		return deferred.promise();
    },
    load: function (id) {
        location.hash = id;
    },
    getActiveId: function () {
        return parseInt(location.hash.replace( /[^0-9]/g, '')) || null;
    },
    getActiveRoute: function () {
        return location.hash.substr(1).split('/');
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
        }).fail(function (jqXHR, textStatus, errorThrown) {
            $('#add-recipe .icon-download').show();
            $('#add-recipe .icon-loading').hide();

            deferred.reject(new Error(jqXHR.responseText));
        });
        return deferred.promise();
    },
    getAll: function () {
        return this._recipes;
    },
    loadAll: function () {
        var deferred = $.Deferred();
        var self = this;
        $.get(this._baseUrl + '/recipes').done(function (recipes) {
            self._recipes = recipes;
            deferred.resolve();
        }).fail(function () {
            deferred.reject();
        });

        return deferred.promise();
    },
    setUpdateInterval: function(interval) {
        var self = this;

        $.ajax({
            url: self._baseUrl + '/config',
            method: 'POST',
            data: { 'update_interval': interval }
        }).fail(function(e) {
            alert(t(appName, 'Could not set recipe update interval to {interval}', {interval: interval}));
        });
    },
    setFolder: function(cb) {
        var self = this;

        OC.dialogs.filepicker(
            t(appName, 'Path to your recipe collection'),
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
                }).fail(function(e) {
                    alert(t(appName, 'Could not set recipe folder to {path}', {path: path}));
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
		var route = location.hash.substr(1);
		
		if(route.length === 0) {
			$('#app-content-wrapper').load(cookbook._baseUrl + '/home');
		} else {
            $.ajax({
                url: cookbook._baseUrl + '/' + route,
                method: 'GET',
            })
            .done(function (html) {
                $('#app-content-wrapper').html(html);
				
				$('#pick-image').off('click');
				$('#pick-image').click(self.onPickImage);
				
				$('#app-content form').on('submit', self.onSubmitForm);
				$('#app-content-wrapper form .icon-add').off('click');
				$('#app-content-wrapper form .icon-add').click(self.onAddListItem);
				
				$('#app-content-wrapper form ul li input[type="text"]').off('keypress');
				$('#app-content-wrapper form ul li input[type="text"]').on('keypress', self.onListInputKeyDown);
				
				$('#print-recipe').click(self.onPrintRecipe);
				$('#delete-recipe').click(self.onDeleteRecipe);
				
				self.updateListItems();
				
                nav.highlightActive();
            })
            .fail(function(e) {
				$('#app-content-wrapper').load(cookbook._baseUrl + '/error');
				
                if(e && e instanceof Error) { throw e; }
            });
		}
    };

    /**
     * Event: Pick image
     */
    self.onPickImage = function(e) {
        e.preventDefault();

        OC.dialogs.filepicker(
            t(appName, 'Path to your Recipe Image'),
            function (path) {
                $('input[name="image"]').val(path);
            },
            false,
            'image/jpeg',
            true,
            OC.dialogs.FILEPICKER_TYPE_CHOOSE
        );
    }

    /**
     * Event: Delete recipe
     */
    self.onDeleteRecipe = function(e) {
        if(!confirm(t(appName, 'Are you sure you want to delete this recipe?'))) { return; }

        var id = e.currentTarget.dataset.id;

        $.ajax({
            url: cookbook._baseUrl + '/recipes/' + id,
            method: 'DELETE',
        })
        .done(function(html) {
            if(cookbook.getActiveId() == id) {
                location.hash = '';
            }

            self.render();
            nav.render();
        })
        .fail(function(e) {
            alert(t(appName, 'Failed to delete recipe'));

            if(e && e instanceof Error) { throw e; }
        });
    };

    /**
     * Event: Print recipe
     */
    self.onPrintRecipe = function(e) {
        window.print();
    };

    /**
     * Updates all lists items with click events
     */
    self.updateListItems = function(e) {
        $('#app-content-wrapper form .icon-delete').off('click');
        $('#app-content-wrapper form .icon-delete').click(self.onDeleteListItem);

        $('#app-content-wrapper form ul li input[type="text"]').off('keypress');
        $('#app-content-wrapper form ul li input[type="text"]').on('keypress', self.onListInputKeyDown);
    }

    /**
     * Event: Click delete list element
     */
    self.onDeleteListItem = function(e) {
        e.preventDefault();

        e.currentTarget.parentElement.parentElement.removeChild(e.currentTarget.parentElement);
    };

    /**
     * Event: Keydown on a list itme input
     */
    self.onListInputKeyDown = function(e) {
        if(e.keyCode === 13 || e.keyCode === 10) {
            e.preventDefault();

            var $li = $(e.currentTarget).parents('li');
            var $ul = $li.parents('ul');

            if($li.index() >= $ul.children('li').length) {
                self.onAddListItem(e);

            } else {
                $ul.children('li').eq($li.index()).find('input').focus();

            }
        }
    };

    /**
     * Event: Click add list item
     */
    self.onAddListItem = function(e) {
        e.preventDefault();

        var $ul = $(e.currentTarget).closest('fieldset').children('ul');
        var template = $ul.find('template').html();
        var $item = $(template);

        $ul.append($item);

        $item.find('input').focus();

        self.updateListItems();
    };

    /**
     * Event: Update recipe
     */
    self.onSubmitForm = function(e) {
        e.preventDefault();

        var route = cookbook.getActiveRoute();
        var data = $(e.currentTarget).serialize();

        cookbook.sendForm(e.currentTarget)
        .then(function(route) {
			location.hash = route;
            self.render();
            nav.render();
        })
        .fail(function(e) {
            alert(t(appName, 'Could not update recipe') + (e instanceof Error ? ': ' + e.message : ''));

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
     * Event: Change recipe update interval
     */
    self.onChangeRecipeUpdateInterval = function(e) {
        cookbook.setUpdateInterval(e.currentTarget.value);
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
        .fail(function(e) {
            alert(t(appName, 'Could not add recipe') + (e instanceof Error ? ': ' + e.message : ''));

            if(e && e instanceof Error) { throw e; }
        });
    };

    /**
     * Event: Pick a tag
     */
    self.onCategorizeRecipes = function(e) {
        e.preventDefault();

        self.render();
    };

    /**
     * Event: Submit new search query
     */
    self.onFindRecipes = function(query) {
		if(query) {
	        location.hash = '#search/' + encodeURIComponent(query);
		} else {
			location.hash = '#';
		}

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
        .fail(function(e) {
            alert(t(appName, 'Could not rebuild recipe index.') + (e instanceof Error ? ': ' + e.message : ''));

            if(e && e instanceof Error) { throw e; }
        });
    };

    /**
     * Event: Clear recipe search
     */
    self.onClearRecipeSearch = function() {
        location.hash = '#';
    }

    /**
     * Get the current input keywords
     *
     * @return {String} Keywords
     */
    self.getKeywords = function() {
        return [self.query].join(',');
    }

    /**
     * Highlight the active item
     */
    self.highlightActive = function() {
        $('#app-navigation #categories a').each(function() {
            $(this).toggleClass('active', $(this).attr('href') === location.hash);
        });
    }

    /**
     * Render the view
     */
    self.render = function () {
        $.ajax({
            url: cookbook._baseUrl + '/recipes?keywords=' + self.getKeywords(),
            method: 'GET',
        })
        .done(function(json) {
            var html = json.map(function (recipeData) {
                var recipeEntry = '<li>';
                recipeEntry += '<a href="#'+recipeData.recipe_id+'">';
                recipeEntry += '<img src="'+recipeData.image_url+'">';
                recipeEntry += recipeData.name;
                recipeEntry += '</a></li>';
                return recipeEntry;

            }).join("\n");

            $('#app-navigation #recipes').html(html);

            self.highlightActive();
        })
        .fail(function(e) {
            alert(t(appName, 'Failed to fetch recipes'));

            if(e && e instanceof Error) { throw e; }
        });

        // Change cache update interval
        $('#recipe-update-interval').off('change');
        $('#recipe-update-interval').change(self.onChangeRecipeUpdateInterval);

        // Change recipe folder
        // $('#recipe-folder').off('change');
        $('#recipe-folder').change(self.onChangeRecipeFolder);

        // Categorise recipes
        $('#categorize-recipes select').off('change');
        $('#categorize-recipes select').on('change', self.onCategorizeRecipes);

        // Clear recipe search
        $('#clear-recipe-search').off('click');
        $('#clear-recipe-search').click(self.onClearRecipeSearch);

        // Reindex recipes
        $('#reindex-recipes').off('click');
        $('#reindex-recipes').click(self.onReindexRecipes);

    };

    this.search = new OCA.Search(self.onFindRecipes, self.onClearRecipeSearch);
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
