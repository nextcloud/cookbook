## [Unreleased]

### Fixed
- Fixed keywords of shared recipes counted multiple times, fixes #491
  [#493](https://github.com/nextcloud/cookbook/pull/493/) @seyfeb
- Added basic structure for documentation
  [#499](https://github.com/nextcloud/cookbook/pull/499) @christianlupus
- Make categories load recipes against
  [#500](https://github.com/nextcloud/cookbook/pull/500) @christianlupus
- Handle recipes without category well
  [#501](https://github.com/nextcloud/cookbook/pull/500) @christianlupus


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
