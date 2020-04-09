
phinx:
	./vendor/bin/phinx migrate -c ./phinx-adapter.php

demodata:
	./vendor/bin/phinx seed:run -c ./phinx-adapter.php

newphinx:
	read -p "Enter CamelCase migration name : " migname ; ./vendor/bin/phinx create $$migname -c ./phinx-adapter.php


deb:
	debuild -i -us -uc -b

