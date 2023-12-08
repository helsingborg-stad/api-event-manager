#!/usr/bin/env bash

TARGET=/var/www/html/wp-content/plugins/advanced-custom-fields-pro

if [ ! -f "${TARGET}/acf.php" ]
then
    mkdir $TARGET
    source "$PWD/.vscode/tasks/download-acf-plugin.sh"
    cp -R /tmp/advanced-custom-fields-pro/* $TARGET
fi