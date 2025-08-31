<?php
    session_start();
    include('conn_db.php');
    
    if(!isset($_SESSION["cid"])){
        header("location: restricted.php");
        exit(1);
    }
    
    if(isset($_GET["rmv"]) && isset($_GET["s_id"])){
        $target_sid = $_GET["s_id"];
        $target_cid = $_SESSION["cid"];
        
        // Remove all items from specific shop for this customer
        $cartdelete_query = "DELETE FROM cart WHERE c_id = {$target_cid} AND s_id = {$target_sid}";
        $cartdelete_result = $mysqli->query($cartdelete_query);
        
        if($cartdelete_result){
            header("location: cust_cart.php?rmv_crt=1");
        } else {
            header("location: cust_cart.php?rmv_crt=0");
        }
    } else {
        // Invalid parameters - redirect to cart
        header("location: cust_cart.php");
    }
    exit(1);
?>