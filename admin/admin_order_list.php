<!DOCTYPE html>
<html lang="en" class="h-100">

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
    <link href="../img/ICON_F.png" rel="icon">
    <link href="../css/main.css" rel="stylesheet">
    <link href="../img/Color Icon with background.png" rel="icon">
    <title>Order List | FOODCAVE</title>
</head>

<body class="d-flex flex-column h-100">

    <?php include('nav_header_admin.php')?>

    <div class="container p-2 pb-0" id="admin-dashboard">
        <div class="mt-4 border-bottom">
            <a class="nav nav-item text-decoration-none text-muted mb-2" href="#" onclick="history.back();">
                <i class="bi bi-arrow-left-square me-2"></i>Go back
            </a>

            <?php
            // Handle delete order request
            if(isset($_GET["delete_order"]) && isset($_GET["tid"])){
                $tid_to_delete = $_GET["tid"];
                
                // Start transaction
                $mysqli->autocommit(FALSE);
                
                try {
                    // Delete from transaction_items first (foreign key constraint)
                    $delete_items = "DELETE FROM transaction_items WHERE tid = ?";
                    $stmt1 = $mysqli->prepare($delete_items);
                    $stmt1->bind_param("s", $tid_to_delete);
                    $stmt1->execute();
                    
                    // Then delete from transaction table
                    $delete_transaction = "DELETE FROM transaction WHERE tid = ?";
                    $stmt2 = $mysqli->prepare($delete_transaction);
                    $stmt2->bind_param("s", $tid_to_delete);
                    $stmt2->execute();
                    
                    // Commit transaction
                    $mysqli->commit();
                    $delete_success = true;
                    
                } catch (Exception $e) {
                    // Rollback on error
                    $mysqli->rollback();
                    $delete_success = false;
                }
                
                $mysqli->autocommit(TRUE);
                
                // Show notification
                if($delete_success) {
                    ?>
                    <!-- START SUCCESSFULLY DELETED ORDER -->
                    <div class="row row-cols-1 notibar">
                        <div class="col mt-2 ms-2 p-2 bg-success text-white rounded text-start">
                            <i class="bi bi-check-circle ms-2"></i>
                            <span class="ms-2 mt-2">Order deleted successfully.</span>
                            <span class="me-2 float-end"><a class="text-decoration-none link-light" href="admin_order_list.php">X</a></span>
                        </div>
                    </div>
                    <!-- END SUCCESSFULLY DELETED ORDER -->
                    <?php
                } else {
                    ?>
                    <!-- START FAILED DELETE ORDER -->
                    <div class="row row-cols-1 notibar">
                        <div class="col mt-2 ms-2 p-2 bg-danger text-white rounded text-start">
                            <i class="bi bi-x-circle ms-2"></i>
                            <span class="ms-2 mt-2">Failed to delete order.</span>
                            <span class="me-2 float-end"><a class="text-decoration-none link-light" href="admin_order_list.php">X</a></span>
                        </div>
                    </div>
                    <!-- END FAILED DELETE ORDER -->
                    <?php
                }
            }

            // Original status update notifications
            if(isset($_GET["up_ods"])){
                if($_GET["up_ods"]==1){
                    ?>
            <!-- START SUCCESSFULLY UPDATE ORDER STATUS -->
            <div class="row row-cols-1 notibar">
                <div class="col mt-2 ms-2 p-2 bg-success text-white rounded text-start">
                    <i class="bi bi-check-circle ms-2"></i>
                    <span class="ms-2 mt-2">Successfully updated order status.</span>
                    <span class="me-2 float-end"><a class="text-decoration-none link-light" href="admin_order_list.php">X</a></span>
                </div>
            </div>
            <!-- END SUCCESSFULLY UPDATE ORDER STATUS -->
            <?php }else{ ?>
            <!-- START FAILED UPDATE ORDER STATUS -->
            <div class="row row-cols-1 notibar">
                <div class="col mt-2 ms-2 p-2 bg-danger text-white rounded text-start">
                    <i class="bi bi-x-circle ms-2"></i><span class="ms-2 mt-2">Failed to update order status.</span>
                    <span class="me-2 float-end"><a class="text-decoration-none link-light" href="admin_order_list.php">X</a></span>
                </div>
            </div>
            <!-- END FAILED UPDATE ORDER STATUS -->
            <?php }
                }

            // Email notification success message
            if(isset($_GET["email_sent"]) && $_GET["email_sent"] == 1){
                ?>
                <!-- EMAIL NOTIFICATION SUCCESS -->
                <div class="row row-cols-1 notibar">
                    <div class="col mt-2 ms-2 p-2 bg-success text-white rounded text-start">
                        <i class="bi bi-envelope-check ms-2"></i>
                        <span class="ms-2 mt-2">Order status updated and customer notified via email! 📧</span>
                        <span class="me-2 float-end"><a class="text-decoration-none link-light" href="admin_order_list.php">X</a></span>
                    </div>
                </div>
                <?php
            }

            // Email notification failed message
            if(isset($_GET["email_failed"]) && $_GET["email_failed"] == 1){
                ?>
                <!-- EMAIL NOTIFICATION FAILED -->
                <div class="row row-cols-1 notibar">
                    <div class="col mt-2 ms-2 p-2 bg-warning text-dark rounded text-start">
                        <i class="bi bi-envelope-x ms-2"></i>
                        <span class="ms-2 mt-2">Order status updated but email notification failed to send.</span>
                        <span class="me-2 float-end"><a class="text-decoration-none link-dark" href="admin_order_list.php">X</a></span>
                    </div>
                </div>
                <?php
            }
            ?>

            <h2 class="pt-3 display-6">Order List</h2>
            <form class="form-floating mb-3" method="GET" action="admin_order_list.php">
                <div class="row g-2">
                    <div class="col">
                        <select class="form-select" id="c_id" name="c_id">
                            <option selected value="">Customer Name</option>
                            <?php
                                $option_query = "SELECT DISTINCT t.c_id, t.name
                                FROM transaction t ORDER BY t.name;";
                                $option_result = $mysqli->query($option_query);
                                $opt_row = $option_result->num_rows;
                                if($option_result->num_rows != 0){
                                    while($option_arr = $option_result->fetch_array()){
                            ?>
                            <option value="<?php echo $option_arr["c_id"]?>" <?php if(isset($_GET["c_id"]) && $_GET["c_id"]==$option_arr["c_id"]){ echo "selected";}?>><?php echo $option_arr["name"]?></option>
                            <?php
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-select" id="year" name="year">
                            <?php if(isset($_GET["search"])){?>
                            <option selected value="">Student Year</option>
                            <option value="1st Year" <?php if($_GET["year"]=="1st Year"){ echo "selected";}?>>1st Year</option>
                            <option value="2nd Year" <?php if($_GET["year"]=="2nd Year"){ echo "selected";}?>>2nd Year</option>
                            <option value="3rd Year" <?php if($_GET["year"]=="3rd Year"){ echo "selected";}?>>3rd Year</option>
                            <option value="4th Year" <?php if($_GET["year"]=="4th Year"){ echo "selected";}?>>4th Year</option>
                            <?php }else{ ?>
                            <option selected value="">Student Year</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-select" id="branch" name="branch">
                            <?php if(isset($_GET["search"])){?>
                            <option selected value="">Branch</option>
                            <option value="IT" <?php if(strpos($_GET["branch"], "IT") !== false){ echo "selected";}?>>IT</option>
                            <option value="CSE" <?php if(strpos($_GET["branch"], "CSE") !== false){ echo "selected";}?>>CSE</option>
                            <option value="ECE" <?php if(strpos($_GET["branch"], "ECE") !== false){ echo "selected";}?>>ECE</option>
                            <option value="EEE" <?php if(strpos($_GET["branch"], "EEE") !== false){ echo "selected";}?>>EEE</option>
                            <option value="MECH" <?php if(strpos($_GET["branch"], "MECH") !== false){ echo "selected";}?>>MECH</option>
                            <option value="CIVIL" <?php if(strpos($_GET["branch"], "CIVIL") !== false){ echo "selected";}?>>CIVIL</option>
                            <?php }else{ ?>
                            <option selected value="">Branch</option>
                            <option value="IT">IT</option>
                            <option value="CSE">CSE</option>
                            <option value="ECE">ECE</option>
                            <option value="EEE">EEE</option>
                            <option value="MECH">MECH</option>
                            <option value="CIVIL">CIVIL</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                
                <!-- Second Row for Filters -->
                <div class="row g-2 mt-2">
                    <div class="col">
                        <select class="form-select" id="orderstatus" name="os">
                            <?php if(isset($_GET["search"])){?>
                            <option selected value="">Order Status</option>
                            <option value="VRFY" <?php if($_GET["os"]=="VRFY"){ echo "selected";}?>>Order Verifying</option>
                            <option value="ACPT" <?php if($_GET["os"]=="ACPT"){ echo "selected";}?>>Order Accepted</option>
                            <option value="PREP" <?php if($_GET["os"]=="PREP"){ echo "selected";}?>>Order Preparing</option>
                            <option value="RDPK" <?php if($_GET["os"]=="RDPK"){ echo "selected";}?>>Ready for Pick-Up</option>
                            <option value="FNSH" <?php if($_GET["os"]=="FNSH"){ echo "selected";}?>>Order Finished</option>
                            <option value="CNCL" <?php if($_GET["os"]=="CNCL"){ echo "selected";}?>>Order Cancelled</option>
                            <?php }else{ ?>
                            <option selected value="">Order Status</option>
                            <option value="VRFY">Order Verifying</option>
                            <option value="ACPT">Order Accepted</option>
                            <option value="PREP">Order Preparing</option>
                            <option value="RDPK">Ready for Pick-Up</option>
                            <option value="FNSH">Order Finished</option>
                            <option value="CNCL">Order Cancelled</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-select" id="ordertype" name="ot">
                            <?php if(isset($_GET["search"])){?>
                            <option selected value="">Order Type</option>
                            <option value="dine-in" <?php if(isset($_GET["ot"]) && $_GET["ot"]=="dine-in"){ echo "selected";}?>>Dine-In</option>
                            <option value="takeaway" <?php if(isset($_GET["ot"]) && $_GET["ot"]=="takeaway"){ echo "selected";}?>>Takeaway</option>
                            <?php }else{ ?>
                            <option selected value="">Order Type</option>
                            <option value="dine-in">Dine-In</option>
                            <option value="takeaway">Takeaway</option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <!-- NEW: Date Filter Options -->
                    <div class="col">
                        <select class="form-select" id="datefilter" name="date_filter" onchange="toggleDateInputs()">
                            <?php if(isset($_GET["search"])){?>
                            <option selected value="">Date Filter</option>
                            <option value="today" <?php if(isset($_GET["date_filter"]) && $_GET["date_filter"]=="today"){ echo "selected";}?>>Today</option>
                            <option value="yesterday" <?php if(isset($_GET["date_filter"]) && $_GET["date_filter"]=="yesterday"){ echo "selected";}?>>Yesterday</option>
                            <option value="last_7_days" <?php if(isset($_GET["date_filter"]) && $_GET["date_filter"]=="last_7_days"){ echo "selected";}?>>Last 7 Days</option>
                            <option value="last_30_days" <?php if(isset($_GET["date_filter"]) && $_GET["date_filter"]=="last_30_days"){ echo "selected";}?>>Last 30 Days</option>
                            <option value="custom_date" <?php if(isset($_GET["date_filter"]) && $_GET["date_filter"]=="custom_date"){ echo "selected";}?>>Specific Date</option>
                            <option value="date_range" <?php if(isset($_GET["date_filter"]) && $_GET["date_filter"]=="date_range"){ echo "selected";}?>>Date Range</option>
                            <?php }else{ ?>
                            <option selected value="">Date Filter</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="last_7_days">Last 7 Days</option>
                            <option value="last_30_days">Last 30 Days</option>
                            <option value="custom_date">Specific Date</option>
                            <option value="date_range">Date Range</option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <!-- Custom Date Input (initially hidden) -->
                    <div class="col" id="custom_date_input" style="display: <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'custom_date') ? 'block' : 'none'; ?>;">
                        <input type="date" class="form-control" id="specific_date" name="specific_date" 
                               value="<?php echo isset($_GET['specific_date']) ? $_GET['specific_date'] : ''; ?>" 
                               max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="col-auto">
                        <button type="submit" name="search" value="1" class="btn btn-success"
                        <?php if($opt_row==0){echo "disabled";} ?>>
                        <i class="bi bi-search me-1"></i>Search</button>
                        <button type="reset" class="btn btn-danger"
                            onclick="javascript: window.location='admin_order_list.php'">
                        <i class="bi bi-x-circle me-1"></i>Clear</button>
                    </div>
                </div>
                
                <!-- Date Range Inputs (initially hidden) -->
                <div class="row g-2 mt-2" id="date_range_inputs" style="display: <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'date_range') ? 'flex' : 'none'; ?>;">
                    <div class="col">
                        <label for="start_date" class="form-label small text-muted">From Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>" 
                               max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col">
                        <label for="end_date" class="form-label small text-muted">To Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>" 
                               max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col"></div> <!-- Empty column for spacing -->
                    <div class="col"></div> <!-- Empty column for spacing -->
                </div>
            </form>
        </div>
    </div>

    <?php
        // Build query based on search parameters (UPDATED to include date filtering)
        $where_conditions = array();
        
        if(isset($_GET["search"])){
            if(!empty($_GET["c_id"])){ 
                $where_conditions[] = "t.c_id = '{$_GET['c_id']}'"; 
            }
            if(!empty($_GET["year"])){ 
                $where_conditions[] = "t.year = '{$_GET['year']}'"; 
            }
            if(!empty($_GET["branch"])){ 
                $where_conditions[] = "t.branch_section LIKE '%{$_GET['branch']}%'"; 
            }
            if(!empty($_GET["os"])){ 
                $where_conditions[] = "t.order_status = '{$_GET['os']}'"; 
            }
            if(!empty($_GET["ot"])){ 
                $where_conditions[] = "t.order_type = '{$_GET['ot']}'"; 
            }
            
            // NEW: Date filtering logic
            if(!empty($_GET["date_filter"])){
                $date_filter = $_GET["date_filter"];
                $current_date = date('Y-m-d');
                
                switch($date_filter){
                    case 'today':
                        $where_conditions[] = "DATE(t.created_at) = '$current_date'";
                        break;
                        
                    case 'yesterday':
                        $yesterday = date('Y-m-d', strtotime('-1 day'));
                        $where_conditions[] = "DATE(t.created_at) = '$yesterday'";
                        break;
                        
                    case 'last_7_days':
                        $seven_days_ago = date('Y-m-d', strtotime('-7 days'));
                        $where_conditions[] = "DATE(t.created_at) BETWEEN '$seven_days_ago' AND '$current_date'";
                        break;
                        
                    case 'last_30_days':
                        $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
                        $where_conditions[] = "DATE(t.created_at) BETWEEN '$thirty_days_ago' AND '$current_date'";
                        break;
                        
                    case 'custom_date':
                        if(!empty($_GET["specific_date"])){
                            $specific_date = $_GET["specific_date"];
                            $where_conditions[] = "DATE(t.created_at) = '$specific_date'";
                        }
                        break;
                        
                    case 'date_range':
                        if(!empty($_GET["start_date"]) && !empty($_GET["end_date"])){
                            $start_date = $_GET["start_date"];
                            $end_date = $_GET["end_date"];
                            $where_conditions[] = "DATE(t.created_at) BETWEEN '$start_date' AND '$end_date'";
                        }
                        break;
                }
            }
        }
        
        $where_clause = "";
        if(!empty($where_conditions)){
            $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        }
        
        // Query includes date filtering
        $query = "SELECT t.id, t.tid, t.c_id, t.order_cost, t.name, t.email, t.rollno, 
                         t.year, t.branch_section, t.pickup_time, t.pickup_notes, t.created_at, t.order_status, t.order_type
                  FROM transaction t 
                  $where_clause 
                  ORDER BY t.created_at DESC";
        
        $result = $mysqli->query($query);
        $numrow = $result->num_rows;
        if($numrow > 0){
    ?>
        <div class="container align-items-stretch pt-2">
            <!-- GRID EACH MENU -->
            <div class="table-responsive">
            <table class="table rounded-5 table-light table-striped table-hover align-middle caption-top mb-3">
                <caption>
                    <?php echo $numrow;?> order(s) found
                    <?php 
                    // Show active date filter info
                    if(isset($_GET["search"]) && !empty($_GET["date_filter"])){
                        $filter_text = "";
                        switch($_GET["date_filter"]){
                            case 'today': $filter_text = "for Today (" . date('F j, Y') . ")"; break;
                            case 'yesterday': $filter_text = "for Yesterday (" . date('F j, Y', strtotime('-1 day')) . ")"; break;
                            case 'last_7_days': $filter_text = "for Last 7 Days"; break;
                            case 'last_30_days': $filter_text = "for Last 30 Days"; break;
                            case 'custom_date': 
                                if(!empty($_GET["specific_date"])){
                                    $filter_text = "for " . date('F j, Y', strtotime($_GET["specific_date"]));
                                }
                                break;
                            case 'date_range':
                                if(!empty($_GET["start_date"]) && !empty($_GET["end_date"])){
                                    $filter_text = "from " . date('F j, Y', strtotime($_GET["start_date"])) . 
                                                  " to " . date('F j, Y', strtotime($_GET["end_date"]));
                                }
                                break;
                        }
                        echo " <span class='text-info'>$filter_text</span>";
                    }
                    ?>
                    <?php if(isset($_GET["search"])){?><br /><a
                            href="admin_order_list.php" class="text-decoration-none text-danger">
                            <i class="bi bi-x-circle me-1"></i>Clear Search Result</a><?php } ?>
                </caption>
                <thead class="bg-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Transaction ID</th>
                        <th scope="col">Customer Details</th>
                        <th scope="col">Food Items</th>
                        <th scope="col">Order Type</th>
                        <th scope="col">Order Status</th>
                        <th scope="col">Order Date</th>
                        <th scope="col">Order Cost</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while($row = $result->fetch_array()){ 
                    
                        // Get food items for each order separately
                        $food_query = "SELECT f.f_name, ti.quantity 
                                       FROM transaction_items ti 
                                       INNER JOIN food f ON ti.f_id = f.f_id 
                                       WHERE ti.tid = ?
                                       ORDER BY f.f_name";
                        $food_stmt = $mysqli->prepare($food_query);
                        $food_stmt->bind_param("s", $row['tid']);
                        $food_stmt->execute();
                        $food_result = $food_stmt->get_result();
                        
                        $food_items_array = array();
                        $item_count = 0;
                        $total_quantity = 0;
                        
                        while($food_row = $food_result->fetch_array()){
                            $food_items_array[] = htmlspecialchars($food_row['f_name']) . ' (x' . $food_row['quantity'] . ')';
                            $item_count++;
                            $total_quantity += $food_row['quantity'];
                        }
                        
                        // Create the food items display string
                        if(count($food_items_array) > 0){
                            $food_items_display = implode(', ', $food_items_array);
                        } else {
                            $food_items_display = '<span class="text-muted">No items found</span>';
                            $item_count = 0;
                        }
                    ?>
                    <tr>
                        <th><?php echo $i++;?></th>
                        <td>
                            <small class="text-primary fw-bold"><?php echo htmlspecialchars($row["tid"]);?></small><br>
                            <small class="text-muted">Roll: <?php echo htmlspecialchars($row["rollno"]);?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row["name"]);?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($row["email"]);?></small><br>
                            <small class="text-info"><?php echo htmlspecialchars($row["year"]);?> - <?php echo htmlspecialchars($row["branch_section"]);?></small>
                        </td>
                        <td style="max-width: 200px;">
                            <small>
                                <?php 
                                echo $food_items_display;
                                if($item_count > 0){
                                    echo "<br><span class='text-muted'>(" . $item_count . " items, " . $total_quantity . " total quantity)</span>";
                                }
                                ?>
                            </small>
                        </td>
                        <td>
                            <?php 
                            $order_type = $row['order_type'] ?? 'takeaway';
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
                        <td>
                            <?php if($row["order_status"]=="VRFY"){ ?>
                                <span class="fw-bold badge rounded-pill bg-info text-dark">
                                    <i class="bi bi-hourglass-split me-1"></i>Verifying
                                </span>
                            <?php }else if($row["order_status"]=="ACPT"){ ?>
                                <span class="fw-bold badge rounded-pill bg-secondary text-white">
                                    <i class="bi bi-check-circle me-1"></i>Accepted
                                </span>
                            <?php }else if($row["order_status"]=="PREP"){ ?>
                                <span class="fw-bold badge rounded-pill bg-warning text-dark">
                                    <i class="bi bi-clock-history me-1"></i>Preparing
                                </span>
                            <?php }else if($row["order_status"]=="RDPK"){ ?>
                                <span class="fw-bold badge rounded-pill bg-primary text-white">
                                    <i class="bi bi-bell me-1"></i>Ready - Customer Notified 📧
                                </span>
                            <?php }else if($row["order_status"]=="FNSH"){?>
                                <span class="fw-bold badge rounded-pill bg-success text-white">
                                    <i class="bi bi-check2-all me-1"></i>Completed
                                </span>
                            <?php }else if($row["order_status"]=="CNCL"){?>
                                <span class="fw-bold badge rounded-pill bg-danger text-white">
                                    <i class="bi bi-x-circle me-1"></i>Cancelled
                                </span>
                            <?php } ?>
                        </td>
                        <td><?php 
                        $order_time = (new DateTime($row["created_at"]))->format("F j, Y H:i");
                        echo $order_time;
                        ?></td>
                        <td><strong class="text-success">₹<?php echo number_format($row["order_cost"], 2);?></strong></td>
                        <td>
                            <div class="btn-group-vertical" role="group">
                                <a href="admin_order_detail.php?tid=<?php echo urlencode($row["tid"]); ?>" 
                                   class="btn btn-sm btn-primary mb-1" 
                                   title="View Order Details - TID: <?php echo htmlspecialchars($row["tid"]); ?>">View</a>
                                <a href="admin_order_update.php?tid=<?php echo urlencode($row["tid"]); ?>" 
                                   class="btn btn-sm btn-outline-success mb-1">
                                   <?php if($row["order_status"]=="RDPK"){ ?>
                                       📧 Update Status
                                   <?php } else { ?>
                                       Update Status
                                   <?php } ?>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteOrder('<?php echo htmlspecialchars($row['tid'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>')">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        </div>
        <?php }else{ ?>
        <div class="container">
        <div class="row">
            <div class="col m-2 p-2 bg-danger text-white rounded text-start">
                <i class="bi bi-x-circle ms-2"></i><span class="ms-2 mt-2">No order found</span>
                <?php if(isset($_GET["search"])){ ?>
                <a href="admin_order_list.php" class="text-white ms-3">Clear Search Result</a>
                <?php } ?>
            </div>
        </div>
        </div>
        <!-- END GRID SHOP SELECTION -->
        <?php } ?>

    <script>
    function deleteOrder(tid, customerName) {
        // Simple JavaScript confirmation dialog
        var confirmDelete = confirm("Are you sure you want to delete this order?\n\nCustomer: " + customerName + "\nTransaction ID: " + tid + "\n\nThis action cannot be undone!");
        
        if (confirmDelete) {
            // Redirect to delete the order
            window.location.href = 'admin_order_list.php?delete_order=1&tid=' + encodeURIComponent(tid);
        }
    }

    // Function to toggle date input fields based on selected filter type
    function toggleDateInputs() {
        var dateFilter = document.getElementById('datefilter').value;
        var customDateInput = document.getElementById('custom_date_input');
        var dateRangeInputs = document.getElementById('date_range_inputs');
        
        // Hide all date inputs first
        customDateInput.style.display = 'none';
        dateRangeInputs.style.display = 'none';
        
        // Show relevant inputs based on selection
        if (dateFilter === 'custom_date') {
            customDateInput.style.display = 'block';
        } else if (dateFilter === 'date_range') {
            dateRangeInputs.style.display = 'flex';
        }
    }

    // Quick date filter buttons functionality
    function setDateFilter(filterType) {
        var dateFilterSelect = document.getElementById('datefilter');
        dateFilterSelect.value = filterType;
        toggleDateInputs();
        
        // Auto-submit form for quick filters
        if (['today', 'yesterday', 'last_7_days', 'last_30_days'].includes(filterType)) {
            document.querySelector('form').submit();
        }
    }

    // Initialize date inputs visibility on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleDateInputs();
    });

    // Add date validation
    document.getElementById('end_date')?.addEventListener('change', function() {
        var startDate = document.getElementById('start_date').value;
        var endDate = this.value;
        
        if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
            alert('End date cannot be earlier than start date');
            this.value = '';
        }
    });

    document.getElementById('start_date')?.addEventListener('change', function() {
        var startDate = this.value;
        var endDate = document.getElementById('end_date').value;
        
        if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
            document.getElementById('end_date').value = '';
        }
    });
    </script>

    <!-- Quick Date Filter Buttons (Optional Enhancement) -->
    <div class="container mb-3">
        <div class="row">
            <div class="col">
                <div class="btn-group btn-group-sm" role="group" aria-label="Quick date filters">
                    <button type="button" class="btn btn-outline-primary" onclick="setDateFilter('today')">
                        <i class="bi bi-calendar-day me-1"></i>Today
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="setDateFilter('yesterday')">
                        <i class="bi bi-calendar-minus me-1"></i>Yesterday
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="setDateFilter('last_7_days')">
                        <i class="bi bi-calendar-week me-1"></i>Last 7 Days
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="setDateFilter('last_30_days')">
                        <i class="bi bi-calendar-month me-1"></i>Last 30 Days
                    </button>
                </div>
                <small class="text-muted ms-2">Quick filters for common date ranges</small>
            </div>
        </div>
    </div>

    <?php include('admin_footer.php')?>
</body>

</html>