<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://kit.fontawesome.com/74e6741759.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
</head>
<body>
<nav>
    <a href="index.php"><img src="images/logo_tdu.png" class="logo" alt=""></a>
    <ul class="nav-links">
        <li><a href="index.php">Popular places</a></li>
        <li><a href="#">Travel Outside</a></li>
        <li><a href="#">Online Packages</a></li>
    </ul>
    <div id="right-side">
        <a href="#" class="register-btn">Register Now</a>
            <button class="btn " type="button" data-bs-toggle="offcanvas" data-bs-target="#demo"><i class="fa-solid fa-cart-shopping" style="color: #ffff;"></i></button>
    </div>
</nav>
<div class="header">
    <div class="container">

    <div class="offcanvas offcanvas-end" id="demo">
            <div class="offcanvas-header">
            <div class="maincont">
    <div class="content">
        <div class="image-part">
            <img src="https://source.unsplash.com/random/800x600" alt="Random Image">
        </div>
        <div class="info-part">
            
            
        </div>
    </div>
   </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
   </div>
</body>
</html>