<?php

use App\Cart;
use App\PaypalPayment;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Symfony\Component\Dotenv\Dotenv;

require '../vendor/autoload.php';

$cart = new Cart();
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__. '/../.env');

$psr17Factory = new Psr17Factory();

$creator = new ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$request = $creator->fromGlobals();
$request = $request->withParsedBody(json_decode($request->getBody(), true));
$payment = new PaypalPayment($_ENV['PAYPAL_CLIENT_ID'], $_ENV['PAYPAL_SECRET_KEY'], true);
$payment->handle($request, $cart);
