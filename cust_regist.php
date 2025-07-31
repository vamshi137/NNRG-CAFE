<!DOCTYPE html>
<html lang="en">

<head>
    <?php session_start(); include("conn_db.php"); include('head.php');?>
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/login.css" rel="stylesheet">

    <title>Customer Registration | FOODCAVE</title>
</head>

<body class="d-flex flex-column">
    <header class="navbar navbar-light fixed-top bg-light shadow-sm mb-auto">
        <div class="container-fluid mx-4">
            <a href="index.php">
            <img src="img/Color logo - no background.png" width="125" class="me-2" alt="FOODCAVE Logo">
            </a>
        </div>
    </header>
    <div class="container mt-4"></div>
    <div class="container form-signin mt-auto">
        <a class="nav nav-item text-decoration-none text-muted" href="#" onclick="history.back();">
            <i class="bi bi-arrow-left-square me-2"></i>Go back
        </a>
        <form method="POST" action="add_cust.php" class="form-floating">
            <h2 class="mt-4 mb-3 fw-normal text-bold"><i class="bi bi-person-plus me-2"></i>Sign Up</h2>
            <div class="form-floating mb-2">
<<<<<<< HEAD
                <input type="text" class="form-control" id="username" placeholder="username" name="username"
                    minlength="5" maxlength="45" required>
                <label for="username">username</label>
=======
                <input type="text" class="form-control" id="username" placeholder="Username" name="username"
                    minlength="5" maxlength="45" required>
                <label for="username">Username</label>
>>>>>>> 5027eac0c6b4220983dc702d727e608a440f1685
            </div>
            <div class="form-floating mb-2">
                <input type="password" class="form-control" id="pwd" placeholder="Password" name="pwd" minlength="8"
                    maxlength="45" required>
                <label for="pwd">Password</label>
            </div>
            <div class="form-floating mb-2">
                <input type="password" class="form-control" id="cfpwd" placeholder="Confirm Password" minlength="8"
                    maxlength="45" name="cfpwd" required>
                <label for="cfpwd">Confirm Password</label>
                <div id="passwordHelpBlock" class="form-text smaller-font">
                    Your password must be at least 8 characters long.
                </div>
            </div>
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="firstname" placeholder="First Name" name="firstname"
                    required>
                <label for="firstname">First Name</label>
            </div>
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="lastname" placeholder="Last Name" name="lastname" required>
                <label for="lastname">Last Name</label>
            </div>
            <div class="form-floating mb-2">
                <input type="email" class="form-control" id="email" placeholder="E-mail" name="email" required>
                <label for="email">E-mail</label>
            </div>
<<<<<<< HEAD
            <div class="form-floating mb-2">
                <input type="tel" class="form-control" id="phone_number" placeholder="Phone Number" name="phone_number" 
                    pattern="[0-9]{10}" minlength="10" maxlength="15" required>
                <label for="phone_number">Phone Number</label>
                <div class="form-text smaller-font">
                    Enter your 10-digit phone number
                </div>
            </div>
=======
>>>>>>> 5027eac0c6b4220983dc702d727e608a440f1685
            <div class="form-floating">
                <select class="form-select mb-2" id="gender" name="gender">
                    <option selected value="-">---</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>
                <label for="gender">Your Gender</label>
            </div>
            <div class="form-floating">
<<<<<<< HEAD
                <select class="form-select mb-2" id="type" name="type" onchange="showDepartmentField()">
=======
                <select class="form-select mb-2" id="type" name="type">
>>>>>>> 5027eac0c6b4220983dc702d727e608a440f1685
                    <option selected value="-">---</option>
                    <option value="STD">Student</option>
                    <option value="STF">Faculty Staff</option>
                    <option value="GUE">Visitor</option>
                    <option value="OTH">Other</option>
                </select>
<<<<<<< HEAD
                <label for="type">Your role</label>
            </div>
            <div class="form-floating" id="departmentField" style="display: none;">
                <select class="form-select mb-2" id="department" name="department">
                    <option selected value="-">---</option>
                    <option value="BTECH">B.Tech</option>
                    <option value="MBA">MBA</option>
                    <option value="BPHARMACY">B.Pharmacy</option>
                    <option value="MTECH">M.Tech</option>
                </select>
                <label for="department">Department/Course</label>
=======
                <label for="gender">Your role</label>
>>>>>>> 5027eac0c6b4220983dc702d727e608a440f1685
            </div>
            <div class="form-floating">
                <div class="mb-2 form-check">
                    <input type="checkbox" class="form-check-input " id="tandc" name="tandc" required>
                    <label class="form-check-label small" for="tandc">I agree to the terms and conditions and the
                        privacy policy</label>
                </div>
            </div>
            <button class="w-100 btn btn-success mb-3" type="submit">Sign Up</button>
        </form>
    </div>
    <div class="container mt-4"></div>
<<<<<<< HEAD
    
    <script>
        function showDepartmentField() {
            const typeSelect = document.getElementById('type');
            const departmentField = document.getElementById('departmentField');
            const departmentSelect = document.getElementById('department');
            
            console.log('Role selected:', typeSelect.value); // Debug line
            
            if (typeSelect.value === 'STD' || typeSelect.value === 'STF') {
                departmentField.style.display = 'block';
                departmentSelect.required = true;
                console.log('Department field shown'); // Debug line
            } else {
                departmentField.style.display = 'none';
                departmentSelect.required = false;
                departmentSelect.value = '-';
                console.log('Department field hidden'); // Debug line
            }
        }
        
        // Also trigger on page load if there's already a selection
        document.addEventListener('DOMContentLoaded', function() {
            showDepartmentField();
        });
    </script>
    
    <?php include('footer.php')?>
</body>

</html>
=======
    <?php include('footer.php')?>
</body>

</html>
>>>>>>> 5027eac0c6b4220983dc702d727e608a440f1685
