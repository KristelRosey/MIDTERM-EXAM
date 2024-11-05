<?php

include 'database4.php';

session_start(); 


if (!isset($_SESSION['fname']) || !isset($_SESSION['lname'])) {
    header("Location: login.php");
    exit();
}


$loggedInUser = $_SESSION['fname'] . " " . $_SESSION['lname'];


$lastEditMessage = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $currentDateTime = date('Y-m-d H:i:s'); 

    if ($_POST['action'] === 'edit') {
        $order_number = $_POST['order_number'];
        
        // Fetch the order details
        $sql = "SELECT * FROM ORDER_TRACKING WHERE ORDER_NUMBER = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_number);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the order exists
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            echo "Order not found.";
            exit;
        }
    } elseif ($_POST['action'] === 'update') {
        // Handle the update process
        $order_number = $_POST['order_number'];
        $product_item_id = $_POST['product_item_id'];
        $product_name = $_POST['product_name'];
        $quantity = $_POST['quantity'];
        $total = $_POST['total'];
        $transport_mode = $_POST['transport_mode'];

        // Prepare an update query
        $sql = "UPDATE ORDER_TRACKING SET 
                    PRODUCT_ITEM_ID = ?, 
                    PRODUCT_NAME = ?, 
                    QUANTITY = ?, 
                    TOTAL = ?, 
                    TRANSPORT_MODE = ? 
                WHERE ORDER_NUMBER = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isddsi", $product_item_id, $product_name, $quantity, $total, $transport_mode, $order_number);
        
        // Execute the query
        if ($stmt->execute()) {
            echo "Order updated successfully.";
            $lastEditMessage = "Last edited by: $loggedInUser on $currentDateTime";
        } else {
            echo "Error updating order: " . $stmt->error;
        }
    } elseif ($_POST['action'] === 'delete') {
        // Handle the delete process
        $order_number = $_POST['order_number'];

        // Prepare a delete query
        $sql = "DELETE FROM ORDER_TRACKING WHERE ORDER_NUMBER = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_number);
        
        // Execute the query
        if ($stmt->execute()) {
            echo "Order deleted successfully.";
            $lastEditMessage = "Last edited by: $loggedInUser on $currentDateTime";
        } else {
            echo "Error deleting order: " . $stmt->error;
        }
    }
}

// Fetch all orders for display
$sql = "SELECT * FROM ORDER_TRACKING";
$result = $conn->query($sql);

// Fetch item prices for the selected product
$prices = [];
$item_sql = "SELECT PRODUCT_NAME, PRICE FROM ITEMS"; 
$item_result = $conn->query($item_sql);
while ($item_row = $item_result->fetch_assoc()) {
    $prices[$item_row['PRODUCT_NAME']] = $item_row['PRICE'];
}


$product_names = [
    "FN FR Eggs",
    "FN ORG Longganisa",
    "FN Whole Chicken",
    "FN Marinated Chicken Breast",
    "FN Chicken Nuggets",
    "FN Jumbo Hotdog",
    "FN Beef Tapa Meat",
    "FN Tocino"
];


$transport_modes = [
    "Delivery",
    "Pick Up"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking</title>
    <link rel="stylesheet" href="path/to/your/styles.css">
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
            overflow: hidden;
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

        .container {
            display: flex;
            justify-content: center; 
            align-items: flex-start; 
            padding: 20px; 
        }
        .table-container {
            flex: 1;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px; 
            padding: 20px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
            max-height: 600px; 
            overflow-y: auto; 
        }
        .edit-form {
            margin-left: 20px;
            display: flex;
            flex-direction: column;
            max-width: 300px; 
        }
        .edit-form label, .edit-form input, .edit-form select {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px; 
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #218838; 
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }

        .btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    background-color: #28a745; 
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 4px; 
    transition: background-color 0.3s;
    text-decoration: none; 
}

.btn:hover {
    background-color: #218838;
}

a.btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 10px 15px;
}
.btn-lower-right {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #28a745; 
    color: white;
    padding: 10px 15px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: bold;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); 
    transition: background-color 0.3s;
}

.btn-lower-right:hover {
    background-color: #218838; 
}

.footer-message {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #FFFFFF;
}



