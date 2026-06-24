<?php
session_start();
include_once 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<script>alert('Please log in first!'); window.location.href='login.html';</script>");
}

// General-purpose file upload function
function uploadFile($fileInput, $uploadDir, $allowMultiple = true) {
    $uploadedPaths = [];

    if (!isset($_FILES[$fileInput])) return json_encode($uploadedPaths);

    $files = $allowMultiple ? $_FILES[$fileInput]['name'] : [$_FILES[$fileInput]['name']];
    $tmpNames = $allowMultiple ? $_FILES[$fileInput]['tmp_name'] : [$_FILES[$fileInput]['tmp_name']];
    $errors = $allowMultiple ? $_FILES[$fileInput]['error'] : [$_FILES[$fileInput]['error']];

    foreach ($files as $index => $fileName) {
        $fileTmpPath = $tmpNames[$index];
        $fileError = $errors[$index];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileError === UPLOAD_ERR_OK) {
            $allowedTypes = ["jpg", "jpeg", "png", "webp", "pdf"];
            if (in_array($fileType, $allowedTypes)) {
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = time() . "_" . uniqid() . ".$fileType";
                $relativePath = "$uploadDir$newFileName";
                $absolutePath = $_SERVER['DOCUMENT_ROOT'] . "/house/" . $relativePath;

                if (move_uploaded_file($fileTmpPath, $absolutePath)) {
                    $uploadedPaths[] = $relativePath;
                } else {
                    echo "❌ Failed to upload: $fileName<br>";
                }
            } else {
                echo "❌ Invalid file type: $fileName<br>";
            }
        } else {
            echo "❌ Error uploading: $fileName (Error Code: $fileError)<br>";
        }
    }

    return json_encode($uploadedPaths, JSON_UNESCAPED_SLASHES);
}

