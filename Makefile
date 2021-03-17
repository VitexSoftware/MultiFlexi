repoversion=$(shell LANG=C aptitude show multi-abraflexi-setup | grep Version: | awk '{print $$2}')
currentversion=$(shell dpkg-parsechangelog --show-field Version)
nextversion=$(shell echo $(repoversion) | perl -ne 'chomp; print join(".", splice(@{[split/\./,$$_]}, 0, -1), map {++$$_} pop @{[split/\./,$$_]}), "\n";')

clean:
	rm -rf vendor composer.lock db/multiabraflexi.sqlite src/*/*dataTables*

migration:
	cd src ; ../vendor/bin/phinx migrate -c ../phinx-adapter.php ; cd ..

seed:
	cd src ; ../vendor/bin/phinx seed:run -c ../phinx-adapter.php ; cd ..


autoload:
	composer update

demodata:
	cd src ; ../vendor/bin/phinx seed:run -c ../phinx-adapter.php ; cd ..

newmigration:
	read -p "Enter CamelCase migration name : " migname ; cd src ; ../vendor/bin/phinx create $$migname -c ../phinx-adapter.php ; cd ..

newseed:
	read -p "Enter CamelCase seed name : " migname ; cd src ; ../vendor/bin/phinx seed:create $$migname -c ./phinx-adapter.php ; cd ..

dbreset:
	sudo rm -f db/multiabraflexi.sqlite
	echo > db/multiabraflexi.sqlite
	chmod 666 db/multiabraflexi.sqlite
	chmod ugo+rwX db
	

demo: dbreset migration demodata

hourly:
	cd lib; php -f executor.php h
daily:
	cd lib; php -f executor.php d
monthly:
	cd lib; php -f executor.php m

postinst:
	DEBCONF_DEBUG=developer /usr/share/debconf/frontend /var/lib/dpkg/info/multi-abraflexi-setup.postinst configure $(nextversion)

redeb:
	 sudo apt -y purge multi-abraflexi-setup; rm ../multi-abraflexi-setup_*_all.deb ; debuild -us -uc ; sudo gdebi  -n ../multi-abraflexi-setup_*_all.deb ; sudo apache2ctl restart

deb:
	debuild -i -us -uc -b


dimage:
	docker build -t vitexsoftware/multi-abraflexi-setup .

drun: dimage
	docker run  -dit --name MultiAbraFlexiSetup -p 8080:80 vitexsoftware/multi-abraflexi-setup
	firefox http://localhost:8080/multi-abraflexi-setup?login=demo\&password=demo

vagrant: deb
	vagrant destroy -f
	mkdir -p deb
	debuild -us -uc
	mv ../multi-abraflexi-setup-sqlite_$(currentversion)_all.deb deb
	mv ../multi-abraflexi-setup-pgsql_$(currentversion)_all.deb  deb
	mv ../multi-abraflexi-setup_$(currentversion)_all.deb        deb
	mv ../multi-abraflexi-setup-mysql_$(currentversion)_all.deb  deb
	cd deb ; dpkg-scanpackages . /dev/null | gzip -9c > Packages.gz; cd ..
	vagrant up
	sensible-browser http://localhost:8080/multi-abraflexi-setup?login=demo\&password=demo

release:
	echo Release v$(nextversion)
	docker build -t vitexsoftware/multi-abraflexi-setup:$(nextversion) .
	dch -v $(nextversion) `git log -1 --pretty=%B | head -n 1`
	debuild -i -us -uc -b
	git commit -a -m "Release v$(nextversion)"
	git tag -a $(nextversion) -m "version $(nextversion)"
	docker push vitexsoftware/multi-abraflexi-setup:$(nextversion)
	docker push vitexsoftware/multi-abraflexi-setup:latest


