#!/bin/bash
#
# Copyright (C) 2018 Camelidae Group SPRL
#
# This file is part of Lumber.
#
# Lumber is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Lumber is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Lumber. If not, see <http://www.gnu.org/licenses/>.
#

#-----------------------------------------------------------------------
# Build parameters
#-----------------------------------------------------------------------

# include .env
-include .env

# Parameters
rev = 
SHELL = /bin/bash
wpcli = bin/wp-cli
TS=`date +%Y%m%d_%H%M `

red=`tput setaf 1`
green=`tput setaf 2`
lightgreen=`tput setaf 6555`
yellow=`tput setaf 3`
blue=`tput setaf 4`
magenta=`tput setaf 5`
cyan=`tput setaf 6`
white=`tput setaf 7`
c8=`tput setaf 8`
c9=`tput setaf 9`
c10=`tput setaf 10`
c11=`tput setaf 11`
c12=`tput setaf 12`
c13=`tput setaf 13`
c14=`tput setaf 14`
c15=`tput setaf 15`
c25=`tput setaf 25`
info=`tput setaf 455`
reset=`tput sgr0`

#-----------------------------------------------------------------------
# Commands
#-----------------------------------------------------------------------

default: help

help:
	@echo "Lumber build utility"
	@echo
	@echo "  make create           	: initialize app by removing .git folder from skeleton and launching make init fn"
	@echo "  make init           	: Fetch libraries with composer and run front-end npm install"
	@echo "  make install           : Install wordpress database and activate the theme (configure the .env before using this command)"
	@echo "  make build/dev 		: launch npm run dev from the theme folder"
	@echo "  make build/prod 		: launch npm run prod from the theme folder"
	@echo

init:
	@echo "${green}Removing git lumber repo${reset}"
	@rm -rf .git
	@${MAKE} start

start:
	@echo "${green}initialization${reset}"
	@$(MAKE) console/create/env
	@echo "${green}Getting the wordpress CLI${reset}"
	@$(MAKE) wp/get-cli
	@echo "${green}Getting php vendors${reset}"
	@composer update --ignore-platform-reqs
	@echo "${green}Removing wordpress junk files${reset}"
	@$(MAKE) wp/remove_default_theme
	@$(MAKE) wp/generate_salts
	@echo "${green}Getting front-end vendors${reset}"
	@$(MAKE) npm/install
	@echo "${green}Done.${reset}"

install:
	@echo "${green}Installing:${reset}"
	@echo ""
	@$(MAKE) wp/install
	@echo ""
	@$(MAKE) wp/activate_theme
	@echo ""
	@$(MAKE) wp/configure
	@echo ""
	@echo "${green}done.${reset}"

.PHONY: init help

#-----------------------------------------------------------------------
# Helper for creating and deploying app
#-----------------------------------------------------------------------
console/create/env:
	@composer update -d bin/phpconsole/
	@php bin/phpconsole/console app:install

deploy/live:
	@sh bin/lumbersync $(ENV_LIVE_REPO)

deploy/staging:
	@sh bin/lumbersync $(ENV_STAGING_REPO)

migrate/live:
	@echo -n "${green}Start migrating db for live env...${reset}"
	@mkdir -p backup
	@php $(wpcli) search-replace '$(ENV_LOCAL_URI)' '$(ENV_LIVE_URI)' --skip-columns=guid > /dev/null 2>&1;
	@php $(wpcli) db export - | sed -e 's/$(ENV_LOCAL_URI)/$(ENV_LIVE_URI)/g' > backup/$(TS).migrate.live.sql
	@php $(wpcli) search-replace '$(ENV_LIVE_URI)' '$(ENV_LOCAL_URI)' --skip-columns=guid > /dev/null 2>&1;
	@echo "${green}done.${reset}"

migrate/staging:
	@echo -n "${green}Start migrating db for staging env...${reset}"
	@mkdir -p backup
	@php $(wpcli) search-replace '$(ENV_LOCAL_URI)' '$(ENV_STAGING_URI)' --skip-columns=guid > /dev/null 2>&1;
	@php $(wpcli) db export - | sed -e 's/$(ENV_LOCAL_URI)/$(ENV_STAGING_URI)/g' > backup/$(TS).migrate.staging.sql
	@php $(wpcli) search-replace '$(ENV_STAGING_URI)' '$(ENV_LOCAL_URI)' --skip-columns=guid > /dev/null 2>&1;
	@echo "${green}done.${reset}"

