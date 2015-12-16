#!/bin/bash
# Ooyala feed manipulation and download.
# Example:
# $ ./oofeed.bash

# Default arguments
URL="http://api.ooyala.com/v2/syndications/cfe3da1f42b64b0ea83fbe5f5565c804/feed?pcode=V5dzkxOmUFf0dFju2v9bPHqRdgjC"
ITEMS=0
TEST=0
SLUG='ooyala_feed'
FILESIZE=0
ITEMS_ADD=1
COUNT=0

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
# If we're not testing, we download the file.
if [ "$TEST" -eq 0 ]; then
    while [ $ITEMS_ADD -gt 0 ]
    do 
        wget -q -O "$SLUG.$COUNT.xml" $URL
        ITEMS_ADD=`grep "<item" ooyala_feed.$COUNT.xml | wc -l`
        # ITEMS_ADD is the number of new items in the current feed
        echo $ITEMS_ADD

        # Keep track of how many total items we've ingested
        ITEMS=$((ITEMS+$ITEMS_ADD))
        echo $ITEMS

        # *** If you want to run any scripts on the current page of the feed, run it here.
        # Example: python get_items.py $SLUG.$COUNT.xml, where get_items.py looks at the xml
        # and downloads each item in the feed.

        # Figure out what the URL for the next page of the feed is
        URL=`grep '<link rel="next">' ooyala_feed.$COUNT.xml | grep -Eo '(http[^<]*)'`
        COUNT=$((COUNT+1))
        echo $URL
    done
fi


echo "DONE"
exit 1
