<?php
    class SamplePaymentModule extends PaymentGatewayModule
    {
        function __construct()
        {
            $this->name             = __CLASS__;

            parent::__construct();
        }

        public function config_fields()
        {
            return [
                'example1'          => [
                    'name'              => "Text",
                    'description'       => "Description for Text",
                    'type'              => "text",
                    'value'             => $this->config["settings"]["example1"] ?? '',
                    'placeholder'       => "Example Placeholder",
                ],
                'example2'          => [
                    'name'              => "Password",
                    'description'       => "Description for Password",
                    'type'              => "password",
                    'value'             => $this->config["settings"]["example2"] ?? '',
                    'placeholder'       => "Example Placeholder",
                ],
                'example3'          => [
                    'name'              => "Approval Button",
                    'description'       => "Description for Approval Button",
                    'type'              => "approval",
                    'value'             => 1,
                    'checked'           => (boolean) (int) ($this->config["settings"]["example3"] ?? 0),
                ],
                'example4'          => [
                    'name'              => "Dropdown Menu 1",
                    'description'       => "Description for Dropdown Menu 1",
                    'type'              => "dropdown",
                    'options'           => "Option 1,Option 2,Option 3,Option 4",
                    'value'             => $this->config["settings"]["example4"] ?? '',
                ],
                'example5'          => [
                    'name'              => "Dropdown Menu 2",
                    'description'       => "Description for Dropdown Menu 2",
                    'type'              => "dropdown",
                    'options'           => [
                        'opt1'     => "Option 1",
                        'opt2'     => "Option 2",
                        'opt3'     => "Option 3",
                        'opt4'     => "Option 4",
                    ],
                    'value'         => $this->config["settings"]["example5"] ?? '',
                ],
                'example6'          => [
                    'name'              => "Radio Button 1",
                    'description'       => "Description for Radio Button 1",
                    'width'             => 40,
                    'description_pos'   => 'L',
                    'is_tooltip'        => true,
                    'type'              => "radio",
                    'options'           => "Option 1,Option 2,Option 3,Option 4",
                    'value'             => $this->config["settings"]["example6"] ?? '',
                ],
                'example7'          => [
                    'name'              => "Radio Button 2",
                    'description'       => "Description for Radio Button 2",
                    'description_pos'   => 'L',
                    'is_tooltip'        => true,
                    'type'              => "radio",
                    'options'           => [
                        'opt1'     => "Option 1",
                        'opt2'     => "Option 2",
                        'opt3'     => "Option 3",
                        'opt4'     => "Option 4",
                    ],
                    'value'             => $this->config["settings"]["example7"] ?? '',
                ],
                'example8'          => [
                    'name'              => "Text Area",
                    'description'       => "Description for text area",
                    'rows'              => "3",
                    'type'              => "textarea",
                    'value'             => $this->config["settings"]["example8"] ?? '',
                    'placeholder'       => "Example placeholder",
                ]
            ];
        }

        public function area($params=[])
        {
            $merchant_id = $this->config["settings"]["example1"] ?? 0;

            return
                '<form action="https://www.sample.com/checkout" method="POST">
                    <input type="hidden" name="merchant_id" value="'.$merchant_id.'">
                    <input type="hidden" name="amount" value="'.$params["amount"].'">
                    <input type="hidden" name="currency" value="'.$this->currency($params["currency"]).'">
                    <input type="hidden" name="custom_id" value="'.$this->checkout_id.'">
                    <input type="hidden" name="description" value="Invoice Payment">
                    <input type="submit" value="'.$this->lang["pay-button"].'">
                </form>';
        }

        public function callback()
        {
            $custom_id      = (int) Filter::init("POST/custom_id","numbers");

            if(!$custom_id){
                $this->error = 'ERROR: Custom id not found.';
                return false;
            }

            $checkout       = $this->get_checkout($custom_id);

            // Checkout invalid error
            if(!$checkout)
            {
                $this->error = 'Checkout ID unknown';
                return false;
            }

            // You introduce checkout to the system
            $this->set_checkout($checkout);

            /*
             * From here, a Subscription ID is defined for each item.
             * You can also use the codes here for "callback" and "capture" operations.
             */
            if($items = $this->subscribable_items())
            {
                $subscribed = [];
                foreach($items AS $item)
                    $subscribed[$item['identifier']] = 'Here Subscription ID';
                if($subscribed) $this->set_subscribed_items($subscribed);
            }



            return [
                /* You can define it as 'successful' or 'pending'.
                 * 'successful' : Write if the payment is complete.
                 * 'pending' : Write if the payment is pending confirmation.
                 */
                'status'            => 'successful',
                /*
                 * If there is anything you need to inform the manager about the payment, please fill it out.
                 * Acceptable value : 'array' and 'string'
                 */
                'message'        => [
                    'Merchant Transaction ID' => '123X456@23',
                ],
                // Write if you want to show a message to the person on the callback page.
                'callback_message'        => 'Transaction Successful',
                'paid'                    => [
                    'amount'        => 15,
                    'currency'      => "USD",
                ],
            ];
        }

        public function get_subscription($params=[])
        {
            $sub_id             = $params["identifier"];

            /*
             * From here, you can send an API command to the payment service provider and report the status of the subscription according to the response you receive.
             */

            return [
                'status'            => 'active',
                'status_msg'        => '',
                'first_paid'        => [
                    'time'              => '2021-07-01 13:00:00',
                    'fee'               => [
                        'amount'    => 15,
                        'currency'  => 'USD',
                    ],
                ],
                'last_paid'         => [
                    'time'          => '2021-08-01 13:00:00',
                    'fee'           => [
                        'amount'    => 15,
                        'currency'  => 'USD',
                    ],
                ],
                'next_payable'      => [
                    'time'              => '2021-09-01 13:00:00',
                    'fee'               => [
                        'amount'            => 15,
                        'currency'          => 'USD',
                    ],
                ],
                'failed_payments'   => 0,
            ];

        }

        public function cancel_subscription($params=[])
        {
            $sub_id = $params["identifier"];

            /*
             * From here you can send an API command to the payment service provider and, based on the response you receive, report the subscription cancellation.
             */
            $request = 'CRITICAL ERROR';

            if($request == 'CRITICAL ERROR')
            {
                $this->error = 'Subscription cannot be cancelled.';
                return false;
            }
            return true;
        }

        /*
         * If your payment service provider does not support it, you can remove the function.
         */
        public function change_subscription_fee($params=[],$value = 0,$currency=0)
        {
            $sub_id     = $params["identifier"];

            $request_fields    = [
                'sub_id'        => $sub_id,
                'amount'        => $value,
                'currency'      => $this->currency($currency),
            ];

            $request            = 'failed';

            if($request == 'failed')
            {
                $this->error = 'The subscription fee cannot change.';
                return false;
            }

            return true;
        }

        /*
         * If your payment service provider does not support it, you can remove the function.
         */
        public function capture_subscription($params=[])
        {
            $sub_id     = $params["identifier"];
            $amount     = $params["next_payable_fee"];
            $curr       = $params["currency"];


            $request_fields        = [
                'note'          => 'pay the remaining balance',
                'currency'      => $this->currency($curr),
                'amount'        => $amount,
            ];

            $request            = "failed";

            if($request == "failed")
            {
                $this->error = 'Payment cannot be captured.';
                return false;
            }


            return true;
        }

        /*
         * If your payment service provider does not support it, you can remove the function.
         */
        public function refund($checkout=[])
        {
            $custom_id      = $checkout["id"];
            $api_key        = $this->config["settings"]["example1"] ?? 'N/A';
            $secret_key     = $this->config["settings"]["example2"] ?? 'N/A';
            $amount         = $checkout["data"]["total"];
            $currency       = $this->currency($checkout["data"]["currency"]);
            $invoice_id     = $checkout["data"]["invoice_id"] ?? 0;

            $invoice            = Invoices::get($invoice_id);
            $method_msg         = $invoice["pmethod_msg"] ?? [];
            $transaction_id     = $method_msg["Transaction ID"] ?? false;



            $force_curr     = $this->config["settings"]["force_convert_to"] ?? 0;
            if($force_curr > 0)
            {
                $amount         = Money::exChange($amount,$currency,$force_curr);
                $currency       = $this->currency($force_curr);
            }

            // Here we are making an API call.
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, "api.sample.com/refund/".$transaction_id);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'APIKEY: '.$api_key,
                'SECRET: '.$secret_key,
                'Content-Type: application/json',
            ));
            $result = curl_exec($curl);
            if(curl_errno($curl))
            {
                $result      = false;
                $this->error = curl_error($curl);
            }
            $result             = json_decode($result,true);

            if($result && $result['status'] == 'OK') $result = true;
            else
            {
                $this->error = $result['message'] ?? 'something went wrong';
                $result = false;
            }

            return $result;
        }

    }