Testing Druplex
===

Because Druplex is a mad-scientist hack, testing is complicated.

Basically, if you want to test this thing locally you must set up a Drupal 7 site in a directory called `drupal/`, and you should do it the way the `.travis.yml` file does, a bit like this:

	$ drush make drushmake/fixture.make drupal
	$ cp -r drushmake/fixture drupal/profiles
	$ cp -r drushmake/drush_feature drupal/sites/all/modules
	$ cd drupal
	$ drush si fixture --db-url=.....

Then you can finally go back to the root level and run PHPUnit:

	$ cd ..
	$ ./vendor/bin/phpunit

Note that on subsequent test runs, the fixture database may still have the old data from before, causing test failures.

On Travis-CI, however, these fixtures are created from nothing each time, so this won't be a problem.

The tests are located in `tests/` and they subclass the Silex/Symfony `WebTestBase` class, and use the `Client` class to mock requests through `Request` injection.
