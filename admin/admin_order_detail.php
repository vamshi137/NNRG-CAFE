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
<<<<<<< HEAD
    <style>
        .info-section {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
        }
        .section-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .info-label {
            font-weight: 500;
            color: #6c757d;
            flex: 0 0 150px;
        }
        .info-value {
            flex: 1;
            color: #495057;
        }
        .delivery-time-badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #17a2b8;
            color: white;
            border-radius: 4px;
            font-size: 0.9em;
        }
        @media (max-width: 768px) {
            .info-row {
                flex-direction: column;
                gap: 5px;
            }
            .info-label {
                flex: none;
                font-weight: 600;
            }
        }
    </style>
=======
>>>>>>> 5027eac0c6b4220983dc702d727e608a440f1685
    <title>Order Detail | FOODCAVE</title>
</head>

<body class="d-flex flex-column h-100">
    <?php include('nav_header_admin.php')?>

    <div class="container px-5 py-4" id="cart-body">
        <div class="row my-4 border-bottom">
            <a class="nav nav-item text-decoration-none text-muted mb-2" href="#" onclick="history.back();">
                <i class="bi bi-arrow-left-square me-2"></i>Go back
            </a>
            <h2 class="pt-3 display-5">Order Detail</h2>

            <?php
                $orh_id = $_GET["orh_id"];
                $orh_query = "SELECT * FROM order_header WHERE orh_id = {$orh_id}";
                $orh_arr = $mysqli -> query($orh_query) -> fetch_array();
            ?>

            <div class="row row-cols-1 row-cols-md-2">
                <div class="col mb-2 mb-md-0">
                    <ul class="list-unstyled fw-light">
                        <li class="list-item mb-2">
                            <?php if($orh_arr["orh_orderstatus"]=="VRFY"){ ?>
                            <h5><span class="fw-bold badge bg-info text-dark">Verifying</span></h5>
                            <?php }else if($orh_arr["orh_orderstatus"]=="ACPT"){ ?>
                            <h5><span class="fw-bold badge bg-secondary text-dark">Accepted</span></h5>
                            <?php }else if($orh_arr["orh_orderstatus"]=="PREP"){ ?>
                            <h5><span class="fw-bold badge bg-warning text-dark">Preparing</span></h5>
                            <?php }else if($orh_arr["orh_orderstatus"]=="RDPK"){ ?>
                            <h5><span class="fw-bold badge bg-primary text-white">Ready to pick up</span></h5>
                            <?php }else if($orh_arr["orh_orderstatus"]=="FNSH"){?>
                            <h5><span class="fw-bold badge bg-success text-white">Completed</span></h5>
                            <?php }
                            else if($orh_arr["orh_orderstatus"]=="CNCL"){?>
                                <h5><span class="fw-bold badge bg-danger text-white">Cancelled</span></h5>
                                <?php } ?>
                        </li>
                        <li class="list-item">Order #<?php echo $orh_arr["t_id"];?></li>
                        <li class="list-item">From
                            <a class="text-decoration-none link-primary"
                                href="admin_shop_detail.php?c_id=<?php echo $orh_arr['s_id']?>">
                                <?php
                                $shop_query = "SELECT s_name FROM shop WHERE s_id = {$orh_arr['s_id']};";
                                $shop_arr = $mysqli -> query($shop_query) -> fetch_array();
                                echo $shop_arr["s_name"];
                            ?>
                            </a>
                        </li>
                        <li class="list-item">Order of
                            <a class="text-decoration-none link-primary"
                                href="admin_customer_detail.php?c_id=<?php echo $orh_arr['c_id']?>">
                                <?php
                                $cust_query = "SELECT c_firstname,c_lastname FROM customer WHERE c_id = {$orh_arr['c_id']};";
                                $cust_arr = $mysqli -> query($cust_query) -> fetch_array();
                                echo $cust_arr["c_firstname"]." ".$cust_arr["c_lastname"];
                            ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col">
                    <ul class="list-unstyled fw-light">
                        <?php 
                            $od_placedate = (new Datetime($orh_arr["orh_ordertime"])) -> format("F j, Y H:i"); 
                            
                        ?>
                        <li class="mb-2">&nbsp;</li>
                        <li>Placed on <?php echo $od_placedate;?></li>
                        
                        <?php if($orh_arr["orh_orderstatus"]!="FNSH"){ ?>
                        <li>The order is not finish yet.</li>
                        <?php }else{
                            $od_finishtime = (new Datetime($orh_arr["orh_finishedtime"])) -> format("F j, Y H:i");
                        ?>
                        <li>Finished on <?php echo $od_finishtime;?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                <a class="btn btn-sm btn-primary mt-2 mt-md-0" href="admin_order_update.php?orh_id=<?php echo $_GET["orh_id"]?>">
                <i class="bi bi-pencil-square"></i>
                Update order status
                </a>
                </div>
            </div>
        </div>
<<<<<<< HEAD

        <!-- Customer Details Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <!-- Personal Information -->
                <div class="info-section">
                    <h5 class="section-title"><i class="bi bi-person-fill me-2"></i>Personal Information</h5>
                    
                    <div class="info-row">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orh_arr["customer_name"] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orh_arr["customer_email"] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Roll Number:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orh_arr["roll_number"] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Academic Year:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orh_arr["academic_year"] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Branch & Section:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orh_arr["branch_section"] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <!-- Delivery Information -->
                <div class="info-section">
                    <h5 class="section-title"><i class="bi bi-geo-alt-fill me-2"></i>Delivery Information</h5>
                    
                    <div class="info-row">
                        <span class="info-label">Room Number:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orh_arr["room_number"] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Delivery Time:</span>
                        <span class="info-value">
                            <?php 
                            if (!empty($orh_arr["preferred_delivery_time"])) {
                                $delivery_time = date('h:i A', strtotime($orh_arr["preferred_delivery_time"]));
                                echo "<span class='delivery-time-badge'>{$delivery_time}</span>";
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Special Instructions:</span>
                        <span class="info-value">
                            <?php 
                            $delivery_notes = $orh_arr["delivery_notes"] ?? '';
                            echo !empty($delivery_notes) ? htmlspecialchars($delivery_notes) : '<em class="text-muted">No special instructions</em>';
                            ?>
                        </span>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="info-section">
                    <h5 class="section-title"><i class="bi bi-credit-card-fill me-2"></i>Payment Information</h5>
                    
                    <div class="info-row">
                        <span class="info-label">Transaction ID:</span>
                        <span class="info-value">
                            <code><?php echo htmlspecialchars($orh_arr["t_id"] ?? 'N/A'); ?></code>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value">
                            <span class="badge bg-primary">QR Payment</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Section -->
=======
>>>>>>> 5027eac0c6b4220983dc702d727e608a440f1685
        <div class="row mb-5">
            <div class="col">
                <div class="row row-cols-1">
                    <div class="col">
<<<<<<< HEAD
                        <h5 class="fw-light">Menu Items</h5>
=======
                        <h5 class="fw-light">Menu</h5>
>>>>>>> 5027eac0c6b4220983dc702d727e608a440f1685
                    </div>
                    <div class="col row row-cols-1 row-cols-md-2 border-bottom">
                        <?php 
                            $ord_query = "SELECT f.f_id,f.f_name,f.f_pic,ord.ord_amount,ord.ord_buyprice,ord_note FROM order_detail ord INNER JOIN food f ON ord.f_id = f.f_id WHERE ord.orh_id = {$orh_id}";
                            $ord_result = $mysqli -> query($ord_query);
                            while($ord_row = $ord_result -> fetch_array()){
                        ?>
                        <div class="col">
                            <ul class="list-group">
                                <a class="text-decoration-none"
                                    href="admin_food_detail.php?f_id=<?php echo $ord_row["f_id"]?>">
                                    <li
                                        class="list-group-item d-flex border-0 pb-3 border-bottom w-100 justify-content-between align-items-center">
                                        <div class="image-parent">
                                            <img <?php
                                            if(is_null($ord_row["f_pic"])){echo "src='../img/default.png'";}
                                            else{echo "src=\"../img/{$ord_row['f_pic']}\"";}
                                        ?> class="img-fluid rounded"
                                                style="width: 100px; height:100px; object-fit:cover;"
                                                alt="<?php echo $ord_row["f_name"]?>">
                                        </div>
                                        <div class="ms-3 me-auto">
                                            <div class="fw-normal"><span class="h5"><?php echo $ord_row["ord_amount"]?>x
                                                </span><?php echo $ord_row["f_name"]?>
                                                <p><?php printf("%.2f INR <small class='text-muted'>(%.2f INR each)</small>",$ord_row["ord_buyprice"]*$ord_row["ord_amount"],$ord_row["ord_buyprice"]);?><br />
                                                    <span
                                                        class="text-muted small"><?php echo $ord_row["ord_note"]?></span>
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                </a>
                            </ul>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="col my-3">
                        <ul class="list-inline justify-content-between">
                            <li class="list-inline-item fw-light me-5">Grand Total</li>
                            <li class="list-inline-item fw-bold h4">
                                <?php
                                    $gt_query = "SELECT SUM(ord_amount*ord_buyprice) AS gt FROM order_detail WHERE orh_id = {$orh_id}";
                                    $gt_arr = $mysqli -> query($gt_query) -> fetch_array();
                                    printf("%.2f INR",$gt_arr["gt"]);
                                ?>
                            </li>
<<<<<<< HEAD
                            <li class="list-item fw-light small">Pay by QR</li>
=======
                            <li class="list-item fw-light small">Pay by QR
                                
                            </li>
>>>>>>> 5027eac0c6b4220983dc702d727e608a440f1685
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('admin_footer.php')?>
</body>

</html>