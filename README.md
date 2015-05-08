Druplex
===

By Paul Mitchum, aka Mile23

What?
--

Druplex is a half-baked way to attach a Silex RESTful API implementation onto Drupal 7.

It acts more as a proof-of-concept and some sloppy code to guide others along the way.

How?
--

1. Install Composer.
2. Go to the root directory of your Drupal 7 installation.
3. Type this: `composer require mile23/druplex @dev`
4. Replace the root-level `index.php` file with the one located in `vendor/mile23/druplex/index/druplex.index.php`. Rename it to `index.php`, of course.
5. Put some settings in your site's `settings.php` file. They should be in the `$druplex` global variable. If you don't set these, druplex will use defaults, some of which might be dangerous. Settings existing now:
  * `$druplex['debug']`
  * `$druplex['api_prefix']` (Mostly working, not entirely..)
  * `$druplex['api_user']`
  * `$druplex['api_password']`

The `api_user` and `api_password` settings are used in protecting the Silex paths behind http authentication. You can't turn this off in settings.

What's the API?
--

Currently, this API allows you to:
* Query for a user by user ID through GET
* Update a user through PUT
* Create a user through POST
* Query for a user by an attached field
* Generate a one-time login for a user

The paths for this API can be seen in the `Druplex\DruplexApplication` class.

More documentation forthcoming. Ping me if you need to.

Where are the tests, Mr. Testing Man?
--

There aren't any. :-)
