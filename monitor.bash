#!/bin/bash
# Keep tabs on an Ooyala feed URL. Could work for any URL.
# Sends an email if the length of the content is something that couldn't be correct.
# Example:
# $ ./monitor.bash
# Monitor a non-Ooyala url:
# $ ./monitor.bash --url http://www.denverpost.com/

# Environment variables are stored in project root.
# Currently two variables are set in it:
#   $RECIPIENTS, a space-separated list of email addresses to send to
#   $SENDER, the sending email address.
source ./source.bash

# Default arguments
URL='http://cdn-api.ooyala.com/v2/syndications/b83ebd1217214b0883639fbc21f66cbf/feed?pcode=V5dzkxOmUFf0dFju2v9bPHqRdgjC'
TEST=0
SLUG='ooyala_feed'
FILESIZE=0

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
if [ "$TEST" -eq 0 ]; then wget -q -O "$SLUG.new" $URL; fi

wget -O "$SLUG.new" "$URL"

FILESIZE=$(du -b "$SLUG.new" | cut -f 1)
if [ $FILESIZE -lt 1000 ]; then
    echo "Filesize: $FILESIZE"
    # The $SENDER and $RECIPIENTS are set via environment variables.
    python mailer.py --state --sender $SENDER $RECIPIENTS
    #rm "$SLUG.new"
    exit 2
fi

echo "DONE"
exit 1
