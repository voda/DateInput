#!/bin/sh

sed -i -e "s|\"nette/nette\": \"~2.0\"|\"nette/nette\": \"$1\"|" -e '1 a "minimum-stability": "dev",' composer.json
