"use strict";

var dataUtils = require("../../../utilities/dataUtils");

var defaultToNull = function (value) {
    return value || null;
};

var defaultToEmpty = function (value) {
    return value || "";
};

var _mapParcelToSql = function (parcel) {
    return {
        id: defaultToNull(parcel.id),
        consignment_id: defaultToNull(parcel.consignmentId),
        parcel_number: defaultToNull(parcel.parcelNumber),
        parcelnet_label_number: defaultToNull(parcel.parcelnetLabelNumber),
        is_voided: dataUtils.setBoolean(parcel.isVoided, false),
        barcode: defaultToNull(parcel.barcode),
        parcelnet_barcode: defaultToNull(parcel.parcelnetBarcode),
        parcelnet_data: defaultToNull(parcel.parcelnetData),
        created_date: parcel.createdDate || new Date(),
        updated_date: defaultToNull(parcel.updatedDate),
        customer_ref1: defaultToEmpty(parcel.customerRef1),
        customer_ref2: defaultToEmpty(parcel.customerRef2),
        customer_ref3: defaultToEmpty(parcel.customerRef3),
        customer_ref4: defaultToEmpty(parcel.customerRef4)
    };
};

// esgold: queryShipmentByConsolidationCriteria
// esgold: queryShipmentByIdAccountIdUserId
var getParcelByConsignmentId = function (mysql, params, callback) {

    var values = [
        params.id
    ];

    var sql = "SELECT * FROM parcel WHERE consignment_id = ?";

    mysql.query(sql, values, function (err, data) {

        callback(err, data);
    });
};

// esgold: updateShipmentById
var insertParcel = function (mysql, params, callback) {

    var values = [

        _mapParcelToSql(params.parcel)

    ];

    var sql = "INSERT INTO parcel SET ?";


    mysql.query(sql, values, function (err, result) {
        return callback(err, result ? result : null);
    });

};

// esgold: updateShipmentById
var updateParcelById = function (mysql, params, callback) {

    var parcel = params.parcel;

    if (params.toSql) {
        parcel = _mapParcelToSql(params.parcel);
    }

    var values = [
        parcel,
        params.parcel.id
    ];

    var sql = "UPDATE parcel SET ?, updated_date = UTC_TIMESTAMP() WHERE id = ?";

    mysql.query(sql, values, callback);
};

// esgold: identifyShipment
var getDpdParcelNumber = function (mysql, params, callback) {
    var sql = "select next_parcel_value() as value";

    mysql.query(sql, [], callback);
};

var voidParcelsByShipmentId = function (mysql, params, callback) {

    var sql = [
        "UPDATE parcel",
        "INNER JOIN consignment ON parcel.consignment_id = consignment.id",
        "INNER JOIN shipment ON consignment.shipment_id = shipment.id",
        "SET parcel.is_voided = ?, parcel.updated_date = CURRENT_TIMESTAMP(), parcel.void_status_date = CURRENT_TIMESTAMP()",
        "WHERE shipment.id = ?"
    ].join(" ");

    var values = [params.isVoided, params.id];

    mysql.query(sql, values, callback);
};

var voidParcelsByShipmentIdAndNumber = function (mysql, params, callback) {
    var sqlQuery = [
        "UPDATE parcel",
        "INNER JOIN consignment ON parcel.consignment_id = consignment.id",
        "INNER JOIN shipment ON consignment.shipment_id = shipment.id",
        "SET parcel.is_voided = ?, parcel.updated_date = CURRENT_TIMESTAMP(), parcel.void_status_date = CURRENT_TIMESTAMP()",
        "WHERE shipment.id = ? AND parcel.parcel_number IN (?)"
    ].join(" ");
    mysql.query(sqlQuery, [params.isVoided, params.id, params.numbers], callback);
};

var getShipmentParcels = function (shipmentId, mysql, callback) {
    var sqlQuery = [
        "SELECT p.* FROM parcel p",
        "INNER JOIN consignment c ON p.consignment_id = c.id",
        "INNER JOIN shipment s ON c.shipment_id = s.id",
        "WHERE s.id = ?"
    ].join(" ");
    mysql.query(sqlQuery, [shipmentId], function (err, data) {
        if (err) {
            callback(err);
        }
        callback(null, data);
    });
};

var getShipmentConsignment = function (shipmentId, mysql, callback) {
    var sqlQuery = [
        "SELECT consignment.*, shipment.is_voided FROM consignment",
        "INNER JOIN shipment ON consignment.shipment_id = shipment.id",
        "WHERE shipment.id = ? LIMIT 1"
    ].join(" ");
    mysql.query(sqlQuery, [shipmentId], function (err, result) {
        if (err) {
            return callback(err);
        }
        callback(null, result[0]);
    });
};

var updateShipmentConsignment = function (consignmentId, consignmentWeight, parcelsCount, mysql, callback) {

    var sqlQuery = [
        "UPDATE consignment",
        "SET total_weight = ?, updated_date = CURRENT_TIMESTAMP(), number_of_parcels = (SELECT COUNT(id) FROM parcel WHERE parcel.consignment_id = ? AND is_voided = FALSE)",
        "WHERE id = ?"
    ].join(" ");
    mysql.query(sqlQuery, [consignmentWeight, consignmentId, consignmentId], function (err) {
        if (err) {
            return callback(err);
        }
        callback(null);
    });
};

module.exports = {
    getParcelByConsignmentId: getParcelByConsignmentId,
    insertParcel: insertParcel,
    updateParcelById: updateParcelById,
    getDpdParcelNumber: getDpdParcelNumber,
    voidParcelsByShipmentId: voidParcelsByShipmentId,
    voidParcelsByShipmentIdAndNumber: voidParcelsByShipmentIdAndNumber,
    getShipmentParcels: getShipmentParcels,
    getShipmentConsignment: getShipmentConsignment,
    updateShipmentConsignment: updateShipmentConsignment
};
