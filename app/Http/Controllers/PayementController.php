<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayementController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }






    public function notify(Request $request)
    {
        //
        $cinetPayCheckUrl = "https://api-checkout.cinetpay.com/v2/payment/check";
        $apiKey = "166216141561f79a8b1c95b8.13514462";
        $siteId = "273954";
        $transaction = Transaction::where('transaction_id', $request->cpm_trans_id)->first();
        if ($transaction->status == "SUCCESS") {
            # code...
            return response('SUCCESS');
        }

        $checkData = array();
        $checkData['apikey'] = $apiKey;
        $checkData['site_id'] = $siteId;
        $checkData['transaction_id'] = $request->cpm_trans_id;
        $payementCheck = Http::post($cinetPayCheckUrl, $checkData)->json();

        if ($payementCheck['code'] == '00') {
            # code...
            if ($transaction->plateforme == "rafset-funding.org") {
                # code...
                $url = 'https://rafset-funding.org/wp-json/wc/v3/orders/' . $transaction->transaction_id . '?consumer_key=ck_eb811745ac3eb1deb54e2bddc05041f2278b5f63&consumer_secret=cs_5964b9e02746d5f0ed5a975f0fab516718dc1f92';
                $data = [
                    'status' => 'processing'
                ];
                $response = Http::put($url, $data);
                if ($response->successful()) {
                    # code...
                    $transaction->status = "SUCCESS";
                    $transaction->save();
                }
            }
        } else {
            # code...

            $transaction->status = "ERROR";
            $transaction->save();
            $data = [
                'status' => 'failed'
            ];;
        }

        return response('SUCCESS');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */



    public function payement(Request $request)
    {
        $apiKey = "166216141561f79a8b1c95b8.13514462";
        $siteId = "273954";
        $cinetPayUrl = 'https://api-checkout.cinetpay.com/v2/payment';
        $cinetPayReturnUrl = $request->returnUrl;
        $cinetPayNotifyUrl = 'https://TONAPI.com/api/notify';
        $now = new DateTime();
        $payementId = $request->orderId;
        $cinetPayData = array();
        $cinetPayData['amount'] = $request->amount;
        $cinetPayData['apikey'] = $apiKey;
        $cinetPayData['site_id'] = $siteId;
        $cinetPayData['currency'] = "XOF";
        $cinetPayData['transaction_id'] = $payementId;
        $cinetPayData['description'] = "ACHATS SUR rafset-funding.org";
        $cinetPayData['return_url'] = $cinetPayReturnUrl;
        $cinetPayData['notify_url'] = $cinetPayNotifyUrl;
        $cinetPayData['customer_name'] = $request->customer_name;
        $cinetPayData['alternative_currency'] = "USD";
        $cinetPayData['customer_email'] = $request->customer_email;
        $cinetPayData['customer_address'] = $request->customer_address;
        $cinetPayData['customer_city'] = $request->customer_city;
        $cinetPayData['customer_country'] = $request->customer_country;
        $cinetPayData['customer_state'] = $request->customer_state;
        $cinetPayData['customer_zip_code'] = $request->customer_zip_code;
        $cinetPayData['customer_phone_number'] = $request->customer_phone_number;
        $cinetPayData['customer_surname'] = $request->customer_surname;

        $cinetPayResponse = Http::post($cinetPayUrl, $cinetPayData)->json();
        $response = array();
        if ($cinetPayResponse['code'] == '201') {
            # code...0
            $transaction = new Transaction();
            $transaction->amount = $request->amount;
            $transaction->currency = "USD";
            $transaction->type = "RAFSET FUNDING";
            $transaction->description = "ACHAT SUR rafset-funding.org";
            $transaction->user_id = $request->userId;
            $transaction->transaction_id = $payementId;
            $transaction->plateforme = "rafset-funding.org";
            $transaction->payement_token = $cinetPayResponse['data']['payment_token'];
            $transaction->save();

            $response['status'] = 'SUCCESS';
            $response['transaction_id'] = $payementId;
            $response['url'] = $cinetPayResponse['data']['payment_url'];
            return redirect($cinetPayResponse['data']['payment_url']);
        } else {


            $response['status'] = 'ERROR';
            $response['message'] = $cinetPayResponse['description'];
            return $response;
        }

        // Appel a CinetPay


    }
}
