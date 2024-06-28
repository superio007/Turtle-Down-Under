<?php

function getRezdyProducts($apiKey) {
    $url = "https://api.rezdy-staging.com/v1/products/marketplace?apiKey=$apiKey";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if ($response === false) {
        die("Error: Curl request failed: " . curl_error($ch));
    }
    curl_close($ch);

    $data = json_decode($response, true);
    
    if ($data === null) {
        die("Error: Failed to decode JSON response");
    }
    return $data;
}

$apiKey = "81c3566e60ef42e6afa1c2719e7843fd";

$productType = $_GET['productType'] ?? '';

// Check if productType is provided
if (empty($productType)) {
    die("Error: Product type must be provided.");
}

$product = getRezdyProducts($apiKey);

// Filter products by productType
$availableSessions = array_filter($product['products'], function($session) use ($productType) {
    return isset($session['productType']) && $session['productType'] === $productType;
});

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rezdy API - Product Results</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include "header.php";?>

    <div class="container mt-5">
        <h1 class="text-center text-primary mb-5" style="margin-top: 7rem;">SELECT A JOURNEY</h1>
        <?php if (!empty($availableSessions)): ?>
        <div class="row">
            <?php foreach($availableSessions as $session): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <?php if (!empty($session['images'][0]['thumbnailUrl'])): ?>
                    <img src="<?php echo htmlspecialchars($session['images'][0]['thumbnailUrl']); ?>"
                        class="card-img-top" alt="<?php echo htmlspecialchars($session['name']); ?>">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($session['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($session['shortDescription']); ?></p>
                        <p class="card-text"><strong>Product Code:</strong> <?php echo htmlspecialchars($session['productCode']); ?></p>
                        <p class="card-text bg-success text-white p-2 rounded">
                            <strong>Advertised Price:</strong>
                            $<?php echo isset($session['advertisedPrice']) ? htmlspecialchars($session['advertisedPrice']) : 'N/A'; ?>
                            <?php echo isset($session['currency']) ? htmlspecialchars($session['currency']) : ''; ?>
                        </p>
                        <p class="card-text"><strong>Price Options:</strong></p>
                        <ul class="list-unstyled">
                            <?php foreach ($session['priceOptions'] as $priceOption): ?>
                            <li><?php echo htmlspecialchars($priceOption['label']); ?>: $<?php echo htmlspecialchars($priceOption['price']); ?></li>
                            <?php break; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="mt-auto">
                            <button type="button" class="btn btn-primary view-details-btn" data-product-code="<?php echo htmlspecialchars($session['productCode']); ?>">View Details</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <h2 class="text-center text-danger">Packages are not available</h2>
        <?php endif; ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".view-details-btn").forEach(button => {
            button.addEventListener("click", function() {
                const productCode = this.getAttribute("data-product-code");
                window.location.href = `productDetails.php?productCode=${productCode}`;
            });
        });
    });
    </script>
</body>

</html>
