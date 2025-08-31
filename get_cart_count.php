<?php
session_start();
include('conn_db.php');

// Return just the cart count for AJAX requests
if(isset($_SESSION['cid'])) {
    $incart_query = "SELECT SUM(ct_amount) AS incart_amt FROM cart WHERE c_id = {$_SESSION['cid']}";
    $incart_result = $mysqli->query($incart_query);
    
    if($incart_result && $incart_row = $incart_result->fetch_array()) {
        $incart_amt = $incart_row["incart_amt"] ? $incart_row["incart_amt"] : 0;
    } else {
        $incart_amt = 0;
    }
    
    echo $incart_amt;
} else {
    echo "0";
}
?>