
migration: autoload
	./vendor/bin/phinx migrate -c ./phinx-adapter.php

autoload:
	composer update

demodata:
	./vendor/bin/phinx seed:run -c ./phinx-adapter.php

newphinx:
	read -p "Enter CamelCase migration name : " migname ; ./vendor/bin/phinx create $$migname -c ./phinx-adapter.php

dbreset:
	echo > db/multiflexibee.sqlite
	chmod 666 db/multiflexibee.sqlite

demo: dbreset migration demodata


deb:
	debuild -i -us -uc -b

