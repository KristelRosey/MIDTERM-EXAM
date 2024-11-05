<?php

include 'database4.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $bdate = $_POST['bdate'];
    $contact_num = $_POST['contact_num'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    

    $check_email_stmt = $conn->prepare("SELECT EMAIL FROM CUSTOMER_INFO WHERE EMAIL = ?");
    $check_email_stmt->bind_param("s", $email);
    $check_email_stmt->execute();
    $check_email_stmt->store_result();
    
    if ($check_email_stmt->num_rows > 0) {
        // Email already exists
        $error = "An account with this email already exists. Please use a different email.";
    } else {
        
        $stmt = $conn->prepare("INSERT INTO CUSTOMER_INFO (FNAME, LNAME, BDATE, CONTACT_NUM, EMAIL, USER, PASS) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $fname, $lname, $bdate, $contact_num, $email, $username, $password);

        if ($stmt->execute()) {
            // Registration successful
            header("Location: login.php");
            exit();
        } else {
            // Registration failed
            $error = "Registration failed. Please try again.";
        }

   
        $stmt->close();
    }


    $check_email_stmt->close();
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        /* General reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Background slideshow */
        body {
            background: url('BG1.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            animation: fadeSlideshow 50s infinite;
    
        }

        /* Slideshow keyframes */
        @keyframes fadeSlideshow {
            0% { background-image: url('BG1.jpg'); }
            20% { background-image: url('BG2.jpg'); }
            40% { background-image: url('BG3.jpg'); }
            60% { background-image: url('BG4.jpg'); }
            80% { background-image: url('BG5.jpg'); }
            100% { background-image: url('BG1.jpg'); }
        }

        /* Form container styling */
        .form-container {
            background-color: rgba(255, 255, 255, 0.85);
            width: 400px;
            padding: 20px;
            border-radius: 8px;
            margin: 100px auto;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        /* Back button styling */
        .back-button {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .back-button:hover {
            background-color: #218838;
        }

        /* Error message styling */
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Back button -->
    <a href="login.php" class="back-button">Back to Login</a>

    <div class="form-container">
        <h2>Register</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="fname">First Name:</label>
            <input type="text" id="fname" name="fname" required><br>
            
            <label for="lname">Last Name:</label>
            <input type="text" id="lname" name="lname" required><br>
            
            <label for="bdate">Birth Date:</label>
            <input type="date" id="bdate" name="bdate" required><br>
            
            <label for="contact_num">Contact Number:</label>
            <input type="text" id="contact_num" name="contact_num" required><br>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
            
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