migrate/gotohttps:
	@echo -n "${green}Start changing scheme...${reset}"
	@php $(wpcli) search-replace 'http://$(ENV_$(ENV_CURRENT)_URI)' 'https://$(ENV_$(ENV_CURRENT)_URI)' --skip-columns=guid > /dev/null 2>&1;
	@sed -i 's/http:\/\/$(ENV_$(ENV_CURRENT)_URI)/https:\/\/$(ENV_$(ENV_CURRENT)_URI)/g' .env
	@echo "${green}done.${reset}"

migrate/gotohttp:
	@echo -n "${green}Start changing scheme...${reset}"
	@php $(wpcli) search-replace 'https://$(ENV_$(ENV_CURRENT)_URI)' 'http://$(ENV_$(ENV_CURRENT)_URI)' --skip-columns=guid > /dev/null 2>&1;
	@sed -i 's/https:\/\/$(ENV_$(ENV_CURRENT)_URI)/http:\/\/$(ENV_$(ENV_CURRENT)_URI)/g' .env
	@echo "${green}done.${reset}"

migrate/fromlive:
	@echo -n "${green}Start migrating db from live env...${reset}"
	@php $(wpcli) search-replace '$(ENV_LIVE_URI)' '$(ENV_$(ENV_CURRENT)_URI)' --skip-columns=guid > /dev/null 2>&1;
	@echo "${green}done.${reset}"

migrate/fromstaging:
	@echo -n "${green}Start migrating db from staging env...${reset}"
	@php $(wpcli) search-replace '$(ENV_STAGING_URI)' '$(ENV_$(ENV_CURRENT)_URI)' --skip-columns=guid > /dev/null 2>&1;
	@echo "${green}done.${reset}"

prepare/live:
	@$(MAKE) migrate/live
	@$(MAKE) util/zip

prepare/staging:
	@$(MAKE) migrate/staging
	@$(MAKE) util/zip

init/live:
	@$(MAKE) prepare/live
	@scp backup/www.zip backup/$(TS).migrate.live.sql $(ENV_LIVE_URI):;

init/local:
	@sudo webctl addwp $(ENV_LIVE_URI)
	@$(MAKE) create/db

init/staging:
	@$(MAKE) prepare/staging
	@scp backup/www.zip backup/$(TS).migrate.staging.sql $(ENV_STAGING_URI):;

create/db:
	@echo -n "${green}Creating database...${reset}"
	@mysql -u $(DB_USER) --password=$(DB_PASSWORD) -h $(DB_HOST) -e 'create database $(DB_NAME)' > /dev/null 2>&1;
	@echo "${green}done${reset}"

connect/staging:
	@ssh $(ENV_STAGING_URI)

connect/live:
	@ssh $(ENV_LIVE_URI)

server/launch:
	@sudo php -S $(ENV_LOCAL_URI):80 -t $(CURDIR)/public/

#-----------------------------------------------------------------------
# Wordpress
#-----------------------------------------------------------------------
wp/get-cli:
	@echo "${green}Getting wp cli:${reset}"
	@curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	@mv wp-cli.phar $(wpcli)
	@echo "${green}done${reset}"

wp/rm-cli:
	@echo -n "${green}Removing wp cli...${reset}"
	@rm $(wpcli)
	@echo "${green}done${reset}"

wp/activate_theme:
	@php $(wpcli) theme activate $(THEME)

wp/generate_salts:
	@node bin/salts.js | cat >> .env

wp/install:
	@$(MAKE) $(ENV_SITE_INSTALL)

wp/install/singlesite:
	@php $(wpcli) core install \
		--url=$(WP_HOME) \
		--title=$(WP_TITLE) \
		--prompt="admin_user,admin_password,admin_email" \
		--skip-email
	@clear
	@echo "${green}Wordpress Installed.${reset}"
		
wp/install/multisite:
	@composer require roots/multisite-url-fixer
	@php $(wpcli) core multisite-install \
		--url=$(WP_HOME) \
		--title=$(WP_TITLE) \
		--prompt="admin_user,admin_password,admin_email" \
		--skip-email
	@clear
	@echo "${green}Wordpress Installed.${reset}"

wp/core/update:
	@php $(wpcli) core update

