
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
                            // Get transaction items with food details
                            $items_query = "SELECT 
                                ti.*,
                                f.f_name,
                                f.f_price,
                                s.s_name as shop_name
                            FROM transaction_items ti
                            LEFT JOIN food f ON ti.f_id = f.f_id
                            LEFT JOIN shop s ON f.s_id = s.s_id
                            WHERE ti.tid = ?
                            ORDER BY ti.id";
                            
                            $stmt2 = $conn->prepare($items_query);
                            $stmt2->bind_param("s", $tid);
                            $stmt2->execute();
                            $items_result = $stmt2->get_result();
                            
                            echo "<!-- Debug: Items query executed, found " . $items_result->num_rows . " items -->";
                            echo "<!-- Debug: Items query: " . str_replace('?', "'" . $tid . "'", $items_query) . " -->";
                            
                            // Let's also check what's actually in transaction_items table
                            $debug_query = "SELECT COUNT(*) as total_items FROM transaction_items";
                            $debug_result = $conn->query($debug_query);
                            $debug_count = $debug_result->fetch_assoc();
                            echo "<!-- Debug: Total items in transaction_items table: " . $debug_count['total_items'] . " -->";
                            
                            // Check if this specific tid exists in transaction_items
                            $debug_tid_query = "SELECT COUNT(*) as tid_count FROM transaction_items WHERE tid = ?";
                            $debug_stmt = $conn->prepare($debug_tid_query);
                            $debug_stmt->bind_param("s", $tid);
                            $debug_stmt->execute();
                            $debug_tid_result = $debug_stmt->get_result();
                            $debug_tid_count = $debug_tid_result->fetch_assoc();
                            echo "<!-- Debug: Items for this TID (" . $tid . "): " . $debug_tid_count['tid_count'] . " -->";
                            
                            if($items_result->num_rows > 0) {
                                $total_items = 0;
                                $total_cost = 0;
                                
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
                                echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                
                                while($item = $items_result->fetch_assoc()) {
                                    $total_items += $item['quantity'];
                                    $total_cost += $item['total_price'];
                                    
                                    echo "<tr>";
                                    echo "<td>";
                                    // Since f_image column doesn't exist, show placeholder
                                    echo "<div style='width: 60px; height: 60px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #6c757d;'>No Image</div>";
                                    echo "</td>";
                                    echo "<td><strong>" . htmlspecialchars($item['f_name'] ?? 'Unknown Item') . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($item['shop_name'] ?? 'Unknown Shop') . "</td>";
                                    echo "<td>" . intval($item['quantity']) . "</td>";
                                    echo "<td>₹" . number_format($item['unit_price'], 2) . "</td>";
                                    echo "<td>₹" . number_format($item['total_price'], 2) . "</td>";
                                    echo "</tr>";
                                }
                                
                                echo "</tbody>";
                                echo "</table>";
                                echo "</div>";
                                
                                // Order Summary
                                echo "<div class='row mt-3'>";
                                echo "<div class='col-md-6 offset-md-6'>";
                                echo "<div class='card'>";
                                echo "<div class='card-body'>";
                                echo "<h5>Order Summary</h5>";
                                echo "<p><strong>Total Items:</strong> " . $total_items . "</p>";
                                echo "<p><strong>Total Cost:</strong> ₹" . number_format($total_cost, 2) . "</p>";
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                                
                            } else {
                                echo "<div class='alert alert-warning'>";
                                echo "<h5>No food items found in this order</h5>";
                                echo "<p>This could mean:</p>";
                                echo "<ul>";
                                echo "<li>The order items were not properly saved</li>";
                                echo "<li>There's a data mismatch in the database</li>";
                                echo "<li>The transaction ID doesn't have associated items</li>";
                                echo "</ul>";
                                echo "</div>";
                                echo "<!-- Debug: No items found for TID = " . $tid . " -->";
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