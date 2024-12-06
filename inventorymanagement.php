<?php
/**
 * Export to PHP Array plugin for PHPMyAdmin
 * @version 5.2.1
 */

/**
 * Database `inventorymanagement`
 */

/* `inventorymanagement`.`customers` */
$customers = array(
  array('customer_name' => 'Bruno Mars','customer_id' => '1'),
  array('customer_name' => 'Taylor Swift','customer_id' => '2')
);

/* `inventorymanagement`.`inventory` */
$inventory = array(
  array('inventory_id' => '1','product_id' => '1','stock' => '20','reorder_threshold' => '100'),
  array('inventory_id' => '2','product_id' => '3','stock' => '99','reorder_threshold' => '100'),
  array('inventory_id' => '3','product_id' => '2','stock' => '50','reorder_threshold' => '100')
);

/* `inventorymanagement`.`orders` */
$orders = array(
  array('order_id' => '2','customer_id' => '2','order_date' => '2024-12-02 00:00:00','total_amount' => '5499.00'),
  array('order_id' => '3','customer_id' => '1','order_date' => '2025-01-15 00:00:00','total_amount' => '4567.00')
);

/* `inventorymanagement`.`products` */
$products = array(
  array('product_id' => '1','product_name' => '1991 Toyota Corolla XL','category' => 'Car','sku' => 'ee90','supplier' => 'Toyota Motor Corporatio','cost' => '198999.00'),
  array('product_id' => '2','product_name' => '2021 Honda HRV','category' => 'Car','sku' => '2021RV','supplier' => 'Honda Motors','cost' => '1600000.00'),
  array('product_id' => '3','product_name' => '2024 Ford Territory','category' => 'Car','sku' => 'CX743MCA','supplier' => 'Ford Motors','cost' => '1700000.00')
);

/* `inventorymanagement`.`users` */
$users = array(
  array('user_id' => '1','username' => 'skritz','password' => '$2y$10$aW7SdjPjseKotnv0TvhD/e7E05bj5BbEgMWCpaAhtgw3WSQTLq12u','role' => 'admin')
);