.logout-button {
            position: absolute;
            top: 5px;
            right: 10px;
            padding: 10px 15px;
            background-color: #f44336; 
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-button:hover {
            background-color: #d32f2f;
        }

    </style>
    <script>
        const prices = <?php echo json_encode($prices); ?>; 
        
        function updateTotal() {
            const productName = document.querySelector('select[name="product_name"]').value;
            const quantity = document.querySelector('input[name="quantity"]').value;
            const totalInput = document.querySelector('input[name="total"]');
            
            if (productName && quantity) {
                const price = prices[productName];
                const total = price * quantity;
                totalInput.value = total.toFixed(2); 
            }
        }

        function confirmDelete() {
            return confirm("Are you sure you want to cancel this order?");
        }

        window.onload = function() {
            const quantityInput = document.querySelector('input[name="quantity"]');
            quantityInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Prevent form submission
                    updateTotal();
                }
            });
        };
    </script>
</head>
<body>

<div class="container">
    <div class="table-container">
        <h1>Order Tracking</h1>

        <?php if (isset($row)): ?>
            <h2>Edit Order</h2>
            <form action="editdelete.php" method="post" class="edit-form">
                <input type="hidden" name="order_number" value="<?php echo $row['ORDER_NUMBER']; ?>">
                
                <label for="buyer_name">Buyer Name:</label>
                <input type="text" name="buyer_name" value="<?php echo $row['BUYER_NAME']; ?>" readonly><br>

                <label for="product_item_id">Product Item ID:</label>
                <input type="text" name="product_item_id" value="<?php echo $row['PRODUCT_ITEM_ID']; ?>"><br>

                <label for="product_name">Product Name:</label>
                <select name="product_name" required onchange="updateTotal()">
                    <?php foreach ($product_names as $product): ?>
                        <option value="<?php echo $product; ?>" <?php echo ($row['PRODUCT_NAME'] === $product) ? 'selected' : ''; ?>><?php echo $product; ?></option>
                    <?php endforeach; ?>
                </select><br>

                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" value="<?php echo $row['QUANTITY']; ?>" min="1" max="99" required><br>

                <label for="total">Total:</label>
                <input type="text" name="total" value="<?php echo $row['TOTAL']; ?>" readonly><br>

                <label for="transport_mode">Transport Mode:</label>
                <select name="transport_mode" required>
                    <?php foreach ($transport_modes as $mode): ?>
                        <option value="<?php echo $mode; ?>" <?php echo ($row['TRANSPORT_MODE'] === $mode) ? 'selected' : ''; ?>><?php echo $mode; ?></option>
                    <?php endforeach; ?>
                </select><br>

                <input type="hidden" name="action" value="update">
                <input type="submit" value="Update" class="btn">
                <a href="editdelete.php" class="btn">Cancel</a> 
            </form>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Buyer Name</th>
                    <th>Product Item ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Transport Mode</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['ORDER_NUMBER']; ?></td>
                        <td><?php echo $row['BUYER_NAME']; ?></td>
                        <td><?php echo $row['PRODUCT_ITEM_ID']; ?></td>
                        <td><?php echo $row['PRODUCT_NAME']; ?></td>
                        <td><?php echo $row['QUANTITY']; ?></td>
                        <td><?php echo $row['TOTAL']; ?></td>
                        <td><?php echo $row['TRANSPORT_MODE']; ?></td>
                        <td>
                            <form action="editdelete.php" method="post" style="display:inline;">
                                <input type="hidden" name="order_number" value="<?php echo $row['ORDER_NUMBER']; ?>">
                                <input type="hidden" name="action" value="edit">
                                <input type="submit" value="Edit" class="btn btn-edit">
                            </form>
                            <form action="editdelete.php" method="post" style="display:inline;" onsubmit="return confirmDelete();">
                                <input type="hidden" name="order_number" value="<?php echo $row['ORDER_NUMBER']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="submit" value="Delete" class="btn btn-delete">
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($lastEditMessage): ?>
        <div class="footer-message">
            <?php echo $lastEditMessage; ?>
        </div>
    <?php endif; ?>
</div>

<a href="main.php" class="btn btn-lower-right">Go to Main</a>


<a href="logout.php" class="logout-button">Logout</a>

<div class="container">
 
</div>

</body>
</html>







