repoversion=$(shell LANG=C aptitude show multi-flexibee-setup | grep Version: | awk '{print $$2}')
nextversion=$(shell echo $(repoversion) | perl -ne 'chomp; print join(".", splice(@{[split/\./,$$_]}, 0, -1), map {++$$_} pop @{[split/\./,$$_]}), "\n";')

clean:
	rm -rf vendor composer.lock db/multiflexibee.sqlite src/*/*dataTables*

migration: autoload
	cd src ; ../vendor/bin/phinx migrate -c ../phinx-adapter.php ; cd ..

autoload:
	composer update

demodata:
	cd src ; ../vendor/bin/phinx seed:run -c ../phinx-adapter.php ; cd ..

newmigration:
	read -p "Enter CamelCase migration name : " migname ; ./vendor/bin/phinx create $$migname -c ./phinx-adapter.php

newseed:
	read -p "Enter CamelCase seed name : " migname ; ./vendor/bin/phinx seed:create $$migname -c ./phinx-adapter.php

dbreset:
	sudo rm -f db/multiflexibee.sqlite
	echo > db/multiflexibee.sqlite
	chmod 666 db/multiflexibee.sqlite
	chmod ugo+rwX db

demo: dbreset migration demodata

hourly:
	cd lib; php -f executor.php h
daily:
	cd lib; php -f executor.php d
monthly:
	cd lib; php -f executor.php m

postinst:
	DEBCONF_DEBUG=developer /usr/share/debconf/frontend /var/lib/dpkg/info/multi-flexibee-setup.postinst configure $(nextversion)

redeb:
	 sudo apt -y purge multi-flexibee-setup; rm ../multi-flexibee-setup_*_all.deb ; debuild -us -uc ; sudo gdebi  -n ../multi-flexibee-setup_*_all.deb ; sudo apache2ctl restart

deb:
	debuild -i -us -uc -b


dimage:
	docker build -t vitexsoftware/multi-flexibee-setup .

drun: dimage
	docker run  -dit --name MultiFlexiBeeSetup -p 8080:80 vitexsoftware/multi-flexibee-setup
	firefox http://localhost:8080/multi-flexibee-setup?login=demo\&password=demo

vagrant:
	vagrant destroy -f
	vagrant up
	firefox http://localhost:8080/multi-flexibee-setup?login=demo\&password=demo

release:
	echo Release v$(nextversion)
	docker build -t vitexsoftware/multi-flexibee-setup:$(nextversion) .
	dch -v $(nextversion) `git log -1 --pretty=%B | head -n 1`
	debuild -i -us -uc -b
	git commit -a -m "Release v$(nextversion)"
	git tag -a $(nextversion) -m "version $(nextversion)"
	docker push vitexsoftware/multi-flexibee-setup:$(nextversion)

