<?php
// verify_transaction.php - Fixed Final Version
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: Starting verification...<br>";

// Try to include database connection
echo "Step 2: Including conn_db.php...<br>";
include("conn_db.php");

// Check if connection exists
echo "Step 3: Checking connection variable...<br>";
if (isset($conn)) {
    echo "Connection variable exists: " . gettype($conn) . "<br>";
    
    if (is_object($conn)) {
        echo "Connection appears to be valid<br>";
    } else {
        echo "Connection is not an object<br>";
    }
} else {
    echo "Connection variable not found<br>";
    die("Database connection failed");
}

// Check session for logged in user
echo "Step 4: Checking session...<br>";
if (!isset($_SESSION['cid'])) {
    echo "User not logged in. Redirecting to login...<br>";
    header("Location: login.php");
    exit();
}

echo "User is logged in successfully!<br>";
echo "Customer ID: " . $_SESSION['cid'] . "<br>";

// Check if form was submitted
echo "Step 5: Checking request method...<br>";
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Not a POST request. Redirecting...<br>";
    header("Location: payment.php");
    exit();
}

echo "POST request confirmed<br>";

// Debug: Show all POST data
echo "Step 6: POST data received:<br>";
foreach ($_POST as $key => $value) {
    echo "$key: $value<br>";
}

// Validate required fields
echo "Step 7: Checking required fields...<br>";
$required_fields = ['name', 'email', 'rollno', 'year', 'branch_section', 'tid', 'cftid', 'tandc'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $missing_fields[] = $field;
        echo "Field $field: MISSING<br>";
    } else {
        echo "Field $field: OK<br>";
    }
}

if (!empty($missing_fields)) {
    echo "Missing required fields: " . implode(', ', $missing_fields) . "<br>";
    header("Location: payment.php?error=missing_fields");
    exit();
}

// Process the form data
echo "Step 8: Processing data...<br>";
$customer_id = $_SESSION['cid'];
$name = mysqli_real_escape_string($conn, trim($_POST['name']));
$email = mysqli_real_escape_string($conn, trim($_POST['email']));
$rollno = mysqli_real_escape_string($conn, trim($_POST['rollno']));
$year = mysqli_real_escape_string($conn, trim($_POST['year']));
$branch_section = mysqli_real_escape_string($conn, trim($_POST['branch_section']));
$tid = mysqli_real_escape_string($conn, trim($_POST['tid']));
$cftid = mysqli_real_escape_string($conn, trim($_POST['cftid']));
$delivery_time = !empty($_POST['delivery_time']) ? mysqli_real_escape_string($conn, trim($_POST['delivery_time'])) : null;
$delivery_notes = !empty($_POST['delivery_notes']) ? mysqli_real_escape_string($conn, trim($_POST['delivery_notes'])) : null;

// Step 8.5: Check if transaction already exists
echo "Step 8.5: Checking for existing transaction...<br>";
$check_query = "SELECT id, order_cost, name FROM transaction WHERE tid = ?";
$stmt = mysqli_prepare($conn, $check_query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $tid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($existing = mysqli_fetch_assoc($result)) {
        echo "Transaction already exists! ID: " . $existing['id'] . ", Cost: ₹" . $existing['order_cost'] . "<br>";
        echo "Redirecting to success page...<br>";
        
        // Set session data for success page
        $_SESSION['last_order_id'] = $existing['id'];
        $_SESSION['last_transaction_id'] = $tid;
        
        // Redirect with order header parameter
        header("Location: order_success.php?orh=" . $existing['id']);
        exit();
    }
    mysqli_stmt_close($stmt);
}

// Get order total from session or calculate from cart
echo "Step 9: Getting order total...<br>";
echo "Current session data:<br>";
foreach ($_SESSION as $key => $value) {
    if (is_string($value) || is_numeric($value)) {
        echo "$key => $value<br>";
    }
}

$order_total = 0;

// Try to get from session first
if (isset($_SESSION['order_cost']) && $_SESSION['order_cost'] > 0) {
    $order_total = $_SESSION['order_cost'];
    echo "Order total from session: ₹$order_total<br>";
} else {
    echo "Session cart data not found. Calculating from database cart table...<br>";
    
    // Get cart total from database
    $cart_query = "SELECT ct.*, f.* FROM cart ct LEFT JOIN food f ON ct.f_id = f.f_id WHERE ct.c_id = ?";
    $cart_stmt = mysqli_prepare($conn, $cart_query);
    
    if ($cart_stmt) {
        mysqli_stmt_bind_param($cart_stmt, "i", $customer_id);
        mysqli_stmt_execute($cart_stmt);
        $cart_result = mysqli_stmt_get_result($cart_stmt);
        
        $cart_total = 0;
        $item_count = 0;
        
        // Try different price column names
        $price_columns = ['price', 'f_price', 'food_price', 'item_price'];
        $price_column = null;
        
        while ($cart_item = mysqli_fetch_assoc($cart_result)) {
            if ($price_column === null) {
                // Find which price column exists
                foreach ($price_columns as $col) {
                    if (array_key_exists($col, $cart_item)) {
                        $price_column = $col;
                        echo "Found price column: $col<br>";
                        break;
                    } else {
                        echo "Column '$col' not found, trying next...<br>";
                    }
                }
            }
            
            if ($price_column && isset($cart_item[$price_column])) {
                $quantity = isset($cart_item['ct_amount']) ? $cart_item['ct_amount'] : 1;
                $price = $cart_item[$price_column];
                $cart_total += ($quantity * $price);
                $item_count++;
                echo "Item: {$cart_item['f_name']} - Qty: $quantity × Price: ₹$price = ₹" . ($quantity * $price) . "<br>";
            }
        }
        
        if ($cart_total > 0) {
            $order_total = $cart_total;
            echo "Calculated order total: ₹$order_total<br>";
        } else {
            echo "No price column found. Checking cart items and using default pricing...<br>";
            // Reset result pointer
            mysqli_data_seek($cart_result, 0);
            $default_total = mysqli_num_rows($cart_result) * 20; // ₹20 per item default
            $order_total = $default_total;
            echo "Using default calculation: " . mysqli_num_rows($cart_result) . " items × ₹20 = ₹$order_total<br>";
        }
        
        mysqli_stmt_close($cart_stmt);
    }
}

