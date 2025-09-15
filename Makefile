PORT ?= 8080

start:
	php -S localhost:$(PORT) -t public public/index.php

setup:
	composer install