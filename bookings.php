<?php
session_start();
$sessionData = $_SESSION['selectedExtras'];
// unset($_SESSION['selectedExtras']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        nav #right-side {
            display: flex;
        }
        nav .register-btn {
            width: 220px;
        }
        .container {
            background-color: white;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 34%;
            margin-top: 95px !important;
        }
        .header {
            margin-bottom: 20px;
        }
        .steps {
            display: flex;
            justify-content: space-around;
            margin-bottom: 10px;
        }
        .steps span {
            font-weight: bold;
        }
        .contact-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .secure-checkout {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            color: green;
            font-weight: bold;
        }
        .secure-checkout input {
            margin-right: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        p {
            font-size: 0.9em;
            color: #666;
            text-align: center;
        }
        p a {
            color: #007bff;
        }
        p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php
        date_default_timezone_set('UTC');
        // echo "<pre>";
        // var_dump($_SESSION['selectedExtras']);
        // echo "</pre>";

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

        function createRezdyBooking($apiKey, $bookingData) {
            $url = "https://api.rezdy-staging.com/v1/bookings?apiKey=" . urlencode($apiKey);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bookingData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
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
        $productCode = $_GET['productCode'] ?? '';
        if (empty($productCode)) {
            die("Error: Product code must be provided.");
        }

        $productDetails = getRezdyProductDetails($apiKey, $productCode);
        $startTimeLocal = (new DateTime())->format('Y-m-d H:i:s');
        $endTimeLocal = (new DateTime())->modify('+1 month')->format('Y-m-d H:i:s');
        $availability = getRezdyAvailability($apiKey, $productCode, $startTimeLocal, $endTimeLocal);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $phone = $_POST['phone'];
            $amount = $_POST['amount'];
            $paymentType = $_POST['paymentType'];
            $adults = $_POST['adults'];
            $children = $_POST['children'];
            $infants = $_POST['infants'];
            $selectedExtras = $_POST['extra'] ?? [];

            if (isset($availability['sessions']) && count($availability['sessions']) > 0) {
                $startTimeLocal = $availability['sessions'][0]['startTimeLocal'];

                $bookingDataArray = [];
                foreach ($sessionData as $session) {
                    for ($i = 0; $i < $session['TotalPassengers']; $i++) {
                        $bookingData = [
                            "customer" => [
                                "firstName" => $firstName,
                                "lastName" => $lastName,
                                "phone" => $phone
                            ],
                            "items" => [
                                [
                                    "productCode" => $session['ProductCode'],
                                    "startTimeLocal" => $startTimeLocal,
                                    "quantities" => [
                                        [
                                            "optionLabel" => "Adult",
                                            "value" => $adults
                                        ]
                                    ]
                                ]
                            ],
                            "payments" => [
                                [
                                    "amount" => $session['Amount'],
                                    "type" => $session['paymentType'],
                                    "recipient" => "AGENT",
                                    "label" => "Paid in cash to API specification demo company"
                                ]
                            ]
                        ];
                        $bookingDataArray[] = $bookingData;
                    }
                }

                foreach ($bookingDataArray as $booking) {
                    $bookingResponse = createRezdyBooking($apiKey, $booking);
                    
                    if (isset($bookingResponse['requestStatus']['success']) && $bookingResponse['requestStatus']['success'] == true) {
                        echo "Booking successful!";
                    } else {
                        echo "Booking failed!";
                    }
                }
            } else {
                echo "No available sessions found for the selected product.";
            }
        }
        var_dump($bookingDataArray);
        var_dump($bookingResponse);
    ?>
    <?php require "header.php" ?>
    <div class="container">
        <form class="contact-form" action="bookings.php" method="POST">
            <h2>Enter your contact details</h2>
            <div class="row">
                <div class="form-group col-6 d-grid">
                    <label for="fname">* First name</label>
                    <input type="text" id="fname" name="firstName" placeholder="Enter first name" required>
                </div>
                <div class="form-group col-6 d-grid">
                    <label for="lname">* Last name</label>
                    <input type="text" id="lname" name="lastName" placeholder="Enter last name" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">* Email</label>
                <input type="email" id="email" name="email" placeholder="Enter email" required>
            </div>
            <div class="form-group">
                <label for="country">* Country</label>
                <select id="country" name="country" required>
                    <option value="" hidden selected>Select Country</option>
                    <option value="india">India (+91)</option>
                    <!-- Add other country options here -->
                </select>
            </div>
            <div class="form-group">
                <label for="phone">* Mobile phone number</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter mobile number" required>
            </div>
            <div class="d-flex align-items-baseline form-group">
                <input type="checkbox" id="email-updates" name="email-updates" checked>
                <label for="email-updates">Send me discounts and other offers by email</label>
            </div>
            <p>We’ll only contact you with essential updates or changes to your booking.</p>
            <button type="submit">Go to payment</button>
            <p>You'll receive email reminders for this and future GetYourGuide products. You can opt out at any time. See our <a href="#">Privacy Policy</a>.</p>
        </form>
    </div>
</body>
</html>
<script>
    // sessionStorage.removeItem('selectedExtras');
</script>
