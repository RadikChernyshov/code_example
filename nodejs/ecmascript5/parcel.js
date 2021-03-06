'use strict';

var dataUtils = require('../../../utilities/dataUtils');

var defaultToNull = function(value) {
   return value || null;
};

var defaultToEmpty = function(value) {
   return value || '';
};

var _mapExampleToSql = function(example) {
   return {
      id: defaultToNull(example.id),
      example_connected_id: defaultToNull(example.example_connectedId),
      example_number: defaultToNull(example.exampleNumber),
      examplenet_label_number: defaultToNull(example.examplenetLabelNumber),
      is_voided: dataUtils.setBoolean(example.isVoided, false),
      barcode: defaultToNull(example.barcode),
      examplenet_barcode: defaultToNull(example.examplenetBarcode),
      examplenet_data: defaultToNull(example.examplenetData),
      created_date: example.createdDate || new Date(),
      updated_date: defaultToNull(example.updatedDate),
      customer_ref1: defaultToEmpty(example.customerRef1),
      customer_ref2: defaultToEmpty(example.customerRef2),
      customer_ref3: defaultToEmpty(example.customerRef3),
      customer_ref4: defaultToEmpty(example.customerRef4),
   };
};

var getExampleByExampleConnectedId = function(connection, params, callback) {
   var values = [params.id];
   var sql = 'SELECT * FROM example WHERE example_connected_id = ?';
   connection.query(sql, values, function(err, data) {
      callback(err, data);
   });
};

var insertExample = function(connection, params, callback) {
   var values = [_mapExampleToSql(params.example)];
   var sql = 'INSERT INTO example SET ?';
   connection.query(sql, values, function(err, result) {
      return callback(err, result ? result : null);
   });

};

var updateExampleById = function(connection, params, callback) {
   var example = params.example;
   if (params.toSql) {
      example = _mapExampleToSql(params.example);
   }
   var values = [
      example,
      params.example.id,
   ];
   var sql = 'UPDATE example SET ?, updated_date = UTC_TIMESTAMP() WHERE id = ?';
   connection.query(sql, values, callback);
};

var getExampleNumber = function(connection, params, callback) {
   var sql = 'select next_example_value() as value';
   connection.query(sql, [], callback);
};

var voidExamplesBySomeId = function(connection, params, callback) {
   var sql = [
      'UPDATE example',
      'INNER JOIN example_connected ON example.example_connected_id = example_connected.id',
      'INNER JOIN some ON example_connected.some_id = some.id',
      'SET example.is_voided = ?, example.updated_date = CURRENT_TIMESTAMP(), example.void_status_date = CURRENT_TIMESTAMP()',
      'WHERE some.id = ?',
   ].join(' ');
   var values = [params.isVoided, params.id];
   connection.query(sql, values, callback);
};

var voidExamplesBySomeIdAndNumber = function(connection, params, callback) {
   var sqlQuery = [
      'UPDATE example',
      'INNER JOIN example_connected ON example.example_connected_id = example_connected.id',
      'INNER JOIN some ON example_connected.some_id = some.id',
      'SET example.is_voided = ?, example.updated_date = CURRENT_TIMESTAMP(), example.void_status_date = CURRENT_TIMESTAMP()',
      'WHERE some.id = ? AND example.example_number IN (?)',
   ].join(' ');
   connection.query(sqlQuery, [params.isVoided, params.id, params.numbers], callback);
};

var getSomeExamples = function(someId, connection, callback) {
   var sqlQuery = [
      'SELECT p.* FROM example p',
      'INNER JOIN example_connected c ON p.example_connected_id = c.id',
      'INNER JOIN some s ON c.some_id = s.id',
      'WHERE s.id = ?',
   ].join(' ');
   connection.query(sqlQuery, [someId], function(err, data) {
      if (err) {
         callback(err);
      }
      callback(null, data);
   });
};

var getSomeExampleConnected = function(someId, connection, callback) {
   var sqlQuery = [
      'SELECT example_connected.*, some.is_voided FROM example_connected',
      'INNER JOIN some ON example_connected.some_id = some.id',
      'WHERE some.id = ? LIMIT 1',
   ].join(' ');
   connection.query(sqlQuery, [someId], function(err, result) {
      if (err) {
         return callback(err);
      }
      callback(null, result[0]);
   });
};

var updateSomeExampleConnected = function(
    exampleConnectedId, exampleConnectedWeight, examplesCount, connection,
    callback) {
   var sqlQuery = [
      'UPDATE example_connected',
      'SET total_weight = ?, updated_date = CURRENT_TIMESTAMP(), number_of_examples = (SELECT COUNT(id) FROM example WHERE example.example_connected_id = ? AND is_voided = FALSE)',
      'WHERE id = ?',
   ].join(' ');
   connection.query(sqlQuery,
       [exampleConnectedWeight, exampleConnectedId, exampleConnectedId],
       function(err) {
          if (err) {
             return callback(err);
          }
          callback(null);
       });
};

module.exports = {
   getExmpleByexampleConnectedId: getExampleByExampleConnectedId,
   insertExample: insertExample,
   updateexampleById: updateExampleById,
   getDpdExampleNumber: getExampleNumber,
   voidExamplesBySomeId: voidExamplesBySomeId,
   voidExamplesBySomeIdAndNumber: voidExamplesBySomeIdAndNumber,
   getSomeExamples: getSomeExamples,
   getSomeExampleConnected: getSomeExampleConnected,
   updateSomeExampleConnected: updateSomeExampleConnected,
};