// Handle property addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id = $_SESSION['user_id'];
    $listingType = $_POST['listingType'];
    $propertyType = $_POST['propertyType'];
    $title = $_POST['title'] ?? 'Untitled';
    $description = $_POST['description'] ?? 'No description';
    $textLocation = $_POST['textLocation'];
    $googleMapsUrl = $_POST['googleMapsUrl'];
    $infrastructure = $_POST['infrastructure'];
    $price = $_POST['price'];

    $timestamp = time();
    $uploadDir = "uploads/properties/$timestamp/"; 
    $receiptDir = "uploads/receipts/";

    // Upload individual room-wise images if applicable
    $roomFields = ['bedroom1', 'bedroom2', 'bedroom3', 'hall', 'kitchen', 'balcony', 'washroom', 'front_view', 'wide_view', 'survey_image'];
    $roomImages = [];
    foreach ($roomFields as $room) {
        $roomImages[$room] = json_decode(uploadFile($room, $uploadDir), true);
    }

    // Upload general images (if using propertyImages[])
    $allImages = json_decode(uploadFile('propertyImages', $uploadDir), true);

    // Merge both sets
    $mergedImages = array_merge($allImages ?? [], ...array_values($roomImages));
    $imagesJson = json_encode($mergedImages, JSON_UNESCAPED_SLASHES);

    // Upload payment receipt
    $paymentReceiptPath = json_decode(uploadFile('paymentReceipt', $receiptDir, false), true);

    // Upload tax slip
    $taxSlipPath = json_decode(uploadFile('taxSlip', $receiptDir, false), true);

    // Check if payment receipt was uploaded successfully
    if (empty($paymentReceiptPath)) {
        echo "❌ No payment receipt uploaded or invalid file.";
        exit;
    }

    try {
        $table = ($listingType === 'Rent') ? 'rent_properties' : 'properties';
        $query = "INSERT INTO $table (
            owner_id, type, title, description, location, textLocation, googleMapsUrl,
            infrastructure, price, images, paymentReceipt, tax_slip
        ) VALUES (
            :owner_id, :type, :title, :description, :location, :textLocation, :googleMapsUrl,
            :infrastructure, :price, :images, :paymentReceipt, :tax_slip
        )";

        // Convert the paymentReceipt to a JSON array
        $paymentReceiptJson = json_encode($paymentReceiptPath);

        // If tax slip is not uploaded, set it as null or an empty array
        $taxSlipJson = !empty($taxSlipPath) ? json_encode($taxSlipPath) : null;

        // Execute the query with the JSON-encoded value for paymentReceipt
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':owner_id' => $owner_id,
            ':type' => $propertyType,
            ':title' => $title,
            ':description' => $description,
            ':location' => $textLocation,
            ':textLocation' => $textLocation,
            ':googleMapsUrl' => $googleMapsUrl,
            ':infrastructure' => $infrastructure,
            ':price' => $price,
            ':images' => $imagesJson,
            ':paymentReceipt' => $paymentReceiptJson, // Use JSON-encoded paymentReceipt
            ':tax_slip' => $taxSlipJson // Use JSON-encoded tax slip (null if not uploaded)
        ]);

        echo "<script>alert('✅ Property added successfully!'); window.location.href='owner_dashboard.html';</script>";
    } catch (PDOException $e) {
        echo "❌ Error adding property: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Property</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: url('image.jpg') no-repeat center center fixed;
      background-size: cover;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .form-container {
      background: rgba(255, 255, 255, 0.9);
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
      width: 480px;
    }

    h2 {
      text-align: center;
      color: #333;
    }

    label {
      font-weight: bold;
      display: block;
      margin-top: 10px;
    }

    select, input, textarea {
      padding: 10px;
      margin-top: 5px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      width: 100%;
      box-sizing: border-box;
    }

    button {
      background-color: #28a745;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      width: 100%;
    }

    button:hover {
      background-color: #218838;
    }

    img {
      display: block;
      margin: 10px auto;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    p {
      font-size: 14px;
    }
  </style>
  <script>
    function showFields() {
      const propertyType = document.getElementById("propertyType").value;
      const fieldsContainer = document.getElementById("imageFields");
      fieldsContainer.innerHTML = "";

      const requiredImages = {
        "3BHK": ["Bedroom 1", "Bedroom 2", "Bedroom 3", "Hall", "Kitchen", "Balcony", "Washroom"],
        "2BHK": ["Bedroom 1", "Bedroom 2", "Hall", "Kitchen", "Washroom"],
        "House": ["Front View", "Hall", "Kitchen", "Bathroom", "Balcony"],
        "Land": ["Wide View", "Land Survey Image"],
        "Agriculture": ["Wide View", "Land Survey Image"]
      };

      if (requiredImages[propertyType]) {
        requiredImages[propertyType].forEach((label, index) => {
          const inputName = `images[${propertyType}_${label.replace(/\s+/g, '_')}]`;
          fieldsContainer.innerHTML += `\
            <label>${label}:</label>\
            <input type="file" name="${inputName}" accept="image/*" required>\
          `;
        });
      }
    }
  </script>
</head>
<body>
  <div class="form-container">
    <h2>Add Property</h2>
    <form action="add_property.php" method="POST" enctype="multipart/form-data">

      <label for="listingType">Listing Type:</label>
      <select name="listingType" id="listingType" required>
        <option value="">Select Listing Type</option>
        <option value="Rent">Rent</option>
        <option value="Sell">Sell</option>
      </select>

      <label for="propertyType">Property Type:</label>
      <select name="propertyType" id="propertyType" onchange="showFields()" required>
        <option value="">Select Property Type</option>
        <option value="3BHK">3BHK</option>
        <option value="2BHK">2BHK</option>
        <option value="House">House</option>
        <option value="Land">Land</option>
        <option value="Agriculture">Agriculture</option>
      </select>

      <label for="title">Property Title:</label>
      <input type="text" name="title" id="title" placeholder="Enter Property Title" required>

      <label for="description">Property Description:</label>
      <textarea name="description" id="description" placeholder="Enter detailed description" required></textarea>

      <label for="textLocation">Location:</label>
      <input type="text" name="textLocation" id="textLocation" required>

      <label for="googleMapsUrl">Google Maps URL:</label>
      <input type="text" name="googleMapsUrl" id="googleMapsUrl" required>

      <label for="infrastructure">Infrastructure:</label>
      <textarea name="infrastructure" id="infrastructure" required></textarea>

      <label for="price">Price:</label>
      <input type="text" name="price" id="price" required>

      <div id="imageFields"></div>
      <label>Tax Payment Slip:</label>
      <input type="file" name="taxSlip" accept="image/*" required>
      <h3 style="color: #d9534f;">Registration Fee: ₹1000</h3>

      <h4>1️⃣ Pay via QR Code:</h4>
      <p>Scan the QR Code below to make the payment:</p>
      <img src="qr_code.jpg" alt="QR Code for Payment" width="200">

      <h4>2️⃣ Pay via Bank Transfer:</h4>
      <p><strong>Bank Name:</strong> HDFC Bank</p>
      <p><strong>Account Number:</strong> 123456789012</p>
      <p><strong>IFSC Code:</strong> HDFC0001234</p>
      <p><strong>Account Holder:</strong> Real Estate Pvt Ltd</p>

      <label for="paymentReceipt">Upload Payment Receipt:</label>
      <input type="file" name="paymentReceipt" id="paymentReceipt" accept=".pdf, .jpg, .png" required>

      <p style="color: #d9534f;"><strong>Note:</strong> If you withdraw the property without selling, 75% of the amount will be refunded.</p>

      <button type="submit">Submit Property</button>
    </form>
  </div>

  <div class="property-list">
    <?php
      // Display active properties
      $stmt = $pdo->prepare("SELECT * FROM properties WHERE status = 'active'");
      $stmt->execute();

      echo '<div class="property-grid">';
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          echo "<div class='property'>";
          if (!empty($row['images'])) {
              $images = json_decode($row['images'], true); // Decode the JSON string into an array
              foreach ($images as $img) {
                  echo "<img src='/house/" . htmlspecialchars($img) . "' alt='Property Image' style='max-width: 300px; margin: 10px;'>";
              }
          } else {
              echo "No images uploaded for this property.";
          }
          echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
          echo "<p><strong>Location:</strong> " . htmlspecialchars($row['location']) . "</p>";
          echo "<p><strong>Price:</strong> ₹" . htmlspecialchars($row['price']) . "</p>";
          echo "<a href='view_property.php?id=" . $row['id'] . "'>View Details</a>";
          echo "</div>";
      }
      echo '</div>';
    ?>
  </div>
</body>
</html>
