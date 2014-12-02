#!/bin/sh

sed -i "s|\"nette/nette\": \"~2.0\"|\"nette/nette\": \"$1\"|" composer.json
