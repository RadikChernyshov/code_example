'use strict';

var async = require('async');
var fs = require('fs');
var path = require('path');
var process = require('process');
var Env = require('./Environment');
var Messaging = require('./Messaging');
var version = require('../package.json').version;
var appInstance = null;
var msgInstance = new Messaging();
var exports = module.exports = {};

Object.defineProperty(exports, 'app', {
   get: function() {
      return appInstance;
   },
});

Object.defineProperty(exports, 'messaging', {
   get: function() {
      return msgInstance;
   },
});

Object.defineProperty(exports, 'version', {
   get: function() {
      return version;
   },
});

function getClusterOption(appPath) {
   var appConfigPath = path.resolve(appPath, 'configs/app.config.js');
   var envAppConfigPath = path.resolve(appPath, 'configs/' + Env.title +
       '/app.config.js');
   var appConfig = require(appConfigPath);
   var clusterSupport = appConfig.cluster || null;
   var envAppConfig = null;
   if (fs.existsSync(envAppConfigPath)) {
      envAppConfig = require(envAppConfigPath);
      if (envAppConfig.cluster) {
         clusterSupport = envAppConfig.cluster;
      }
   }
   return clusterSupport;
}

function createApp(appPath, callback) {
   var clusterOption;
   var Application;
   if (appInstance) {
      if (appPath !== appInstance.appPath) {
         return callback(new Error('Application is already initialized'));
      }
      return callback(null, appInstance);
   }
   clusterOption = getClusterOption(appPath);
   if (clusterOption) {
      appInstance = require('./cluster')(appPath, msgInstance, clusterOption,
          callback);
   } else {
      Application = require('./Application');
      appInstance = new Application(appPath, msgInstance);
   }
   appInstance.init(callback);
   return appInstance;
}

function createStartApp(appPath, callback) {
   async.waterfall([
      function(callback) {
         createApp(appPath, callback);
      },
      
      function(app, callback) {
         app.start(callback);
      },
   ], callback);
}

function destroyApplication(callback) {
   if (!appInstance) {
      return callback(null);
   }
   msgInstance.reset();
   async.waterfall([
      function(callback) {
         appInstance.destroy(callback);
      },
      function(app, callback) {
         appInstance = null;
         return callback(null);
      },
   ], callback);
}

exports.createApplication = createApp;
exports.createApplicationAndStart = createStartApp;
exports.destroyApplication = destroyApplication;