<?php

include 'database4.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    $username = $_POST['username'];
    $password = $_POST['password'];
    
   
    $stmt = $conn->prepare("SELECT * FROM CUSTOMER_INFO WHERE USER = ? AND PASS = ?");
    $stmt->bind_param("ss", $username, $password);
    

    $stmt->execute();
    $result = $stmt->get_result();
    
  
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
      
        $_SESSION['customer_id'] = $row['CUSTOMER_ID'];
        $_SESSION['fname'] = $row['FNAME'];
        $_SESSION['lname'] = $row['LNAME'];
        
       
        header("Location: main.php");
        exit();
    } else {
      
        $error = "Invalid username or password.";
    }
    
   
    $stmt->close();
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            overflow: hidden; /* Remove scroll */
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

        /* Error message styling */
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }

        /* Register link styling */
        .register-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
        }

        .register-link:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            
            <button type="submit">Login</button>
        </form>
        <a href="register.php" class="register-link">Don't have an account? Register here</a>
    </div>
</body>
</html>
