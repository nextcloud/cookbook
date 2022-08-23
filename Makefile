# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
# @author Bernhard Posselt <dev@bernhard-posselt.com>
# @copyright Bernhard Posselt 2016

# Generic Makefile for building and packaging a Nextcloud app which uses npm and
# Composer.
#
# Dependencies:
# * make
# * which
# * curl: used if phpunit and composer are not installed to fetch them from the web
# * tar: for building the archive
# * npm: for building and testing everything JS
#
# The npm command by launches the npm build script:
#
#    npm run build
#
# The idea behind this is to be completely testing and build tool agnostic. All
# build tools and additional package managers should be installed locally in
# your project, since this won't pollute people's global namespace.
#
# The following npm scripts in your package.json install and update the bower
# and npm dependencies and use gulp as build system (notice how everything is
# run from the node_modules folder):
#
#    "scripts": {
#        "test": "node node_modules/gulp-cli/bin/gulp.js karma",
#        "prebuild": "npm install && node_modules/bower/bin/bower install && node_modules/bower/bin/bower update",
#        "build": "node node_modules/gulp-cli/bin/gulp.js"
#    },

app_name=$(notdir $(CURDIR))
build_tools_directory=$(CURDIR)/build/tools
source_build_directory=$(CURDIR)/build/artifacts/source
source_package_name=$(source_build_directory)/$(app_name)
appstore_build_directory=$(CURDIR)/build/artifacts/appstore
appstore_package_name=$(appstore_build_directory)/$(app_name)
npm=$(shell which npm 2> /dev/null)
composer=$(shell which composer 2> /dev/null)

ifeq (, $(composer))
	composer_bin:=php $(build_tools_directory)/composer.phar
else
	composer_bin:=composer
endif

all: build appinfo/info.xml

# Fetches the PHP and JS dependencies and compiles the JS. If no composer.json
# is present, the composer step is skipped, if no package.json or js/package.json
# is present, the npm step is skipped
.PHONY: build
build: prepare_phpunit composer npm

.PHONY: install_composer
install_composer:
ifeq (, $(composer))
	@echo "No composer command available, downloading a copy from the web"
	mkdir -p $(build_tools_directory)
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar $(build_tools_directory)
endif

.PHONY: prepare_phpunit
prepare_phpunit: install_composer
	cd tests/phpunit && $(composer_bin) install --prefer-dist

# Installs and updates the composer dependencies. If composer is not installed
# a copy is fetched from the web
.PHONY: composer
composer: install_composer
	$(composer_bin) install --prefer-dist
	$(composer_bin) update --prefer-dist

.PHONY: composer_dist
composer_dist: install_composer
	$(composer_bin) install --prefer-dist --no-dev

# Installs npm dependencies
.PHONY: npm
npm:
	npm run build

# Removes the appstore build
.PHONY: clean
clean:
	rm -rf ./build

# Same as clean but also removes dependencies installed by composer, bower and
# npm
.PHONY: distclean
distclean: clean
	rm -rf vendor node_modules tests/phpunit/vendor

# Builds the source and appstore package
.PHONY: dist
dist:
	$(MAKE) source
	$(MAKE) appstore

# Builds the source package
.PHONY: source
source: appinfo/info.xml
	rm -rf $(source_build_directory)
	mkdir -p $(source_build_directory)
	tar cvzf $(source_package_name).tar.gz \
	--exclude-vcs \
	--exclude="../$(app_name)/build" \
	--exclude="../$(app_name)/js/node_modules" \
	--exclude="../$(app_name)/node_modules" \
	--exclude="../$(app_name)/*.log" \
	--exclude="../$(app_name)/js/*.log" \
	--exclude="../$(app_name)/.hooks" \
	--exclude="../$(app_name)/.github/actions/run-tests/volumes" \
	--exclude="../$(app_name)/cookbook.code-workspace" \
	../$(app_name)

# Builds the source package for the app store, ignores php and js tests
.PHONY: appstore
appstore: appinfo/info.xml
	rm -rf $(appstore_build_directory)
	mkdir -p $(appstore_build_directory)
	tar cvzf $(appstore_package_name).tar.gz \
	--exclude-vcs \
	--exclude="../$(app_name)/build" \
	--exclude="../$(app_name)/docs" \
	--exclude="../$(app_name)/documentation" \
	--exclude="../$(app_name)/tests" \
	--exclude="../$(app_name)/Makefile" \
	--exclude="../$(app_name)/*.log" \
	--exclude="../$(app_name)/phpunit*xml" \
	--exclude="../$(app_name)/composer.*" \
	--exclude="../$(app_name)/node_modules" \
	--exclude="../$(app_name)/src" \
	--exclude="../$(app_name)/translationfiles" \
	--exclude="../$(app_name)/draft-release.sh" \
	--exclude="../$(app_name)/package.json" \
	--exclude="../$(app_name)/package-lock.json" \
	--exclude="../$(app_name)/bower.json" \
	--exclude="../$(app_name)/karma.*" \
	--exclude="../$(app_name)/protractor\.*" \
	--exclude="../$(app_name)/.*" \
	--exclude="../$(app_name)/webpack.*.js" \
	--exclude="../$(app_name)/stylelint.config.js" \
	--exclude="../$(app_name)/js/.*" \
	--exclude="../$(app_name)/.hooks" \
	--exclude="../$(app_name)/cookbook.code-workspace" \
	../$(app_name)

.PHONY: test
test: composer
	@echo "This functionality has been move to the file .github/acrions/run-tests/run-locally.sh. See its output with parameter --help."

.PHONY: code_style
code_style:
	$(composer_bin) cs:fix
	npm run stylelint-fix
	npm run prettier-fix

appinfo/info.xml: .github/actions/deploy/patch .github/actions/deploy/minor .github/actions/deploy/major .github/actions/deploy/appinfo/info.xml.dist
	.github/actions/deploy/update-data.sh --from-files
