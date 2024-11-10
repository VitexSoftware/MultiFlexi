#SPECURI=https://api.swaggerhub.com/apis/VitexSoftware/MultiFlexi/1.0.0/swagger.yaml

SPECURI="./openapi.yaml"

all: clean server 
# client

prepare:
	npm install --save-dev --save-exact prettier

clean: 
	rm -rf client server

server:
	PHP_POST_PROCESS_FILE="node_modules/.bin/prettier --write" openapi-generator-cli generate -i ${SPECURI} -g php-slim4 -c templates/server.yaml --git-user-id VitexSoftware --git-repo-id MultiFlexi -t templates/server -o server

client:
	PHP_POST_PROCESS_FILE="node_modules/.bin/prettier --write" openapi-generator-cli generate -i ${SPECURI} -g php -o client

frontend:
	PHP_POST_PROCESS_FILE="node_modules/.bin/prettier --write" openapi-generator-cli generate -i ${SPECURI} -g typescript-angular -o frontend

help:
	openapi-generator-cli config-help -g php-slim4
