#!/bin/sh

sed -iE -e "s#\"nette/forms\":.*#\"nette/forms\": \"$1\"#" -e '1 a "minimum-stability": "dev",' composer.json
