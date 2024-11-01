<?php

namespace WcPagaleve\API\Routes;

class CheckoutFields extends Route
{
    public function __construct()
    {
        $this->setNamespace();
        $this->registerRoute(
            'checkout-fields',
            [$this, 'handleRequest'],
            ['POST']
        );
    }

    public function handleRequest($data)
    {
        if ($data->get_param('type') === 'document-data') {
            $this->saveCustomerDocument($data->get_params());
        }

        $this->sendJsonResponse(
            "Invalid Params",
            false,
            422
        );
    }

    private function saveCustomerDocument($params)
    {
        $data = $params['fields'];
        $session = [
            'personType' => $data['personType'] ?? '',
            'document' => $data['document'] ?? ''
        ];

        if (isset($_COOKIE['document-data'])) {
            unset($_COOKIE['document-data']);
        }

        setcookie('document-data', serialize($session), time() + (86400 * 30), "/", true);
        $this->sendJsonResponse('success!');
    }
}
