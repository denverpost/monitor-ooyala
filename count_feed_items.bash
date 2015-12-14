#!/bin/bash
# Count the # of items in an Ooyala feed.
# Example:
# $ ./

# Default arguments
URL="http://api.ooyala.com/v2/syndications/cfe3da1f42b64b0ea83fbe5f5565c804/feed?pcode=V5dzkxOmUFf0dFju2v9bPHqRdgjC"
ITEMS=0
TEST=0
SLUG='ooyala_feed'
FILESIZE=0
ITEMS_ADD=1

# What arguments do we pass?
while [ "$1" != "" ]; do
    case $1 in
        -u | --url ) shift
            URL=$1
            ;;
        -t | --test ) shift
            TEST=1
            ;;
    esac
    shift
done

# DOWNLOAD!
# If we're not testing, we download the file
if [ "$TEST" -eq 0 ]; then
    while [ $ITEMS_ADD -gt 0 ]
    do 
        wget -q -O "$SLUG.new" $URL
        ITEMS_ADD=`grep "<item" ooyala_feed.new | wc -l`
        echo $ITEMS_ADD
        ITEMS=$((ITEMS+$ITEMS_ADD))
        echo $ITEMS
        URL=`grep '<link rel="next">' ooyala_feed.new | grep -Eo '(http[^<]*)'`
        echo $URL
    done
fi


echo "DONE"
exit 1
