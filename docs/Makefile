all: build run

build:
	swagger-cli bundle api.yaml --outfile concretizza.yaml --type yaml

run:
	@echo "\033[0;32mRunning at http://localhost:9200\033[0m"
	docker ps -a -q --filter "name=concretizza-accounts" | xargs -r docker rm || true
	docker run --name concretizza-accounts -p 9200:8080 -v $(shell pwd)/concretizza.yaml:/usr/share/nginx/html/swagger/concretizza.yaml -e SWAGGER_JSON=/usr/share/nginx/html/swagger/concretizza.yaml swaggerapi/swagger-ui
