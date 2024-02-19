demoimage:
	docker build -f Dockerfile.demo -t vitexsoftware/multiflexi-demo .

demorun:
	docker run  -dit --name MultiFlexiDemo -p 8282:80 vitexsoftware/multiflexi-demo
	firefox http://localhost:8282?login=demo\&password=demo

