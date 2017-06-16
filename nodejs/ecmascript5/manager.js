"use strict";

var all = require("./require");
var extend = require("extend")
var objectPath = require("object-path")
var util = require("util");

function Manager(kind, opts) {
    this._kind = kind;
    this._resources = {};
    this._opts = opts || {};
}

Manager.IGONRE_DIRECTORIES = /^\.(git|svn)$/;

Manager.prototype.load = function (path, callback) {
    var recursive = this._opts.recursive || true;
    var filter = this._opts.filter || /(.+)\.js$/;
    var excludeDirs = this._opts.excludeDirs || Manager.IGONRE_DIRECTORIES;
    var resolve = this._opts.resolve;
    var opts = {
        dirname: path,
        filter: filter,
        excludeDirs: excludeDirs,
        recursive: recursive,
        resolve: resolve
    };
    all(opts, true, function (err, objects) {
        if (err) {
            return callback(err);
        }
        this._resources = extend(true, this._resources, objects);
        return callback(null, this);
    }.bind(this));
};

Manager.prototype.merge = function (src) {
    if (this._kind !== src._kind) {
        throw new Error(util.format("Cannot merge resources of different kind/type", this._kind, src._kind));
    }
    this._resources = extend(true, this._resources, src._resources);
};

Manager.prototype.find = function (path) {
    return objectPath.get(this._resources, path);
};

Manager.prototype.__defineGetter__("resources", function () {
    return this._resources;
});

module.exports = Manager;