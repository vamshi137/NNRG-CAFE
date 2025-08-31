<!DOCTYPE html>
<html lang="en">

<head>
    <?php session_start(); include("conn_db.php"); include('head.php');?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/login.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .payment-success {
            display: none;
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .payment-processing {
            display: none;
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .payment-error {
            display: none;
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* QR Code styling - updated for payment section */
        .qr-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        #payment-qr-code {
            width: 250px;
            height: 250px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Form container styling */
        .form-container {
            background: white;
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto;
        }

        /* Payment header */
        .payment-header {
            background: white;
            padding: 25px 30px 20px;
            border-bottom: 1px solid #e9ecef;
            border-radius: 12px 12px 0 0;
        }

        .payment-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
        }

        /* Order Summary Card */
        .order-summary-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 30px;
        }

        .order-summary-title {
            font-size: 18px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 15px;
            border-bottom: none;
            padding-bottom: 0;
        }

        .cost-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .cost-item.total {
            border-top: 1px solid #dee2e6;
            padding-top: 12px;
            margin-top: 12px;
            font-weight: bold;
            font-size: 18px;
        }

        /* Section styling */
        .form-section {
            padding: 25px 30px;
            border-bottom: 1px solid #f0f0f0;
        }

        .form-section:last-child {
            border-bottom: none;
            border-radius: 0 0 12px 12px;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header i {
            font-size: 20px;
            margin-right: 10px;
            color: #6c757d;
        }

        .section-header h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #495057;
        }

        /* Form controls */
        .form-floating {
            margin-bottom: 20px;
        }

        .form-control, .form-select {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 16px;
            height: auto;
            min-height: 50px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
        }

        .form-floating label {
            padding: 12px 16px;
            color: #6c757d;
            font-weight: 500;
        }

        /* Auto-filled field styling */
        .form-control[readonly] {
            background-color: #f8f9fa;
            border-color: #e9ecef;
            color: #6c757d;
        }

        .form-control[readonly]:focus {
            background-color: #f8f9fa;
            border-color: #ced4da;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
        }

        /* Form row for side-by-side inputs */
        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-floating {
            flex: 1;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        /* Time Slot Styling */
        .time-slot-notice {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            color: white;
            font-size: 15px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .time-slot-notice i {
            color: #fff;
            margin-right: 8px;
            font-size: 16px;
        }

        .time-slot-notice strong {
            color: #fff;
        }

        /* Order Time Notice */
        .order-time-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-left: 4px solid #f39c12;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            color: #856404;
            font-size: 15px;
        }

        .order-time-notice i {
            color: #f39c12;
            margin-right: 8px;
        }

        /* Order Type Selection */
        .order-type-container {
            margin-bottom: 25px;
        }

        .order-type-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .order-type-options {
            display: flex;
            gap: 15px;
        }

        .order-type-option {
            flex: 1;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            background-color: white;
            cursor: pointer;
            position: relative;
        }

        .order-type-option:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        }

        .order-type-option.selected {
            border-color: #28a745;
            background-color: #f8fff9;
            box-shadow: 0 2px 12px rgba(40, 167, 69, 0.15);
        }

        .order-type-option label {
            cursor: pointer;
            margin-bottom: 0;
            width: 100%;
            display: flex;
            align-items: flex-start;
        }

        .order-type-option input[type="radio"] {
            margin-right: 12px;
            margin-top: 3px;
            transform: scale(1.1);
        }

        .order-type-content {
            flex: 1;
        }

        .order-type-title {
            font-weight: 600;
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .order-type-subtitle {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .takeaway-charge {
            color: #dc3545;
            font-size: 13px;
            font-weight: 500;
        }

        /* Time input styling */
        .time-warning {
            color: #856404;
            font-size: 14px;
            margin-top: 8px;
            display: flex;
            align-items: center;
        }

        .time-warning i {
            margin-right: 6px;
        }

        /* Transaction ID Validation */
        .transaction-input.error {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15) !important;
        }

        .transaction-input.success {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.15) !important;
        }

        .transaction-error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 6px;
            display: none;
        }

        .transaction-success {
            color: #28a745;
            font-size: 14px;
            margin-top: 6px;
            display: none;
        }

        .digit-counter {
            font-size: 13px;
            color: #6c757d;
            text-align: right;
            margin-top: 6px;
        }

        .example-text {
            font-size: 14px;
            color: #6c757d;
            font-style: italic;
            margin-top: 6px;
        }

        /* Submit button */
        .submit-button {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 15px;
        }

        .submit-button:hover:not(:disabled) {
            background: linear-gradient(135deg, #218838, #1ea384);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .submit-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Terms checkbox */
        .terms-container {
            margin: 20px 0;
        }

        .form-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .form-check-input {
            margin-top: 4px;
            transform: scale(1.1);
        }

        .form-check-label {
            font-size: 14px;
            color: #6c757d;
            line-height: 1.4;
        }

        @media (max-width: 768px) {
            .order-type-options {
                flex-direction: column;
                gap: 12px;
            }

            .form-container {
                margin: 0 15px;
            }

            .form-section {
                padding: 20px;
            }

            .order-summary-card {
                margin: 15px 20px;
                padding: 15px;
            }

            .payment-header {
                padding: 20px;
            }

            #payment-qr-code {
                width: 200px;
                height: 200px;
            }
        }

        /* Container adjustments - removed complex layout since QR is now in form */
        #shop-body {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>

    <title>Payment | NNRG-CAFE</title>
</head>

<body class="d-flex flex-column">
    <header class="navbar navbar-light fixed-top bg-light shadow-sm mb-auto">
        <div class="container-fluid mx-4">
            <a href="index.php">
            <img src="img/Color logo - no background.png" width="125" class="me-2" alt="FOODCAVE Logo">
            </a>
        </div>
    </header>

    <div class="container px-5 py-4" id="shop-body">
        <div class="row my-4">
            <a class="nav nav-item text-decoration-none text-muted mb-2" href="#" onclick="history.back();">
                <i class="bi bi-arrow-left-square me-2"></i>Go back
            </a>
        </div>
        
        <!-- Display session error messages if any -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>
        
        <div id="payment-success" class="payment-success">
            <i class="bi bi-check-circle-fill me-2"></i> Payment Successful! Redirecting to order confirmation...
        </div>
        
        <div id="payment-processing" class="payment-processing">
            <i class="bi bi-hourglass-split me-2"></i> Processing your payment, please wait...
        </div>
        
        <div id="payment-error" class="payment-error">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <span id="error-message">Error processing payment.</span>
        </div>

        <?php
        // Detect shop type from cart
        $shop_detection_query = "SELECT DISTINCT s.s_id, s.s_name 
                                FROM cart c 
                                INNER JOIN shop s ON c.s_id = s.s_id 
                                WHERE c.c_id = {$_SESSION['cid']} 
                                LIMIT 1";
        $shop_detection_result = $mysqli->query($shop_detection_query);
        $current_shop = $shop_detection_result->fetch_array();
        $is_tiffin_shop = ($current_shop && $current_shop['s_id'] == 5);
        
        // Your UPI details
        $merchant_vpa = "9059988828@idfcfirst";  // Your UPI ID
        $merchant_name = "NNRG-CAFE";
        $transaction_note = "Food Order Payment";
        
        // Get cart total
        $gt_query = "SELECT SUM(ct.ct_amount*f.f_price) AS grandtotal FROM cart ct INNER JOIN food f 
        ON ct.f_id = f.f_id WHERE ct.c_id = {$_SESSION['cid']} GROUP BY ct.c_id";
        $gt_arr = $mysqli -> query($gt_query) -> fetch_array();
        $base_cost = $gt_arr["grandtotal"];
        
        // Base amount
        $amount = $base_cost;
        
        // Create UPI payment string
        $upi_string = "upi://pay?pa={$merchant_vpa}&pn=" . urlencode($merchant_name) . "&am={$amount}&cu=INR&tn=" . urlencode($transaction_note);
        
        // Generate QR code URL
        $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($upi_string);
        ?>

        <!-- Form Section -->
        <form id="payment-form" method="POST" action="verify_transaction.php" class="form-container">
            <!-- Payment Header -->
            <div class="payment-header">
                <h2><i class="bi bi-qr-code-scan me-2"></i>Payment</h2>
            </div>
            
            <!-- Order Summary Card -->
            <div class="order-summary-card">
                <h6 class="order-summary-title">Order Summary</h6>
                <div class="cost-item">
                    <span>Food Items:</span>
                    <span id="base-cost"><?php printf("%.2f INR", $base_cost); ?></span>
                </div>
                <div class="cost-item" id="takeaway-charge-item" style="display: none;">
                    <span>Takeaway Charges:</span>
                    <span class="text-danger">5.00 INR</span>
                </div>
                <div class="cost-item total">
                    <span>Grand Total:</span>
                    <span id="grand-total"><?php printf("%.2f INR", $base_cost); ?></span>
                </div>
            </div>
            
            <!-- Hidden field to store final amount and shop type -->
            <input type="hidden" name="final_amount" id="final-amount" value="<?php echo $base_cost; ?>">
            <input type="hidden" name="shop_type" id="shop-type" value="<?php echo $is_tiffin_shop ? 'tiffin' : 'regular'; ?>">
            
            <!-- Personal Information Section -->
            <?php
            // Fetch user details from database
            $user_query = "SELECT c_username, c_firstname, c_lastname, c_email FROM customer WHERE c_id = {$_SESSION['cid']} LIMIT 0,1";
            $user_result = $mysqli->query($user_query);
            $user_data = $user_result->fetch_array();
            
            $full_name = $user_data["c_firstname"] . " " . $user_data["c_lastname"];
            $user_email = $user_data["c_email"];
            $roll_number = $user_data["c_username"];
            ?>
            <div class="form-section">
                <div class="section-header">
                    <i class="bi bi-person-fill"></i>
                    <h5>Personal Information</h5>
                </div>
                
                <div class="form-floating">
                    <input type="text" class="form-control" id="name" placeholder="Full Name" name="name" 
                           value="<?php echo htmlspecialchars($full_name); ?>" readonly>
                    <label for="name">Full Name</label>
                </div>
                
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" placeholder="E-mail" name="email" 
                           value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                    <label for="email">E-mail</label>
                </div>
                
                <div class="form-row">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="rollno" placeholder="Roll Number" name="rollno" 
                               value="<?php echo htmlspecialchars($roll_number); ?>" readonly>
                        <label for="rollno">Roll Number</label>
                    </div>
                    
                    <div class="form-floating">
                        <select class="form-select" id="year" name="year" required>
                            <option value="">Select Year</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                        <label for="year">Academic Year</label>
                    </div>
                </div>
                
                <div class="form-floating">
                    <input type="text" class="form-control" id="branch_section" placeholder="Branch & Section" name="branch_section" required>
                    <label for="branch_section">Branch & Section (e.g., CSE-A, ECE-B)</label>
                </div>
            </div>
            
            <!-- Pickup Information Section -->
            <div class="form-section">
                <div class="section-header">
                    <i class="bi bi-geo-alt-fill" id="pickup-icon"></i>
                    <h5 id="pickup-title">Pickup Information</h5>
                </div>
                
                <!-- Time Slot Notice - Dynamic based on shop type -->
                <div class="time-slot-notice">
                    <i class="bi bi-clock-history"></i>
                    <strong>Pickup Time Slots:</strong>
                    <?php if($is_tiffin_shop): ?>
                        <span id="time-slot-info">Tiffin Shop - Available pickup times: <strong>8:00 AM to 10:00 AM</strong></span>
                    <?php else: ?>
                        <span id="time-slot-info">Regular Shop - Available pickup times: <strong>12:00 PM to 4:00 PM</strong></span>
                    <?php endif; ?>
                </div>
                
                <!-- Order Time Restriction Notice -->
                <div class="order-time-notice">
                    <i class="bi bi-clock-fill"></i>
                    <strong>Important:</strong> Orders are accepted only before 11:45 AM. Please place your order accordingly.
                </div>
                
                <!-- Order Type Selection -->
                <div class="order-type-container">
                    <div class="order-type-label">Order Type <span class="text-danger">*</span></div>
                    <div class="order-type-options">
                        <div class="order-type-option" onclick="selectOrderType('dine-in')">
                            <label for="dine-in">
                                <input type="radio" id="dine-in" name="order_type" value="dine-in" required>
                                <div class="order-type-content">
                                    <div class="order-type-title">
                                        <i class="bi bi-house-door-fill me-2 text-primary"></i>Dine-In
                                    </div>
                                    <div class="order-type-subtitle">Eat at restaurant</div>
                                </div>
                            </label>
                        </div>
                        <div class="order-type-option" onclick="selectOrderType('takeaway')">
                            <label for="takeaway">
                                <input type="radio" id="takeaway" name="order_type" value="takeaway" required>
                                <div class="order-type-content">
                                    <div class="order-type-title">
                                        <i class="bi bi-bag-fill me-2 text-success"></i>Takeaway
                                    </div>
                                    <div class="order-type-subtitle">Pick up to go</div>
                                    <div class="takeaway-charge">+ ₹5.00 takeaway charges</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Time Selection Input -->
                <div class="form-floating">
                    <input type="time" class="form-control" id="delivery_time" name="delivery_time" required
                           min="<?php echo $is_tiffin_shop ? '08:00' : '12:00'; ?>" 
                           max="<?php echo $is_tiffin_shop ? '10:00' : '16:00'; ?>">
                    <label for="delivery_time" id="time-label">Preferred Pickup Time</label>
                    <div class="time-warning" id="time-warning">
                        <i class="bi bi-info-circle"></i>
                        <span id="time-warning-text">
                            <?php if($is_tiffin_shop): ?>
                                Select any time between 8:00 AM and 10:00 AM
                            <?php else: ?>
                                Select any time between 12:00 PM and 4:00 PM
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <div class="form-floating">
                    <textarea class="form-control" id="delivery_notes" placeholder="Special instructions" name="delivery_notes" style="height: 100px"></textarea>
                    <label for="delivery_notes">Special Instructions (Optional)</label>
                </div>
            </div>
            
            <!-- Payment Information Section -->
            <div class="form-section">
                <div class="section-header">
                    <i class="bi bi-credit-card-fill"></i>
                    <h5>Payment Information</h5>
                </div>
                
                <!-- QR Code Section - Now in Payment Information -->
                <div class="qr-container">
                    <img 
                        id="payment-qr-code"
                        src="<?php echo $qr_code_url; ?>"
                        alt="Payment QR Code"
                        data-base-amount="<?php echo $base_cost; ?>"
                        data-merchant-vpa="<?php echo $merchant_vpa; ?>"
                        data-merchant-name="<?php echo urlencode($merchant_name); ?>"
                        data-transaction-note="<?php echo urlencode($transaction_note); ?>">
                </div>
                
                <div class="form-floating">
                    <input type="text" class="form-control transaction-input" id="tid" placeholder="Transaction ID" name="tid" 
                        maxlength="12" required autocomplete="off">
                    <label for="tid">Enter 12 Digit Transaction ID</label>
                    <div class="digit-counter">
                        <span id="tid-digit-count">0</span>/12 digits
                    </div>
                    <div class="example-text">
                        Example: 123456789012
                    </div>
                    <div id="tid-error" class="transaction-error"></div>
                    <div id="tid-success" class="transaction-success">
                        <i class="bi bi-check-circle-fill me-1"></i>Valid transaction ID
                    </div>
                </div>
                
                <div class="form-floating">
                    <input type="text" class="form-control transaction-input" id="cftid" placeholder="Confirm Transaction ID" 
                        maxlength="12" name="cftid" required autocomplete="off">
                    <label for="cftid">Confirm Transaction ID</label>
                    <div class="digit-counter">
                        <span id="cftid-digit-count">0</span>/12 digits
                    </div>
                    <div class="example-text">
                        Re-enter the same 12-digit transaction ID
                    </div>
                    <div id="cftid-error" class="transaction-error"></div>
                    <div id="cftid-success" class="transaction-success">
                        <i class="bi bi-check-circle-fill me-1"></i>Transaction IDs match
                    </div>
                </div>
                
                <div class="terms-container">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="tandc" name="tandc" required>
                        <label class="form-check-label" for="tandc">I agree to the terms and conditions and the privacy policy</label>
                    </div>
                </div>
                
                <button class="submit-button" id="submit-payment" type="submit" disabled>Submit Payment</button>
            </div>
        </form>
    </div>
    
    <?php include('footer.php')?>
    
    <script>
        // Global variables
        let isFormSubmitting = false;
        const baseCost = <?php echo $base_cost; ?>;
        const takeawayCharge = 5.00;
        const isTiffinShop = <?php echo $is_tiffin_shop ? 'true' : 'false'; ?>;
        
        // Transaction ID validation state
        let tidValid = false;
        let cftidValid = false;
        let idsMatch = false;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize QR code with base amount
            updateQRCode('dine-in');
            
            // Listen for time input changes
            document.getElementById('delivery_time').addEventListener('change', function() {
                validateTimeSlot(this.value);
            });
            
            // Listen for order type changes
            document.querySelectorAll('input[name="order_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    updateLabelsBasedOnOrderType(this.value);
                    updateCostDisplay(this.value);
                });
            });
            
            // Transaction ID validation for TID field
            document.getElementById('tid').addEventListener('input', function(e) {
                validateTransactionId('tid', e.target.value);
            });
            
            // Transaction ID validation for Confirm TID field
            document.getElementById('cftid').addEventListener('input', function(e) {
                validateTransactionId('cftid', e.target.value);
            });
            
            // Form submission handling
            document.getElementById('payment-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Prevent multiple submissions
                if (isFormSubmitting) {
                    return false;
                }
                
                // Validate all required fields
                const requiredFields = [
                    { id: 'year', name: 'Academic Year' },
                    { id: 'branch_section', name: 'Branch & Section' },
                    { id: 'delivery_time', name: 'Pickup Time' }
                ];
                
                let missingFields = [];
                for (let field of requiredFields) {
                    const fieldValue = document.getElementById(field.id).value.trim();
                    if (!fieldValue) {
                        missingFields.push(field.name);
                    }
                }
                
                // Check if order type is selected
                const orderType = document.querySelector('input[name="order_type"]:checked');
                if (!orderType) {
                    missingFields.push('Order Type (Dine-In or Takeaway)');
                }
                
                if (missingFields.length > 0) {
                    showError('Please fill in the following required fields: ' + missingFields.join(', '));
                    return false;
                }
                
                // Check terms and conditions
                if (!document.getElementById('tandc').checked) {
                    showError('Please accept the terms and conditions.');
                    return false;
                }
                
                // Validate time slot
                const selectedTime = document.getElementById('delivery_time').value;
                if (!validateTimeSlot(selectedTime)) {
                    return false;
                }
                
                // Validate transaction IDs
                if (!tidValid) {
                    showError('Please enter a valid 12-digit transaction ID.');
                    return false;
                }
                
                if (!cftidValid) {
                    showError('Please confirm your 12-digit transaction ID.');
                    return false;
                }
                
                if (!idsMatch) {
                    showError('Transaction IDs do not match. Please confirm your transaction ID.');
                    return false;
                }
                
                // Update final amount before submission
                const finalAmount = orderType.value === 'takeaway' ? baseCost + takeawayCharge : baseCost;
                document.getElementById('final-amount').value = finalAmount.toFixed(2);
                
                // Set flag to prevent multiple submissions
                isFormSubmitting = true;
                
                // Hide any previous errors
                hideAllMessages();
                
                // Show processing message
                document.getElementById('payment-processing').style.display = 'block';
                
                // Disable submit button
                const submitBtn = document.getElementById('submit-payment');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                
                // Check if transaction ID already exists
                const tid = document.getElementById('tid').value;
                fetch('check_transaction.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'tid=' + encodeURIComponent(tid)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        showError(data.message || 'Error checking transaction.');
                        resetFormSubmission();
                    } else if (data.exists) {
                        showError('This transaction ID has already been used. Please try another payment.');
                        resetFormSubmission();
                    } else {
                        // Transaction ID is valid, proceed with form submission
                        setTimeout(function() {
                            document.getElementById('payment-processing').style.display = 'none';
                            document.getElementById('payment-success').style.display = 'block';
                            
                            setTimeout(function() {
                                document.getElementById('payment-form').submit();
                            }, 1500);
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Network Error:', error);
                    showError('Network error. Please check your connection and try again.');
                    resetFormSubmission();
                });
            });
        });
        
        // Function to validate selected time slot
        function validateTimeSlot(selectedTimeValue) {
            if (!selectedTimeValue) {
                return false;
            }
            
            // Parse the selected time
            const [hours, minutes] = selectedTimeValue.split(':').map(Number);
            
            // Additional validation: Check if time is within allowed shop hours
            let validTimeSlot = false;
            
            if (isTiffinShop) {
                // Tiffin shop: 8:00 AM to 10:00 AM
                validTimeSlot = (hours >= 8 && hours <= 10);
            } else {
                // Regular shop: 12:00 PM to 4:00 PM (16:00 in 24-hour format)
                validTimeSlot = (hours >= 12 && hours <= 16);
            }
            
            if (!validTimeSlot) {
                document.getElementById('delivery_time').value = '';
                if (isTiffinShop) {
                    showError('Tiffin shop pickup times are only available between 8:00 AM and 10:00 AM.');
                } else {
                    showError('Regular shop pickup times are only available between 12:00 PM and 4:00 PM.');
                }
                return false;
            }
            
            return true;
        }
        
        // Function to validate transaction ID
        function validateTransactionId(fieldId, value) {
            const input = document.getElementById(fieldId);
            const errorElement = document.getElementById(fieldId + '-error');
            const successElement = document.getElementById(fieldId + '-success');
            const digitCountElement = document.getElementById(fieldId + '-digit-count');
            
            // Remove any non-digit characters
            let cleanValue = value.replace(/\D/g, '');
            
            // Update input with clean value if it was different
            if (cleanValue !== value) {
                input.value = cleanValue;
            }
            
            // Update digit counter
            digitCountElement.textContent = cleanValue.length;
            
            // Reset validation state
            input.classList.remove('error', 'success');
            errorElement.style.display = 'none';
            successElement.style.display = 'none';
            
            // Validate length and content
            if (cleanValue.length === 0) {
                if (fieldId === 'tid') tidValid = false;
                if (fieldId === 'cftid') cftidValid = false;
            } else if (cleanValue.length < 12) {
                input.classList.add('error');
                errorElement.textContent = `Enter exactly 12 digits (${cleanValue.length}/12)`;
                errorElement.style.display = 'block';
                
                if (fieldId === 'tid') tidValid = false;
                if (fieldId === 'cftid') cftidValid = false;
            } else if (cleanValue.length === 12) {
                input.classList.add('success');
                successElement.style.display = 'block';
                
                if (fieldId === 'tid') tidValid = true;
                if (fieldId === 'cftid') cftidValid = true;
                
                checkTransactionIdsMatch();
            }
            
            updateSubmitButtonState();
        }
        
        // Function to check if transaction IDs match
        function checkTransactionIdsMatch() {
            const tid = document.getElementById('tid').value;
            const cftid = document.getElementById('cftid').value;
            const cftidErrorElement = document.getElementById('cftid-error');
            const cftidSuccessElement = document.getElementById('cftid-success');
            const cftidInput = document.getElementById('cftid');
            
            if (tidValid && cftidValid) {
                if (tid === cftid) {
                    idsMatch = true;
                    cftidInput.classList.remove('error');
                    cftidInput.classList.add('success');
                    cftidErrorElement.style.display = 'none';
                    cftidSuccessElement.style.display = 'block';
                } else {
                    idsMatch = false;
                    cftidInput.classList.remove('success');
                    cftidInput.classList.add('error');
                    cftidSuccessElement.style.display = 'none';
                    cftidErrorElement.textContent = 'Transaction IDs do not match';
                    cftidErrorElement.style.display = 'block';
                }
            } else {
                idsMatch = false;
            }
            
            updateSubmitButtonState();
        }
        
        // Function to update submit button state
        function updateSubmitButtonState() {
            const submitBtn = document.getElementById('submit-payment');
            
            if (tidValid && cftidValid && idsMatch) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }
        
        // Function to handle order type selection
        function selectOrderType(type) {
            // Remove selected class from all options
            document.querySelectorAll('.order-type-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.getElementById(type).checked = true;
            
            // Update labels based on selection
            updateLabelsBasedOnOrderType(type);
            
            // Update cost display
            updateCostDisplay(type);
        }
        
        // Function to update labels based on order type
        function updateLabelsBasedOnOrderType(orderType) {
            const pickupTitle = document.getElementById('pickup-title');
            const pickupIcon = document.getElementById('pickup-icon');
            const timeLabel = document.getElementById('time-label');
            
            if (orderType === 'dine-in') {
                pickupTitle.textContent = 'Reservation Information';
                pickupIcon.className = 'bi bi-clock-fill';
                timeLabel.textContent = 'Preferred Arrival Time';
            } else {
                pickupTitle.textContent = 'Pickup Information';
                pickupIcon.className = 'bi bi-geo-alt-fill';
                timeLabel.textContent = 'Preferred Pickup Time';
            }
        }
        
        // Function to update cost display based on order type
        function updateCostDisplay(orderType) {
            const takeawayChargeItem = document.getElementById('takeaway-charge-item');
            const grandTotalElement = document.getElementById('grand-total');
            
            if (orderType === 'takeaway') {
                takeawayChargeItem.style.display = 'flex';
                const totalWithCharge = baseCost + takeawayCharge;
                grandTotalElement.textContent = totalWithCharge.toFixed(2) + ' INR';
            } else {
                takeawayChargeItem.style.display = 'none';
                grandTotalElement.textContent = baseCost.toFixed(2) + ' INR';
            }
            
            updateQRCode(orderType);
        }
        
        // Function to update QR code based on order type and amount
        function updateQRCode(orderType) {
            const qrImage = document.getElementById('payment-qr-code');
            const baseCost = parseFloat(qrImage.dataset.baseAmount);
            const merchantVpa = qrImage.dataset.merchantVpa;
            const merchantName = qrImage.dataset.merchantName;
            const transactionNote = qrImage.dataset.transactionNote;
            
            const finalAmount = orderType === 'takeaway' ? baseCost + takeawayCharge : baseCost;
            
            const upiString = `upi://pay?pa=${merchantVpa}&pn=${merchantName}&am=${finalAmount.toFixed(2)}&cu=INR&tn=${transactionNote}`;
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(upiString)}`;
            
            qrImage.src = qrUrl;
        }
        
        function showError(message) {
            hideAllMessages();
            document.getElementById('error-message').textContent = message;
            document.getElementById('payment-error').style.display = 'block';
        }
        
        function hideAllMessages() {
            document.getElementById('payment-error').style.display = 'none';
            document.getElementById('payment-processing').style.display = 'none';
            document.getElementById('payment-success').style.display = 'none';
        }
        
        function resetFormSubmission() {
            isFormSubmitting = false;
            const submitBtn = document.getElementById('submit-payment');
            submitBtn.disabled = tidValid && cftidValid && idsMatch ? false : true;
            submitBtn.textContent = 'Submit Payment';
            document.getElementById('payment-processing').style.display = 'none';
        }
    </script>
</body>

</html>