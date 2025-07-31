
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
=======
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
        session_start(); 
        include("../conn_db.php"); 
        include('../head.php');
        if($_SESSION["utype"]!="ADMIN"){
            header("location: ../restricted.php");
            exit(1);
        }
    ?>
    <meta charset="UTF-8">
     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/main.css" rel="stylesheet">
    <link href="../css/menu.css" rel="stylesheet">
    <link href="../img/Color Icon with background.png" rel="icon">
    <title>Food Detail | FOODCAVE</title>
</head>

<body class="d-flex flex-column h-100">
    <?php include('nav_header_admin.php')?>

    <?php
        $f_id = $_GET["f_id"];
        $query = "SELECT s.s_id,s.s_name,f.f_name,f.f_price,f.f_pic
        FROM food f INNER JOIN shop s ON f.s_id = s.s_id WHERE f.f_id = $f_id LIMIT 0,1;";
        $result = $mysqli -> query($query);
        $food_row = $result -> fetch_array();
    ?>

    <div class="container px-5 py-4" id="shop-body">
        <a class="nav nav-item text-decoration-none text-muted mb-2" href="#" onclick="history.back();">
            <i class="bi bi-arrow-left-square me-2"></i>Go back
        </a>
        <?php
            if(isset($_GET["up_fdt"])){
                if($_GET["up_fdt"]==1){
                    ?>
            <!-- START SUCCESSFULLY UPDATE DETAIL -->
            <div class="row row-cols-1 notibar">
                <div class="col mt-2 ms-2 p-2 bg-success text-white rounded text-start">
                    <i class="bi bi-check-circle ms-2"></i>
                    <span class="ms-2 mt-2">Successfully updated menu detail.</span>
                </div>
            </div>
            <!-- END SUCCESSFULLY UPDATE DETAIL -->
            <?php }else{ ?>
            <!-- START FAILED UPDATE DETAIL -->
            <div class="row row-cols-1 notibar">
                <div class="col mt-2 ms-2 p-2 bg-danger text-white rounded text-start">
                    <i class="bi bi-x-circle ms-2"></i><span class="ms-2 mt-2">Failed to update menu detail.</span>
                </div>
            </div>
            <!-- END FAILED UPDATE DETAIL -->
            <?php }
                }
            ?>
        <div class="container row row-cols-6 row-cols-md-12 g-5 pt-4 mb-4" id="shop-header">
            <div class="rounded-25 col-6" id="shop-img" style="
                    background: url(
                        <?php
                            if(is_null($food_row["f_pic"])){echo "'../img/default.png'";}
                            else{echo "'../img/{$food_row['f_pic']}'";}
                        ?> 
                    ) center; height: 225px;
                    background-size: cover; background-repeat: no-repeat; object-fit:fill;
                    background-position: center;">
            </div>
            <div class="col-6">
                <h1 class="display-5 strong"><?php echo $food_row["f_name"];?></h1>
                <h3 class="fw-light"><?php echo $food_row["f_price"]?> INR</h3>
                <ul class="list-unstyled">
                    <li class=""><?php echo "from ".$food_row["s_name"];?></li>
                    
                </ul>
        <a class="btn btn-sm btn-primary mt-2 mt-md-0" href="admin_food_edit.php?s_id=<?php echo $food_row["s_id"]?>&f_id=<?php echo $f_id?>">
            <i class="bi bi-pencil-square"></i>
            Update this menu
        </a>
        <a class="btn btn-sm btn-danger mt-2 mt-md-0" href="admin_food_delete.php?f_id=<?php echo $f_id?>">
            <i class="bi bi-trash"></i>
            Delete this menu
        </a>
            </div>
        </div>

        <div class="container">
        <h3 class="border-top pt-3 mt-2">Orders</h3>
            <form class="form-floating mb-3" method="GET" action="admin_food_detail.php">
                <input type="hidden" name="f_id" value="<?php echo $f_id;?>">
                <div class="row g-2">
                    <div class="col">
                        <select class="form-select" id="c_id" name="c_id">
                            <option selected value="">Customer Name</option>
                            <?php
                                $option_query = "SELECT DISTINCT c.c_id, c.c_firstname,c.c_lastname
                                FROM order_header orh INNER JOIN order_detail ord ON orh.orh_id = ord.orh_id
                                INNER JOIN customer c ON orh.c_id = c.c_id WHERE ord.f_id = {$f_id};";
                                $option_result = $mysqli -> query($option_query);
                                $opt_row = $option_result -> num_rows;
                                if($option_result -> num_rows != 0){
                                    while($option_arr = $option_result -> fetch_array()){
                            ?>
                            <option value="<?php echo $option_arr["c_id"]?>"><?php echo $option_arr["c_firstname"]." ".$option_arr["c_lastname"]?></option>
                            <?php
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-select" id="utype" name="ut">
                            <?php if(isset($_GET["search"])){?>
                            <option selected value="">Customer Type</option>
                            <option value="STD" <?php if($_GET["ut"]=="STD"){ echo "selected";}?>>Student</option>
                            <option value="STF" <?php if($_GET["ut"]=="STF"){ echo "selected";}?>>Faculty Staff</option>
                            <option value="GUE" <?php if($_GET["ut"]=="GUE"){ echo "selected";}?>>Visitor</option>
                            <option value="ADM" <?php if($_GET["ut"]=="ADM"){ echo "selected";}?>>Admin</option>
                            <option value="OTH" <?php if($_GET["ut"]=="OTH"){ echo "selected";}?>>Other</option>
                            <?php }else{ ?>
                            <option selected value="">Customer Type</option>
                            <option value="STD">Student</option>
                            <option value="STF">Faculty Staff</option>
                            <option value="GUE">Visitor</option>
                            <option value="ADM">Admin</option>
                            <option value="OTH">Other</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-select" id="orderstatus" name="os">
                            <?php if(isset($_GET["search"])){?>
                            <option selected value="">Order Status</option>
                            <option value="VRFY" <?php if($_GET["os"]=="VRFY"){ echo "selected";}?>>Order Verifying</option>
                            
                            <option value="ACPT" <?php if($_GET["os"]=="ACPT"){ echo "selected";}?>>Order Accepted</option>
                            <option value="PREP" <?php if($_GET["os"]=="PREP"){ echo "selected";}?>>Order Preparing</option>
                            <option value="RDPK" <?php if($_GET["os"]=="RDPK"){ echo "selected";}?>>Ready for Pick-Up</option>
                            <option value="FNSH" <?php if($_GET["os"]=="FNSH"){ echo "selected";}?>>Order Finished</option>
                            <?php }else{ ?>
                            <option selected value="">Order Status</option>
                            <option value="VRFY">Order Verifying</option>
                            <option value="ACPT">Order Accepted</option>
                            <option value="PREP">Order Preparing</option>
                            <option value="RDPK">Ready for Pick-Up</option>
                            <option value="FNSH">Order Finished</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="search" value="1" class="btn btn-success"
                        <?php if($opt_row==0){echo "disabled";} ?>>Search</button>
                        <button type="reset" class="btn btn-danger"
                            onclick="javascript: window.location='admin_food_detail.php?f_id=<?php echo $f_id?>'">Clear</button>
                    </div>
                </div>
            </form>
        </div>

        <?php
            $result -> free_result();
            if(isset($_GET["search"])){
                if($_GET["c_id"]!=''){ $cid_clause = " AND orh.c_id = '{$_GET['c_id']}';"; }else{ $cid_clause = ";";}
                $query = "SELECT orh.orh_id,orh.orh_ordertime,c.c_firstname,c.c_lastname,orh.orh_orderstatus,ord.ord_amount
                FROM order_header orh INNER JOIN order_detail ord ON orh.orh_id = ord.orh_id INNER JOIN customer c ON orh.c_id = c.c_id 
                WHERE ord.f_id = {$f_id} AND c.c_type LIKE '%{$_GET['ut']}%' AND orh.orh_orderstatus LIKE '%{$_GET['os']}%'".$cid_clause;
            }else{
                $query = "SELECT orh.orh_id,orh.orh_ordertime,c.c_firstname,c.c_lastname,orh.orh_orderstatus,orh.t_id,ord.ord_amount
                FROM order_header orh INNER JOIN order_detail ord ON orh.orh_id = ord.orh_id
                INNER JOIN customer c ON orh.c_id = c.c_id WHERE ord.f_id = {$f_id};";
            }
            $result = $mysqli -> query($query);
            $numrow = $result -> num_rows;
            if( $numrow > 0){
        ?>
        <div class="container align-items-stretch">
            <!-- GRID EACH MENU -->
            <div class="table-responsive">
            <table class="table rounded-5 table-light table-striped table-hover align-middle caption-top mb-3">
                <caption><?php echo $numrow;?> order(s) <?php if(isset($_GET["search"])){?><br /><a
                        href="admin_food_detail.php?f_id=<?php echo $f_id?>" class="text-decoration-none text-danger">Clear Search
                        Result</a><?php } ?></caption>
                <thead class="bg-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Transaction Id</th>
                        <th scope="col">Order Status</th>
                        <th scope="col">Order Date</th>
                        <th scope="col">Customer Name</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while($row = $result -> fetch_array()){ ?>
                    <tr>
                        <th><?php echo $i++;?></th>
                        <td><?php echo $row["t_id"];?></td>
                        <td>
                            <?php if($row["orh_orderstatus"]=="VRFY"){ ?>
                                <h5><span class="fw-bold badge bg-info text-dark">Verifying</span></h5>
                            
                            <?php }else if($row["orh_orderstatus"]=="ACPT"){ ?>
                                <h5><span class="fw-bold badge bg-secondary text-dark">Accepted</span></h5>
                            <?php }else if($row["orh_orderstatus"]=="PREP"){ ?>
                                <h5><span class="fw-bold badge bg-warning text-dark">Preparing</span></h5>
                            <?php }else if($row["orh_orderstatus"]=="RDPK"){ ?>
                                <h5><span class="fw-bold badge bg-primary text-white">Ready to pick up</span></h5>
                            <?php }else if($row["orh_orderstatus"]=="FNSH"){?>
                                <h5><span class="fw-bold badge bg-success text-white">Completed</span></h5>
                            <?php }
                            else if($row["orh_orderstatus"]=="CNCL"){?>
                                <h5><span class="fw-bold badge bg-danger text-white">Cancelled</span></h5>
                            <?php } ?>
                        </td>
                        <td><?php 
                        $order_time = (new Datetime($row["orh_ordertime"])) -> format("F j, Y H:i");
                        echo $order_time;
                        ?></td>
                        
                        <td><?php echo $row["c_firstname"]." ".$row["c_lastname"];?></td>
                        <td><?php echo $row["ord_amount"];?></td>
                        <td><a href="admin_order_detail.php?orh_id=<?php echo $row["orh_id"]?>" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        </div>
        <?php }else{ ?>
        <div class="row">
            <div class="col m-2 p-2 bg-danger text-white rounded text-start">
                <i class="bi bi-x-circle ms-2"></i><span class="ms-2 mt-2">No order found with this menu</span>
                <?php if(isset($_GET["search"])){ ?>
                <a href="admin_food_detail.php?f_id=<?php echo $f_id;?>" class="text-white">Clear Search Result</a>
                <?php } ?>
            </div>
        </div>
        <!-- END GRID SHOP SELECTION -->
        <?php } ?>

    </div>
    <?php include('admin_footer.php')?>
</body>
</html>