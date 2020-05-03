
clean:
	rm -rf vendor composer.lock db/multiflexibee.sqlite

migration: autoload
	./vendor/bin/phinx migrate -c ./phinx-adapter.php

autoload:
	composer update

demodata:
	./vendor/bin/phinx seed:run -c ./phinx-adapter.php

newphinx:
	read -p "Enter CamelCase migration name : " migname ; ./vendor/bin/phinx create $$migname -c ./phinx-adapter.php

dbreset:
	sudo rm -f db/multiflexibee.sqlite
	echo > db/multiflexibee.sqlite
	chmod 666 db/multiflexibee.sqlite
	chmod ugo+rwX db

demo: dbreset migration demodata

redeb:
	 sudo apt -y purge multi-flexibee-setup; rm ../multi-flexibee-setup_*_all.deb ; debuild -us -uc ; sudo gdebi  -n ../multi-flexibee-setup_*_all.deb ; sudo apache2ctl restart

deb:
	debuild -i -us -uc -b


dimage:
	docker build -t vitexsoftware/multi-flexibee-setup .

drun: dimage
	docker run  -dit --name MultiFlexiBeeSetup -p 2323:80 vitexsoftware/multi-flexibee-setup

