repoversion=$(shell LANG=C aptitude show multiflexi | grep Version: | awk '{print $$2}')
currentversion=$(shell dpkg-parsechangelog --show-field Version)
nextversion=$(shell echo $(repoversion) | perl -ne 'chomp; print join(".", splice(@{[split/\./,$$_]}, 0, -1), map {++$$_} pop @{[split/\./,$$_]}), "\n";')

clean:
	rm -rf vendor composer.lock db/multiflexi.sqlite src/*/*dataTables*

migration:
	cd src ; ../vendor/bin/phinx migrate -c ../phinx-adapter.php ; cd ..

seed:
	cd src ; ../vendor/bin/phinx seed:run -c ../phinx-adapter.php ; cd ..

probeapp:
	cd src ; ../vendor/bin/phinx seed:run -c ../phinx-adapter.php -s MultiFlexiProbeApp ; cd ..


autoload:
	composer update

demodata:
	cd src ; ../vendor/bin/phinx seed:run -c ../phinx-adapter.php ; cd ..

newmigration:
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

