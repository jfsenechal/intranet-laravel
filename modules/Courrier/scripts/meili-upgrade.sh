#!/bin/sh
#Try to upgrade data
systemctl stop meilisearch
meilisearch --experimental-dumpless-upgrade
systemctl start meilisearch
