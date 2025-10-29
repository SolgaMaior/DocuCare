<?php
// model/Inventorydb.php


function get_inventory($category = 'all', $search = '') {
    global $db;

    $query = "SELECT * FROM inventory WHERE 1=1";
    $params = [];

    if ($category !== 'all') {
        $query .= " AND category = ?";
        $params[] = $category;
    }

    if (!empty($search)) {
        $query .= " AND name LIKE ?";
        $params[] = "%$search%";
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function update_multiple_stocks($stocks) {
    global $db;
    $stmt = $db->prepare("UPDATE inventory SET stock = :stock WHERE id = :id");
    foreach ($stocks as $id => $stock) {
        $stmt->execute([
            ':id' => (int)$id,
            ':stock' => (int)$stock
        ]);
    }
}

function add_inventory_item($name, $category, $stock) {
    global $db;
    $stmt = $db->prepare("INSERT INTO inventory (name, category, stock) VALUES (:name, :category, :stock)");
    $stmt->execute([
        ':name' => $name,
        ':category' => $category,
        ':stock' => $stock
    ]);
}


function delete_inventory_item($id) {
    global $db;

    $query = "DELETE FROM inventory WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', intval($id), PDO::PARAM_INT);
    $statement->execute();

    return $statement->rowCount() > 0;
}

function calculate_stock_status($stock) {
    if ($stock <= 0) return 'out-stock';
    if ($stock <= 5) return 'low-stock';
    return 'in-stock';
}

function get_inventory_statistics() {
    global $db;

    $query = "
        SELECT 
            COUNT(*) AS total_items,
            SUM(CASE WHEN status = 'in-stock' THEN 1 ELSE 0 END) AS in_stock,
            SUM(CASE WHEN status = 'low-stock' THEN 1 ELSE 0 END) AS low_stock,
            SUM(CASE WHEN status = 'out-stock' THEN 1 ELSE 0 END) AS out_stock
        FROM inventory
    ";

    $statement = $db->prepare($query);
    $statement->execute();

    return $statement->fetch(PDO::FETCH_ASSOC);
}
