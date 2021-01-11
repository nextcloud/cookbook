# Documentation of the app

- TOC
{:toc}

## General notes

The documentation of the app uses github pages.
The source of any content is located in `/docs` within the [app reposotory]({{ site.github.url }}).
The content in this folder will be compiled automatically when pushing to `master` and published accordingly.
Any changes to the master branch should be reflected in the published page within seconds or minutes.

The pages are generated from normal markdown files as commonly known from github.
If you do not know markdown, you can read it up in the net e.g. [here](https://www.markdownguide.org/cheat-sheet/) or otherwise.

## Local preview

To simplify the writing of documentation, it is possible to have  a local preview of the rendered site.
This preview is generated using the static HTML generator `Jekyll`.
For more detailed information see [its documentation](https://jekyllrb.com/docs/).

### Installation

If you want to contribute and have a quick way to fire up a jekyll instance yourself, you need `ruby` and `bundle` installed.
`bundle` is a tool to manage ruby dependencies.

The first step is to have a local clone of the reposotory.
I assume you know how to create one yourself using `git`.

Navigate to the `docs/` folder within the repository.

To avoid changes in the system file system, I suggest to install `jekyll` locally in the project.
This is not the default but can be achieved by
```
bundle config path 'vendor/bundle' --local
```
Every installed ruby package will be located in the `vendor/bundle` folder within the `docs` folder.

Now you need to install the ruby dependencies.
Just issue
```
bundle install
```
This will download and install a bunch of ruby gems (as the ruby packages are called) and take maybe a few minutes.

### Building the HTML codes

Now, you can run the Jekyll code generator by invoking `bundle exec jekyll ...` where the three dots represent any parameter to the Jekyll program.
Please note, that you need to call that command from the `/docs/` folder.
Any other folder might fail to find the installed dependencies.

After the installation has succeeded, you can build the current HTML version of the state in the repository using the command
```
bundle exec jekyll build
```
This will generate a folder `_site` that contains all the relevant pages in HTML format.

### Serving the content locally for development and writing

To simplify writing documentation, there is another command quite handy:
You can call
```
bundle exec jekyll --incremental
```
to build the current state and keep watching for changes in the folders.
An changes will be built incrementally (only the changed parts) into HTML pages.
Additionally, the generated pages are visible on a local web server.
By default this server runs on [http://localhost:4000](http://localhost:4000).

Any changes you make as long as the web server is running are reflected instantly in the web server's content.
All you need to do is to reload the page (or use a plugin of your browser that automatically reloads, e.g. [this one](https://github.com/blaise-io/live-reload)).
