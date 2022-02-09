# Setting up a development environment and running for development

## 1. Setting up Nextcloud for development

There are two methods to set up the Nextcloud development environment.

1. The [official guide](https://docs.nextcloud.com/server/latest/developer_manual/getting_started/devenv.html) walks you through installing Nextcloud and its dependencies on bare metal.
1. Alternatively, you can use [this Docker Compose configuration](https://github.com/juliushaertl/nextcloud-docker-dev). This method is easier in that you don't have to manually install Nextcloud, a SQL database, etc.<br>
    Follow the instructions in this repository, noting that the default username/password are admin/admin.<br>
    Please pay attention to the warning from that repository:
    > :warning: DO NOT USE THIS IN PRODUCTION Various settings in this setup are considered insecure and default passwords and secrets are used all over the place

## 2. Add Cookbook to your Nextcloud environment's apps directory.

If you installed on bare metal:
```
cd /var/www/nextcloud/apps
git clone https://github.com/nextcloud/cookbook.git # you may want to clone your own fork if you are contributing pull requests
```

If you installed via Docker, [the volumes section of the `docker-compose.yml`](https://github.com/juliushaertl/nextcloud-docker-dev/blob/2bbf26cc257081d9ed72abc947441849fca59dcd/docker-compose.yml#L68) shows that there are many options for specifying apps.
I opted to add a new line in this section as I cloned [`nextcloud/cookbook`](https://github.com/nextcloud/cookbook) in the same folder as [`juliushaertl/nextcloud-docker-dev`](https://github.com/juliushaertl/nextcloud-docker-dev):
```
- ../cookbook:/var/www/html/apps/cookbook
```
Be sure to restart the containers after modifying `docker-compose.yml`.

## 3. Download NPM dependencies

Open a terminal to the directory where you cloned Cookbook.
Then download the dependencies with:
```
npm install
```

## 4. Create the bundled assets

Use the NPM script to prepare the Webpack bundle:
```
npm run dev
```

## 5. Enable the app

By default, the Cookbook app will be disabled.
Open the Nextcloud web interface and login.
Click your avatar, then Apps.
Find the Cookbook app and click enable.
