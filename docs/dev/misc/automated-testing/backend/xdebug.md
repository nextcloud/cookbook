# Debugging the tests

The debugging of the tests can be activated to allow for step-debugging of the tests in case of issues.

The debugging framework uses the xdebug module of PHP.
If the debugging is enabled, the PHP executable will open a network connection to the IDE of the developer.
Thus, it is required to set up the IDE appropriately _before_ the debug session is started.

Using the command lien options (like `--debug-port`, `--debug-host`, etc) of the runner script, the connection options can be configured.
The defaults are sensible for a local docker installation.
To request the debugger to be enabled, you mist provide the `--debug` (`-d`) option for each invocation.

If you are using VS code, the running configuration as committed to git provides all required configuration to make the step-debugger run.
Just start the debugger listening on port `9000`.

For other IDEs, you need to configure appropriately.
Feel free to ask for help with other IDEs but be prepared to enable the logging feature of xdebug to debug the debugger.
