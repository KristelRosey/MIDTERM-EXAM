<?php

include 'database4.php';


if (isset($_GET['item_id'])) {
    $itemId = (int)$_GET['item_id'];

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("SELECT ITEM_ID, PRODUCT_NAME, PRICE FROM ITEMS WHERE ITEM_ID = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the item details
    if ($row = $result->fetch_assoc()) {
      
        echo json_encode($row);
        exit;
    } else {
  
        echo json_encode([]);
        exit;
    }
    $stmt->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert') {
    
    $buyerName = $_POST['buyer'];
    $productItemId = $_POST['product_id'];
    $productName = $_POST['product'];
    $quantity = $_POST['quantity'];
    $total = $_POST['total'];
    $transportMode = $_POST['mode'];

    
    $insertStmt = $conn->prepare("INSERT INTO ORDER_TRACKING (BUYER_NAME, PRODUCT_ITEM_ID, PRODUCT_NAME, QUANTITY, TOTAL, TRANSPORT_MODE) VALUES (?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("sissis", $buyerName, $productItemId, $productName, $quantity, $total, $transportMode);

    if ($insertStmt->execute()) {
        // fetch updated ORDER_TRACKING data
        $orderTrackingData = [];
        $orderStmt = $conn->prepare("SELECT ORDER_NUMBER, BUYER_NAME, BUYER_STATUS, PRODUCT_ITEM_ID, PRODUCT_NAME, QUANTITY, TOTAL, TRANSPORT_MODE FROM ORDER_TRACKING");
        $orderStmt->execute();
        $orderResult = $orderStmt->get_result();

        while ($orderRow = $orderResult->fetch_assoc()) {
            $orderTrackingData[] = $orderRow;
        }

        $orderStmt->close();

      
        $response = [
            'success' => true,
            'message' => 'Order submitted successfully.',
            'data' => $orderTrackingData 
        ];
    } else {

        $response = [
            'success' => false,
            'message' => 'Error submitting order.'
        ];
    }


    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Fetch all buyer names for the dropdown
$buyers = [];
$buyerStmt = $conn->prepare("SELECT BUYER_NAME FROM BUYER");
$buyerStmt->execute();
$buyerResult = $buyerStmt->get_result();

while ($buyerRow = $buyerResult->fetch_assoc()) {
    $buyers[] = $buyerRow['BUYER_NAME'];
}

$buyerStmt->close();

// Fetch ORDER_TRACKING data for initial display
$orderTrackingData = [];
$orderStmt = $conn->prepare("SELECT ORDER_NUMBER, BUYER_NAME, BUYER_STATUS, PRODUCT_ITEM_ID, PRODUCT_NAME, QUANTITY, TOTAL, TRANSPORT_MODE FROM ORDER_TRACKING");
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

while ($orderRow = $orderResult->fetch_assoc()) {
    $orderTrackingData[] = $orderRow;
}

$orderStmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshNest Farms & Foods</title>
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

/* Main layout styling with light cream panel */
#container {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 80%;
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
    max-height: 90vh;
    overflow-y: auto;
    background: rgba(250, 245, 235, 0.9); 
    border-radius: 10px;
}

/* Header styling inside panel */
#header {
    display: flex;
    align-items: center;
    width: 100%;
    color: #333;
    margin-bottom: 20px;
}
#header img {
    width: 80px;
    margin-right: 20px;
}
#header h1 {
    font-size: 32px; /* Adjusted title size */
    font-weight: bold;
    color: #333;
}
#header p {
    font-size: 16px;
    color: #666;
}

/* Content layout */
#content {
    display: flex;
    width: 100%;
    gap: 20px;
}

/* Table section on the left */
#table-container {
    flex: 1;
    padding: 10px;
    border: 2px solid #333;
    background: rgba(255, 255, 255, 0.8); 
    border-radius: 5px;
    text-align: center;
    overflow-y: auto;
    max-height: 70vh;
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
}

