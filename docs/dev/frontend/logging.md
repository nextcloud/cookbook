# Debugging in production through logging

Sometimes, it is necessary to debug problems in production
where it is not possible to use a debugger or other developer tools to find the
source of the problem.
For example, if a user reports an issue that cannot be reproduced locally,
you can instruct them to enable logging to narrow down the source of the issue.

To enable logging, a user only has to run the following before loading the app/refreshing the page:
```js
localStorage.setItem('COOKBOOK_LOGGING_ENABLED', 'true')
```

This will automatically be reset after 30 minutes so verbose logging is not enabled permanently for the user.

The log level is also configurable. For example:
```js
localStorage.setItem('COOKBOOK_LOGGING_LEVEL', 'debug')
```

The documentation for the logging library used is available [here](https://www.npmjs.com/package/vuejs-logger).
