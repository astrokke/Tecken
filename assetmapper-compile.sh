#!/usr/bin/env bash

echo 'assetmapper watcher is running'

daemon() {
    while true; do
        inotifywait -r -e modify,create,delete,move ./assets/ > /dev/null 2>&1
        
        echo "Change detected, running asset-map:compile"
        symfony console asset-map:compile
    done
}

daemon
