<?php
// Add this right after the opening <?php tag
echo "<h1>VERIFY TRANSACTION DEBUG</h1>";
echo "File accessed at: " . date('Y-m-d H:i:s') . "<br>";
echo "GET parameters: " . print_r($_GET, true) . "<br>";
echo "POST parameters: " . print_r($_POST, true) . "<br>";


// verify_transaction.php - Complete Fixed Version
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

// Step 9: FIRST - Process cart items BEFORE creating transaction
echo "Step 9: CRITICAL - Processing cart items FIRST...<br>";
$cart_items = [];
$order_total = 0;

try {
    // FIXED: Using correct column names from your cart table structure
    $cart_query = "SELECT 
        ct.f_id, 
        ct.ct_amount as quantity,    -- Using your actual column name
        f.f_name, 
        f.f_price, 
        ct.ct_note as cart_note      -- Using your actual column name
    FROM cart ct 
    JOIN food f ON ct.f_id = f.f_id 
    WHERE ct.c_id = ?";
    
    $cart_stmt = mysqli_prepare($conn, $cart_query);
    
    if (!$cart_stmt) {
        throw new Exception("Failed to prepare cart query: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($cart_stmt, "i", $customer_id);
    
    if (!mysqli_stmt_execute($cart_stmt)) {
        throw new Exception("Failed to execute cart query: " . mysqli_stmt_error($cart_stmt));
    }
    
    $cart_result = mysqli_stmt_get_result($cart_stmt);
    
    // Check if cart has items
    if (mysqli_num_rows($cart_result) == 0) {
        echo "ERROR: No cart items found for customer ID: $customer_id<br>";
        echo "Redirecting back to cart...<br>";
        header("Location: cart.php?error=empty_cart");
        exit();
    }
    
    echo "Found " . mysqli_num_rows($cart_result) . " items in cart:<br>";
    
    // Store cart items in array and calculate total
    while ($cart_item = mysqli_fetch_assoc($cart_result)) {
        $cart_items[] = $cart_item;
        $item_total = $cart_item['f_price'] * $cart_item['quantity'];
        $order_total += $item_total;
        
        echo "- {$cart_item['f_name']}: {$cart_item['quantity']} × ₹{$cart_item['f_price']} = ₹{$item_total}<br>";
    }
    
    echo "Total cart value: ₹{$order_total}<br>";
    
    mysqli_stmt_close($cart_stmt);
    
} catch (Exception $e) {
    echo "CRITICAL ERROR in cart processing: " . $e->getMessage() . "<br>";
    die("Cannot proceed without cart items");
}

// Step 10: Create the main transaction record
echo "Step 10: Inserting transaction record...<br>";
$pickup_time = $delivery_time ? $delivery_time : null;
$pickup_notes = $delivery_notes ? $delivery_notes : null;

$insert_query = "INSERT INTO transaction (tid, c_id, order_cost, name, email, rollno, year, branch_section, pickup_time, pickup_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$insert_stmt = mysqli_prepare($conn, $insert_query);

if (!$insert_stmt) {
    echo "Prepare failed: " . mysqli_error($conn) . "<br>";
    die("Database prepare error");
}

// Bind parameters
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

// Execute the transaction insert
if (!mysqli_stmt_execute($insert_stmt)) {
    echo "Transaction insert failed: " . mysqli_stmt_error($insert_stmt) . "<br>";
    die("Transaction insertion failed");
}

$insert_id = mysqli_insert_id($conn);
echo "Transaction inserted successfully! ID: $insert_id<br>";
mysqli_stmt_close($insert_stmt);

// Step 11: Insert cart items into transaction_items
echo "Step 11: Inserting cart items into transaction_items...<br>";

try {
    // Prepare the insert statement for transaction_items
    $item_insert_query = "INSERT INTO transaction_items (tid, f_id, quantity, unit_price, total_price, notes) VALUES (?, ?, ?, ?, ?, ?)";
    $item_stmt = mysqli_prepare($conn, $item_insert_query);
    
    if (!$item_stmt) {
        echo "CRITICAL SQL Error: " . mysqli_error($conn) . "<br>";
        echo "Failed query: $item_insert_query<br>";
        throw new Exception("Failed to prepare item insert query: " . mysqli_error($conn));
    }
    
    $items_added = 0;
    
    // Insert each cart item
    foreach ($cart_items as $cart_item) {
        $f_id = $cart_item['f_id'];
        $quantity = $cart_item['quantity'];
        $unit_price = $cart_item['f_price'];
        $total_price = $unit_price * $quantity;
        $notes = $cart_item['cart_note'] ?? '';
        
        echo "Inserting: Food ID=$f_id, Qty=$quantity, Price=₹$unit_price, Total=₹$total_price<br>";
        
        // Bind parameters for this item
        mysqli_stmt_bind_param($item_stmt, "siidds", $tid, $f_id, $quantity, $unit_price, $total_price, $notes);
        
        if (!mysqli_stmt_execute($item_stmt)) {
            echo "Item insert ERROR: " . mysqli_stmt_error($item_stmt) . "<br>";
            echo "Failed to insert: Food ID=$f_id, TID=$tid<br>";
            throw new Exception("Failed to insert cart item: " . mysqli_stmt_error($item_stmt));
        }
        
        $items_added++;
        echo "✅ Successfully added: {$cart_item['f_name']} x{$quantity}<br>";
    }
    
    echo "🎉 SUCCESS: Added $items_added items to transaction_items!<br>";
    mysqli_stmt_close($item_stmt);
    
    // Verify the items were inserted
    echo "Step 12: Verifying transaction_items insertion...<br>";
    $verify_query = "SELECT COUNT(*) as item_count FROM transaction_items WHERE tid = ?";
    $verify_stmt = mysqli_prepare($conn, $verify_query);
    mysqli_stmt_bind_param($verify_stmt, "s", $tid);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);
    $verify_data = mysqli_fetch_assoc($verify_result);
    
    echo "Verification: Found {$verify_data['item_count']} items in transaction_items for TID: $tid<br>";
    mysqli_stmt_close($verify_stmt);
    
    if ($verify_data['item_count'] != count($cart_items)) {
        echo "WARNING: Mismatch in item count!<br>";
    } else {
        echo "✅ Perfect! All items verified in database<br>";
    }
    
} catch (Exception $e) {
    echo "CRITICAL ERROR inserting transaction items: " . $e->getMessage() . "<br>";
    // Don't die here - transaction was created, just items failed
}

// Step 13: Clear cart after successful order
echo "Step 13: Clearing cart...<br>";
$clear_cart_query = "DELETE FROM cart WHERE c_id = ?";
$clear_stmt = mysqli_prepare($conn, $clear_cart_query);
if ($clear_stmt) {
    mysqli_stmt_bind_param($clear_stmt, "i", $customer_id);
    if (mysqli_stmt_execute($clear_stmt)) {
        echo "✅ Cart cleared successfully!<br>";
    } else {
        echo "Warning: Failed to clear cart: " . mysqli_stmt_error($clear_stmt) . "<br>";
    }
    mysqli_stmt_close($clear_stmt);
}

// Step 14: Set session success data
echo "Step 14: Setting success session data...<br>";
$_SESSION['last_order_id'] = $insert_id;
$_SESSION['last_transaction_id'] = $tid;
$_SESSION['transaction_success'] = true;

echo "Step 15: Redirecting to success page...<br>";

// Final verification before redirect
echo "Final verification before redirect:<br>";
echo "- Transaction ID: $tid<br>";
echo "- Order ID: $insert_id<br>";
echo "- Items processed: " . count($cart_items) . "<br>";

// Redirect to success page
header("Location: order_success.php?orh=" . $insert_id);
exit();
?>