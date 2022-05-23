<?php

namespace App;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Payments\AuthorizationsCaptureRequest;
use PayPalCheckoutSdk\Payments\AuthorizationsGetRequest;
use Psr\Http\Message\ServerRequestInterface;

class PaypalPayment
{

    public function __construct(private string $clientID, private string $secretKey, private bool $sandbox)
    {

    }

    public function ui(Cart $cart): string
    {
        $order = json_encode([
            "purchase_units" => [
                [
                    "description" => "Mon panier test",
                    "items" => array_map(function($product) {
                        return [
                            "name" => $product['name'],
                            "quantity" => 1,
                            "unit_amount" => [
                                "value" => number_format($product['price'] / 100, 2, ".", ""),
                                "currency_code" => "EUR"
                            ]
                        ];
                    }, $cart->getProducts()),
                    "amount" => [
                        "currency_code" => "EUR",
                        "value" => number_format($cart->getTotal() / 100, 2, ".", ""),
                        "breakdown" => [
                            "item_total" => [
                                'currency_code' => 'EUR',
                                "value" => number_format($cart->getTotal() / 100, 2, ".", ""),
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        return <<<HTML
        <script src="https://www.paypal.com/sdk/js?client-id={$this->clientID}&currency=EUR&intent=authorize&components=buttons"></script>
        <!-- Set up a container element for the button -->
        <div id="paypal-button-container"></div>
        <script>
            paypal.Buttons({
                style: {
                    color:  'blue',
                    height: 40,
                    layout:  'vertical',
                },
                // Sets up the transaction when a payment button is clicked
                createOrder: (data, actions) => {
                    return actions.order.create({$order});
                },
                // Finalize the transaction after payer approval
                onApprove: async (data, actions) => {
                    const authorization = await actions.order.authorize();
                    const authorizationId = authorization.purchase_units[0].payments.authorizations[0].id;
                    
                    await fetch('/paypal.php', {
                        method: 'post',
                        headers: {
                          'content-type': 'application/json'
                        },
                        body: JSON.stringify({authorizationId})
                    })
                    
                    alert('Votre paiement a bien été enregistré');
                }
            }).render('#paypal-button-container');
        </script>
HTML;

    }

    public function handle(ServerRequestInterface $serverRequest, Cart $cart) {
        if ($this->sandbox) {
            $environment = new SandboxEnvironment( $this->clientID, $this->secretKey);
        } else {
            $environment = new ProductionEnvironment( $this->clientID, $this->secretKey);
        }

        $authorizationId = $serverRequest->getParsedBody()['authorizationId'];

        $client = new PayPalHttpClient($environment);
        $request = new AuthorizationsGetRequest($authorizationId);
        $response = $client->execute($request);

        // check amount
        $amountValue = $response->result->amount->value;
        $expectedAmountValue = number_format($cart->getTotal() / 100, 2, ".", "");

        if ($amountValue !== $expectedAmountValue) {
            throw new PaymentAmountException($amountValue, $expectedAmountValue);
        }

        // Vérifier si le stock est dispo

        // Save user info

        // Capture payment (one time)
        $request = new AuthorizationsCaptureRequest($authorizationId);
        $response = $client->execute($request);
        if ($response->result->status !== "COMPLETED") {
            throw new \Exception("oops something bad happen");
        }
    }
}