echo "Final order cost: $order_total<br>";

// Prepare data for insertion
echo "Step 10: Preparing data for insertion...<br>";
$pickup_time = $delivery_time ? $delivery_time : null;
$pickup_notes = $delivery_notes ? $delivery_notes : null;

echo "Pickup time: " . ($pickup_time ? $pickup_time : "NULL") . "<br>";
echo "Pickup notes: " . ($pickup_notes ? $pickup_notes : "NULL") . "<br>";

// Insert transaction into database
echo "Step 11: Inserting transaction...<br>";
$insert_query = "INSERT INTO transaction (tid, c_id, order_cost, name, email, rollno, year, branch_section, pickup_time, pickup_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$insert_stmt = mysqli_prepare($conn, $insert_query);

if (!$insert_stmt) {
    echo "Prepare failed: " . mysqli_error($conn) . "<br>";
    die("Database prepare error");
}

// Bind parameters - Fixed parameter count
mysqli_stmt_bind_param($insert_stmt, "sidsssssss", 
    $tid,           // s - string
    $customer_id,   // i - integer  
    $order_total,   // d - decimal
    $name,          // s - string
    $email,         // s - string
    $rollno,        // s - string
    $year,          // s - string
    $branch_section,// s - string
    $pickup_time,   // s - string (can be NULL)
    $pickup_notes   // s - string (can be NULL)
);

// Execute the statement
if (mysqli_stmt_execute($insert_stmt)) {
    $insert_id = mysqli_insert_id($conn);
    echo "Step 12: Transaction inserted successfully!<br>";
    echo "Insert ID: $insert_id<br>";
    
    // Store success data in session
    $_SESSION['last_order_id'] = $insert_id;
    $_SESSION['last_transaction_id'] = $tid;
    $_SESSION['transaction_success'] = true;
    
    // Step 12: Copy cart items to transaction_items table
    echo "Step 12: Processing cart items...<br>";
    try {
        // Get cart items for this customer - Updated to match your cart table structure
        $cart_query = "SELECT ct.f_id, ct.cart_qty as quantity, f.f_name, f.f_price, ct.cart_note 
                       FROM cart ct 
                       JOIN food f ON ct.f_id = f.f_id 
                       WHERE ct.c_id = ?";
        $cart_stmt = mysqli_prepare($conn, $cart_query);
        
        if (!$cart_stmt) {
            throw new Exception("Failed to prepare cart query: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($cart_stmt, "i", $customer_id);
        mysqli_stmt_execute($cart_stmt);
        $cart_result = mysqli_stmt_get_result($cart_stmt);
        
        // Insert each cart item into transaction_items
        $item_insert_query = "INSERT INTO transaction_items (tid, f_id, quantity, unit_price, total_price, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $item_stmt = mysqli_prepare($conn, $item_insert_query);
        
        if (!$item_stmt) {
            throw new Exception("Failed to prepare item insert query: " . mysqli_error($conn));
        }
        
        while ($cart_item = mysqli_fetch_assoc($cart_result)) {
            $f_id = $cart_item['f_id'];
            $quantity = $cart_item['quantity'];
            $unit_price = $cart_item['f_price'];
            $total_price = $unit_price * $quantity;
            $notes = $cart_item['cart_note'] ?? '';
            
            mysqli_stmt_bind_param($item_stmt, "siidds", $tid, $f_id, $quantity, $unit_price, $total_price, $notes);
            if (!mysqli_stmt_execute($item_stmt)) {
                throw new Exception("Failed to insert cart item: " . mysqli_stmt_error($item_stmt));
            }
            echo "Added item: {$cart_item['f_name']} x{$quantity}<br>";
        }
        
        // Clear cart after successful order
        $clear_cart_query = "DELETE FROM cart WHERE c_id = ?";
        $clear_stmt = mysqli_prepare($conn, $clear_cart_query);
        if ($clear_stmt) {
            mysqli_stmt_bind_param($clear_stmt, "i", $customer_id);
            mysqli_stmt_execute($clear_stmt);
            echo "Cart cleared successfully!<br>";
            mysqli_stmt_close($clear_stmt);
        }
        
        mysqli_stmt_close($cart_stmt);
        mysqli_stmt_close($item_stmt);
        
    } catch (Exception $e) {
        echo "Error processing cart items: " . $e->getMessage() . "<br>";
        // Don't fail the transaction, just log the error
    }
    
    echo "Step 13: Redirecting to success page...<br>";
    
    // Redirect with order header parameter
    header("Location: order_success.php?orh=" . $insert_id);
    exit();
    
} else {
    echo "Execute failed: " . mysqli_stmt_error($insert_stmt) . "<br>";
    echo "Error: " . mysqli_error($conn) . "<br>";
    die("Transaction insertion failed");
}

mysqli_stmt_close($insert_stmt);
?>