# Setting up a development environment and running for development

* TOC
{:toc}

## Setting up Nextcloud server for development

First a nextcloud server is needed to run the app within.

There are multiple methods to set up the Nextcloud development environment.

1. The [official guide](https://docs.nextcloud.com/server/latest/developer_manual/getting_started/devenv.html) walks you through installing Nextcloud and its dependencies on bare metal.
1. Alternatively, you can use [this Docker Compose configuration](https://github.com/juliushaertl/nextcloud-docker-dev). This method is easier in that you don't have to manually install Nextcloud, a SQL database, etc.<br>
    Follow the instructions in this repository, noting that the default username/password are admin/admin.<br>
    Please pay attention to the warning from that repository:
    > :warning: DO NOT USE THIS IN PRODUCTION Various settings in this setup are considered insecure and default passwords and secrets are used all over the place
1. There is also [this repository](https://github.com/christianlupus/nextcloud-docker-debug) that contains a configuration using docker-compose as well. This setup is tailored down to allow easy debugging and profiling of apps. The repository has an extensive documentation attached in form of a README file on how to setup.<br>
    **This repository is only for development purposes. Do not use for productive usage!**

## Add Cookbook to your Nextcloud environment's apps directory

You must install the checked out version of the app into the corresponding folder of the nextcloud server.

### Bare metal installation

If you installed on bare metal (directly in the operating system's HTTP server):
```
cd /var/www/nextcloud/apps
git clone https://github.com/nextcloud/cookbook.git # you may want to clone your own fork if you are contributing pull requests
```

### Usage of the docker-compose file by juliushaertl

If you installed via Docker, [the volumes section of the `docker-compose.yml`](https://github.com/juliushaertl/nextcloud-docker-dev/blob/2bbf26cc257081d9ed72abc947441849fca59dcd/docker-compose.yml#L68) shows that there are many options for specifying apps.

The easiest way might be to add a new line in this section after cloning [`nextcloud/cookbook`](https://github.com/nextcloud/cookbook) in the same folder as [`juliushaertl/nextcloud-docker-dev`](https://github.com/juliushaertl/nextcloud-docker-dev):
```
- ../cookbook:/var/www/html/apps/cookbook:ro
```
You might need to adopt the path specification according to your local setup. Also note that docker-compose needs the correct indentation (spaces and no tabs!) to work well.

Be sure to recreate the containers after modifying `docker-compose.yml` using `docker-compose up -d`.

### Usage of the docker-compose scripts by christianlupus

The installation process is described in the README of the project. Feel free to contact the author in cases of problems.

## Install PHP dependencies

The app needs some depdencies in the PHP backend. These are managed via [composer](http://composer.org). Make sure, you have composer ready on your development machine.

To install the required packages just run in the root of the checked-out source folder of the cookbook app
```
composer install
```
This will download all required PHP packages into the `vendor` folder.

If you need to reset for some reason after having broken things, remove the `vendor` folder and the `composer.lock` file _only from the source code of the cookbook app_. Then, you can install the PHP dependencies from scratch.

## Download NPM dependencies

The frontend is based on Vue. Some Javascript/NPM dependencies are needed in order to build the frontend from the source code. Make sure you have a matching node.js version installed and make sure `npm` is present on your machine.

Open a terminal to the directory where you cloned the cookbook app. Then download the dependencies with:
```
npm install
```

To reset you can remove the folder `node_modules` and the file `package-lock.json`. Then you can install all packages from scratch.

## Create the bundled assets for the frontend

Use the NPM script to prepare the Webpack bundle:
```
npm run build-dev
```

Alternatively, you can create a incremental watcher while programming. This will rebuild as soon as you save any of the `*.vue` files. You can do this by
```
npm run dev
```

## Enable the app

By default, the Cookbook app will be disabled. You need to make sure the app is enabled in the NC server. You can use the occ interface if you have access.

Alternatively, open the Nextcloud web interface and login. Click your avatar, then Apps. Find the cookbook app and click on _Enable_.
