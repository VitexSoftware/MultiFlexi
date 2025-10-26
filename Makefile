# vim: set tabstop=8 softtabstop=8 noexpandtab:
.PHONY: help
help: ## 📋 Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: static-code-analysis
static-code-analysis: vendor ## 🔍 Runs a static code analysis with phpstan/phpstan
	vendor/bin/phpstan analyse --configuration=phpstan-default.neon.dist --memory-limit=-1

.PHONY: static-code-analysis-baseline
static-code-analysis-baseline: check-symfony vendor ## 📊 Generates a baseline for static code analysis with phpstan/phpstan
	vendor/bin/phpstan analyze --configuration=phpstan-default.neon.dist --generate-baseline=phpstan-default-baseline.neon --memory-limit=-1

.PHONY: tests
tests: vendor
	vendor/bin/phpunit tests

.PHONY: vendor
vendor: composer.json composer.lock ## 📦 Installs composer dependencies
	composer install

.PHONY: cs
cs: ## ✨ Update Coding Standards
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --diff --verbose

.PHONY: clean
clean:
	rm -rf vendor composer.lock db/multiflexi.sqlite src/*/*dataTables*


.PHONY: docs
docs: ## 📚 Build Sphinx HTML documentation
	@if [ -x .venv/bin/python ]; then \
		.venv/bin/python -m sphinx -b html docs/source docs/_build/html; \
	else \
		python -m sphinx -b html docs/source docs/_build/html; \
	fi

.PHONY: autoload
autoload: ## 🔄 Run composer autoload
	composer update

postinst: ## ⚙️ Run post-installation script
	DEBCONF_DEBUG=developer /usr/share/debconf/frontend /var/lib/dpkg/info/multiflexi.postinst configure $(nextversion)

redeb: ## 📦 Rebuild and reinstall deb package
	sudo apt -y purge multiflexi; rm ../multiflexi_*_all.deb ; debuild -us -uc ; sudo gdebi  -n ../multiflexi_*_all.deb ; sudo apache2ctl restart

debs: ## 📦 Build debian packages
	debuild -i -us -uc -b

debs2deb: debs ## 📁 Move built debs to dist and create multi-flexi-dist
	mkdir -p ./dist/; rm -rf ./dist/* ; for deb in $$(cat debian/files | awk '{print $$1}'); do mv "../$$deb" ./dist/; done
	debs2deb ./dist/ multi-flexi-dist
	mv multi-flexi-dist_*_all.deb dist

dimage: ## 🐳 Build docker image for MultiFlexi
	docker build -t vitexsoftware/multiflexi .

demoimage: ## 🎯 Build demo docker image
	docker build -f Dockerfile.demo -t vitexsoftware/multiflexi-demo .

demorun: ## 🚀 Run demo docker image and open in browser
	docker run  -dit --name MultiFlexiDemo -p 8282:80 vitexsoftware/multiflexi-demo
	firefox http://localhost:8282?login=demo\&password=demo


drun: dimage ## 🚀 Run main docker image and open in browser
	docker run  -dit --name MultiServersetup -p 8080:80 vitexsoftware/multiflexi
	firefox http://localhost:8080?login=demo\&password=demo

vagrant: packages ## 📱 Build and run vagrant environment
	vagrant destroy -f
	mkdir -p deb
	debuild -us -uc
	mv ../multiflexi-*_$(currentversion)_all.deb deb
	mv ../multiflexi_$(currentversion)_all.deb deb
	cd deb ; dpkg-scanpackages . /dev/null | gzip -9c > Packages.gz; cd ..
	vagrant up
	sensible-browser http://localhost:8080/multiflexi?login=demo\&password=demo

release: ## 🚀 Build and release new version
	echo Release v$(nextversion)
	docker build -t vitexsoftware/multiflexi:$(nextversion) .
	dch -v $(nextversion) `git log -1 --pretty=%B | head -n 1`
	debuild -i -us -uc -b
	git commit -a -m "Release v$(nextversion)"
	git tag -a $(nextversion) -m "version $(nextversion)"
	docker push vitexsoftware/multiflexi:$(nextversion)
	docker push vitexsoftware/multiflexi:latest

baseline: ## 📊 Generate phpstan baseline
	phpstan analyse --level 7   --configuration phpstan.neon   src/ --generate-baseline

phpunit: ## 🧪 Run phpunit tests with custom configuration
	vendor/bin/phpunit -c tests/configuration.xml tests/


reset: ## 🔄 Reset local branch to origin
	git fetch origin
	git reset --hard origin/$(git rev-parse --abbrev-ref HEAD)

gdpr-migration: ## 📋 Run GDPR Article 16 database migration
	php src/gdpr-article16-migration.php

gdpr-migration-sample: ## 📋 Run GDPR Article 16 migration with sample data
	php src/gdpr-article16-migration.php --with-sample-data
