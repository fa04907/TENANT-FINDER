<?php
session_start();
include 'database.php'; // Ensure $pdo is defined inside this file

// Ensure only owners can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];

// Handle property withdrawal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_property_id'])) {
    $property_id = intval($_POST['withdraw_property_id']); // Ensure integer input
    
    // Fetch property price and images
    $stmt = $pdo->prepare("SELECT price FROM properties WHERE id = ? AND owner_id = ?");
    $stmt->execute([$property_id, $owner_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($property) {
        $refund_amount = $property['price'] * 0.75; // Calculate 75% refund

        // Delete property from database
        $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ? AND owner_id = ?");
        $stmt->execute([$property_id, $owner_id]);

        echo "<script>alert('✅ Property withdrawn. Refund Amount: ₹$refund_amount'); window.location.href='owner_dashboard.php';</script>";
    } else {
        echo "<script>alert('⚠️ Error: Property not found.'); window.location.href='owner_dashboard.php';</script>";
    }
}

// Fetch properties added by owner
$stmt = $pdo->prepare("SELECT id, location, price, status FROM properties WHERE owner_id = ?");
$stmt->execute([$owner_id]);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Tenant Finder</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background: #003366;
            color: white;
            padding: 15px;
            text-align: center;
        }
        nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 10px;
        }
        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .container {
            width: 90%;
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            min-width: 600px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #003366;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .withdraw-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
        }
        .withdraw-btn:hover {
            background: #cc0000;
        }
        @media (max-width: 768px) {
            table, th, td {
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Owner Dashboard</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="add_property.html">Add Property</a>
            <a href="available_properties.php">Available Properties</a>
            <a href="admin_details.php">Admin Details</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <section class="container">
        <h2>Welcome, Owner</h2>
        <p>Manage your properties and track tenant/customer interest.</p>

        <h3>Your Properties</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Property ID</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="propertyList">
                    <!-- Dynamic Data Will Be Loaded Here -->
                </tbody>
            </table>
        </div>

        <h3>Interested Customers/Tenants</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Type</th>
                        <th>Property ID</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody id="customerList">
                    <!-- Dynamic Data Will Be Loaded Here -->
                </tbody>
            </table>
        </div>

        <h3>Withdraw Property</h3>
        <p>If you withdraw a property listing, you will receive <strong>75%</strong> of the registration fee back.</p>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            loadProperties();
            loadCustomers();

            function loadProperties() {
                fetch('fetch_properties.php')  // Fetch data from PHP
                    .then(response => response.json())
                    .then(data => {
                        let tableBody = document.getElementById("propertyList");
                        tableBody.innerHTML = "";  // Clear previous data
                        data.forEach(property => {
                            let row = document.createElement("tr");
                            row.innerHTML = `
                                <td>${property.id}</td>
                                <td>${property.location}</td>
                                <td>₹${property.price}</td>
                                <td>${property.status}</td>
                                <td>
                                    <button class="withdraw-btn" onclick="withdrawProperty(${property.id})">
                                        Withdraw
                                    </button>
                                </td>
                            `;
                            tableBody.appendChild(row);
                        });
                    });
            }

            function loadCustomers() {
                fetch('fetch_customers.php')  // Fetch data from PHP
                    .then(response => response.json())
                    .then(data => {
                        let tableBody = document.getElementById("customerList");
                        tableBody.innerHTML = "";  // Clear previous data
                        data.forEach(customer => {
                            let row = document.createElement("tr");
                            row.innerHTML = `
                                <td>${customer.name}</td>
                                <td>${customer.type}</td>
                                <td>${customer.property_id}</td>
                                <td>${customer.contact}</td>
                            `;
                            tableBody.appendChild(row);
                        });
                    });
            }

            window.withdrawProperty = function(propertyId) {
                if (confirm(`Are you sure you want to withdraw property ID ${propertyId}? You will receive a 75% refund.`)) {
                    fetch('withdraw_property.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ property_id: propertyId })
                    })
                    .then(response => response.json())
                    .then(result => {
                        alert(result.message);
                        if (result.success) {
                            loadProperties();  // Refresh the list after withdrawal
                        }
                    });
                }
            };
        });
    </script>
</body>
</html>
