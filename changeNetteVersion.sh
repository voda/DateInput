#!/bin/sh

sed -i -e "s|\"nette/froms\": \"~2.0\"|\"nette/forms\": \"$1\"|" -e '1 a "minimum-stability": "dev",' composer.json
