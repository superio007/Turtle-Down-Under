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

function searchAvailability($sessions, $productType) {
    $availableSessions = array();

    foreach ($sessions as $session) {
        if (isset($session['productType']) && $session['productType'] === $productType) {
            $availableSessions[] = $session;
        }
    }

    return $availableSessions;
}

// Filter products by productType
$availableSessions = searchAvailability($product['products'], $productType);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rezdy API - Product Results</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <nav class="bg-white shadow-md py-4">
        <div class="container mx-auto flex items-center justify-between">
            <img src="images/logo_tdu.png" class="h-12" alt="Logo">
            <ul class="flex space-x-4">
                <li><a href="#" class="text-gray-700 hover:text-gray-900">Popular places</a></li>
                <li><a href="#" class="text-gray-700 hover:text-gray-900">Travel Outside</a></li>
                <li><a href="#" class="text-gray-700 hover:text-gray-900">Online Packages</a></li>
            </ul>
            <a href="#" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Register Now</a>
        </div>
    </nav>

    <div class="container mx-auto mt-5">
        <h1 class="text-center text-4xl font-bold mb-5">SELECT A JOURNEY</h1>
        <?php if (!empty($availableSessions)): ?>
        <div class="flex flex-wrap -mx-4">
            <?php foreach($availableSessions as $session): ?>
            <div class="w-full md:w-1/3 px-4 mb-4">
                <div class="bg-white rounded-lg overflow-hidden shadow-lg flex flex-col h-full">
                    <div class="relative">
                        <?php if (!empty($session['images'][0]['thumbnailUrl'])): ?>
                        <img src="<?php echo htmlspecialchars($session['images'][0]['thumbnailUrl']); ?>"
                            class="w-full h-48 object-cover" alt="<?php echo htmlspecialchars($session['name']); ?>">
                        <?php endif; ?>
                        <div class="absolute bottom-0 w-full bg-black bg-opacity-50 text-white p-3">
                            <h5 class="text-center"><?php echo htmlspecialchars($session['name']); ?></h5>
                        </div>
                    </div>
                    <div class="p-4 flex flex-col flex-grow">
                        <p class="mb-2"><?php echo htmlspecialchars($session['shortDescription']); ?></p>
                        <p class="mb-2"><strong>Product Code:</strong>
                            <?php echo htmlspecialchars($session['productCode']); ?></p>
                        <p class="bg-green-500 text-white p-2 rounded-full mt-2 mb-2 inline-block"><strong>Advertised Price:</strong>
                            $<?php echo htmlspecialchars($session['advertisedPrice']); ?>
                            <?php echo htmlspecialchars($session['currency']); ?></p>
                        <p class="mb-2"><strong>Price Options:</strong></p>
                        <ul class="mb-4">
                            <?php foreach ($session['priceOptions'] as $priceOption): ?>
                            <li><?php echo htmlspecialchars($priceOption['label']); ?>:
                                $<?php echo htmlspecialchars($priceOption['price']); ?></li>
                            <?php break; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="mt-auto flex justify-center py-3">
                            <button type="button"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded view-details-btn"
                                data-product-code="<?php echo htmlspecialchars($session['productCode']); ?>">View
                                Details</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <h2 class="text-center text-2xl">Packages are not available</h2>
        <?php endif; ?>
    </div>

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
