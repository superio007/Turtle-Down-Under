<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://kit.fontawesome.com/74e6741759.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        nav {
            min-height: 2vh;
            display: flex;
            background-color: #177E89;
            align-items: center;
            justify-content: space-between;
            padding: 5px 7%;
            position: absolute;
            top: 0;
            width: 100%;
        }

        .logo {
            width: 140px;
            cursor: pointer;
        }

        .nav-links li {
            list-style: none;
            display: inline-block;
            margin: 10px 30px;
        }

        .nav-links li a {
            text-decoration: none;
            color: #fff;
        }

        .register-btn {
            background: #fff;
            color: black;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
    <script>
        function getCartItems() {
            let cartItems = sessionStorage.getItem('selectedExtras');
            return cartItems ? JSON.parse(cartItems) : [];
        }

        function updateCartCounter() {
            let cartItems = getCartItems();
            $('#cart_ounter').text(cartItems.length);
        }

        function updateCartItems() {
            let cartItems = getCartItems();
            let cartItemsContainer = $('.offcanvas-body .maincont .content');
            cartItemsContainer.empty();

            let totalAmount = cartItems.reduce((sum, item) => sum + Number(item.Amount), 0);

            let cartContent = `<h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">Your Cart</h3>`;
            cartItems.forEach(function(item) {
                cartContent += `
                    <div class="d-flex gap-3" style="align-items: center;background-color:#64A6BD ;padding: 8px;border-radius: 10px;margin-bottom: 25px;">
                        <div>
                            <img style="width:100px;height:100px;" src="${item.imgUrl}" alt="">
                        </div>
                        <div>
                            <p style="font-size: small;font-weight:bold;">${item.ProductName}</p>
                            <p style="font-size: small;">${item.productdescription}</p>
                            <p style="font-size: small;">Amount :$AUD ${item.Amount}</p>
                        </div>
                    </div>
                `;
            });

            if (cartItems.length != 0) {
                cartContent += `
                <div class="d-flex justify-content-between align-items-baseline">
                    <p class="m-0 p-2 btn text-light" style="background-color:#1be414;">$ AUD ${totalAmount}</p>
                    <a class="btn btn-primary" href="payment.php">Continue</a>
                </div>
                `;
            } else {
                cartContent += `
                <p class="text-center">Your cart is empty!</p>
                `;
            }

            cartItemsContainer.html(cartContent);
        }

        $(document).ready(function() {
            updateCartCounter();
            updateCartItems();
        });
    </script>
</head>
<body>
<nav>
    <a href="index.php"><img src="images/logo_tdu.png" class="logo" alt=""></a>
    <ul class="nav-links">
        <li><a href="index.php">Popular places</a></li>
        <li><a href="#">Travel Outside</a></li>
        <li><a href="#">Online Packages</a></li>
    </ul>
    <div style="position: relative;" id="right-side">
        <a href="#" class="register-btn">Register Now</a>
        <button class="btn " type="button" data-bs-toggle="offcanvas" data-bs-target="#demo"><i class="fa-solid fa-cart-shopping" style="color: #ffff;"></i></button>
        <p id="cart_ounter" style="position: absolute;top: 0;right: 0;background-color: red;color: white;width: 21px;margin: 0;text-align: center;border-radius: 50px;font-size: 12px;">0</p>
    </div>
</nav>
<div class="header1">
    <div class="container">

        <div class="offcanvas offcanvas-end" id="demo">
            <div class="offcanvas-header">
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <div class="maincont">
                    <div class="content" >
                          <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">Your Cart</h3>
                        <!-- Cart items will be loaded here via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
