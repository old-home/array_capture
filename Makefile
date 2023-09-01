build:
	docker-compose run php composer build

doc:
	docker-compose run phpdoc
	sudo chown -R 1000:1000 build

.PHONY: build doc
