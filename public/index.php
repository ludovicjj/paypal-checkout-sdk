<?php

use App\Cart;
use App\PaypalPayment;
use Symfony\Component\Dotenv\Dotenv;

require '../vendor/autoload.php';
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__. '/../.env');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <title>Paypal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css"
          rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor"
          crossorigin="anonymous">
</head>
<body>
<?php
$cart = new Cart();
?>

<div class="container py-5">
    <h1 class="">Récapitulatif du Panier</h1>
    <div class="list-group py-4">
        <?php foreach ($cart->getProducts() as $product): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <?= $product["name"] ?>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <?= number_format($product["price"]/100, 2, ".", "") ?> €
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="py-2">
        <?php
        $payment = new PaypalPayment($_ENV['PAYPAL_CLIENT_ID'], $_ENV['PAYPAL_SECRET_KEY'], true);
        echo $payment->ui($cart)
        ?>
    </div>
</div>
</body>
</html>