#!/bin/bash
php artisan filament:clear-cached-components
php artisan cache:clear
rm -fr storage/logs/*.log
rm -fr storage/framework/views/*.php
