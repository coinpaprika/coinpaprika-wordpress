#!/bin/sh
if [ -z "$1" ]
  then
    echo "Usage: translate.sh ../wordpress/trunk/tools/i18n/makepot.php"
    exit 1
fi

MAKEPOT="$1"
php $MAKEPOT wp-plugin coinpaprika coinpaprika/languages/coinpaprika.pot
