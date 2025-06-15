# vim: set tabstop=8 softtabstop=8 noexpandtab:
.PHONY: help
help: ## Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: static-code-analysis
static-code-analysis: vendor ## Runs a static code analysis with phpstan/phpstan
	vendor/bin/phpstan analyse --configuration=phpstan-default.neon.dist --memory-limit=-1

.PHONY: static-code-analysis-baseline
static-code-analysis-baseline: check-symfony vendor ## Generates a baseline for static code analysis with phpstan/phpstan
	vendor/bin/phpstan analyze --configuration=phpstan-default.neon.dist --generate-baseline=phpstan-default-baseline.neon --memory-limit=-1

.PHONY: tests
tests: vendor
	vendor/bin/phpunit tests

.PHONY: vendor
vendor: composer.json composer.lock ## Installs composer dependencies
	composer install

.PHONY: cs
cs: ## Update Coding Standards
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --diff --verbose

.PHONY: clean
clean:
	rm -rf vendor composer.lock db/multiflexi.sqlite src/*/*dataTables*

.PHONY: migration
migration: ## Run database migrations
	cd src ; ../vendor/bin/phinx migrate -c ../phinx-adapter.php ; cd ..

.PHONY: sysmigration
sysmigration: ## Run database migrations using system phinx
	cd src ; /usr/bin/phinx migrate -c /usr/lib/multiflexi/phinx-adapter.php ; cd ..

.PHONY: seed
seed: ## Run database seeds
	cd src ; ../vendor/bin/phinx seed:run -c ../phinx-adapter.php ; cd ..

.PHONY: probeapp
probeapp: ## Run database seeds
	cd src ; ../vendor/bin/phinx seed:run -c ../phinx-adapter.php -s MultiFlexiProbeApp ; cd ..

.PHONY: autoload
autoload: ## Run composer autoload
	composer update

.PHONY: appstatus
appstatus: ## Show application status
	./cli.sh appstatus

demodata:
	cd src ; ../vendor/bin/phinx seed:run -c ../phinx-adapter.php ; cd ..

.PHONY: newmigration
newmigration: ## Prepare new Database Migration
	read -p "Enter CamelCase migration name : " migname ; cd src ; ../vendor/bin/phinx create $$migname -c ../phinx-adapter.php ; cd ..

newseed:
	read -p "Enter CamelCase seed name : " migname ; cd src ; ../vendor/bin/phinx seed:create $$migname -c ./phinx-adapter.php ; cd ..

dbreset:
	sudo rm -f db/multiflexi.sqlite
	echo > db/multiflexi.sqlite
	chmod 666 db/multiflexi.sqlite
	chmod ugo+rwX db
	

demo: dbreset migration demodata

hourly:
	cd lib; php -f executor.php h
daily:
	cd lib; php -f executor.php d
monthly:
	cd lib; php -f executor.php m

postinst:
	DEBCONF_DEBUG=developer /usr/share/debconf/frontend /var/lib/dpkg/info/multiflexi.postinst configure $(nextversion)

redeb:
	 sudo apt -y purge multiflexi; rm ../multiflexi_*_all.deb ; debuild -us -uc ; sudo gdebi  -n ../multiflexi_*_all.deb ; sudo apache2ctl restart

debs:
	debuild -i -us -uc -b

debs2deb: debs
	mkdir -p ./dist/; rm -rf ./dist/* ; for deb in $$(cat debian/files | awk '{print $$1}'); do mv "../$$deb" ./dist/; done
	debs2deb ./dist/ multi-flexi-dist
	mv multi-flexi-dist_*_all.deb dist

dimage:
	docker build -t vitexsoftware/multiflexi .

demoimage:
	docker build -f Dockerfile.demo -t vitexsoftware/multiflexi-demo .

demorun:
	docker run  -dit --name MultiFlexiDemo -p 8282:80 vitexsoftware/multiflexi-demo
	firefox http://localhost:8282?login=demo\&password=demo


drun: dimage
	docker run  -dit --name MultiServersetup -p 8080:80 vitexsoftware/multiflexi
	firefox http://localhost:8080?login=demo\&password=demo

vagrant: packages
	vagrant destroy -f
	mkdir -p deb
	debuild -us -uc
	mv ../multiflexi-*_$(currentversion)_all.deb deb
	mv ../multiflexi_$(currentversion)_all.deb deb
	cd deb ; dpkg-scanpackages . /dev/null | gzip -9c > Packages.gz; cd ..
	vagrant up
	sensible-browser http://localhost:8080/multiflexi?login=demo\&password=demo

release:
	echo Release v$(nextversion)
	docker build -t vitexsoftware/multiflexi:$(nextversion) .
	dch -v $(nextversion) `git log -1 --pretty=%B | head -n 1`
	debuild -i -us -uc -b
	git commit -a -m "Release v$(nextversion)"
	git tag -a $(nextversion) -m "version $(nextversion)"
	docker push vitexsoftware/multiflexi:$(nextversion)
	docker push vitexsoftware/multiflexi:latest

baseline:
	phpstan analyse --level 7   --configuration phpstan.neon   src/ --generate-baseline

phpunit:
	vendor/bin/phpunit -c tests/configuration.xml tests/

daemon:
	export $(grep -v '#' .env | xargs) && cd lib && php -f ./daemon.php

testimage:
	podman build -f tests/Containerfile . -t docker.io/vitexsoftware/multiflexi-probe

testimagex:
	docker buildx build -f tests/Containerfile . --push --platform linux/arm/v7,linux/arm64/v8,linux/amd64 --tag docker.io/vitexsoftware/multiflexi-probe


packages:
	debuild -us -uc

# Use phpcs to reformat code to PSR12
codingstandards:
	phpcbf --colors --standard=PSR12 --extensions=php --ignore=vendor/ src/

