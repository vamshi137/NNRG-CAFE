<?php
session_start();
include("../conn_db.php");
include('../head.php');

if($_SESSION["utype"]!="ADMIN"){
    header("location: ../restricted.php");
    exit(1);
}

// Get the transaction ID from URL
$tid = isset($_GET['tid']) ? $_GET['tid'] : '';

if(empty($tid)) {
    echo "<div class='alert alert-danger'>Transaction ID is required!</div>";
    exit(1);
}

echo "<!-- Debug: TID received = " . $tid . " -->";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>Order Details</h2>
                
                <?php
                try {
                    // Get main transaction details
                    $transaction_query = "SELECT * FROM transaction WHERE tid = ?";
                    $stmt = $conn->prepare($transaction_query);
                    $stmt->bind_param("s", $tid);
                    $stmt->execute();
                    $transaction_result = $stmt->get_result();
                    
                    if($transaction_result->num_rows == 0) {
                        echo "<div class='alert alert-danger'>Transaction not found!</div>";
                        echo "<!-- Debug: No transaction found for TID = " . $tid . " -->";
                        exit(1);
                    }
                    
                    $transaction = $transaction_result->fetch_assoc();
                    echo "<!-- Debug: Transaction found - ID: " . $transaction['id'] . " -->";
                    
                    ?>
                    
                    <!-- Order Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Order Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($transaction['tid']); ?></p>
                                    <p><strong>Order Date:</strong> <?php echo date('Y-m-d H:i:s', strtotime($transaction['created_at'])); ?></p>
                                    <p><strong>Status:</strong> <span class="badge bg-<?php echo ($transaction['order_status'] == 'COMPLETED') ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($transaction['order_status']); ?></span></p>
                                    <p><strong>Total Cost:</strong> ₹<?php echo number_format($transaction['order_cost'], 2); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Pickup Time:</strong> <?php echo htmlspecialchars($transaction['pickup_time'] ?? 'Not specified'); ?></p>
                                    <?php if(!empty($transaction['pickup_notes'])): ?>
                                    <p><strong>Special Instructions:</strong> <?php echo htmlspecialchars($transaction['pickup_notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Customer Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($transaction['name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($transaction['email']); ?></p>
                                    <p><strong>Roll Number:</strong> <?php echo htmlspecialchars($transaction['rollno']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Year:</strong> <?php echo htmlspecialchars($transaction['year']); ?></p>
                                    <p><strong>Branch & Section:</strong> <?php echo htmlspecialchars($transaction['branch_section']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Food Items -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Food Items Ordered</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            // SIMPLE FIX: Find order by matching user and time instead of broken t_id
                            $items_found = false;
                            $items_result = null;
                            
                            // Get user info from transaction
                            $user_query = "SELECT user_id, t_date, t_time FROM transaction WHERE tid = ?";
                            $user_stmt = $conn->prepare($user_query);
                            $user_stmt->bind_param("s", $tid);
                            $user_stmt->execute();
                            $user_result = $user_stmt->get_result();
                            
                            if($user_row = $user_result->fetch_assoc()) {
                                $user_id = $user_row['user_id'];
                                $t_date = $user_row['t_date'];
                                $t_time = $user_row['t_time'];
                                
                                // Find matching order_header by user and approximate time (within 10 minutes)
                                $order_query = "SELECT orh_id FROM order_header 
                                              WHERE user_id = ? 
                                              AND orh_date = ? 
                                              AND ABS(TIME_TO_SEC(orh_time) - TIME_TO_SEC(?)) <= 600
                                              ORDER BY ABS(TIME_TO_SEC(orh_time) - TIME_TO_SEC(?)) ASC
                                              LIMIT 1";
                                
                                $order_stmt = $conn->prepare($order_query);
                                $order_stmt->bind_param("isss", $user_id, $t_date, $t_time, $t_time);
                                $order_stmt->execute();
                                $order_result = $order_stmt->get_result();
                                
                                if($order_row = $order_result->fetch_assoc()) {
                                    $orh_id = $order_row['orh_id'];
                                    
                                    // Now get the food items using the found orh_id
                                    $items_query = "SELECT 
                                        ord.ord_amount as quantity,
                                        ord.ord_buyprice as unit_price,
                                        (ord.ord_amount * ord.ord_buyprice) as total_price,
                                        ord.ord_note as note,
                                        f.f_id,
                                        f.f_name,
                                        f.f_pic,
                                        f.f_price,
                                        s.s_name as shop_name
                                    FROM order_detail ord
                                    INNER JOIN food f ON ord.f_id = f.f_id
                                    LEFT JOIN shop s ON f.s_id = s.s_id
                                    WHERE ord.orh_id = ?
                                    ORDER BY ord.ord_id";
                                    
                                    $items_stmt = $conn->prepare($items_query);
                                    $items_stmt->bind_param("i", $orh_id);
                                    $items_stmt->execute();
                                    $items_result = $items_stmt->get_result();
                                    
                                    if($items_result->num_rows > 0) {
                                        $items_found = true;
                                        echo "<!-- SUCCESS: Found items by matching user and time -->";
                                    }
                                }
                            }
                            
                            echo "<!-- Debug: Items found: " . ($items_found ? "YES" : "NO") . " -->";
                            echo "<!-- Debug: Items count: " . ($items_result ? $items_result->num_rows : "0") . " -->";
                            
                            if($items_found && $items_result->num_rows > 0) {
                                $total_items = 0;
                                $total_cost = 0;
                                
                                echo "<div class='alert alert-success mb-3'>";
                                echo "<h5>✅ Food Items Found!</h5>";
                                echo "<small class='text-muted'>Order matched by user and time (fixing broken t_id link)</small>";
                                echo "</div>";
                                
                                echo "<div class='table-responsive'>";
                                echo "<table class='table table-striped'>";
                                echo "<thead>";
                                echo "<tr>";
                                echo "<th>Image</th>";
                                echo "<th>Food Item</th>";
                                echo "<th>Shop</th>";
                                echo "<th>Quantity</th>";
                                echo "<th>Unit Price</th>";
                                echo "<th>Total Price</th>";
                                echo "<th>Notes</th>";
                                echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                
                                while($item = $items_result->fetch_assoc()) {
                                    $total_items += $item['quantity'];
                                    $total_cost += $item['total_price'];
                                    
                                    echo "<tr>";
                                    echo "<td>";
                                    // Display food image if available
                                    if(!empty($item['f_pic'])) {
                                        echo "<img src='../img/" . htmlspecialchars($item['f_pic']) . "' class='img-fluid rounded' style='width: 60px; height: 60px; object-fit: cover;' alt='" . htmlspecialchars($item['f_name']) . "'>";
                                    } else {
                                        echo "<img src='../img/default.png' class='img-fluid rounded' style='width: 60px; height: 60px; object-fit: cover;' alt='No Image'>";
                                    }
                                    echo "</td>";
                                    echo "<td><strong>" . htmlspecialchars($item['f_name'] ?? 'Unknown Item') . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($item['shop_name'] ?? 'Unknown Shop') . "</td>";
                                    echo "<td><span class='badge bg-primary'>" . intval($item['quantity']) . "</span></td>";
                                    echo "<td>₹" . number_format($item['unit_price'], 2) . "</td>";
                                    echo "<td><strong>₹" . number_format($item['total_price'], 2) . "</strong></td>";
                                    echo "<td>";
                                    if(!empty($item['note'])) {
                                        echo "<small class='text-info'>" . htmlspecialchars($item['note']) . "</small>";
                                    } else {
                                        echo "<small class='text-muted'>No notes</small>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                
                                echo "</tbody>";
                                echo "</table>";
                                echo "</div>";
                                
                                // Order Summary
                                echo "<div class='row mt-3'>";
                                echo "<div class='col-md-6 offset-md-6'>";
                                echo "<div class='card bg-light'>";
                                echo "<div class='card-body'>";
                                echo "<h5 class='card-title'>Order Summary</h5>";
                                echo "<p class='card-text'><strong>Total Items:</strong> <span class='badge bg-info'>" . $total_items . "</span></p>";
                                echo "<p class='card-text'><strong>Total Cost:</strong> <span class='text-success h5'>₹" . number_format($total_cost, 2) . "</span></p>";
                                
                                // Compare with transaction total
                                if(abs($total_cost - $transaction['order_cost']) > 0.01) {
                                    echo "<p class='card-text'><small class='text-warning'>Note: Calculated total (₹" . number_format($total_cost, 2) . ") differs from transaction total (₹" . number_format($transaction['order_cost'], 2) . ")</small></p>";
                                }
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                                
                            } else {
                                echo "<div class='alert alert-warning'>";
                                echo "<h5><i class='fas fa-exclamation-triangle'></i> No food items found for this order</h5>";
                                echo "<p>This could mean:</p>";
                                echo "<ul>";
                                echo "<li>The order was placed but no items were recorded</li>";
                                echo "<li>There's a data synchronization issue between systems</li>";
                                echo "<li>The order is still being processed</li>";
                                echo "</ul>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="text-center">
                        <a href="Adminorderlist.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Back to Order List
                        </a>
                    </div>

                    <?php
                    
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>";
                    echo "<h5>Database Error</h5>";
                    echo "<p>There was an error retrieving the order details.</p>";
                    echo "<p><strong>Error Details:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "</div>";
                    echo "<!-- Debug Exception: " . $e->getMessage() . " -->";
                }
                ?>
                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>