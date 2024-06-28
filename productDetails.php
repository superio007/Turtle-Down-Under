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

function checkRezdyAvailability($apiKey, $productCode, $startTimeLocal, $endTimeLocal) {
    $url = "https://api.rezdy-staging.com/v1/availability?apiKey=" . urlencode($apiKey) . "&productCode=" . urlencode($productCode) . "&startTimeLocal=" . urlencode($startTimeLocal) . "&endTimeLocal=" . urlencode($endTimeLocal);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if ($response === false) {
        return "Error: Curl request failed: " . curl_error($ch);
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if ($data === null) {
        return "Error: Failed to decode JSON response";
    }
    return $data;
}

function formatRezdyDate($date) {
    return $date->format('Y-m-d H:i:s');
}

$apiKey = "81c3566e60ef42e6afa1c2719e7843fd";
$productCode = $_GET['productCode'] ?? '';
if (empty($productCode)) {
    die("Error: Product code must be provided.");
}

$productDetails = getRezdyProductDetails($apiKey, $productCode);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkAvailability'])) {
    $datetime = $_POST['datetime'];
    $startTimeLocal = formatRezdyDate(new DateTime($datetime));
    $endTimeLocal = formatRezdyDate((new DateTime($datetime))->modify('+2 days'));
    $availability = checkRezdyAvailability($apiKey, $productCode, $startTimeLocal, $endTimeLocal);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rezdy API - Product Details</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<style>
    #payee {
        border: 1px solid #0000001f;
        padding: 8px 15px;
        width: fit-content;
    }
