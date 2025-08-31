<?php
    session_start();
    include('conn_db.php');

    if(!isset($_SESSION["cid"])){
        header("location: cust_login.php");
        exit(1);
    }

    $f_id = $_POST["f_id"];
    $s_id = $_POST["s_id"];
    $c_id = $_SESSION["cid"];
    $amount = $_POST["amount"];
    $request = $_POST["request"];

    // Check if this exact item (same food from same shop) already exists in cart
    $existing_item_query = "SELECT ct_amount FROM cart WHERE c_id = {$c_id} AND f_id = {$f_id} AND s_id = {$s_id}";
    $existing_item_result = $mysqli->query($existing_item_query);

    if($existing_item_result->num_rows > 0) {
        // Item already exists - update quantity
        $existing_item = $existing_item_result->fetch_array();
        $new_amount = $existing_item["ct_amount"] + $amount;
        
        $update_query = "UPDATE cart SET ct_amount = {$new_amount}, ct_note = '{$request}' 
                        WHERE c_id = {$c_id} AND f_id = {$f_id} AND s_id = {$s_id}";
        $atc_result = $mysqli->query($update_query);
    } else {
        // New item - insert into cart
        $insert_query = "INSERT INTO cart (c_id, s_id, f_id, ct_amount, ct_note) 
                        VALUES ({$c_id}, {$s_id}, {$f_id}, {$amount}, '{$request}')";
        $atc_result = $mysqli->query($insert_query);
    }

    // Redirect back to shop menu with success/failure status
    if($atc_result){
        header("location: shop_menu.php?s_id={$s_id}&atc=1");
        exit(1);
    } else {
        header("location: shop_menu.php?s_id={$s_id}&atc=0");
        exit(1);
    }
?>