<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
        session_start(); 
        include("../conn_db.php"); 
        if($_SESSION["utype"]!="ADMIN"){
            header("location: ../restricted.php");
            exit(1);
        }
        
        // Handle form submission
        if(isset($_POST["upd_confirm"])){
            $tid = $_POST["tid"];
            $status = $_POST["os"];
            
            try {
                // Start transaction for data integrity
                $mysqli->begin_transaction();
                
                if($status == 'FNSH'){
                    $fnsh_date = date('Y-m-d H:i:s');
                    $query = "UPDATE transaction SET order_status = ?, finished_time = ? WHERE tid = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param("sss", $status, $fnsh_date, $tid);
                } else {
                    $query = "UPDATE transaction SET order_status = ?, finished_time = NULL WHERE tid = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param("ss", $status, $tid);
                }
                
                $result = $stmt->execute();
                
                if($result){
                    // Log the status change for admin tracking
                    $admin_id = $_SESSION['cid']; // Assuming admin has cid in session
                    $log_query = "INSERT INTO admin_status_log (tid, old_status, new_status, changed_by, change_time, notes) 
                                  SELECT ?, order_status, ?, ?, NOW(), 'Status updated by admin' 
                                  FROM transaction WHERE tid = ?";
                    $log_stmt = $mysqli->prepare($log_query);
                    $log_stmt->bind_param("ssss", $tid, $status, $admin_id, $tid);
                    $log_stmt->execute();
                    
                    $mysqli->commit();
                    header("location: admin_order_list.php?up_ods=1");
                } else {
                    $mysqli->rollback();
                    header("location: admin_order_list.php?up_ods=0");
                }
            } catch (Exception $e) {
                $mysqli->rollback();
                header("location: admin_order_list.php?up_ods=0");
            }
            exit(1);
        }
        include('../head.php');
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/main.css" rel="stylesheet">
    <link href="../css/login.css" rel="stylesheet">
    <link href="../img/Color Icon with background.png" rel="icon">
    <title>Update Order Status | FOODCAVE</title>
</head>

<body class="d-flex flex-column h-100">
    <?php include('nav_header_admin.php')?>

    <div class="container form-signin mt-auto w-50">
        <a class="nav nav-item text-decoration-none text-muted" href="#" onclick="history.back();">
            <i class="bi bi-arrow-left-square me-2"></i>Go back
        </a>
        <?php 
            // Get transaction details from transaction table
            $tid = $_GET["tid"];
            $query = "SELECT t.tid, t.name, t.email, t.rollno, t.year, t.branch_section, 
                             t.order_cost, t.order_status, t.pickup_time, t.pickup_notes,
                             t.created_at, t.finished_time
                      FROM transaction t 
                      WHERE t.tid = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $tid);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if (!$row) {
                header("location: admin_order_list.php?error=transaction_not_found");
                exit(1);
            }
        ?>
        <form method="POST" action="admin_order_update.php" class="form-floating">
            <h2 class="mt-4 mb-3 fw-normal text-bold"><i class="bi bi-pencil-square me-2"></i>Update Order Status</h2>
            
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="transactionid" placeholder="Transaction ID" value="<?php echo $row["tid"];?>" disabled>
                <label for="transactionid">Transaction ID</label>
            </div>
            
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="customername" placeholder="Customer Name" value="<?php echo $row["name"];?>" disabled>
                <label for="customername">Customer Name</label>
            </div>
            
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="rollno" placeholder="Roll Number" value="<?php echo $row["rollno"];?>" disabled>
                <label for="rollno">Roll Number</label>
            </div>
            
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="academic" placeholder="Academic Info" value="<?php echo $row["year"] . " - " . $row["branch_section"];?>" disabled>
                <label for="academic">Year & Branch</label>
            </div>
            
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="ordercost" placeholder="Order Cost" value="₹<?php echo number_format($row["order_cost"], 2);?>" disabled>
                <label for="ordercost">Order Cost</label>
            </div>
            
            <?php if($row["pickup_time"]): ?>
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="pickuptime" placeholder="Pickup Time" value="<?php echo $row["pickup_time"];?>" disabled>
                <label for="pickuptime">Pickup Time</label>
            </div>
            <?php endif; ?>
            
            <?php if($row["pickup_notes"]): ?>
            <div class="form-floating mb-2">
                <textarea class="form-control" id="pickupnotes" placeholder="Pickup Notes" style="height: 60px" disabled><?php echo $row["pickup_notes"];?></textarea>
                <label for="pickupnotes">Pickup Notes</label>
            </div>
            <?php endif; ?>
            
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="orderdate" placeholder="Order Date" value="<?php echo date('M d, Y h:i A', strtotime($row["created_at"]));?>" disabled>
                <label for="orderdate">Order Date</label>
            </div>
           
            <div class="form-floating mb-2">
                <select class="form-select" id="orderstatus" name="os" required>
                    <option value="">Select Order Status</option>
                    <option value="VRFY" <?php if($row["order_status"]=="VRFY"){ echo "selected";}?>>VRFY | Order Verifying</option>
                    <option value="ACPT" <?php if($row["order_status"]=="ACPT"){ echo "selected";}?>>ACPT | Order Accepted</option>
                    <option value="PREP" <?php if($row["order_status"]=="PREP"){ echo "selected";}?>>PREP | Order Preparing</option>
                    <option value="RDPK" <?php if($row["order_status"]=="RDPK"){ echo "selected";}?>>RDPK | Ready for Pick-Up</option>
                    <option value="FNSH" <?php if($row["order_status"]=="FNSH"){ echo "selected";}?>>FNSH | Order Finished</option>
                    <option value="CNCL" <?php if($row["order_status"]=="CNCL"){ echo "selected";}?>>CNCL | Order Cancelled</option>
                </select>
                <label for="orderstatus">Order Status</label>
            </div>
            
            <!-- Current Status Display -->
            <div class="alert alert-info mb-3">
                <strong>Current Status:</strong> 
                <?php 
                    $status_map = [
                        'VRFY' => 'Order Verifying',
                        'ACPT' => 'Order Accepted', 
                        'PREP' => 'Order Preparing',
                        'RDPK' => 'Ready for Pick-Up',
                        'FNSH' => 'Order Finished',
                        'CNCL' => 'Order Cancelled'
                    ];
                    echo $status_map[$row["order_status"]] ?? $row["order_status"];
                ?>
                <?php if($row["finished_time"]): ?>
                    <br><small>Finished: <?php echo date('M d, Y h:i A', strtotime($row["finished_time"])); ?></small>
                <?php endif; ?>
            </div>
            
            <input type="hidden" name="tid" value="<?php echo $tid;?>">
            <button class="w-100 btn btn-success mb-3" name="upd_confirm" type="submit">Update Order Status</button>
            
            <div class="text-center">
                <a href="admin_order_list.php" class="btn btn-outline-secondary">Cancel & Return to List</a>
            </div>
        </form>
    </div>

    <?php include('admin_footer.php')?>
</body>

</html>