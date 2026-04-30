#!/bin/bash
php artisan migrate:fresh
php artisan db:seed
sh clean.sh
rm -fr storage/data/*
