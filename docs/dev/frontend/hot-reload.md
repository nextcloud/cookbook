# Hot reload during development

During development it is useful to have hot reload capability.
That means that any changes made to the JS/Vue/CSS files are automatically compiled upon saving.
Additionally, when set up correctly, the browser will detect the changes and reload/update some relevant parts.

For Vue components that means, that change the CSS style will for example **not** require a complete page reload but happen almost instantly.

You can also have a look at <a href='assets/hot-reload.mp4' target='_blank'>this video</a> to get a impression.

Please note that this approach is **only for a development environment**.
Do not use it in production.

## Preparation on the test instance: disable CSP

The reloaded code are transmitted over an external connection from a different name.
This is needed to side-load the JS code and allow for live-reloading.

Normally, this would trigger CSP protection by the browser.
So, no Nextcloud app is allowed to load JS/CSS content from a foreign domain unless explicitly specified.

In a local debugging environment the risk involved is minimal and it allows to live reload the Vue components and JS code.

To disable CSP protection, you have to install the [hmr_enabler app](https://github.com/nextcloud/hmr_enabler).
Again the warning:
**Do not do this on your production site!**.
Do it on a local debugging instance of Nextcloud with no connection to the internet.

At the time of writing, you had to checkout the app to you `/apps` folder of Nextcloud and call `make composer`.
This might change and we can only redirect any questions to the documentation of the `hmr_enabler` app.

## Build the app with hot reload enabled

In order to start a hot reload session, you have to build it with `npm run serve`.
A first build will take a few seconds.

Any further build will detect changes files and build incrementally.
This is much faster and takes only a blink, typically.

## Reload the browser

Reload the browser or navigate to the cookbook app anew.
This is to make sure that you are using the latest build with hot reload enabled.

In the web console you will find a hint:
![](assets/confirmation-hot-reload.png)

## Start coding

You have setup everything correctly and can start coding on the app.
