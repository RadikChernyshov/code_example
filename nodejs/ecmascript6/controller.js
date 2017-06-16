"use strict";

import User from "../models/user.model";

function load(req, res, next, id) {
  User.get(id).then((user) => {
    req.user = user;
    return next();
  }).catch(e => next(e));
}

function get(req, res) {
  return res.json(req.user);
}

function create(req, res, next) {
  const userRecord = new User({
    username: req.body.username,
    phoneNumber: req.body.phoneNumber,
  });
  userRecord.save().then(savedUser => res.json(savedUser)).catch(e => next(e));
}

function update(req, res, next) {
  const userRecord = req.user;
  userRecord.username = req.body.username;
  userRecord.phoneNumber = req.body.phoneNumber;
  userRecord.save().then(savedUser => res.json(savedUser)).catch(e => next(e));
}

function list(req, res, next) {
  const {limit = 10, skip = 0} = req.query;
  User.list({limit, skip}).then(users => res.json(users)).catch(e => next(e));
}

function remove(req, res, next) {
  const user = req.user;
  user.remove().then(deletedUser => res.json(deletedUser)).catch(e => next(e));
}

export default {load, get, create, update, list, remove};