</style>
<body class="bg-light">
    <?php require "header.php";?>
    <div class="container mt-5 p-4 bg-white rounded-lg shadow-lg">
        <form id="availability-form" action="" method="post">
            <h1 id="product_name" class="text-4xl font-bold mb-4"><?php echo htmlspecialchars($productDetails['product']['name'] ?? ''); ?></h1>
            <p id="product_description" class="mb-4"><?php echo htmlspecialchars($productDetails['product']['shortDescription'] ?? ''); ?></p>
            <input type="text" value="<?php echo htmlspecialchars($productDetails['product']['images'][0]['itemUrl']); ?>" id="imgurl" hidden>
            <?php if (isset($productDetails['product']['images'][0]['itemUrl'])): ?>
                <img src="<?php echo htmlspecialchars($productDetails['product']['images'][0]['itemUrl']); ?>" alt="Product Image" class="w-full h-auto mb-4">
            <?php endif; ?>
            <input type="text" id="amount" value="<?php echo htmlspecialchars($productDetails['product']['priceOptions'][0]['price'] ?? ''); ?>" class="form-control mb-4" disabled>
            
            <div class="form-group mb-4">
                <label for="passengerCount" class="font-weight-bold">Select Passengers</label>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-secondary" type="button" id="adults-minus">-</button>
                    </div>
                    <input type="text" class="form-control text-center" id="adults-count" value="1" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="adults-plus">+</button>
                    </div>
                </div>
                <input type="hidden" id="adultsInput" name="adults" value="1">
                <input type="hidden" id="childrenInput" name="children" value="0">
                <input type="hidden" id="infantsInput" name="infants" value="0">
            </div>

            <div class="form-group mb-4">
                <label for="childrenCount" class="font-weight-bold">Children</label>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-secondary" type="button" id="children-minus">-</button>
                    </div>
                    <input type="text" class="form-control text-center" id="children-count" value="0" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="children-plus">+</button>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label for="paymentType" class="font-weight-bold">Payment Option</label>
                <select required name="paymentType" id="paymentType" class="form-control">
                    <option value="" selected hidden>Select</option>
                    <option value="CASH">CASH</option>
                    <option value="CREDIT CARD">CREDIT CARD</option>
                </select>
            </div>

            <div class="form-group mb-4">
                <h2 class="text-2xl font-bold mb-4">Choose Extras</h2>
                <?php if (isset($productDetails['product']['extras']) && is_array($productDetails['product']['extras'])): ?>
                    <?php foreach ($productDetails['product']['extras'] as $extra): ?>
                        <div class="d-flex gap-3 align-items-center mb-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="extra[]" id="extra-<?php echo htmlspecialchars($extra['name']); ?>" value="<?php echo htmlspecialchars($extra['name']); ?>" data-price="<?php echo htmlspecialchars($extra['price']); ?>" class="custom-control-input extra-checkbox">
                                <label class="custom-control-label" for="extra-<?php echo htmlspecialchars($extra['name']); ?>"><?php echo htmlspecialchars($extra['name']); ?></label>
                            </div>
                            <div class="form-group mb-0">
                                <label for="extra-qty-<?php echo htmlspecialchars($extra['name']); ?>">Qty:</label>
                                <input type="number" name="Extras_quantity[]" id="extra-qty-<?php echo htmlspecialchars($extra['name']); ?>" min="0" value="0" class="extra-quantity form-control" style="width: 60px; display: inline-block;">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No extras available for this product.</p>
                <?php endif; ?>
            </div>

            <div class="form-group mb-4">
                <h2 class="text-2xl font-bold mb-4">Select Date and Time</h2>
                <div class="form-group">
                    <input type="datetime-local" id="datetimeInput" name="datetime" class="form-control">
                </div>
            </div>

            <div class="form-group mb-4">
                <h2 class="text-2xl font-bold mb-4">Find Us!</h2>
                <div id="map" class="w-100 h-64"></div>
            </div>

            <button id="check-availability" type="submit" name="checkAvailability" class="btn btn-secondary mr-2">Check Availability</button>
            <button id="add-to-cart" type="submit" class="btn btn-primary mr-2">Add to cart</button>
            <a id="continue" class="btn btn-primary" href="bookings.php?productCode=<?php echo $productCode;?>">Continue</a>
            <div id="availability-result" class="mt-4">
                <?php
                if (isset($availability)) {
                    if (is_string($availability)) {
                        echo '<div class="alert alert-danger" role="alert">' . $availability . '</div>';
                    } else {
                        if ($availability['requestStatus']['success'] && !empty($availability['sessions'])) {
                            $counter = 0; // Initialize the counter
                            foreach ($availability['sessions'] as $session) {
                                $counter++; // Increment the counter for each session
                                $sessionDivId = 'availability_' . $counter; // Create a unique ID
                                echo '<div id="' . $sessionDivId . '" class="alert alert-success" role="alert">Available session from ' . htmlspecialchars($session['startTimeLocal']) . ' to ' . htmlspecialchars($session['endTimeLocal']) . ' <button id="add-to-Cart" data-session_start_time="' . htmlspecialchars($session['startTimeLocal']) . '" type="submit" class="btn btn-primary mr-2">Book Now</button></div>';
                            }
                            
                        } else {
                            echo '<div class="alert alert-danger" role="alert">No available sessions found.</div>';
                        }
                    }
                }
                ?>
            </div>
        </form>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY"></script>
    <!-- <script>
        //  $('#add-to-Cart').on('click', function(event) {
        //     // Prevent the form from submitting if it's inside a form
        //         // pushTovalue();

           

        //     // You can now use the sessionStartTime value as needed
        //     // For example, send it to the server via AJAX
        // });
        
        $(document).ready(function() {
            let adultsCount = 1;
            let childrenCount = 0;
            let infantsCount = 0;

            function updatePassengerCount() {
                $('#adultsInput').val(adultsCount);
                $('#childrenInput').val(childrenCount);
                $('#infantsInput').val(infantsCount);
                updateAmount();
            }

            function updateAmount() {
                const pricePerAdult = <?php echo json_encode($productDetails['product']['priceOptions'][0]['price'] ?? 0); ?>;
                const pricePerChild = <?php echo json_encode($productDetails['product']['priceOptions'][1]['price'] ?? 0); ?>;
                let totalAmount = (adultsCount * pricePerAdult + childrenCount * pricePerChild);

                // Include extras in the amount calculation
                $('.extra-checkbox:checked').each(function() {
                    const extraPrice = parseFloat($(this).data('price'));
                    const quantity = parseInt($(this).closest('.d-flex').find('.extra-quantity').val()) || 0;
                    totalAmount += extraPrice * quantity;
                });

                $('#amount').val(totalAmount.toFixed(2));
            }

            $('.extra-checkbox, .extra-quantity').change(function() {
                updateAmount();
            });

            $('#adults-plus').click(function() {
                adultsCount++;
                $('#adults-count').val(adultsCount);
                updatePassengerCount();
            });

            $('#adults-minus').click(function() {
                if (adultsCount > 1) {
                    adultsCount--;
                    $('#adults-count').val(adultsCount);
                    updatePassengerCount();
                }
            });

            $('#children-plus').click(function() {
                childrenCount++;
                $('#children-count').val(childrenCount);
                updatePassengerCount();
            });

            $('#children-minus').click(function() {
                if (childrenCount > 0) {
                    childrenCount--;
                    $('#children-count').val(childrenCount);
                    updatePassengerCount();
                }
            });

            // Initialize datetime input with current date and time
            const now = new Date();
            const formattedDateTime = now.toISOString().substring(0, 16);
            $('#datetimeInput').val(formattedDateTime);

            $('#check-availability').click(function(event) {
                $('#availability-form').submit();
            });

            $('#check-availability').click(function(event) {
                const paymentType = $('#paymentType').val();
                const Amount = $('#amount').val();
                const adults = $('#adultsInput').val();
                const children = $('#childrenInput').val();
                let extras = [];

                $('.extra-checkbox:checked').each(function() {
                    const extraName = $(this).val();
                    const quantity = $(this).closest('.d-flex').find('.extra-quantity').val();
                    extras.push({ name: extraName, quantity: quantity });
                });

                let storeToretrive = {
                    Adults: adults,
                    Children: children,
                    Amount: Amount,
                    paymentType: paymentType,
                    Extras: extras,
                };

                sessionStorage.setItem('storeToretrive', JSON.stringify(storeToretrive));
            });
            function pushTovalue(){
                // Retrieve the stored value from sessionStorage and parse it
                let storedValue = JSON.parse(sessionStorage.getItem('storeToretrive')) || {};

                // Set default values if the stored value is not present
                $('#paymentType').val(storedValue['paymentType'] || '');
                $('#amount').val(storedValue['Amount'] || ''); 
                $('#adults-count').val(storedValue['Adults'] || ''); 
                $('#children-count').val(storedValue['Children'] || '');

                $('#adultsInput').val(storedValue['Adults'] || '');
                $('#childrenInput').val(storedValue['Children'] || '');

                if (storedValue['Extras']) {
                    storedValue['Extras'].forEach(extra => {
                        const checkbox = $(`.extra-checkbox[value="${extra.name}"]`);
                        const quantityInput = checkbox.closest('.d-flex').find('.extra-quantity');

                        checkbox.prop('checked', extra.isChecked);
                        quantityInput.val(extra.quantity);
                    });
                }

                updateAmount();
            }

            $('#add-to-cart, #add-to-Cart, #continue').click(function(event) {
                event.preventDefault();
                 // Access the data attribute
                var sessionStartTime = $(this).data('session_start_time');
                console.log('Session Start Time:', sessionStartTime);
                const adults = $('#adultsInput').val();
                const children = $('#childrenInput').val();
                const productCode = '<?php echo $productCode; ?>';
                const productname = $('#product_name').text();
                const productdescription = $('#product_description').text();
                const Amount = $('#amount').val();
                const TotalPassengers = Number(adults) + Number(children);
                const imgUrl = $('#imgurl').val();
                const paymentType = $('#paymentType').val();
                let extras = [];

                $('.extra-checkbox:checked').each(function() {
                    const extraName = $(this).val();
                    const quantity = $(this).closest('.d-flex').find('.extra-quantity').val();
                    extras.push({ name: extraName, quantity: quantity });
                });

                let selectedExtra = {
                    Adults: adults,
                    Children: children,
                    Amount: Amount,
                    ProductCode: productCode,
                    ProductName: productname,
                    productdescription: productdescription,
                    TotalPassengers: TotalPassengers,
                    imgUrl: imgUrl,
                    paymentType: paymentType,
                    Extras: extras,
                    sessionStartTime : sessionStartTime
                };

                let selectedExtrasArray = JSON.parse(sessionStorage.getItem('selectedExtras')) || [];
                selectedExtrasArray.push(selectedExtra);
                sessionStorage.setItem('selectedExtras', JSON.stringify(selectedExtrasArray));

                if ($(this).attr('id') === 'continue') {
                    window.location.href = `bookings.php?productCode=${productCode}`;
                } else {
                    storeSessionAndRedirect(productCode);
                    updateCartCounter();
                    updateCartItems();
                }
            });

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
                        alert("Trip has been added into your cart.");
                        console.log('Session Start Time:', sessionStartTime);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            }
            pushTovalue();
            // updatePassengerCount();
        });
        if(isset($_GET['sessionStart'])){
            $sessionStart = $_GET['sessionStart'];
        }
    </script> -->
    <script>
    $(document).ready(function() {
        let adultsCount = 1;
        let childrenCount = 0;
        let infantsCount = 0;

        function updatePassengerCount() {
            $('#adultsInput').val(adultsCount);
            $('#childrenInput').val(childrenCount);
            $('#infantsInput').val(infantsCount);
            updateAmount();
        }

        function updateAmount() {
            const pricePerAdult = <?php echo json_encode($productDetails['product']['priceOptions'][0]['price'] ?? 0); ?>;
            const pricePerChild = <?php echo json_encode($productDetails['product']['priceOptions'][1]['price'] ?? 0); ?>;
            let totalAmount = (adultsCount * pricePerAdult + childrenCount * pricePerChild);

            // Include extras in the amount calculation
            $('.extra-checkbox:checked').each(function() {
                const extraPrice = parseFloat($(this).data('price'));
                const quantity = parseInt($(this).closest('.d-flex').find('.extra-quantity').val()) || 0;
                totalAmount += extraPrice * quantity;
            });

            $('#amount').val(totalAmount.toFixed(2));
        }

        function storeExtras() {
            let extras = [];

            $('.extra-checkbox').each(function() {
                const extraName = $(this).val();
                const isChecked = $(this).is(':checked');
                const quantity = $(this).closest('.d-flex').find('.extra-quantity').val();
                extras.push({ name: extraName, isChecked: isChecked, quantity: quantity });
            });

            let storeToretrive = {
                Adults: $('#adultsInput').val(),
                Children: $('#childrenInput').val(),
                Amount: $('#amount').val(),
                paymentType: $('#paymentType').val(),
                Extras: extras,
            };

            sessionStorage.setItem('storeToretrive', JSON.stringify(storeToretrive));
        }

        function loadStoredExtras() {
            let storedValue = JSON.parse(sessionStorage.getItem('storeToretrive')) || {};

            // Set default values if the stored value is not present
            $('#paymentType').val(storedValue['paymentType'] || '');
            $('#amount').val(storedValue['Amount'] || ''); 
            $('#adults-count').val(storedValue['Adults'] || ''); 
            $('#children-count').val(storedValue['Children'] || '');

            $('#adultsInput').val(storedValue['Adults'] || '');
            $('#childrenInput').val(storedValue['Children'] || '');

            if (storedValue['Extras']) {
                storedValue['Extras'].forEach(extra => {
                    const checkbox = $(`.extra-checkbox[value="${extra.name}"]`);
                    const quantityInput = checkbox.closest('.d-flex').find('.extra-quantity');

                    checkbox.prop('checked', extra.isChecked);
                    quantityInput.val(extra.quantity);
                });
            }

            updateAmount();
        }

        // Remove any existing event handlers to prevent multiple triggers
        $('#adults-plus').off('click').on('click', function() {
            adultsCount++;
            $('#adults-count').val(adultsCount);
            updatePassengerCount();
            storeExtras();
        });

        $('#adults-minus').off('click').on('click', function() {
            if (adultsCount > 1) {
                adultsCount--;
                $('#adults-count').val(adultsCount);
                updatePassengerCount();
                storeExtras();
            }
        });

        $('#children-plus').off('click').on('click', function() {
            childrenCount++;
            $('#children-count').val(childrenCount);
            updatePassengerCount();
            storeExtras();
        });

        $('#children-minus').off('click').on('click', function() {
            if (childrenCount > 0) {
                childrenCount--;
                $('#children-count').val(childrenCount);
                updatePassengerCount();
                storeExtras();
            }
        });

        $('.extra-checkbox, .extra-quantity').off('change').on('change', function() {
            updateAmount();
            storeExtras();
        });

        // Initialize datetime input with current date and time
        const now = new Date();
        const formattedDateTime = now.toISOString().substring(0, 16);
        $('#datetimeInput').val(formattedDateTime);

        $('#check-availability').click(function(event) {
            $('#availability-form').submit();
        });

        $('#add-to-cart, #add-to-Cart, #continue').click(function(event) {
            event.preventDefault();
            var sessionStartTime = $(this).data('session_start_time');
            console.log('Session Start Time:', sessionStartTime);
            const adults = $('#adultsInput').val();
            const children = $('#childrenInput').val();
            const productCode = '<?php echo $productCode; ?>';
            const productname = $('#product_name').text();
            const productdescription = $('#product_description').text();
            const Amount = $('#amount').val();
            const TotalPassengers = Number(adults) + Number(children);
            const imgUrl = $('#imgurl').val();
            const paymentType = $('#paymentType').val();
            let extras = [];

            $('.extra-checkbox:checked').each(function() {
                const extraName = $(this).val();
                const quantity = $(this).closest('.d-flex').find('.extra-quantity').val();
                extras.push({ name: extraName, quantity: quantity });
            });

            let selectedExtra = {
                Adults: adults,
                Children: children,
                Amount: Amount,
                ProductCode: productCode,
                ProductName: productname,
                productdescription: productdescription,
                TotalPassengers: TotalPassengers,
                imgUrl: imgUrl,
                paymentType: paymentType,
                Extras: extras,
                sessionStartTime : sessionStartTime
            };

            let selectedExtrasArray = JSON.parse(sessionStorage.getItem('selectedExtras')) || [];
            selectedExtrasArray.push(selectedExtra);
            sessionStorage.setItem('selectedExtras', JSON.stringify(selectedExtrasArray));

            if ($(this).attr('id') === 'continue') {
                window.location.href = `bookings.php?productCode=${productCode}`;
            } else {
                storeSessionAndRedirect(productCode);
                updateCartCounter();
                updateCartItems();
            }
        });

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
                    alert("Trip has been added into your cart.");
                    console.log('Session Start Time:', sessionStartTime);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }

        loadStoredExtras(); // Load stored values on page load
    });
</script>



</body>
</html>