/* Header styling */
th {
    background-color: #4CAF50;
    color: white;
}

/* Row styling */
tr:nth-child(even) {
    background-color: #f2f2f2;
}

tr:hover {
    background-color: #ddd; 
}

/* Right-side panel for buttons, form, and total */
#right-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
    overflow-y: auto;
    max-height: 70vh;
}

/* Button grid styling */
#button-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    width: 100%;
}
.btn {
    padding: 10px;
    border: none;
    background-color: #4CAF50;
    color: white;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}
.btn:hover {
    background-color: #45a049;
}

/* Form styling */
#form-container {
    width: 100%;
}
label {
    font-weight: bold;
    margin-top: 5px;
    display: block;
}
input[type="text"], select {
    width: 100%;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}
#submit-btn {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    background-color: #4CAF50;
    color: white;
    font-size: 14px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}
#submit-btn:hover {
    background-color: #45a049;
}

/* Total and action buttons styling */
#total {
    font-size: 16px;
    font-weight: bold;
    margin-top: 10px;
}
#edit-btn, #cancel-btn, #clear-btn {
    padding: 8px;
    width: 80px;
    background-color: #4CAF50;
    color: white;
    font-size: 14px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    margin-top: 5px;
    border-radius: 5px;
}
#edit-btn:hover, #cancel-btn:hover, #clear-btn:hover {
    background-color: #45a049;
}


/* Align product price label and value */
.product-price-container {
    display: flex;
    align-items: center; 
    gap: 5px; 
}

/* Style for Check button */
#check-btn {
    padding: 10px;
    border: none;
    background-color: #007BFF;
    color: white;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease;
    margin-left: 10px; 
}
#check-btn:hover {
    background-color: #0056b3; 
}

/* Style for the action buttons */
.edit-button, .cancel-button {
    background-color: #28a745; 
    color: white;              
    border: none;              
    padding: 8px 12px;         
    cursor: pointer;           
    border-radius: 4px;       
    font-size: 14px;           
    margin-right: 5px;         
    transition: background-color 0.3s ease; 
}

/* Hover effect */
.edit-button:hover, .cancel-button:hover {
    background-color: #218838; 
}


    </style>
    <script>
     
        function setProductDetails(itemId) {
            
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "main.php?item_id=" + itemId, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response) {
                        document.getElementById('product-id').value = response.ITEM_ID;
                        document.getElementById('product').value = response.PRODUCT_NAME;
                        document.getElementById('quantity').value = ""; 
                        
                        document.getElementById('product-price').innerText = response.PRICE.toFixed(2); 
                        document.getElementById('total-amount').innerText = (0).toFixed(2); 
                    }
                }
            };
            xhr.send();
        }

        
        function validateForm() {
            
            const quantityInput = document.getElementById('quantity');
            quantityInput.addEventListener('input', function () {
                
                this.value = this.value.replace(/[^0-9]/g, '');

                let quantity = parseInt(this.value);
                if (quantity < 1 || quantity > 99) {
                    this.value = ""; 
                }
            });
        }

        
        function calculateTotal() {
            const quantity = parseInt(document.getElementById('quantity').value) || 0; 
            const price = parseFloat(document.getElementById('product-price').innerText.replace('₱', '').replace(',', '')) || 0; 
            const total = quantity * price;
            document.getElementById('total-amount').innerText = total.toFixed(2);
        }

        
        function clearInputs() {
            document.getElementById('product-id').value = '';
            document.getElementById('product').value = '';
            document.getElementById('quantity').value = '';
            document.getElementById('buyer').value = '';
            document.getElementById('mode').value = '';
            document.getElementById('product-price').innerText = '₱0.00';
            document.getElementById('total-amount').innerText = '0.00'; 
        }

        window.onload = validateForm;
    </script>
</head>
<body>

<?php

include 'database4.php';


