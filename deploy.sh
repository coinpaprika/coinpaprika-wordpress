#!/bin/sh
if [ -z "$1" ]
  then
    echo "Usage: deploy.sh ../svn-checkout-directory."
    exit 1
fi

OUTPUTDIR="$1"
rsync -av --progress coinpaprika/* $OUTPUTDIR/trunk --exclude "*.git*" --exclude "*.DS_Store"
rsync -av --progress assets $OUTPUTDIR --exclude "*.git*" --exclude "*.DS_Store"
