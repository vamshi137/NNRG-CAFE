<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php session_start(); include("conn_db.php"); include("head.php");?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | NNRG-CÁFE | FOODMUNCH </title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/main1.css">
    <style>
        .shop-image-container {
            position: relative;
            overflow: hidden;
        }
        
        .coming-soon-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(220, 53, 69, 0.95);
            color: white;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 2;
            pointer-events: none;
            backdrop-filter: blur(2px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .shop-closed .card-img-top {
            filter: grayscale(70%) brightness(0.7);
        }
        
        .shop-closed .card-body {
            background-color: #f8f9fa;
        }
        
        .btn-disabled-custom {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: white !important;
            cursor: not-allowed !important;
        }
        
        .btn-disabled-custom:hover {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
        }
    </style>
</head>
<body class="d-flex flex-column h-100">
    <?php include('nav_header.php')?>
    
    <!-- Error Messages for Closed Shops -->
    <?php if(isset($_GET['error'])){ ?>
        <div class="container mt-3">
            <?php if($_GET['error'] == 'shop_closed'){ ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Shop Closed!</strong> 
                    <?php if(isset($_GET['shop_name'])){ ?>
                        "<?php echo htmlspecialchars(urldecode($_GET['shop_name'])); ?>" is currently closed and not accepting orders.
                    <?php } else { ?>
                        The shop you're trying to access is currently closed and not accepting orders.
                    <?php } ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } elseif($_GET['error'] == 'shop_not_found'){ ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-x-circle-fill"></i>
                    <strong>Shop Not Found!</strong> The shop you're looking for doesn't exist.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
    
    <div class="d-flex text-center text-white position-relative promo-banner-bg py-3">
        <div class="p-lg-2 mx-auto my-5">
            <h1 class="display-5 fw-normal">Welcome to NNRG-CÁFE</h1>
            <p class="lead fw-normal">Food ordering system of NNRG</p>
            <span class="xsmall-font text-muted"></span>
        </div>
    </div>
    <div class="container p-5" id="recommended-shop">
        <h2 class="border-bottom pb-2"><i class="bi bi-shop align-top"></i> Recommended For You</h2>

        <!-- GRID SHOP SELECTION -->
        <div class="row row-cols-1 row-cols-lg-3 align-items-stretch g-4 py-3">

            <?php
            // Show ALL shops (both open and closed)
            $query = "SELECT s_id,s_name,s_pic,s_status FROM shop";
            $result = $mysqli -> query($query);
            if($result -> num_rows > 0){
            while($row = $result -> fetch_array()){
                // Check if shop is closed
                $is_closed = ($row["s_status"] == 'CLOSED');
        ?>
            <!-- GRID EACH SHOP -->
            <div class="col">
                <div class="card rounded-25 position-relative <?php echo $is_closed ? 'shop-closed' : ''; ?>">
                    <!-- Shop Status Badge -->
                    <div class="position-absolute top-0 end-0 m-2" style="z-index: 3;">
                        <?php if($is_closed){ ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-shop-window"></i> CLOSED
                            </span>
                        <?php } else { ?>
                            <span class="badge bg-success">
                                <i class="bi bi-shop"></i> OPEN
                            </span>
                        <?php } ?>
                    </div>
                    
                    <!-- Shop Image Container -->
                    <div class="shop-image-container">
                        <?php if($is_closed){ ?>
                            <!-- Coming Soon Watermark -->
                            <div class="coming-soon-watermark">
                                Coming Soon
                            </div>
                        <?php } ?>
                        
                        <img <?php
                            if(is_null($row["s_pic"])){echo "src='img/default.png'";}
                            else{echo "src=\"img/{$row['s_pic']}\"";}
                        ?> style="width:100%; height:175px; object-fit:cover;"
                            class="card-img-top rounded-25 img-fluid" alt="<?php echo $row["s_name"]?>">
                    </div>
                    
                    <div class="card-body">
                        <h4 name="shop-name" class="card-title"><?php echo $row["s_name"]?></h4>
                        <p class="card-subtitle <?php echo $is_closed ? 'text-danger' : 'text-success'; ?>">
                            <?php if($is_closed){ ?>
                                <i class="bi bi-circle-fill" style="font-size: 8px;"></i> Currently unavailable
                            <?php } else { ?>
                                <i class="bi bi-circle-fill" style="font-size: 8px;"></i> Available for orders
                            <?php } ?>
                        </p>
                        
                        <div class="text-end">
                            <?php if($is_closed){ ?>
                                <!-- Disabled button for closed shops -->
                                <button class="btn btn-sm btn-disabled-custom" disabled>
                                    <i class="bi bi-lock-fill"></i> Shop Closed
                                </button>
                            <?php } else { ?>
                                <!-- Normal link for open shops -->
                                <a href="<?php echo "shop_menu.php?s_id=".$row["s_id"]?>"
                                    class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-arrow-right"></i> Go to shop
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END GRID EACH SHOP -->
            <?php }
        }else{
            ?>
            <div class="row row-cols-1 w-100">
                <div class="col mt-4 pt-3 px-3 bg-danger text-white rounded text-center">
                    <i class="bi bi-x-circle-fill"></i>
                    <p class="ms-2 mt-2">No shop currently available.</p>
                </div>
            </div>
            <?php
        }
        $result -> free_result();
        ?>
        </div>
        <!-- END GRID SHOP SELECTION -->

    </div>
    <?php include('footer.php')?>
    
        
</body>
</html>