if (isset($_GET['item_id'])) {
    $itemId = (int)$_GET['item_id'];

   
    $stmt = $conn->prepare("SELECT ITEM_ID, PRODUCT_NAME, PRICE FROM ITEMS WHERE ITEM_ID = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the item details
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
        exit;
    } else {
        echo json_encode([]);
        exit;
    }
    $stmt->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert') {
    $buyerName = $_POST['buyer'];
    $productItemId = (int)$_POST['product_id'];
    $productName = $_POST['product'];
    $quantity = (int)$_POST['quantity'];
    $total = (float)$_POST['total'];
    $transportMode = $_POST['mode'];

    // Fetch buyer status
    $statusStmt = $conn->prepare("SELECT BUYER_STATUS FROM BUYER WHERE BUYER_NAME = ?");
    $statusStmt->bind_param("s", $buyerName);
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result();
    $buyerStatus = $statusResult->fetch_assoc()['BUYER_STATUS'] ?? 'Unknown';
    $statusStmt->close();

    // Insert data into ORDER_TRACKING
    $insertStmt = $conn->prepare("INSERT INTO ORDER_TRACKING (BUYER_NAME, BUYER_STATUS, PRODUCT_ITEM_ID, PRODUCT_NAME, QUANTITY, TOTAL, TRANSPORT_MODE) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("ssisdss", $buyerName, $buyerStatus, $productItemId, $productName, $quantity, $total, $transportMode);
    
    if ($insertStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order inserted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert order']);
    }
    $insertStmt->close();
    exit;
}

// Fetch all buyer names for the dropdown
$buyers = [];
$buyerStmt = $conn->prepare("SELECT BUYER_NAME FROM BUYER");
$buyerStmt->execute();
$buyerResult = $buyerStmt->get_result();

while ($buyerRow = $buyerResult->fetch_assoc()) {
    $buyers[] = $buyerRow['BUYER_NAME'];
}

$buyerStmt->close();

// Fetch ORDER_TRACKING data
$orderTrackingData = [];
$orderStmt = $conn->prepare("SELECT ORDER_NUMBER, BUYER_NAME, BUYER_STATUS, PRODUCT_ITEM_ID, PRODUCT_NAME, QUANTITY, TOTAL, TRANSPORT_MODE FROM ORDER_TRACKING");
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

while ($orderRow = $orderResult->fetch_assoc()) {
    $orderTrackingData[] = $orderRow;
}

$orderStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshNest Farms & Foods</title>
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

        /* Main layout styling with light cream panel */
        #container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 80%;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            max-height: 90vh;
            overflow-y: auto;
            background: rgba(250, 245, 235, 0.9); 
            border-radius: 10px;
        }

        /* Header styling inside panel */
        #header {
            display: flex;
            align-items: center;
            width: 100%;
            color: #333;
            margin-bottom: 20px;
        }
        #header img {
            width: 80px;
            margin-right: 20px;
        }
        #header h1 {
            font-size: 32px; 
            font-weight: bold;
            color: #333;
        }
        #header p {
            font-size: 16px;
            color: #666;
        }

        /* Content layout */
        #content {
            display: flex;
            width: 100%;
            gap: 20px;
        }

        /* Table section on the left */
        #table-container {
            flex: 1;
            padding: 10px;
            border: 2px solid #333;
            background: rgba(255, 255, 255, 0.8); 
            border-radius: 5px;
            text-align: center;
            overflow-y: auto;
            max-height: 70vh;
        }
        #table-container p {
            font-size: 18px;
            color: #333;
        }

        /* Right-side panel for buttons, form, and total */
        #right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            overflow-y: auto;
            max-height: 70vh;
        }

        /* Button grid styling */
        #button-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            width: 100%;
        }
        .btn {
            padding: 10px;
            border: none;
            background-color: #4CAF50;
            color: white;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #45a049;
        }

        /* Form styling */
        #form-container {
            width: 100%;
        }
        label {
            font-weight: bold;
            margin-top: 5px;
            display: block;
        }
        input[type="text"], select {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        #submit-btn {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            background-color: #4CAF50;
            color: white;
            font-size: 14px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        #submit-btn:hover {
            background-color: #45a049;
        }

        /* Total and action buttons styling */
        #total {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        #edit-btn, #cancel-btn, #clear-btn {
            padding: 8px;
            width: 80px;
            background-color: #4CAF50;
            color: white;
            font-size: 14px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            margin-top: 5px;
            border-radius: 5px;
        }
        #edit-btn:hover, #cancel-btn:hover, #clear-btn:hover {
            background-color: #45a049;
        }
        
        /* Align product price label and value */
        .product-price-container {
            display: flex;
            align-items: center;
            gap: 5px; 
        }

        /* Style for Check button */
        #check-btn {
            padding: 10px;
            border: none;
            background-color: #007BFF; 
            color: white;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin-left: 10px; 
        }
        #check-btn:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        // Function to set product details based on button clicked
        function setProductDetails(itemId) {
           
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "main.php?item_id=" + itemId, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response) {
                        document.getElementById('product-id').value = response.ITEM_ID;
                        document.getElementById('product').value = response.PRODUCT_NAME;
                        document.getElementById('quantity').value = "";
                        document.getElementById('product-price').innerText = response.PRICE.toFixed(2);
                        document.getElementById('total-amount').innerText = (0).toFixed(2);
                    }
                }
            };
            xhr.send();
        }

        function validateForm() {
            const quantityInput = document.getElementById('quantity');
            quantityInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
                let quantity = parseInt(this.value);
                if (quantity < 1 || quantity > 99) {
                    this.value = "";
                }
            });
        }

        function calculateTotal() {
            const quantity = parseInt(document.getElementById('quantity').value) || 0;
            const price = parseFloat(document.getElementById('product-price').innerText.replace('₱', '').replace(',', '')) || 0;
            const total = quantity * price;
            document.getElementById('total-amount').innerText = total.toFixed(2);
        }

        function clearInputs() {
            document.getElementById('product-id').value = '';
            document.getElementById('product').value = '';
            document.getElementById('quantity').value = '';
            document.getElementById('buyer').value = '';
            document.getElementById('mode').value = '';
            document.getElementById('product-price').innerText = '₱0.00';
            document.getElementById('total-amount').innerText = '0.00';
        }

        function submitForm() {
    const buyer = document.getElementById('buyer').value;
    const productId = document.getElementById('product-id').value;
    const product = document.getElementById('product').value;
    const quantity = document.getElementById('quantity').value;
    const total = document.getElementById('total-amount').innerText;
    const mode = document.getElementById('mode').value;

    if (!buyer || !productId || !product || !quantity || !mode) {
        alert('Please fill in all fields');
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "main.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            alert(response.message);

            if (response.success) {
                updateOrderTable(response.data); 
                clearInputs(); 
            }
        }
    };
    xhr.send(`action=insert&buyer=${encodeURIComponent(buyer)}&product_id=${productId}&product=${encodeURIComponent(product)}&quantity=${quantity}&total=${total}&mode=${mode}`);
}

