#!/bin/bash
export NODE_ENV=production && set -e && node db/init.js && node app.js --prod
#export NODE_ENV=development && set -e && node db/init.js && node app.js
