#!/usr/bin/env bash

TMPDIR=${TMPDIR-/tmp}
REPO_NAME="wp-paid-plugins"

if [ ! -f "${TMPDIR}/advanced-custom-fields-pro/acf.php" ]
then
    rm -rf $TMPDIR/$REPO_NAME
    rm -rf "${TMPDIR}/advanced-custom-fields-pro"
    git clone git@github.com:helsingborg-stad/$REPO_NAME.git $TMPDIR/$REPO_NAME
    unzip $TMPDIR/$REPO_NAME/acf.zip -d $TMPDIR
fi