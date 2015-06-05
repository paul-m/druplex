[![Build Status](https://travis-ci.org/paul-m/druplex.svg?branch=master)](https://travis-ci.org/paul-m/druplex)

Druplex
===

By Paul Mitchum, aka Mile23

What?
--

Druplex is a half-baked way to attach a Silex RESTful API implementation onto Drupal 7.

It acts more as a proof-of-concept and some sloppy code to guide others along the way.

How To Install?
--

1. Install Composer: https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx
2. Go to the root directory of your Drupal 7 installation.
3. Type this: `composer require mile23/druplex @dev`
4. Replace the root-level `index.php` file with the one located in `vendor/mile23/druplex/index/druplex.index.php`. Rename it to `index.php`, of course.
5. Put some settings in your site's `settings.php` file. They should be in the `$druplex` global variable. If you don't set these, druplex will use defaults, some of which might not be secure. Settings existing now:
  * `$druplex['debug']`
  * `$druplex['api_prefix']`
  * `$druplex['api_user']`
  * `$druplex['api_password']`
6. Point your web browser to `http://example.com/api`. After logging in with the http authentication, you will see a Silex error. Success!
7. Try `http://example.com/api/user/1` and you'll see a bit of JSON representing user 1. Rock on.

The `api_user` and `api_password` settings are used in protecting the Silex paths behind http authentication. You can't turn this off in settings. Default values: paul password

Note that in order to add Druplex to your Pantheon-hosted site, you'll have to say `composer require mile23/druplex @dev --prefer-dist` since Pantheon (rightly) balks when you try to include git submodules.

RESTful API
--

This API is normalized on JSON.

Currently, you can:

* Query for a user by user ID through GET
* Create a user through POST
* Update a user through PUT
* Query for a user by an attached field
* Generate a one-time login for a user

### `GET /api/user/{uid}`

Gets a sanitized version of the user for the user ID. Included here mostly for completeness and testing.

Returns a resource with `uid` and `name`.

### `POST /api/user`

Post a user resource to the Drupal site.

Requires `name`, `mail`.

If you try to send `pass` it will fail. A random password will be generated for the user. Never send passwords over the internet, OK? :-) See the user one-time login part of this API for a solution to changing the password.

POSTing a user can only add fields which are present in Drupal's user schema. That is, no attached fields.

### `PUT /api/user/{uid}`

Change a user record.

You can change any field which is present in the Drupal user schema, other than `pass` or `name`.

You can change the value of any attached field, too. You must include `fieldname`, `fieldcolumn`, and `fieldvalue`. You can only change one field per PUT, and multivalue fields are unsupported.

### `GET /api/user/{fieldname}/{fieldcolumn}/{fieldvalue}`

Query for a user with a given attached field value.

You must specify the field name, the field column, and the value. The field column is the field's schema column. Fields can have more than one column, so we specify which column. For `text` fields, the column is `value`.

Returns a sanitized user record, as with GET.

### `GET /api/user/uli/{uid}`

Get a one-time login URL for the given user.

Note that 'uli' is the Drush command for returning a one-time login, and that's why the name is used here.

Returns a resource with two properties:
 * `user` is the sanitized user object.
 * `uli` is the one-time login URL.

Where are the tests, Mr. Testing Man?
--

See TESTING.md for all the testing lowdown.
