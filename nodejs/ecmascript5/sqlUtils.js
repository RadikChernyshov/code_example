'use strict';

var _ = require('underscore');

function _connectionFromPool(connectionPool, fn, callback) {
   connectionPool.getConnection(function(err, connection) {
      if (err) {
         return callback(err);
      }
      fn(connection, function(err, result) {
         connection.release();
         if (err) {
            return callback(err);
         }
         callback(null, result);
      });
   });
}

function _withinTransaction(connection, fn, callback) {
   connection.beginTransaction(function(err) {
      if (err) {
         return callback(err);
      }
      fn(connection, function(err, result) {
         if (err) {
            connection.rollback();
            return callback(err);
         }
         connection.commit(function(err) {
            if (err) {
               connection.rollback();
               return callback(err);
            }
            callback(null, result);
         });
      });
   });
}

function usingTransactionFromPool(connectionPool, fn, callback) {
   _connectionFromPool(connectionPool, function(connection, releaseFn) {
      _withinTransaction(connection, fn, releaseFn);
   }, callback);
}

function generateInClause(size) {
   var sql = 'in (';
   for (var i = 0; i < size; i++) {
      sql += '?';
      if (i < size - 1) {
         sql += ', ';
      }
   }
   sql += ')';
   return sql;
}

function getDateInCorrectFormat(date) {
   return 'STR_TO_DATE(\'' + date.toISOString() + '\', \'%Y-%m-%dT%H:%i:%s\')';
}

function generateBetweenClause(value) {
   var startDate = '';
   var endDate = '';
   if (value instanceof Date) {
      startDate = new Date(value.getTime());
      endDate = new Date(value.getTime());
   }
   if (value instanceof Array) {
      startDate = value[0];
      endDate = value[1];
   }
   if (startDate !== '' && endDate !== '') {
      startDate.setHours(0, 0, 0, 0);
      endDate.setHours(23, 59, 59, 999);
      return 'BETWEEN ' + getDateInCorrectFormat(startDate) + ' AND ' +
          getDateInCorrectFormat(endDate);
   }
   return '';
}

function generateWhereClauseFromFieldList(fieldList) {
   if (0 === fieldList.length) {
     return '';
  }
   var clause = ' WHERE ';
   for (var i = 0; i < fieldList.length - 1; i++) {
      var field = fieldList[i];
      clause += field + ' = ' + '? AND ';
   }
   clause += fieldList[fieldList.length - 1] + ' = ?';
   return clause;
}

function reduceFieldListArrayWithPrefix(fieldList, prefix) {
   if (!Array.isArray(fieldList)) {
      return '';
   }
   var fieldListLastIndex = fieldList.length - 1;
   return fieldList.reduce(function(fieldString, field, index) {
      fieldString += prefix + field;
      if (index !== fieldListLastIndex) {
         fieldString += ', ';
      }
      return fieldString;
   }, '');
}

function getUpdateValues(columns) {
   return _.map(columns, function(value) {
      return '`' + value + '` = VALUES(`' + value + '`)';
   });
}

module.exports = {
   usingTransactionFromPool: usingTransactionFromPool,
   generateInClause: generateInClause,
   generateBetweenClause: generateBetweenClause,
   generateWhereClauseFromFieldList: generateWhereClauseFromFieldList,
   reduceFieldListArrayWithPrefix: reduceFieldListArrayWithPrefix,
   getUpdateValues: getUpdateValues,
};
