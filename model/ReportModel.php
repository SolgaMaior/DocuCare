<?php
// model/databases/reportsdb.php

require_once('model/databases/db_con.php');

/**
 * Get citizen statistics
 */
function get_citizen_statistics($purokID = null, $startDate = null, $endDate = null) {
    global $db;

    $query = "SELECT 
                COUNT(*) as total_citizens,
                SUM(CASE WHEN sex = 'Male' THEN 1 ELSE 0 END) as male_count,
                SUM(CASE WHEN sex = 'Female' THEN 1 ELSE 0 END) as female_count,
                SUM(CASE WHEN civilstatus = 'Single' THEN 1 ELSE 0 END) as single_count,
                SUM(CASE WHEN civilstatus = 'Married' THEN 1 ELSE 0 END) as married_count,
                SUM(CASE WHEN civilstatus = 'Widowed' THEN 1 ELSE 0 END) as widowed_count,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 18 THEN 1 ELSE 0 END) as minors,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 18 AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 60 THEN 1 ELSE 0 END) as adults,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 60 THEN 1 ELSE 0 END) as seniors
              FROM citizens 
              WHERE isArchived = 0";

    $params = [];
    if ($purokID) {
        $query .= " AND purokID = ?";
        $params[] = $purokID;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get illness statistics
 */
function get_illness_statistics($purokID = null, $startDate = null, $endDate = null) {
    global $db;

    $query = "SELECT 
                i.illness_name,
                COUNT(ir.recordID) as case_count,
                p.purokName
              FROM illness_records ir
              JOIN illnesses i ON ir.illness_id = i.illness_id
              JOIN purok p ON ir.purokID = p.purokID
              WHERE 1=1";

    $params = [];

    if ($purokID) {
        $query .= " AND ir.purokID = ?";
        $params[] = $purokID;
    }

    if ($startDate) {
        $query .= " AND ir.record_date >= ?";
        $params[] = $startDate;
    }

    if ($endDate) {
        $query .= " AND ir.record_date <= ?";
        $params[] = $endDate;
    }

    $query .= " GROUP BY i.illness_id, i.illness_name, p.purokName
                ORDER BY case_count DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get appointment statistics
 */
function get_appointment_statistics($startDate = null, $endDate = null) {
    global $db;

    $query = "SELECT 
                COUNT(*) as total_appointments,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'Denied' THEN 1 ELSE 0 END) as denied
              FROM appointments
              WHERE 1=1";

    $params = [];

    if ($startDate) {
        $query .= " AND schedule >= ?";
        $params[] = $startDate;
    }

    if ($endDate) {
        $query .= " AND schedule <= ?";
        $params[] = $endDate;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get inventory statistics
 */
function get_inventory_statistics() {
    global $db;

    $query = "SELECT 
                COUNT(*) as total_items,
                SUM(stock) as total_stock,
                SUM(CASE WHEN status = 'in-stock' THEN 1 ELSE 0 END) as in_stock,
                SUM(CASE WHEN status = 'low-stock' THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN status = 'out-stock' THEN 1 ELSE 0 END) as out_stock,
                SUM(CASE WHEN category = 'medicine' THEN 1 ELSE 0 END) as medicines,
                SUM(CASE WHEN category = 'equipment' THEN 1 ELSE 0 END) as equipment
              FROM inventory";

    $stmt = $db->query($query);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get purok list
 */
function get_puroks() {
    global $db;
    $stmt = $db->query("SELECT purokID, purokName FROM purok ORDER BY purokName ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get citizens by purok
 */
function get_citizens_by_purok_report($purokID = null) {
    global $db;

    $query = "SELECT 
                p.purokName,
                COUNT(c.citID) as citizen_count
              FROM purok p
              LEFT JOIN citizens c ON p.purokID = c.purokID AND c.isArchived = 0";

    $params = [];

    if ($purokID) {
        $query .= " WHERE p.purokID = ?";
        $params[] = $purokID;
    }

    $query .= " GROUP BY p.purokID, p.purokName
                ORDER BY p.purokName ASC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get detailed citizen list for report
 */
function get_citizens_list_report($purokID = null, $limit = 100) {
    global $db;

    $query = "SELECT 
                c.citID,
                CONCAT(c.firstname, ' ', COALESCE(c.middlename, ''), ' ', c.lastname) as full_name,
                c.birth_date,
                TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE()) as age,
                c.sex,
                c.civilstatus,
                c.occupation,
                c.contactnum,
                p.purokName
              FROM citizens c
              LEFT JOIN purok p ON c.purokID = p.purokID
              WHERE c.isArchived = 0";

    $params = [];

    if ($purokID) {
        $query .= " AND c.purokID = ?";
        $params[] = $purokID;
    }

    $query .= " ORDER BY c.lastname, c.firstname
                LIMIT ?";

    $stmt = $db->prepare($query);
    if ($purokID) {
        $stmt->bindValue(1, $purokID, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get illness trends over time
 */
function get_illness_trends($months = 6) {
    global $db;

    $query = "SELECT 
                DATE_FORMAT(ir.record_date, '%Y-%m') as month,
                i.illness_name,
                COUNT(ir.recordID) as count
              FROM illness_records ir
              JOIN illnesses i ON ir.illness_id = i.illness_id
              WHERE ir.record_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
              GROUP BY DATE_FORMAT(ir.record_date, '%Y-%m'), i.illness_id, i.illness_name
              ORDER BY month DESC, count DESC";

    $stmt = $db->prepare($query);
    $stmt->execute([$months]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get low stock items
 */
function get_low_stock_items() {
    global $db;
    $query = "SELECT name, stock, category, status
              FROM inventory
              WHERE status IN ('low-stock', 'out-stock')
              ORDER BY stock ASC, name ASC";

    $stmt = $db->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