wp/update:
	@echo "${info}Start updating all assets (Wp and plugins)${reset}"
	@$(MAKE) util/dump
	@$(MAKE) wp/backup/create/plugins
	@while [ -z "$$CONTINUE" ]; do \
        read -r -p "${yellow}Continue updating [y/n]${reset}: " CONTINUE; \
    done ; \
    [ $$CONTINUE = "y" ] || [ $$CONTINUE = "Y" ] || (echo "${info}Update stopped.${reset}"; exit 1)
	@$(MAKE) wp/core/update
	@$(MAKE) wp/plugins/update	
	@echo "${info}Updates done.${reset}"

wp/configure:
	@php $(wpcli) rewrite structure '/%postname%/' --hard

wp/plugins/recommended:
	@php $(wpcli) plugin install wordpress-seo acf-content-analysis-for-yoast-seo easy-wp-smtp acf-better-search --activate

wp/plugins/woocommerce:
	@echo "${info}Starting Woocommerce ecosytem...${reset}"
	@composer require ffaltin/timber-integration-woocommerce
	@php $(wpcli) plugin install woocommerce --activate
	@echo "${info}Copying Woocommerce addons...${reset}"
	@cp -r bin/plugins/polylang-wc/ public/app/plugins/polylang-wc/
	@echo "${info}Polylang wc copied...${reset}"
	@echo "${info}...Woocommerce ecosystem completed.${reset}"

wp/plugins/update:
	@php $(wpcli) plugin update --all

wp/remove_default_theme:
	@rm -rf public/wp/wp-content/themes/*

.PHONY: wp/remove_default_theme wp/install wp/activate_theme wp/plugins/update

#-----------------------------------------------------------------------
# Utilities
#-----------------------------------------------------------------------
util/lighthouse/analyze:
	@echo "${green}Starting analyses${reset}"
	@mkdir -p reports
	@lighthouse $(WP_HOME) --view --output-path="reports/$(TS).report.html"
	@echo "${green}Analyses done.${reset}"

util/lighthouse/analyze/desktop:
	@echo "${green}Starting analyses${reset}"
	@mkdir -p reports
	@lighthouse $(WP_HOME) --config-path="reports/.lighthouse.desktop" --view --output-path="reports/$(TS).report.desktop.html"
	@echo "${green}Analyses done.${reset}"

util/lighthouse/analyze/mobile:
	@echo "${green}Starting analyses${reset}"
	@mkdir -p reports
	@lighthouse $(WP_HOME) --config-path="reports/.lighthouse.mobile" --view --output-path="reports/$(TS).report.mobile.html"
	@echo "${green}Analyses done.${reset}"

util/permissions:
	@echo -n "${green}Changing folders permissions to 755...${reset}"
	@find . -type d -exec chmod 755 {} \;
	@echo "${green}done.${reset}"
	@echo -n "${green}Changing files permissions to 644...${reset}"
	@find . -type f -exec chmod 644 {} \;
	@echo "${green}done.${reset}"

util/backup:
	@$(MAKE) util/dump
	@$(MAKE) util/zip

util/zip:
	@echo -n "${green}Start zipping files...${reset}"
	@mkdir -p backup
	@zip -r backup/www.zip * .env -x *.git* backup/* /**/node_modules/* bin/lumbersync
	@echo "${green}done.${reset}"

util/dump:
	@echo -n "${green}Start backuping db...${reset}"
	@mkdir -p backup
	@php $(wpcli) db export - > backup/$(TS).sql
	@echo "${green}done.${reset}"

wp/backup/create/plugins:
	@echo -n "${green}Start zipping plugins files...${reset}"
	@zip -r backup/plugins.zip public/app/plugins/* > /dev/null 2>&1;
	@echo "${green}done.${reset}"
	@echo "${green}Start testing zipfile:${cyan}"
	@echo -n "   > ";
	@zip -T backup/plugins.zip;
	@echo "${green}Done backuping plugins.${reset}"

wp/backup/rm/plugins:
	@echo -n "${green}Removing plugins zipfile...${reset}"
	@rm backup/plugins.zip
	@echo "${green}done.${reset}"

#-----------------------------------------------------------------------
# Front-end helper
#-----------------------------------------------------------------------
npm/install:
	@npm i --prefix public/app/themes/$(THEME)/app/web

build/dev: 
	@npm run dev --prefix public/app/themes/$(THEME)/app/web

build/prod: 
	@npm run prod --prefix public/app/themes/$(THEME)/app/web

build: prod

goto/front:
	@echo public/app/themes/$(THEME)/app/web

.PHONY: build dev prod npm/install
