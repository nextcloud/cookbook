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
    /**
     * Reindexes all recipes
     */
    reindex: function () {
        var deferred = $.Deferred();
        var self = this;
        $.ajax({
            url: this._baseUrl + '/reindex',
            method: 'POST'
        }).done(function () {
            deferred.resolve();
        }).fail(function (jqXHR, textStatus, errorThrown) {
            deferred.reject(new Error(jqXHR.responseText));
        });
        return deferred.promise();
    },

    /**
     * Updates a recipe with form data
     */
    update: function(form) {
		var action = form.getAttribute('action');
		var url = action === '#' ? location.hash.substr(1) : action;
        var data = $(form).serialize();
        var deferred = $.Deferred();
		
        $.ajax({
            url: this._baseUrl + '/' + url,
            method: form.getAttribute('method'),
            data: data
        }).done(function (response) {
            deferred.resolve(response);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            deferred.reject(new Error(jqXHR.responseText));
        });
    
        return deferred.promise();
    },
    
    /**
     * Loads a recipe by id
     *
     * @param {String} id
     */
    load: function (id) {
        location.hash = id;
    },

    /**
     * Gets the loaded recipe id
     *
     * @return {String} Id
     */
    getActiveId: function () {
        return parseInt(location.hash.replace( /[^0-9]/g, '')) || null;
    },

    /**
     * Imports a recipe from a URL
     *
     * @param {String} url
     */
    import: function (url) {
        var deferred = $.Deferred();
        var self = this;

        $('#import-recipe .icon-download').hide();
        $('#import-recipe .icon-loading').show();

        $.ajax({
            url: this._baseUrl + '/import',
            method: 'POST',
            data: 'url=' + url
        }).done(function (recipe) {
            $('#import-recipe .icon-download').show();
            $('#import-recipe .icon-loading').hide();

            location.hash = 'recipes/' + recipe.id;
            deferred.resolve();
        }).fail(function (jqXHR, textStatus, errorThrown) {
            $('#import-recipe .icon-download').show();
            $('#import-recipe .icon-loading').hide();

            deferred.reject(new Error(jqXHR.responseText));
        });
        return deferred.promise();
    },

    /**
     * Gets all recipes
     *
     * @return {Array} Recipes
     */
    getAll: function () {
        return this._recipes;
    },

    /**
     * Loads all recipes for display
     */
    loadAll: function () {
        var deferred = $.Deferred();
        var self = this;
        $.get(this._baseUrl + '/recipes').done(function (recipes) {
            self._recipes = recipes;
            deferred.resolve();
        }).fail(function (jqXHR, textStatus, errorThrown) {
            deferred.reject(new Error(jqXHR.responseText));
        });

        return deferred.promise();
    },

    /**
     * Sets the config update interval
     *
     * @param {Number} interval
     */
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

    /**
     * Sets the recipe base directory using a callback
     *
     * @param {Function} cb
     */
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
    },

    /**
     * Shows a notification to the user
     *
     * @param {String} title
     * @param {Object} options
     */
	notify: function notify(title, options) {
		if(!('Notification' in window)) {
			return;
		} else if(Notification.permission === "granted") {
			var notification = new Notification(title, options);
		} else if(Notification.permission !== 'denied') {
			Notification.requestPermission(function(permission) {
				if(!('permission' in Notification)) {
					Notification.permission = permission;
				}
				if(permission === "granted") {
					var notification = new Notification(title, options);
				} else {
					alert(title);
				}
			});
		}
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
			route = 'home';
		}
        $.ajax({
            url: cookbook._baseUrl + '/' + route,
            method: 'GET',
        })
        .done(function (html) {
            $('#app-content-wrapper').html(html);
			
            // Common
            $('#print-recipe').click(self.onPrintRecipe);
			$('#delete-recipe').click(self.onDeleteRecipe);
					
            // Editor
            $('#app-content-wrapper form').off('submit');
            $('#app-content-wrapper form').submit(self.onUpdateRecipe);

            $('#pick-image').off('click');
			$('#pick-image').click(self.onPickImage);
			
			$('#app-content-wrapper form ul + button.add-list-item').off('click');
			$('#app-content-wrapper form ul + button.add-list-item').click(self.onAddListItem);
			
			$('#app-content-wrapper form ul li input[type="text"]').off('keypress');
			$('#app-content-wrapper form ul li input[type="text"]').on('keypress', self.onListInputKeyDown);
			
			$('#app-settings [title]').tooltip('destroy');
			$('#app-settings [title]').tooltip();
			
            self.updateListItems();
			
            // View
            $('header img').click(self.onImageClick);
			$('main .instruction').click(self.onInstructionClick);
			$('.time button').click(self.onTimerToggle);
			
            nav.highlightActive();
        })
        .fail(function(e) {
			$('#app-content-wrapper').load(cookbook._baseUrl + '/error');
			
            if(e && e instanceof Error) { throw e; }
        });
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
            ['image/jpeg', 'image/png'],
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
            url: cookbook._baseUrl + '/api/recipes/' + id,
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
     * Event: click on a recipe instruction
     */
    self.onInstructionClick = function(e) {
        $(e.target).toggleClass('done');
    }

    /**
     * Event: click the recipe's image
     */
    self.onImageClick = function(e) {
        $(e.target).parent().toggleClass('collapsed');
    }

    /**
     * Event: Toggle timer
     */
    self.onTimerToggle = function(e) {
		if($(e.target).hasClass('icon-play')) {
			var hours = parseInt($(e.target).data('hours'));
			var minutes = parseInt($(e.target).data('minutes'));
			var seconds = 0;

			if(
                (self.hours === undefined || self.hours === hours) &&
                (self.minutes === undefined || self.minutes === minutes)
            ) {
				self.hours = hours;
				self.minutes = minutes;
                self.seconds = 0;
			}
			
            self.timer = window.setInterval(function() {
				self.seconds--;

                if(self.seconds <= 0) {
                    self.seconds = 59;
                    self.minutes--;
                }

                if(self.minutes <= 0) {
                    self.minutes = 59;
                    self.hours--;
                }

                var text = '';

                if(self.hours < 10) { text += '0'; }
                text += self.hours + ':';
                
                if(self.minutes < 10) { text += '0'; }
                text += self.minutes + ':';

                if(self.seconds < 10) { text += '0'; }
                text += self.seconds;
				
                $(e.target).closest('.time').find('p').text(text);

				if(self.hours < 0 || self.minutes < 0) {
					self.onTimerEnd(e.target);
				}
			}, 1000);
		} else {
			window.clearInterval(self.timer);
		}
		$(e.target).toggleClass('icon-play icon-pause');
    }

    /**
     * Event: Timer ended
     */
	self.onTimerEnd = function(button) {
		window.clearInterval(self.timer);
		$(button).removeClass('icon-pause').addClass('icon-play');
		cookbook.notify(t(appName, 'Cooking time is up!'));
	}

    /**
     * Updates all lists items with click events
     */
    self.updateListItems = function(e) {
        $('#app-content-wrapper form .remove-list-item').off('click');
        $('#app-content-wrapper form .remove-list-item').click(self.onDeleteListItem);
        
        $('#app-content-wrapper form .move-list-item-up').off('click');
        $('#app-content-wrapper form .move-list-item-up').click(self.onMoveListItemUp);
        
        $('#app-content-wrapper form .move-list-item-down').off('click');
        $('#app-content-wrapper form .move-list-item-down').click(self.onMoveListItemDown);

        $('#app-content-wrapper form ul li input[type="text"]').off('keypress');
        $('#app-content-wrapper form ul li input[type="text"]').on('keypress', self.onListInputKeyDown);
    }

    /**
     * Event: Click delete list element
     */
    self.onDeleteListItem = function(e) {
        e.preventDefault();

        var button = e.currentTarget;
        var tools = button.parentElement;
        var listItem = tools.parentElement;
        var list = listItem.parentElement;

        list.removeChild(listItem);
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
     * Event: Click move list item up
     */
    self.onMoveListItemUp = function(e) {
        e.preventDefault();

        var button = e.currentTarget;
        var tools = button.parentElement;
        var listItem = tools.parentElement;

        if(!listItem.previousElementSibling) {
            return;
        }

        $(listItem).insertBefore($(listItem.previousElementSibling));
    };
    
    /**
     * Event: Click move list item down
     */
    self.onMoveListItemDown = function(e) {
        e.preventDefault();

        var button = e.currentTarget;
        var tools = button.parentElement;
        var listItem = tools.parentElement;
        
        if(!listItem.nextElementSibling) {
            return;
        }

        $(listItem).insertAfter($(listItem.nextElementSibling));
    };

    /**
     * Event: Update recipe
     */
    self.onUpdateRecipe = function(e) {
        e.preventDefault();
		
        cookbook.update(e.currentTarget)
        .then(function(id) {
			location.hash = '/recipes/' + id;
            self.render();
            nav.render();
        })
        .fail(function(e) {
            alert(t(appName, 'Could not update recipe') + (e instanceof Error ? ': ' + e.message : ''));

            if(e && e instanceof Error) { throw e; }
        });
		return false;
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
     * Event: Import new recipe
     */
    self.onImportRecipe = function(e) {
        e.preventDefault();

        var url = e.currentTarget.url.value;

        cookbook.import(url)
        .done(function() {
            self.render();
        })
        .fail(function(e) {
            alert(t(appName, 'Could not add recipe') + (e instanceof Error ? ': ' + e.message : ''));

            if(e && e instanceof Error) { throw e; }
        });
    };

    /**
     * Event: Pick a category
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
            $(this).toggleClass('active', $(this).attr('href').substr(1) === location.hash.substr(1));
        });
    }

    /**
     * Render the view
     */
    self.render = function () {
        $.ajax({
            url: cookbook._baseUrl + '/categories',
            method: 'GET',
        })
        .done(function(json) {
            json = json || [];

            var html = '<li class="icon-category-organization"><a href="#">' + t(appName, 'All recipes') + '</a></li>';
			
			html += json.map(function(category) {
                var entry = '<li class="icon-category-files">';
                entry += '<a href="#category/' + encodeURIComponent(category.name) + '">';
                entry += '<span class="pull-right">' + category.recipe_count + '</span>';
                entry += category.name === '*' ? t(appName, 'No category') : category.name;
                entry += '</a></li>';
                return entry;
            }).join("\n");
			
            $('#app-navigation #categories').html(html);

            self.highlightActive();
        })
        .fail(function(e) {
            alert(t(appName, 'Failed to fetch categories'));

            if(e && e instanceof Error) { throw e; }
        });

        // Add a new recipe
        $('#import-recipe').off('submit');
        $('#import-recipe').submit(self.onImportRecipe);

        // Change cache update interval
        $('#recipe-update-interval').off('change');
        $('#recipe-update-interval').change(self.onChangeRecipeUpdateInterval);

        // Change recipe folder
        $('#recipe-folder').off('change');
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
