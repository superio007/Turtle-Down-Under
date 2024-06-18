<?php
    session_start();
    date_default_timezone_set('UTC');

    function getRezdyProductDetails($apiKey, $productCode) {
        $url = "https://api.rezdy-staging.com/v1/products/$productCode?apiKey=" . urlencode($apiKey);
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

    function getRezdyAvailability($apiKey, $productCode, $startTimeLocal, $endTimeLocal) {
        $url = "https://api.rezdy-staging.com/v1/availability?apiKey=" . urlencode($apiKey) . "&productCode=" . urlencode($productCode) . "&startTimeLocal=" . urlencode($startTimeLocal) . "&endTimeLocal=" . urlencode($endTimeLocal);
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

    function getCurrentLocalDateTime() {
        $date = new DateTime();
        $formattedDate = $date->format('Y-m-d H:i:s');
        return $formattedDate;
    }

    $apiKey = "81c3566e60ef42e6afa1c2719e7843fd";
    $productCode = $_GET['productCode'] ?? '';
    if (empty($productCode)) {
        die("Error: Product code must be provided.");
    }

    $productDetails = getRezdyProductDetails($apiKey, $productCode);
    $startTimeLocal = getCurrentLocalDateTime();
    $endTimeLocal = (new DateTime())->modify('+1 month')->format('Y-m-d H:i:s');
    $availability = getRezdyAvailability($apiKey, $productCode, $startTimeLocal, $endTimeLocal);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Handle form submission
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rezdy API - Product Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<style>
    #payee{
        border: 1px solid #0000001f;
        padding: 8px 15px;
        width: fit-content;
    }
</style>
<body class="bg-gray-100">
    <?php require "header.php";?>
    <div class="container mx-auto mt-5 p-4 bg-white rounded-lg shadow-lg">
        <form action="" method="post">
            <h1 id="product_name" class="text-4xl font-bold mb-4"><?php echo htmlspecialchars($productDetails['product']['name'] ?? ''); ?></h1>
            <p id="product_description" class="mb-4"><?php echo htmlspecialchars($productDetails['product']['shortDescription'] ?? ''); ?></p>
            <input type="text" value="<?php echo htmlspecialchars($productDetails['product']['images'][0]['itemUrl']); ?>" id="imgurl" hidden>
            <?php if (isset($productDetails['product']['images'][0]['itemUrl'])): ?>
                <img src="<?php echo htmlspecialchars($productDetails['product']['images'][0]['itemUrl']); ?>" alt="Product Image" class="w-full h-auto mb-4">
            <?php endif; ?>
            <input type="text" id="amount" value="<?php echo htmlspecialchars($productDetails['product']['priceOptions'][0]['price'] ?? ''); ?>" class="block w-full p-2 mb-4 border border-gray-300 rounded">
            <div class="form-group mb-4">
                <p class="text-left mb-2 font-bold">Select passengers</p>
                <div class="custom-dropdown relative">
                    <div class="flex items-center justify-between border border-gray-300 p-2 cursor-pointer rounded" id="passengerDropdown">
                        <span id="passengerCount">1</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    <div class="custom-dropdown-menu absolute bg-white shadow-lg rounded mt-2 w-full hidden z-10">
                        <div class="p-4">
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <span>Adults</span>
                                    <div class="text-sm text-gray-600">ages 18 and over</div>
                                </div>
                                <div class="flex items-center">
                                    <button type="button" class="border border-gray-300 px-2 py-1 rounded-l" id="adults-minus">-</button>
                                    <span class="mx-2" id="adults-count">1</span>
                                    <button type="button" class="border border-gray-300 px-2 py-1 rounded-r" id="adults-plus">+</button>
                                </div>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <span>Children</span>
                                    <div class="text-sm text-gray-600">ages 2 - 12</div>
                                </div>
                                <div class="flex items-center">
                                    <button type="button" class="border border-gray-300 px-2 py-1 rounded-l" id="children-minus">-</button>
                                    <span class="mx-2" id="children-count">0</span>
                                    <button type="button" class="border border-gray-300 px-2 py-1 rounded-r" id="children-plus">+</button>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button class="bg-blue-500 text-white px-4 py-2 rounded mt-2" id="passenger-ready" type="button">Ready</button>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="adultsInput" name="adults" value="1">
                <input type="hidden" id="childrenInput" name="children" value="0">
                <input type="hidden" id="infantsInput" name="infants" value="0">
            </div>
            <p class="text-left mb-2 font-bold">Payment option</p>
            <div id="payee" class="mb-3">
                <select required name="paymentType" id="paymentType">
                    <option value="" selected hidden>Select</option>
                    <option value="CASH">CASH</option>
                    <option value="CREDIT CARD">CREDIT CARD</option>
                </select>
            </div>
            <div class="mb-4">
                <h2 class="text-2xl font-bold mb-4">Choose extras</h2>
                <?php if (isset($productDetails['product']['extras']) && is_array($productDetails['product']['extras'])): ?>
                    <?php foreach ($productDetails['product']['extras'] as $extra): ?>
                        <div>
                            <input type="checkbox" name="extra[]" id="extra-<?php echo htmlspecialchars($extra['name']); ?>" value="<?php echo htmlspecialchars($extra['name']); ?>" class="mr-2">
                            <label for="extra-<?php echo htmlspecialchars($extra['name']); ?>"><?php echo htmlspecialchars($extra['name']); ?></label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No extras available for this product.</p>
                <?php endif; ?>
            </div>
            <div class="mb-4">
                <h2 class="text-2xl font-bold mb-4">Find Us!</h2>
                <div id="map" class="w-full h-64"></div>
            </div>
            <button id="check-availability" type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Add to cart</button>
            <a id="continue" class="bg-blue-500 text-white px-4 py-2 rounded" href="bookings.php?productCode=<?php echo $productCode;?>">Continue</a>
            <div id="availability-result" class="mt-4"></div>
        </form>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY"></script>
    <script>
        function initMap() {
            var location = { lat: <?php echo json_encode($productDetails['product']['latitude'] ?? 0); ?>, lng: <?php echo json_encode($productDetails['product']['longitude'] ?? 0); ?> };
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 10,
                center: location
            });
            var marker = new google.maps.Marker({
                position: location,
                map: map
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            initMap();
        });

        $(document).ready(function() {
            let adultsCount = 1;
            let childrenCount = 0;
            let infantsCount = 0;

            function updatePassengerDropdown() {
                let totalPassengers = adultsCount + childrenCount + infantsCount;
                $('#passengerCount').text(totalPassengers);
                $('#adultsInput').val(adultsCount);
                $('#childrenInput').val(childrenCount);
                $('#infantsInput').val(infantsCount);
                updateAmount();
            }

            function updateAmount() {
                const pricePerAdult = <?php echo json_encode($productDetails['product']['priceOptions'][0]['price'] ?? 0); ?>;
                const totalAmount = (adultsCount + childrenCount + infantsCount) * pricePerAdult;
                $('#amount').val(totalAmount);
            }

            $('#passengerDropdown').click(function() {
                $('.custom-dropdown-menu').toggle();
            });

            $('#adults-plus').click(function() {
                adultsCount++;
                $('#adults-count').text(adultsCount);
                updatePassengerDropdown();
            });

            $('#adults-minus').click(function() {
                if (adultsCount > 1) {
                    adultsCount--;
                    $('#adults-count').text(adultsCount);
                    updatePassengerDropdown();
                }
            });

            $('#children-plus').click(function() {
                childrenCount++;
                $('#children-count').text(childrenCount);
                updatePassengerDropdown();
            });

            $('#children-minus').click(function() {
                if (childrenCount > 0) {
                    childrenCount--;
                    $('#children-count').text(childrenCount);
                    updatePassengerDropdown();
                }
            });

            $('#infants-plus').click(function() {
                infantsCount++;
                $('#infants-count').text(infantsCount);
                updatePassengerDropdown();
            });

            $('#infants-minus').click(function() {
                if (infantsCount > 0) {
                    infantsCount--;
                    $('#infants-count').text(infantsCount);
                    updatePassengerDropdown();
                }
            });

            $('#passenger-ready').click(function() {
                $('.custom-dropdown-menu').hide();
            });

            $(document).click(function(event) {
                if (!$(event.target).closest('#passengerDropdown, .custom-dropdown-menu').length) {
                    $('.custom-dropdown-menu').hide();
                }
            });

            $('#check-availability , #continue').click(function(event) {
                event.preventDefault();
                const adults = $('#adultsInput').val();
                const children = $('#childrenInput').val();
                // const infants = $('#infantsInput').val();
                const productCode = '<?php echo $productCode; ?>';
                const productname = $('#product_name').text();
                const productdescription = $('#product_description').text();
                const Amount = $('#amount').val();
                const TotalPassengers = Number(adults) + Number(children);
                const imgUrl = $('#imgurl').val();
                const paymentType = $('#paymentType').val();
                let selectedExtra = {
                    Adults: adults,
                    Children: children,
                    // Infants: infants,
                    Amount: Amount,
                    ProductCode: productCode,
                    ProductName: productname,
                    productdescription: productdescription,
                    TotalPassengers : TotalPassengers,
                    imgUrl:imgUrl,
                    paymentType : paymentType
                };

                let selectedExtrasArray = JSON.parse(sessionStorage.getItem('selectedExtras')) || [];
                selectedExtrasArray.push(selectedExtra);
                sessionStorage.setItem('selectedExtras', JSON.stringify(selectedExtrasArray));

                if ($(this).attr('id') === 'continue') {
                    window.location.href = `bookings.php?productCode=${productCode}`;
                    storeSessionAndRedirect(productCode);
                    updateCartCounter();
                    updateCartItems();
                } else {
                    storeSessionAndRedirect(productCode);
                    updateCartCounter();
                    updateCartItems();
                }
            });

            var availabilityResponse = <?php echo json_encode($availability); ?>;

            if (availabilityResponse.requestStatus.success && availabilityResponse.sessions.length > 0) {
                // Availability check success handling
            } else {
                var productCode = '<?php echo $productCode; ?>';
                window.location.href = `results.php?productCode=${productCode}`;
            }

            function storeSessionAndRedirect(productCode) {
                var selectedExtrasArray = sessionStorage.getItem('selectedExtras');
                if (selectedExtrasArray) {
                    fetch('storesession.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ selectedExtras: selectedExtrasArray })
                    })
                    .then(response => response.text())
                    .then(data => {
                        console.log(data);
                        window.location.href = url;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                } else {
                    window.location.href = url;
                }
            }
        });
    </script>
</body>
</html>
