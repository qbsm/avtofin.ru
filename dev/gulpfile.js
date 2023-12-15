const path = require('path');
const gulpfile = require('ismart-gulpfile');
const packageJSON = require('./package.json');
gulpfile(path.resolve(), packageJSON);
