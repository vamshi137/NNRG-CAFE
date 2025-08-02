<?php 
session_start();
include("../conn_db.php");
include('../head.php');

if($_SESSION["utype"]!="ADMIN"){
    header("location: ../restricted.php");
    exit(1);
}
?>

<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/ICON_F.png" rel="icon">
    <link href="../css/main.css" rel="stylesheet">
    <link href="../img/Color Icon with background.png" rel="icon">
    <title>Order Details | FOODCAVE</title>
</head>

<body class="d-flex flex-column h-100">

    <?php include('nav_header_admin.php')?>

    <div class="container p-2 pb-0" id="admin-dashboard">
        <div class="mt-4 border-bottom">
            <a class="nav nav-item text-decoration-none text-muted mb-2" href="admin_order_list.php">
                <i class="bi bi-arrow-left-square me-2"></i>Back to Order List
            </a>

            <?php
            // Get the transaction ID
            $tid = isset($_GET['tid']) ? $_GET['tid'] : '';

            if(empty($tid)){
                ?>
                <div class="row">
                    <div class="col m-2 p-2 bg-danger text-white rounded text-start">
                        <i class="bi bi-x-circle ms-2"></i><span class="ms-2 mt-2">Order ID is required!</span>
                    </div>
                </div>
                <?php
                exit;
            }

            // Get order details from transaction table (UPDATED to include order_type)
            $query = "SELECT * FROM transaction WHERE tid = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $tid);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows == 0){
                ?>
                <div class="row">
                    <div class="col m-2 p-2 bg-danger text-white rounded text-start">
                        <i class="bi bi-x-circle ms-2"></i><span class="ms-2 mt-2">Order not found!</span>
                    </div>
                </div>
                <?php
                exit;
            }

            $order = $result->fetch_array();
            ?>

            <h2 class="pt-3 display-6">Order Details</h2>
        </div>
    </div>

    <div class="container align-items-stretch pt-2">
        <!-- Order Information Card -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-circle"></i> Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Customer ID:</strong></td>
                                <td><?php echo htmlspecialchars($order['c_id']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Roll Number:</strong></td>
                                <td><?php echo htmlspecialchars($order['rollno']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Year:</strong></td>
                                <td><?php echo htmlspecialchars($order['year']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Branch:</strong></td>
                                <td><?php echo htmlspecialchars($order['branch_section']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-receipt"></i> Order Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Transaction ID:</strong></td>
                                <td><span class="text-primary fw-bold"><?php echo htmlspecialchars($order['tid']); ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Order Date:</strong></td>
                                <td><?php echo date('F j, Y H:i:s', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <!-- NEW: Order Type Display -->
                            <tr>
                                <td><strong>Order Type:</strong></td>
                                <td>
                                    <?php 
                                    $order_type = $order['order_type'] ?? 'takeaway'; // Default fallback
                                    if($order_type == 'dine-in'): ?>
                                        <span class="badge bg-primary rounded-pill">
                                            <i class="bi bi-house-door-fill me-1"></i>Dine-In
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success rounded-pill">
                                            <i class="bi bi-bag-fill me-1"></i>Takeaway
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <?php if($order["order_status"]=="VRFY"){ ?>
                                        <span class="fw-bold badge rounded-pill bg-info text-dark">Verifying</span>
                                    <?php }else if($order["order_status"]=="ACPT"){ ?>
                                        <span class="fw-bold badge rounded-pill bg-secondary text-white">Accepted</span>
                                    <?php }else if($order["order_status"]=="PREP"){ ?>
                                        <span class="fw-bold badge rounded-pill bg-warning text-dark">Preparing</span>
                                    <?php }else if($order["order_status"]=="RDPK"){ ?>
                                        <span class="fw-bold badge rounded-pill bg-primary text-white">Ready to pick up</span>
                                    <?php }else if($order["order_status"]=="FNSH"){?>
                                        <span class="fw-bold badge rounded-pill bg-success text-white">Completed</span>
                                    <?php }else if($order["order_status"]=="CNCL"){?>
                                        <span class="fw-bold badge rounded-pill bg-danger text-white">Cancelled</span>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo ($order_type == 'dine-in') ? 'Arrival Time:' : 'Pickup Time:'; ?></strong></td>
                                <td><?php echo !empty($order['pickup_time']) ? date('F j, Y H:i', strtotime($order['pickup_time'])) : 'Not specified'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Special Notes:</strong></td>
                                <td><?php echo !empty($order['pickup_notes']) ? htmlspecialchars($order['pickup_notes']) : 'No notes'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Order Cost:</strong></td>
                                <td><strong class="text-success">₹<?php echo number_format($order['order_cost'], 2); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Food Items Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-bag-fill"></i> Food Items Ordered</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get food items for this transaction
                        $food_query = "SELECT ti.*, f.f_name, f.f_price, f.f_image 
                                       FROM transaction_items ti 
                                       INNER JOIN food f ON ti.f_id = f.f_id 
                                       WHERE ti.tid = ?
                                       ORDER BY f.f_name";

                        $food_stmt = $mysqli->prepare($food_query);
                        if (!$food_stmt) {
                            echo "<!-- DEBUG: Prepare failed: " . $mysqli->error . " -->";
                        }

                        $food_stmt->bind_param("s", $tid);
                        $food_stmt->execute();
                        $food_result = $food_stmt->get_result();

                        if($food_result->num_rows > 0){
                            ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Image</th>
                                            <th>Food Name</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                            <?php if(!empty($order['pickup_notes'])): ?>
                                            <th>Notes</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $grand_total = 0;
                                        while($food_row = $food_result->fetch_array()){ 
                                            $subtotal = $food_row['f_price'] * $food_row['quantity'];
                                            $grand_total += $subtotal;
                                        ?>
                                        <tr>
                                            <td>
                                                <?php if(!empty($food_row['f_image'])): ?>
                                                    <img src="../uploads/<?php echo htmlspecialchars($food_row['f_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($food_row['f_name']); ?>" 
                                                         class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="bi bi-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($food_row['f_name']); ?></td>
                                            <td>₹<?php echo number_format($food_row['f_price'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-primary rounded-pill"><?php echo $food_row['quantity']; ?></span>
                                            </td>
                                            <td><strong>₹<?php echo number_format($subtotal, 2); ?></strong></td>
                                            <?php if(!empty($order['pickup_notes'])): ?>
                                            <td>
                                                <?php echo !empty($food_row['notes']) ? htmlspecialchars($food_row['notes']) : '-'; ?>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="<?php echo (!empty($order['pickup_notes'])) ? '5' : '4'; ?>" class="text-end"><strong>Grand Total:</strong></td>
                                            <td><strong class="text-success">₹<?php echo number_format($grand_total, 2); ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger">
                                <h6><i class="bi bi-exclamation-triangle"></i> No Food Items Found</h6>
                                <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($tid); ?></p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4 mb-4">
            <div class="col-12">
                <div class="d-flex gap-2">
                    <a href="admin_order_list.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Order List
                    </a>
                    <a href="admin_order_update.php?tid=<?php echo urlencode($tid); ?>" class="btn btn-success">
                        <i class="bi bi-pencil-square"></i> Update Status
                    </a>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include('admin_footer.php')?>
</body>

</html>