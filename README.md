# PhpProfiler -- Profiling TYPO3 Flow

PhpProfiler is a profiling and tracing tool that measures time spent in various parts of
your application flow and can leverage XHProf to profile applications.

It stores data in a format understood by Plumber and can also store to the databases used
by XHProf.io (http://xhprof.io/) and XHGui (https://github.com/preinheimer/xhgui).

## Configuration

This is the default configuration:

```Sandstorm:
  PhpProfiler:
    plumber:
      profilePath: '%FLOW_PATH_DATA%Logs/Profiles'

    # xhprof.io settings (see http://xhprof.io/)
    'xhprof.io':
      enable: false
      dsn: 'mysql:dbname=xhprofio;host=localhost;charset=utf8'
      username: ''
      password: ''

    # preinheimer-xhgui settings (see https://github.com/preinheimer/xhgui)
    'xhgui':
      enable: false
      host: 'mongodb://localhost:27017'
      dbname: 'xhprof'
```

To enable the XHProf.io and XHGui backends just adjust tje configuration as needed.

## Profiling method calls using an Aspect

You can use the `Sandstorm\PhpProfiler\Annotations\Profile` annotation on a method in order
to profile it:

```php
class MyClass {

	/**
	 * @Sandstorm\PhpProfiler\Annotations\Profile
	 */
	public function myMethod() {
	}
}
```

## Viewing the results

For the Plumber UI install the Plumber package as described in it's manual.

For XHProf.ui and XHGui follow the instructions given on their websites.

Both can be run using the built-in web server of PHP 5.4. Just go to the project
root (for XHProf.io) or the *web/webroot* folder (for XHGui) and run
``php -S localhost:8888`` to be able to access their UIs.

## Credits

Originally developed by Sebastian Kurfürst, Sandstorm Media UG (haftungsbeschränkt)

Code from the XHProf.io and XHGui projects is included for storing the data.

## License

All the code is licensed under the GPL license.