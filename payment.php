<!DOCTYPE html>
<html lang="en">

<head>
    <?php session_start(); include("conn_db.php"); include('head.php');?>
    <meta charset="UTF-8">
     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/login.css" rel="stylesheet">
    <style>
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
        .rounded-25 {
            border-radius: 25px;
        }
        .delivery-section {
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
        .form-row {
            display: flex;
            gap: 15px;
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
        .time-warning {
            color: #856404;
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        /* Order Time Restriction Notice */
        .order-time-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            color: #856404;
            font-size: 0.95em;
        }
        .order-time-notice i {
            color: #f39c12;
        }
        
        /* Order Type Selection Styles */
        .order-type-container {
            margin-bottom: 20px;
        }
        .order-type-options {
            display: flex;
            gap: 15px;
        }
        .order-type-option {
            flex: 1;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s ease;
            background-color: white;
            cursor: pointer;
        }
        .order-type-option:hover {
            border-color: #adb5bd;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .order-type-option.selected {
            border-color: #28a745;
            background-color: #f8fff9;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
        }
        .order-type-option label {
            cursor: pointer;
            margin-bottom: 0;
            width: 100%;
            display: flex;
            align-items: center;
        }
        .order-type-option input[type="radio"] {
            margin-right: 10px;
            margin-top: 2px;
        }
        @media (max-width: 768px) {
            .order-type-options {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>

    <title>Payment | FOODCAVE</title>
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
        
        <div class="row row-cols-1 row-cols-md-2 mb-5">
            <div class="col mb-3 qr mb-md-0">
                <img 
                    src="img/qpr.jpeg"
                    class="img-fluid rounded-25 float-start" 
                    alt="qr">
            </div>
        
            <form id="payment-form" method="POST" action="verify_transaction.php" class="form-floating">
                <h2 class="mt-4 mb-3 fw-normal text-bold"><i class="bi bi-qr-code-scan"></i> Payment</h2>
                <div class="col my-3">
                    <ul class="list-inline justify-content-between">
                    <li class="list-inline-item fw-light me-5">Grand Total</li>
                        <li class="list-inline-item fw-bold h4">
                            <?php
                                $gt_query = "SELECT SUM(ct.ct_amount*f.f_price) AS grandtotal FROM cart ct INNER JOIN food f 
                                ON ct.f_id = f.f_id WHERE ct.c_id = {$_SESSION['cid']} GROUP BY ct.c_id";
                                $gt_arr = $mysqli -> query($gt_query) -> fetch_array();
                                $order_cost = $gt_arr["grandtotal"];
                                printf("%.2f INR",$order_cost);
                            ?>
                        </li>
                    </ul>
                </div>
                
                <!-- Personal Information Section -->
                <div class="delivery-section">
                    <h5 class="section-title"><i class="bi bi-person-fill me-2"></i>Personal Information</h5>
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" placeholder="Full Name" name="name" required>
                        <label for="name">Full Name</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" placeholder="E-mail" name="email" required>
                        <label for="email">E-mail</label>
                    </div>
                    
                    <div class="form-row mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="rollno" placeholder="Roll Number" name="rollno" required>
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
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="branch_section" placeholder="Branch & Section" name="branch_section" required>
                        <label for="branch_section">Branch & Section (e.g., CSE-A, ECE-B)</label>
                    </div>
                </div>
                
                <!-- Pickup Information Section with Order Type -->
                <div class="delivery-section">
                    <h5 class="section-title" id="pickup-title"><i class="bi bi-geo-alt-fill me-2"></i>Pickup Information</h5>
                    
                    <!-- Order Time Restriction Notice -->
                    <div class="order-time-notice">
                        <i class="bi bi-clock-fill me-2"></i>
                        <strong>Important:</strong> Orders are accepted only before 11:45 AM. Please place your order accordingly.
                    </div>
                    
                    <!-- Order Type Selection -->
                    <div class="order-type-container">
                        <label class="form-label fw-semibold mb-3">Order Type <span class="text-danger">*</span></label>
                        <div class="order-type-options">
                            <div class="order-type-option" onclick="selectOrderType('dine-in')">
                                <label for="dine-in">
                                    <input type="radio" id="dine-in" name="order_type" value="dine-in" required>
                                    <div>
                                        <i class="bi bi-house-door-fill me-2 text-primary"></i>
                                        <strong>Dine-In</strong>
                                        <small class="d-block text-muted">Eat at restaurant</small>
                                    </div>
                                </label>
                            </div>
                            <div class="order-type-option" onclick="selectOrderType('takeaway')">
                                <label for="takeaway">
                                    <input type="radio" id="takeaway" name="order_type" value="takeaway" required>
                                    <div>
                                        <i class="bi bi-bag-fill me-2 text-success"></i>
                                        <strong>Takeaway</strong>
                                        <small class="d-block text-muted">Pick up to go</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="time" class="form-control" id="delivery_time" name="delivery_time" required>
                        <label for="delivery_time" id="time-label">Preferred Pickup Time</label>
                        <div class="time-warning" id="time-warning">
                            <i class="bi bi-info-circle me-1"></i>
                            Minimum pickup time: <span id="min-time-display"></span>
                        </div>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="delivery_notes" placeholder="Special instructions" name="delivery_notes" style="height: 80px"></textarea>
                        <label for="delivery_notes">Special Instructions (Optional)</label>
                    </div>
                </div>
                
                <!-- Payment Information Section -->
                <div class="delivery-section">
                    <h5 class="section-title"><i class="bi bi-credit-card-fill me-2"></i>Payment Information</h5>
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="tid" placeholder="Transaction ID" name="tid" minlength="12"
                            maxlength="45" required>
                        <label for="tid">Transaction ID</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="cftid" placeholder="Confirm Transaction ID" minlength="12"
                            maxlength="45" name="cftid" required>
                        <label for="cftid">Confirm Transaction ID</label>
                    </div>
                </div>
                
                <div class="form-floating">
                    <div class="mb-2 form-check">
                        <input type="checkbox" class="form-check-input" id="tandc" name="tandc" required>
                        <label class="form-check-label small" for="tandc">I agree to the terms and conditions and the
                            privacy policy</label>
                    </div>
                </div>
                
                <button class="w-100 btn btn-success mb-3" id="submit-payment" type="submit">Submit Payment</button>
            </form>
        </div>
    </div>
    
    <?php include('footer.php')?>
    
    <script>
        // Global variables to store minimum pickup time
        let minPickupTime;
        let minTimeString;
        let isFormSubmitting = false; // Flag to prevent multiple submissions
        
        document.addEventListener('DOMContentLoaded', function() {
            // Calculate and set minimum pickup time (current time + 30 minutes)
            updateMinimumPickupTime();
            
            // Update minimum time every minute to keep it current
            setInterval(updateMinimumPickupTime, 60000);
            
            // Form validation for pickup time - on change
            document.getElementById('delivery_time').addEventListener('change', function() {
                validatePickupTime(this.value);
            });
            
            // Form validation for pickup time - on input (real-time)
            document.getElementById('delivery_time').addEventListener('input', function() {
                validatePickupTime(this.value);
            });
            
            // Listen for order type changes to update labels
            document.querySelectorAll('input[name="order_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    updateLabelsBasedOnOrderType(this.value);
                });
            });
            
            // IMPROVED Form submission handling with better redirect
            document.getElementById('payment-form').addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission initially
                
                // Prevent multiple submissions
                if (isFormSubmitting) {
                    return false;
                }
                
                // Validate all required fields
                const requiredFields = [
                    { id: 'name', name: 'Full Name' },
                    { id: 'email', name: 'Email' },
                    { id: 'rollno', name: 'Roll Number' },
                    { id: 'year', name: 'Academic Year' },
                    { id: 'branch_section', name: 'Branch & Section' },
                    { id: 'delivery_time', name: 'Pickup Time' },
                    { id: 'tid', name: 'Transaction ID' },
                    { id: 'cftid', name: 'Confirm Transaction ID' }
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
                
                // Validate pickup time
                const pickupTime = document.getElementById('delivery_time').value;
                if (!validatePickupTime(pickupTime)) {
                    showError('Please select a pickup time at least 30 minutes from now.');
                    return false;
                }
                
                // Validate transaction IDs match
                const tid = document.getElementById('tid').value;
                const cftid = document.getElementById('cftid').value;
                
                if (tid !== cftid) {
                    showError('Transaction IDs do not match. Please confirm your transaction ID.');
                    return false;
                }
                
                // Set flag to prevent multiple submissions
                isFormSubmitting = true;
                
                // Hide any previous errors
                hideAllMessages();
                
                // Show processing message
                document.getElementById('payment-processing').style.display = 'block';
                
                // Disable submit button to prevent double submission
                const submitBtn = document.getElementById('submit-payment');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                
                // Check if transaction ID already exists in the database using AJAX
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
                        // Handle API errors
                        showError(data.message || 'Error checking transaction.');
                        resetFormSubmission();
                    } else if (data.exists) {
                        // Transaction ID already used
                        showError('This transaction ID has already been used. Please try another payment.');
                        resetFormSubmission();
                    } else {
                        // Transaction ID is valid, proceed with form submission
                        setTimeout(function() {
                            document.getElementById('payment-processing').style.display = 'none';
                            document.getElementById('payment-success').style.display = 'block';
                            
                            // FIXED: Submit form properly after delay
                            setTimeout(function() {
                                // Create form data to submit
                                const form = document.getElementById('payment-form');
                                
                                // Simply submit the form - no need for complex cloning
                                form.submit();
                            }, 1500); // Reduced delay for better UX
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
        }
        
        // Function to update labels based on order type
        function updateLabelsBasedOnOrderType(orderType) {
            const pickupTitle = document.getElementById('pickup-title');
            const timeLabel = document.getElementById('time-label');
            
            if (orderType === 'dine-in') {
                pickupTitle.innerHTML = '<i class="bi bi-clock-fill me-2"></i>Reservation Information';
                timeLabel.textContent = 'Preferred Arrival Time';
            } else {
                pickupTitle.innerHTML = '<i class="bi bi-geo-alt-fill me-2"></i>Pickup Information';
                timeLabel.textContent = 'Preferred Pickup Time';
            }
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
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Payment';
            document.getElementById('payment-processing').style.display = 'none';
        }
        
        function updateMinimumPickupTime() {
            const now = new Date();
            
            // Add 30 minutes to current time
            minPickupTime = new Date(now.getTime() + 30 * 60000);
            const minHours = String(minPickupTime.getHours()).padStart(2, '0');
            const minMinutes = String(minPickupTime.getMinutes()).padStart(2, '0');
            minTimeString = `${minHours}:${minMinutes}`;
            
            // Set the min attribute for the time input
            document.getElementById('delivery_time').min = minTimeString;
            
            // Update the display
            document.getElementById('min-time-display').textContent = minTimeString;
        }
        
        function validatePickupTime(selectedTimeValue) {
            if (!selectedTimeValue) {
                return false;
            }
            
            const now = new Date();
            const selectedDateTime = new Date();
            
            // Parse the selected time
            const [hours, minutes] = selectedTimeValue.split(':').map(Number);
            selectedDateTime.setHours(hours, minutes, 0, 0);
            
            // Calculate minimum pickup time (current time + 30 minutes)
            const currentMinPickupTime = new Date(now.getTime() + 30 * 60000);
            
            // Check if selected time is at least 30 minutes from now
            if (selectedDateTime <= currentMinPickupTime) {
                // Clear the input
                document.getElementById('delivery_time').value = '';
                
                // Show error message
                alert(`Please select a pickup time at least 30 minutes from now. Minimum time: ${minTimeString}`);
                
                return false;
            }
            
            return true;
        }
    </script>
</body>

</html>