var config     = require('./webpack.config');
config.target  = 'node';
config.mode    = 'development';
module.exports = config;