// Function to update the order table
function updateOrderTable(orderTrackingData) {
    const tableBody = document.getElementById('orderTableBody'); 
    tableBody.innerHTML = '';

    // Populate table with new data
    orderTrackingData.forEach(order => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${order.ORDER_NUMBER}</td>
            <td>${order.BUYER_NAME}</td>
            <td>${order.BUYER_STATUS}</td>
            <td>${order.PRODUCT_ITEM_ID}</td>
            <td>${order.PRODUCT_NAME}</td>
            <td>${order.QUANTITY}</td>
            <td>${parseFloat(order.TOTAL).toFixed(2)}</td>
            <td>${order.TRANSPORT_MODE}</td>
        `;
        tableBody.appendChild(row);
    });
}




        window.onload = validateForm;
    </script>
</head>
<body>

    <div id="container">
        <div id="header">
            <img src="FN_LOGO.png" alt="Logo">
            <div>
                <h1>FreshNest Farms & Foods</h1>
                <p>Freshness in Every Bite</p>
            </div>
        </div>

        <div id="content">
    <!-- Table section -->
    <div id="table-container">
        <p>Order Tracking</p>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                 <tr>
                        <th>Order Number</th>
                        <th>Buyer Name</th>
                        <th>Buyer Status</th>
                        <th>Product Item ID</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Transport Mode</th>
                    </tr>
                    <?php foreach ($orderTrackingData as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['ORDER_NUMBER']); ?></td>
                            <td><?php echo htmlspecialchars($order['BUYER_NAME']); ?></td>
                            <td><?php echo htmlspecialchars($order['BUYER_STATUS']); ?></td>
                            <td><?php echo htmlspecialchars($order['PRODUCT_ITEM_ID']); ?></td>
                            <td><?php echo htmlspecialchars($order['PRODUCT_NAME']); ?></td>
                            <td><?php echo htmlspecialchars($order['QUANTITY']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($order['TOTAL'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($order['TRANSPORT_MODE']); ?></td>
                        </tr>
                    <?php endforeach; ?>

                </table>
            </div>

            <!-- Right-side panel -->
            <div id="right-panel">
               <!-- Button grid for products -->
                <div id="button-grid">
                    <button class="btn" onclick="setProductDetails(1)">FN FR Eggs</button>
                    <button class="btn" onclick="setProductDetails(2)">FN ORG Longganisa</button>
                    <button class="btn" onclick="setProductDetails(3)">FN Whole Chicken</button>
                    <button class="btn" onclick="setProductDetails(4)">FN Marinated Chicken</button>
                    <button class="btn" onclick="setProductDetails(5)">FN Chicken Nuggets</button>
                    <button class="btn" onclick="setProductDetails(6)">FN Jumbo Hotdog</button>
                    <button class="btn" onclick="setProductDetails(7)">FN Beef Tapa Meat</button>
                    <button class="btn" onclick="setProductDetails(8)">FN Tocino</button> 
                </div>

                <!-- Form section for user input -->
                <div id="form-container">
                    <label for="product-id">PRODUCT ID</label>
                    <input type="text" id="product-id" name="product-id" readonly>
                    
                    <label for="product">PRODUCT</label>
                    <input type="text" id="product" name="product" readonly>
                    
                    <label for="quantity">QUANTITY</label>
                    <div style="display: flex; align-items: center;">
                        <input type="text" id="quantity" name="quantity" pattern="\d*" maxlength="2" required>
                        <button id="check-btn" onclick="calculateTotal()">Check</button>
                    </div>

                    <label for="buyer">BUYER</label>
                    <select id="buyer" name="buyer" required>
                        <option value="">Select BUYER</option>
                        <?php foreach ($buyers as $buyerName): ?>
                            <option value="<?php echo htmlspecialchars($buyerName); ?>"><?php echo htmlspecialchars($buyerName); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="mode">MODE</label>
                    <select id="mode" name="mode" required>
                        <option value="">Select MODE</option>
                        <option value="PICK-UP">PICK-UP</option>
                        <option value="DELIVERY">DELIVERY</option>
                    </select>
                    
                    <div class="product-price-container">
                        <label for="product-price">PRODUCT PRICE</label>
                        <div id="product-price">₱0.00</div>
                    </div>
                    
                    <div id="total">TOTAL AMOUNT: ₱<span id="total-amount">0.00</span></div>
                    <button id="submit-btn" onclick="submitForm()">SUBMIT</button> 
                    
                </div>

                <!-- Edit, Cancel, and Clear buttons -->
                <div id="clear">
                <button id="edit-btn" onclick="window.location.href='editdelete.php'">EDIT</button>
                    <button id="clear-btn" onclick="clearInputs()">CLEAR</button> 
                </div>
            </div>
        </div>
    </div>
</body>
</html>
