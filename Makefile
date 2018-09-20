.PHONY: cs tests

deps: vendor/autoload.php npm

cs:
	./vendor/bin/php-cs-fixer fix

tests:
	./vendor/bin/phpunit

# we only do `install`, as composer.json may change
# without wanting to update dependencies.
composer.lock: composer.json
	composer install
	touch $@

# make sure deps get installed even if
# composer.lock exists initially (or
# after `git pull`)
vendor/autoload.php: composer.lock
	composer install
	touch $@

npm:
	docker run -it -v $(shell pwd):/usr/src/app -w /usr/src/app --rm node:9 npm install
	docker run -it -v $(shell pwd):/usr/src/app -w /usr/src/app --rm node:9 chown -R $(shell id -u):$(shell id -g) node_modules/

build:
	docker run -it -v $(shell pwd):/usr/src/app -w /usr/src/app --rm node:9 npm run build
	docker run -it -v $(shell pwd):/usr/src/app -w /usr/src/app --rm node:9 chown -R $(shell id -u):$(shell id -g) dist/

phar:
	docker run -it -v $(shell pwd):/usr/src/app -w /usr/src/app --rm node:9 npm run package
	docker run -it -v $(shell pwd):/usr/src/app -w /usr/src/app --rm node:9 chown -R $(shell id -u):$(shell id -g) dist/
	./vendor/bin/box build

serve:
	php -S 127.0.0.1:8080 -t web

release: phar
	./vendor/bin/RMT release
