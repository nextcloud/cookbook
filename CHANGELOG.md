## [Unreleased]

### Fixed
- Fix empty Category
  [#805](https://github.com/nextcloud/cookbook/pull/805) @jotoeri
- Fix CI test scripts
  [#809](https://github.com/nextcloud/cookbook/pull/809) @christianlupus


## 0.9.4 - 2021-09-29

### Fixed
- Failed database caching in case of ill-formatted json file (category/keyword)
  [#797](https://github.com/nextcloud/cookbook/pull/797) @christianlupus
- Added Nook app in README
  [#798](https://github.com/nextcloud/cookbook/pull/798) @christianlupus


## 0.9.3 - 2021-09-26

### Added
- Added unit tests for controllers
  [#790](https://github.com/nextcloud/cookbook/pull/790) @christianlupus
- Added CI test to check for open todo tags in the source code
  [#791](https://github.com/nextcloud/cookbook/pull/791) @christianlupus

### Fixed
- Mark app as compatible with Nextcloud 22
  [#778](https://github.com/nextcloud/cookbook/pull/778) @christianlupus
- Usage of PHAR-based PHPUnit to avoid dependency on nikic/php-parser and dependency conflicts
  [#780](https://github.com/nextcloud/cookbook/pull/780) @christianlupus
- Extracted abstract class for migration testing and added tests code for existing migrations
  [#783](https://github.com/nextcloud/cookbook/pull/783) @christianlupus
- Reactivate step debugging in automated testing
  [#784](https://github.com/nextcloud/cookbook/pull/784) @christianlupus
- Added test result to PR messages
  [#788](https://github.com/nextcloud/cookbook/pull/788) @christianlupus

### Removed
- Removed app info XML file to avoid confusion
  [#778](https://github.com/nextcloud/cookbook/pull/778) @christianlupus


## 0.9.2 - 2021-08-09

### Added
- Added debugging helpers in the CI scripts
  [#774](https://github.com/nextcloud/cookbook/pull/774) @christianlupus

### Fixed
- Fixed changes from #774 and minor extensions
  [#775](https://github.com/nextcloud/cookbook/pull/775) @christianlupus
- Clean tables from old, redundant, and non-unique data to allow migrations (see #762 #763)
  [#776](https://github.com/nextcloud/cookbook/pull/776) @christianlupus


## 0.9.1 - 2021-07-05

### Added
- OpenAPI specification and documentation of the valid API endpoints
  [#757](https://github.com/nextcloud/cookbook/pull/757) @christianlupus

### Fixed
- Correct handling of uploads to codecov
  [#758](https://github.com/nextcloud/cookbook/pull/758) @christianlupus
- Added issue template and documentation regarding website support
  [#759](https://github.com/nextcloud/cookbook/pull/759) @christianlupus
- Avoid sharing of recipes does break the database upgrade process
  [#755](https://github.com/nextcloud/cookbook/pull/755) @christianlupus

### Removed
- Obsolete API routes that are no longer working due to missing files
  [#757](https://github.com/nextcloud/cookbook/pull/757) @christianlupus


## 0.9.0 - 2021-07-01

### Added
- Make recipes searchable through unified search
  [#611](https://github.com/nextcloud/cookbook/pull/611) @PFischbeck
- Enhanced keyword cloud in recipe list with option to hide/show keywords, enlarge area, and ordering alphabetically
  [#678](https://github.com/nextcloud/cookbook/pull/678) @seyfeb
- User documentation
  [#709](https://github.com/nextcloud/cookbook/pull/709) @seyfeb

### Fixed
- Calling reindex
  [#653](https://github.com/nextcloud/cookbook/pull/653) @seyfeb
- Setting nutrition information on recipes with an array assigned for nutrition
  [#653](https://github.com/nextcloud/cookbook/pull/653) @seyfeb
- Fix empty error message upon import
  [#647](https://github.com/nextcloud/cookbook/pull/647) @christianlupus
- Update code styling to match with current version of php-cs-fixer
  [#668](https://github.com/nextcloud/cookbook/pull/668) @christianlupus
- Fix version of `@nextcloud/capabilities` to `1.0.2`
  [#672](https://github.com/nextcloud/cookbook/pull/672) @christianlupus
- Really only show recipe-reference popup on '#'
  [#676](https://github.com/nextcloud/cookbook/pull/676) @seyfeb
- Correct styling of PHP files accoring to php-cs-fixer
  [#692](https://github.com/nextcloud/cookbook/pull/692) @christianlupus
- Removed explicit dependency of @nextcloud/capabilities
  [#693](https://github.com/nextcloud/cookbook/pull/693) @christianlupus
- Add indices to database for all tables
  [#698](https://github.com/nextcloud/cookbook/pull/698) @christianlupus
- Codebase maintenance
  [#699](https://github.com/nextcloud/cookbook/pull/699) @christianlupus
- Enable stalebot
  [#700](https://github.com/nextcloud/cookbook/pull/700) @christianlupus
- Correct error messages when recipe already exists
  [#702](https://github.com/nextcloud/cookbook/pull/702) @christianlupus
- Update webpack version to 5.x
  [#717](https://github.com/nextcloud/cookbook/pull/717) @christianlupus
- Update sass-loader
  [#720](https://github.com/nextcloud/cookbook/pull/720) @christianlupus
- Update compression-webpack-plugin
  [#721](https://github.com/nextcloud/cookbook/pull/721) @christianlupus
- Fix array in recipeYields field according to #722
  [#725](https://github.com/nextcloud/cookbook/pull/725) @christianlupus
- Fix recipe-editor layout as in #729
  [#725](https://github.com/nextcloud/cookbook/pull/732) @seyfeb
- Corrected style of stale bot messages
  [#749](https://github.com/nextcloud/cookbook/pull/749) @christianlupus
- Update the screenshots in the appstore
  [#747](https://github.com/nextcloud/cookbook/pull/747) @mMuck
- Fix visual issues at device width of 1024px #689
  [#751](https://github.com/nextcloud/cookbook/pull/751) @christianlupus
- Removed obsolete dependency on @nextcloud/event-bus
  [#719](https://github.com/nextcloud/cookbook/pull/719) @christianlupus
  

## 0.8.4 - 2021-03-08

### Added
- Sorting recipes in list by creation and modification date
  [#623](https://github.com/nextcloud/cookbook/pull/623) @seyfeb

### Fixed
- Minor errors in displaying ingredients and instructions
  [#642](https://github.com/nextcloud/cookbook/pull/642) @seyfeb
- Missing translation
  [#644](https://github.com/nextcloud/cookbook/pull/644) @seyfeb
- Recipe-reference popup being shown on the wrong input depending on keyboard layout
  [#648](https://github.com/nextcloud/cookbook/pull/648) @seyfeb

## 0.8.3 - 2021-03-03

### Fixed
- Corrected compatibility list
  [#632](https://github.com/nextcloud/cookbook/pull/632) @christianlupus

## 0.8.2 - 2021-03-03

### Fixed
- Added translation for nutritient-value label placeholder
  [#596](https://github.com/nextcloud/cookbook/pull/596) @seyfeb
- Updated dependency of eslint-config-prettier
  [#603](https://github.com/nextcloud/cookbook/pull/603) @christianlupus
- Enforce basic code styling using prettier in vue files
  [#607](https://github.com/nextcloud/cookbook/pull/607) @christianlupus
- Enforce CSS styling using stylelint
  [#608](https://github.com/nextcloud/cookbook/pull/608) @christianlupus
- More code styling, cleanup & minor bugfixes
  [#615](https://github.com/nextcloud/cookbook/pull/615) @seyfeb
- Avoid daily issues in personal forks due to missing secrets
  [#620](https://github.com/nextcloud/cookbook/pull/620) @christianlupus
- Avoid descending of CS_fixer into non-code folders
  [#621](https://github.com/nextcloud/cookbook/pull/621) @christianlupus
- Fixed compatiblity with Nextcloud 21
  [#605](https://github.com/nextcloud/cookbook/pull/605) @icewind1991

## Deprecated
- Obsolete routes to old user interface, see `appinfo/routes.php`
  [#580](https://github.com/nextcloud/cookbook/pull/580) @christianlupus

## Removed
- Dropped support for NC core version <= 18
  [#630](https://github.com/nextcloud/cookbook/pull/630) @christianlupus

## 0.8.1 - 2021-02-15

### Added
- Code style checker in Vue files
  [#581](https://github.com/nextcloud/cookbook/pull/581) @christianlupus

### Fixed
- Remove look-behind to support Safari users as well
  [#591](https://github.com/nextcloud/cookbook/pull/591) @christianlupus

## 0.8.0 - 2021-02-14

### Added
- Markdown rendering for Description
  [#381](https://github.com/nextcloud/cookbook/pull/381) @thembeat
- Changing category name for all recipes in a category
  [#555](https://github.com/nextcloud/cookbook/pull/555/) @seyfeb
- Functionality to reference other recipes by id in description, tools, ingredients, and instructions
  [#562](https://github.com/nextcloud/cookbook/pull/562/) @seyfeb
- Bundle Analyzer documentation
  [#573](https://github.com/nextcloud/cookbook/pull/573/) @seyfeb
- Added button to allow adding empty ingredient, instruction, and tool entries above existing ones in editor
  [#575](https://github.com/nextcloud/cookbook/pull/575/) @seyfeb

### Changed
- Using computed property in recipe view
  [#522](https://github.com/nextcloud/cookbook/pull/522/) @seyfeb
- Split off list/grid of recipes to separate Vue component
  [#526](https://github.com/nextcloud/cookbook/pull/526/) @seyfeb
- CSS Cleanup, removed central css styling
  [#528](https://github.com/nextcloud/cookbook/pull/528/) @seyfeb
- Timers are hidden when time is zero (prep, cook, total time)
  [#543](https://github.com/nextcloud/cookbook/pull/543/) @seyfeb
- Introduced left navigation pane visibility as Vuex state
  [#544](https://github.com/nextcloud/cookbook/pull/544/) @seyfeb
- Centralized some recipe tasks (create, update, delete)
  [#546](https://github.com/nextcloud/cookbook/pull/546/) @seyfeb
- Added icon for recipes in navigation pane, closes #550
  [#560](https://github.com/nextcloud/cookbook/pull/560/) @seyfeb
- Bumped @nextcloud/vue to 3.5.4
  [#561](https://github.com/nextcloud/cookbook/pull/561/) @seyfeb
- Bump webpack-merge from 4.2.2 to 5.7.3
  [#458](https://github.com/nextcloud/cookbook/pull/458/) @seyfeb
- Bump webpack-cli from 3.3.12 to 4.5.0
  [#565](https://github.com/nextcloud/cookbook/pull/565/)
- Enhanced testing interface
  [#564](https://github.com/nextcloud/cookbook/pull/564) @christianlupus
- Allow guest users to use the cookbook and avoid nextcloud exception handling
  [#506](https://github.com/nextcloud/cookbook/pull/506) @christianlupus

### Fixed
- Added some documentation how to install GH action generated builds
  [#538](https://github.com/nextcloud/cookbook/pull/538) @christianlupus
- Fixed problem where timers are not updated after saving recipe edits
  [#543](https://github.com/nextcloud/cookbook/pull/543/) @seyfeb
- Fixed overlapping misaligned navigation toggles (as in #534)
  [#544](https://github.com/nextcloud/cookbook/pull/544/) @seyfeb
- Refreshing left navigation pane after downloading recipe data, closes #465
  [#547](https://github.com/nextcloud/cookbook/pull/547/) @seyfeb
- Check for existing `@context` setting in json checker
  [#554](https://github.com/nextcloud/cookbook/pull/554) @christianlupus
- Introduced updating recipe directory to Vuex state, fixes #542
  [#546](https://github.com/nextcloud/cookbook/pull/546/) @seyfeb
- Push docker images for different PHP versions
  [#574](https://github.com/nextcloud/cookbook/pull/574) @christianlupus
- Enhanced the CI scripts to be more verbose regarding issues
  [#452](https://github.com/nextcloud/cookbook/pull/452) @christianlupus
- Code cleanup
  [#579](https://github.com/nextcloud/cookbook/pull/579) @christianlupus
- Added label to Dockerfile to be consistent with docker guidelines
  [#582](https://github.com/nextcloud/cookbook/pull/582) @christianlupus
- Corrected jekyll documentation
  [#584](https://github.com/nextcloud/cookbook/pull/584) @christianlupus

### Removed
- Removal of old contoller no longer in use
  [#536](https://github.com/nextcloud/cookbook/pull/536) @christianlupus

## 0.7.10 - 2021-01-16

### Fixed
- Replaced function calls only available in PHP 8 with generic ones
  [#524](https://github.com/nextcloud/cookbook/pull/524) @christianlupus

## 0.7.9 - 2021-01-15

### Changed
- Indentation of ingredients depends on existence of subgroups
  [#512](https://github.com/nextcloud/cookbook/pull/512/) @seyfeb
- Speed up index of recipes by using computed properties
  [#513](https://github.com/nextcloud/cookbook/pull/513) @christianlupus
- Central parsing of parameters for POST/PUT requests to simplify development
  [#518](https://github.com/nextcloud/cookbook/pull/518) @christianlupus
- Removed dependencies on the global jQuery
  [#497](https://github.com/nextcloud/cookbook/pull/497/) @seyfeb

### Fixed
- Fixed keywords of shared recipes counted multiple times, fixes #491
  [#493](https://github.com/nextcloud/cookbook/pull/493/) @seyfeb
- Added basic structure for documentation
  [#499](https://github.com/nextcloud/cookbook/pull/499) @christianlupus
- Make categories load recipes against
  [#500](https://github.com/nextcloud/cookbook/pull/500) @christianlupus
- Handle recipes without category well
  [#501](https://github.com/nextcloud/cookbook/pull/500) @christianlupus
- Allow to save recipes with custom image URLs
  [#505](https://github.com/nextcloud/cookbook/pull/505) @christianlupus
- Allow pasting of instructions without newline again
  [#503](https://github.com/nextcloud/cookbook/pull/503) @christianlupus
- Updated color and bullets in nutrition information, fixes #510
  [#511](https://github.com/nextcloud/cookbook/pull/511/) @seyfeb
- Update README with more clients
  [#457](https://github.com/nextcloud/cookbook/pull/457) @geeseven

## 0.7.8 - 2021-01-08

### Added
- Parse a textual yield field in an imported recipe to a certain degree
  [#327](https://github.com/nextcloud/cookbook/pull/327) @zwoabier
- Search and filter for recipes in the web interface
  [#318](https://github.com/nextcloud/cookbook/pull/318) @sam-19
- CI: Use github actions to check the latest head against unittests
  [#346](https://github.com/nextcloud/cookbook/pull/346) @christianlupus
- CI: Create source code packages for each commit
  [#346](https://github.com/nextcloud/cookbook/pull/346) @christianlupus
- Allow for inconsistent schema: Parse instructions as list of elements
  [#347](https://github.com/nextcloud/cookbook/pull/347) @victorjoos
- Show button to view all recipes without a category
  [#362](https://github.com/nextcloud/cookbook/pull/362/) @seyfeb
- Add a basic changelog to the repository
  [#366](https://github.com/nextcloud/cookbook/pull/366/) @christianlupus
- Enforce update of changelog through CI
  [#366](https://github.com/nextcloud/cookbook/pull/366/) @christianlupus
- Keyword cloud is displayed in recipe
  [#373](https://github.com/nextcloud/cookbook/pull/373/) @seyfeb
- Pasted content with newlines creates new input fields automatically for tools and ingredients in recipe editor
  [#379](https://github.com/nextcloud/cookbook/pull/379/) @seyfeb
- Selectable keywords for filtering in recipe lists
  [#375](https://github.com/nextcloud/cookbook/pull/375/) @seyfeb
- Service to handle schema.org JSON data in strings easier
  [#383](https://github.com/nextcloud/cookbook/pull/383/) @christianlupus
- Unit tests for JSON object service
  [#387](https://github.com/nextcloud/cookbook/pull/387) @TobiasMie
- PHP linter and style checker enabled
  [#390](https://github.com/nextcloud/cookbook/pull/390) @christianlupus
- Automatic deployment of new releases to the nextcloud app store
  [#433](https://github.com/nextcloud/cookbook/pull/433) @christianlupus
- Category and keyword selection from list of existing ones in recipe editor
  [#402](https://github.com/nextcloud/cookbook/pull/402/) @seyfeb
- Allow checking of ingredients in web UI
  [#393](https://github.com/nextcloud/cookbook/pull/393) @christianlupus
- Support for dateCreated and dateModified field of schema.org Recipe
  [#377](https://github.com/nextcloud/cookbook/pull/366/) @seyfeb
- Bundle-Analyzer and Optimization
  [#403](https://github.com/nextcloud/cookbook/pull/403) @thembeat
- Nutrition information display and editing
  [#416](https://github.com/nextcloud/cookbook/pull/416/) @seyfeb
- Asking user for confirmation when leaving recipe-editor form with changes
  [#464](https://github.com/nextcloud/cookbook/pull/464/) @seyfeb
- Exporting the maximal API endpoint version
  [#487](https://github.com/nextcloud/cookbook/pull/487) @christianlupus

### Changed
- Switch of project ownership to nextcloud organization in GitHub
- Change in issue management
- Changes to `.gitignore` file
- Translation issues
- Added available Android apps to README
- Update dev dependencies to recent phpunit to avoid warnings and issues
  [#376](https://github.com/nextcloud/cookbook/pull/376) @christianlupus
- Made the layout more responsive to shift the metadata right of the image in very wide screens
  [#349](https://github.com/nextcloud/cookbook/pull/349/) @christianlupus
- Optimization for SVG to reduce the file size
  [#404](https://github.com/nextcloud/cookbook/pull/404) @thembeat
- Replace Default Recipe-Thumb and Full Image with SVG
  [#418](https://github.com/nextcloud/cookbook/pull/418) @thembeat
- Enhance the CI tests and build valid dist tarballs during the CI runs
  [#435](https://github.com/nextcloud/cookbook/pull/435) @christianlupus
- Images in recipe list are lazily loaded
  [#413](https://github.com/nextcloud/cookbook/pull/413/) @seyfeb
- Improved keyword filtering in recipe lists
  [#408](https://github.com/nextcloud/cookbook/pull/408/) @seyfeb

### Fixed
- Add a min PHP restriction in the metadata
  [#282](https://github.com/nextcloud/cookbook/issues/282) @mrzapp
- Make the codebase consistent with the code standards of the main nextcloud team
  [#295](https://github.com/nextcloud/cookbook/pull/295) @rakekniven
- Improved tooltips of navigation
  [#317](https://github.com/nextcloud/cookbook/pull/317) @sam-19
- Optimize database request for faster access
  [#297](https://github.com/nextcloud/cookbook/pull/297) @christianlupus
- Ignore case during sorting of recipes
  [#333](https://github.com/nextcloud/cookbook/issues/333) @christianlupus
- CI: Repair codecov file paths for correct linkings
  [#348](https://github.com/nextcloud/cookbook/pull/348) @christianlupus
- Make project name compatible with composer 2.0
  [#352](https://github.com/nextcloud/cookbook/pull/352) @christianlupus
- Compress nextcloud logs in case of HTML parsing errors during import
  [#350](https://github.com/nextcloud/cookbook/pull/350) @maxammann
- Make complete sentence in transifex translation from parts
  [#358](https://github.com/nextcloud/cookbook/pull/358) @christianlupus
- Avoid recipe are no longer reachable when user changes locales
  [#371](https://github.com/nextcloud/cookbook/pull/371) @christianlupus
- Hide tooltips in printouts
  [#343](https://github.com/nextcloud/cookbook/pull/343/) @christianlupus
- Creating new recipe not possible due to null reference
  [#378](https://github.com/nextcloud/cookbook/pull/378/) @seyfeb
- Reenabling CI testing with current xdebug 3
  [#417](https://github.com/nextcloud/cookbook/pull/417/) @christianlupus
- Corrected code style in appinfo path
  [#427](https://github.com/nextcloud/cookbook/pull/427) @christianlupus
- Clear filtered keywords when changing the route, fixes #425
  [#426](https://github.com/nextcloud/cookbook/pull/426/) @seyfeb
- Removed typo introduced during refactory cycles
  [#434](https://github.com/nextcloud/cookbook/pull/434) @christianlupus
- Update dependency on code style to version 0.4.x
  [#437](https://github.com/nextcloud/cookbook/pull/437) @christianlupus
- Corrected bugs in CI system
  [#447](https://github.com/nextcloud/cookbook/pull/447) @christianlupus
- Recipe-editing Vue components are not tightly coupled anymore
  [#386](https://github.com/nextcloud/cookbook/pull/386/) @seyfeb
- Fixed trying to remove already removed img DOM nodes in image lazyloading, fixes #462
  [#463](https://github.com/nextcloud/cookbook/pull/463/) @seyfeb
- Fixed cooking time being removed if recipe is saved, fixes #472
  [#473](https://github.com/nextcloud/cookbook/pull/473/) @seyfeb
- Remove keywords from database when a recipe is removed
  [#478](https://github.com/nextcloud/cookbook/pull/478) @christianlupus
- Correct CI to allow creation of releases
  [#482](https://github.com/nextcloud/cookbook/pull/482) @christianlupus
- New version as reported in the API should be saved in the MainController file and thus checked in
  [#488](https://github.com/nextcloud/cookbook/pull/488) @christianlupus

### Removed
- Travis build system
- Support for PHP 7.2
- Removed info button (not) showing the last update time from settings menu, fixes #279
  [#477](https://github.com/nextcloud/cookbook/pull/477/) @seyfeb



## 0.7.7 - 2020-12-10

### Fixed
- Increase version compatibility to nextcloud 20 @mrzapp


## 0.7.6 - 2020-06-27

### Added
- Allow forward slashes in ingredients
  [#272](https://github.com/nextcloud/cookbook/pull/272) @timandrews335

### Fixed
- Swapping ingredients and instructions cause items been deleted
  [#278](https://github.com/nextcloud/cookbook/pull/278) @sam-19
