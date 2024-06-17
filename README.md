# Lumber

Lumber is a modern WordPress stack made by [Alpaga](https://alpaga.agency/) that helps you get started with the best development tools and project structure. Based on [Bedrock](https://roots.io/bedrock/) and [Spruce](https://github.com/AlpagaAgency/spruce)

## Features

* Better folder structure
* Dependency management with [Composer](http://getcomposer.org)
* Easy WordPress configuration with environment specific files
* Environment variables with [Dotenv](https://github.com/vlucas/phpdotenv)
* Autoloader for mu-plugins (use regular plugins as mu-plugins)
* Enhanced security (separated web root and secure passwords with [wp-password-bcrypt](https://github.com/roots/wp-password-bcrypt))
* Easy maintenance and deploy services
* Front-end made with Typescript and Sass

## Requirements

* PHP >= 7.0
* npm
* MySQL
* Composer - [Install](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)

## Installation

1. Clone the git repo - `git clone https://code.alpaga.agency/alpaga/lumber.git`
2. Run `make init`
3. Follow the wizard for creating `.env` file if not created before:
  * `DB_NAME` - Database name
  * `DB_USER` - Database user
  * `DB_PASSWORD` - Database password
  * `DB_HOST` - Database host
  * `WP_ENV` - Set to environment (`development`, `staging`, `production`)
  * `ENV_CURRENT` - Set the current DNS environment (`LOCAL`, `STAGING`, `LIVE`)
  * `WP_HOME` - Full URL to WordPress home ([your-dns])
  * `WP_TITLE` - Set the default title of the website
  * `ENV_LOCAL_URI` - Set the local dns
  * `ENV_LIVE_URI` - Set the live dns
  * `ENV_STAGING_URI` - Set the staging dns
  * `ENV_LIVE_REPO` - Set the hosting url and folder on the live repo (ex: example.com:www/)
  * `ENV_STAGING_REPO` - Set the hosting url and folder on the staging repo (ex: example.com:staging/)
4. Installing Database and Vhost, you have three methods:
  * 1. Run `make init/local` if you have `webctl` bin installed
  * 2. run `create/db` to create the mysql database and set your site vhost document root to `/path/to/site/public/`
  * 3. Create manually your database and vhost
5. Run `make install` to perform the full installation (database, theme, url and admin user)
6. Access WP admin at `[your-dns]/wp/wp-admin` (Info, in local environment, just add .local after the live dns. Example: live uri => alpaga.agency, local uri => alpaga.agency.local)

## Deploys

There are two kinds of deploys, first deploy and update deploy.

The first thing to do is to configure your `ssh config` file with your staging/live dns

### First deploy

1. Choose which environment to deploy the website:
  * 1. Run `make init/live` to dump your database, zip your files and send it to the live environment.
  * 2. Run `make init/staging` to dump your database, zip your files and send it to the staging environment.
2. Run `make connect/live` (or `make connect/staging`) to connect through ssh tunnel to the environment.
3. Run `unzip www.zip`, then `mysql ...(mysql credentials) < [file]` and finally update the `.env` with the credentials provided by the hosting.
4. Navigate to `[your-dns]` for viewing your website.

### Next update

1. Run `make deploy/live` (or `make deploy/staging`) and chill.

## Documentation

There are a lot of commands provided to improve productivity

### Change Scheme

You want to change the scheme of the wordpress installation, perfom these commands:
1. Choose scheme migration: 
  * 1. Run `make migrate/gotohttps` for going to https
  * 2. Run `make migrate/gotohttp` for going to http

### Update wordpress and plugins

1. Choose type of update: 
  * 1. Run `make wp/update` for updating wp/core and plugins. With this command, the wordpress will perform a backup before updating all.
  * 2. Run `make wp/core/update` for updating wp/core only

### Add recommended plugins to the wordpress website

1. Run `make wp/plugins/recommended`

### Front-end development

There are three methods : 
1. `make npm/install` for installing all front-end library
2. `make build/dev` for compiling script and style (with watch method)
3. `make build/live` for compiling script and style before sending it to staging or live environment
