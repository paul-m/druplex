Testing Druplex
===

Because Druplex is a mad-scientist hack, testing is complicated.

Basically, if you want to test this thing locally you must set up a Drupal 7 site in a directory called `drupal/`, and you should do it the way the `.travis.yml` file does, a bit like this:

	$ drush make drushmake/fixture.make drupal
	$ cp -r drushmake/fixture drupal/profiles
	$ cd drupal
	$ drush si fixture --mysql-url.....

Then you can finally go back to the root level and run PHPUnit:

	$ cd ..
	$ ./vendor/bin/phpunit

