#!/bin/bash

docker run --rm --tmpfs /tmp -v $PWD/archive:/mnt/output:rw -e OUTPUT_FOLDER="/mnt/output" -v $PWD:/mnt/source:ro vbatts/slackware:latest /mnt/source/pkg_build.sh