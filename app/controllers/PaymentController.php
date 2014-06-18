<?php

use ninja\repositories\PaymentRepository;
use ninja\repositories\InvoiceRepository;

class PaymentController extends \BaseController 
{
    protected $creditRepo;

    public function __construct(PaymentRepository $paymentRepo, InvoiceRepository $invoiceRepo)
    {
        parent::__construct();

        $this->paymentRepo = $paymentRepo;
        $this->invoiceRepo = $invoiceRepo;
    }   

	public function index()
	{
        return View::make('list', array(
            'entityType'=>ENTITY_PAYMENT, 
            'title' => trans('texts.payments'),
            'columns'=>Utils::trans(['checkbox', 'invoice', 'client', 'transaction_reference', 'method', 'payment_amount', 'payment_date', 'action'])
        ));
	}

	public function getDatatable($clientPublicId = null)
    {
        $payments = $this->paymentRepo->find($clientPublicId, Input::get('sSearch'));
        $table = Datatable::query($payments);        

        if (!$clientPublicId) {
            $table->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; });
        }

        $table->addColumn('invoice_number', function($model) { return $model->invoice_public_id ? link_to('invoices/' . $model->invoice_public_id . '/edit', $model->invoice_number) : ''; });

        if (!$clientPublicId) {
            $table->addColumn('client_name', function($model) { return link_to('clients/' . $model->client_public_id, Utils::getClientDisplayName($model)); });
        }        

        $table->addColumn('transaction_reference', function($model) { return $model->transaction_reference ? $model->transaction_reference : '<i>Manual entry</i>'; })
              ->addColumn('payment_type', function($model) { return $model->payment_type ? $model->payment_type : ($model->account_gateway_id ? '<i>Online payment</i>' : ''); });

        return $table->addColumn('amount', function($model) { return Utils::formatMoney($model->amount, $model->currency_id); })
    	    ->addColumn('payment_date', function($model) { return Utils::dateToString($model->payment_date); })
            ->addColumn('dropdown', function($model) 
            { 
                return '<div class="btn-group tr-action" style="visibility:hidden;">
                            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
                            '.trans('texts.select').' <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                            <li><a href="javascript:archiveEntity(' . $model->public_id. ')">'.trans('texts.archive_payment').'</a></li>
                            <li><a href="javascript:deleteEntity(' . $model->public_id. ')">'.trans('texts.delete_payment').'</a></li>                          
                          </ul>
                        </div>';
            })         
    	    ->make();
    }


    public function create($clientPublicId = 0, $invoicePublicId = 0)
    {       
        $data = array(
            'clientPublicId' => Input::old('client') ? Input::old('client') : $clientPublicId,
            'invoicePublicId' => Input::old('invoice') ? Input::old('invoice') : $invoicePublicId,
            'invoice' => null,
            'invoices' => Invoice::scope()->where('is_recurring', '=', false)->where('is_quote', '=', false)
                            ->with('client', 'invoice_status')->orderBy('invoice_number')->get(),
            'payment' => null, 
            'method' => 'POST', 
            'url' => "payments", 
            'title' => trans('texts.new_payment'),
            //'currencies' => Currency::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
            'paymentTypes' => PaymentType::remember(DEFAULT_QUERY_CACHE)->orderBy('id')->get(),
            'clients' => Client::scope()->with('contacts')->orderBy('name')->get());

        return View::make('payments.edit', $data);
    }

    public function edit($publicId)
    {
        $payment = Payment::scope($publicId)->firstOrFail();        
        $payment->payment_date = Utils::fromSqlDate($payment->payment_date);

        $data = array(
            'client' => null,
            'invoice' => null,
            'invoices' => Invoice::scope()->where('is_recurring', '=', false)->where('is_quote', '=', false)
                            ->with('client', 'invoice_status')->orderBy('invoice_number')->get(),
            'payment' => $payment, 
            'method' => 'PUT', 
            'url' => 'payments/' . $publicId, 
            'title' => 'Edit Payment',
            //'currencies' => Currency::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
            'paymentTypes' => PaymentType::remember(DEFAULT_QUERY_CACHE)->orderBy('id')->get(),
            'clients' => Client::scope()->with('contacts')->orderBy('name')->get());
        return View::make('payments.edit', $data);
    }

    private function createGateway($accountGateway)
    {
        $gateway = Omnipay::create($accountGateway->gateway->provider); 
        $config = json_decode($accountGateway->config);
        
        /*
        $gateway->setSolutionType("Sole");
        $gateway->setLandingPage("Billing");
        */
        
        foreach ($config as $key => $val)
        {
            if (!$val)
            {
                continue;
            }

            $function = "set" . ucfirst($key);
            $gateway->$function($val);
        }

        /*
        if (!Utils::isProd())
        {
            $gateway->setTestMode(true);   
        }        
        */

        return $gateway;        
    }

    private function getPaymentDetails($invoice, $input = null)
    {
        $key = $invoice->invoice_number . '_details';
		$gateway = $invoice->client->account->account_gateways[0]->gateway;
        $paymentLibrary = $gateway->paymentlibrary;

        if ($input && $paymentLibrary->id == PAYMENT_LIBRARY_OMNIPAY)
        {
            $data = [
                'firstName' => $input['first_name'],
                'lastName' => $input['last_name'],
                'number' => $input['card_number'],
                'expiryMonth' => $input['expiration_month'],
                'expiryYear' => $input['expiration_year'],
                'cvv' => $input['cvv'],
                'billingAddress1' => $input['address1'],
                'billingAddress2' => $input['address2'],
                'billingCity' => $input['city'],
                'billingState' => $input['state'],
                'billingPostcode' => $input['postal_code'],
                'shippingAddress1' => $input['address1'],
                'shippingAddress2' => $input['address2'],
                'shippingCity' => $input['city'],
                'shippingState' => $input['state'],
                'shippingPostcode' => $input['postal_code'],
            ];

            Session::put($key, $data);
        }
		else if ($input && $paymentLibrary->id == PAYMENT_LIBRARY_PHP_PAYMENTS)
        {
        	$input = Input::all();
            $data = [
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'cc_number' => $input['card_number'],
                'cc_exp' => $input['expiration_month'].$input['expiration_year'],
                'cc_code' => $input['cvv'],
                'street' => $input['address1'],
                'street2' => $input['address2'],
                'city' => $input['city'],
                'state' => $input['state'],
                'postal_code' => $input['postal_code'],
                'amt' => $invoice->amount,
                'ship_to_street' => $input['address1'],
                'ship_to_city' => $input['city'],
                'ship_to_state' => $input['state'],
                'ship_to_postal_code' => $input['postal_code'],
            	'currency_code' => $invoice->client->currency->code,
            ];
			
			switch($gateway->id)
			{
				case GATEWAY_BEANSTREAM:
	            	$data['phone'] = $input['phone'];
	            	$data['email'] = $input['email'];
	            	$data['country'] = $input['country'];
					$data['ship_to_country'] = $input['country'];
					break;
				case GATEWAY_BRAINTREE:
					$data['ship_to_state'] = 'Ohio'; //$input['state'];
					break;
			}
			
			if(strlen($data['cc_exp']) == 5)
			{
				$data['cc_exp'] = '0'.$data['cc_exp'];
			}

            Session::put($key, $data);
			return $data;
        }
        else if (Session::get($key))
        {
            $data = Session::get($key);
        }
        else
        {
            $data = [];
        }

		if($paymentLibrary->id == PAYMENT_LIBRARY_OMNIPAY)
		{
	        $card = new CreditCard($data);
	        
	        return [
	            'amount' => $invoice->amount,
	            'card' => $card,
	            'currency' => $invoice->client->currency->code,
	            'returnUrl' => URL::to('complete'),
	            'cancelUrl' => URL::to('/')
	        ];
		}
		else 
		{
			return $data;
		}
    }
    
    
    /** HÄR SKALL DET HÄMTAS UT BILDER!!!!! **/
    public function show_payment($invitationKey)
    {
        // For PayPal Express we redirect straight to their site
        $invitation = Invitation::with('invoice.client.account', 'invoice.client.account.account_gateways.gateway')->where('invitation_key', '=', $invitationKey)->firstOrFail();
        $account = $invitation->invoice->client->account;
        
        if ($account->isGatewayConfigured(GATEWAY_PAYPAL_EXPRESS))
        {
            if (Session::has('error'))
            {                
                Session::reflash();
                return Redirect::to('view/' . $invitationKey);
            }
            else
            {
                return self::do_payment($invitationKey, false);
            }            
        }  
                
        $invitation = Invitation::with('invoice.invoice_items', 'invoice.client.currency', 'invoice.client.account.account_gateways.gateway')->where('invitation_key', '=', $invitationKey)->firstOrFail();
        $invoice = $invitation->invoice;         
        $client = $invoice->client;    
        $gateway = $invoice->client->account->account_gateways[0]->gateway;
        $paymentLibrary = $gateway->paymentlibrary;
        
        $mask = $invoice->client->account->account_gateways[0]->accepted_credit_cards;
        $acceptedCreditCardTypes = Utils::getCreditcardTypes($mask);

        $data = [
            'showBreadcrumbs' => false,
            'hideHeader' => true,
            'invitationKey' => $invitationKey,
            'invoice' => $invoice,
            'client' => $client,
            'contact' => $invitation->contact,
            'paymentLibrary' => $paymentLibrary,
            'gateway' => $gateway,
            'acceptedCreditCardTypes' => $acceptedCreditCardTypes,     
			'countries' => Country::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),     
        ];

        return View::make('payments.payment', $data);
    }

    public function do_payment($invitationKey, $onSite = true)
    {
        $rules = array(
            'first_name' => 'required',
            'last_name' => 'required',
            'card_number' => 'required',
            'expiration_month' => 'required',
            'expiration_year' => 'required',
            'cvv' => 'required',
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'postal_code' => 'required',
        );

        if ($onSite)
        {
            $validator = Validator::make(Input::all(), $rules);

            if ($validator->fails()) 
            {
                return Redirect::to('payment/' . $invitationKey)
                    ->withErrors($validator);
            } 
        }

        $invitation = Invitation::with('invoice.invoice_items', 'invoice.client.currency', 'invoice.client.account.account_gateways.gateway')->where('invitation_key', '=', $invitationKey)->firstOrFail();
        $invoice = $invitation->invoice;        
        $accountGateway = $invoice->client->account->account_gateways[0];
        $paymentLibrary = $accountGateway->gateway->paymentlibrary;

        if ($onSite)
        {
            $client = $invoice->client;
            $client->address1 = trim(Input::get('address1'));
            $client->address2 = trim(Input::get('address2'));
            $client->city = trim(Input::get('city'));
            $client->state = trim(Input::get('state'));
            $client->postal_code = trim(Input::get('postal_code'));
            $client->save();
        }
		
        try
        {
        	if($paymentLibrary->id == PAYMENT_LIBRARY_OMNIPAY)
			{
	        	$gateway = self::createGateway($accountGateway);
	            $details = self::getPaymentDetails($invoice, Input::all());
	            $response = $gateway->purchase($details)->send();           
	            $ref = $response->getTransactionReference();
	
	            if (!$ref)
	            {
	                Session::flash('error', $response->getMessage());  
	                return Redirect::to('payment/' . $invitationKey)
	                    ->withInput();
	            }

	            if ($response->isSuccessful())
	            {
	                $payment = self::createPayment($invitation, $ref);
		
	                Session::flash('message', trans('texts.applied_payment'));  
	                return Redirect::to('view/' . $payment->invitation->invitation_key);                                    
	            }
	            else if ($response->isRedirect()) 
	            {
	                $invitation->transaction_reference = $ref;
	                $invitation->save();
	
	                $response->redirect();          
	            }
	            else                    
	            {
	                Session::flash('error', $response->getMessage());  
	                return Utils::fatalError('Sorry, there was an error processing your payment. Please try again later.<p>', $response->getMessage());
	            }
            }
			else if ($paymentLibrary->id == PAYMENT_LIBRARY_PHP_PAYMENTS)
	        {
	        	$gateway = $accountGateway->gateway;
	        	$provider = $gateway->provider;
				$p = new PHP_Payments(array('mode' => 'test'));
				
				$config = Payment_Utility::load('config', 'drivers/'.$provider);
				
				switch($gateway->id)
				{
					case GATEWAY_BEANSTREAM:
						$config['delay_charge'] = FALSE;
						$config['bill_outstanding'] = TRUE;
						break;
					case GATEWAY_AMAZON:
			            $config['return_url'] = URL::to('complete');
			            $config['abandon_url'] = URL::to('/');
						$config['immediate_return'] = 0;
						$config['process_immediate'] = 1;
						$config['ipn_url'] = URL::to('ipn');
						$config['collect_shipping_address'] = false;
						break;
				}
				
	            $details = self::getPaymentDetails($invoice, Input::all());
				
				$response = $p->oneoff_payment($provider, $details, $config);

	            if (strtolower($response->status) == 'success')
	            {
	                $payment = self::createPayment($invitation, $response->response_message);
		
	                Session::flash('message', trans('texts.applied_payment'));  
	                return Redirect::to('view/' . $payment->invitation->invitation_key);                                    
	            }
	            else                    
	            {
	                Session::flash('error', $response->response_message);  
	                return Utils::fatalError('Sorry, there was an error processing your payment. Please try again later.<p>', $response->response_message);
	            }
	        }
        } 
        catch (\Exception $e) 
        {
            $errorMessage = trans('texts.payment_error');
            Session::flash('error', $errorMessage);  
            Utils::logError($e->getMessage());
            return Redirect::to('payment/' . $invitationKey)
                ->withInput();
        }
    }

    private function createPayment($invitation, $ref, $payerId = null)
    {
        $invoice = $invitation->invoice;
        $accountGateway = $invoice->client->account->account_gateways[0];

        if ($invoice->account->account_key == NINJA_ACCOUNT_KEY)
        {
            $account = Account::find($invoice->client->public_id);
            $account->pro_plan_paid = date_create()->format('Y-m-d');
            $account->save();
        }
        
        if ($invoice->is_quote)
        {
            $invoice = $this->invoiceRepo->cloneInvoice($invoice, $invoice->id);
        }
        
        $payment = Payment::createNew($invitation);
        $payment->invitation_id = $invitation->id;
        $payment->account_gateway_id = $accountGateway->id;
        $payment->invoice_id = $invoice->id;
        $payment->amount = $invoice->amount;            
        $payment->client_id = $invoice->client_id;
        $payment->contact_id = $invitation->contact_id;
        $payment->transaction_reference = $ref;
        $payment->payment_date = date_create()->format('Y-m-d');
        
        if ($payerId)
        {
            $payment->payer_id = $payerId;                
        }
        
        $payment->save();
        
        Event::fire('invoice.paid', $payment);
        
        return $payment;
    }

    public function offsite_payment()
    {
        $payerId = Request::query('PayerID');
        $token = Request::query('token');               

        $invitation = Invitation::with('invoice.client.currency', 'invoice.client.account.account_gateways.gateway')->where('transaction_reference', '=', $token)->firstOrFail();
        $invoice = $invitation->invoice;

        $accountGateway = $invoice->client->account->account_gateways[0];
        $gateway = self::createGateway($accountGateway);

        try
        {
            $details = self::getPaymentDetails($invoice);
            $response = $gateway->completePurchase($details)->send();
            $ref = $response->getTransactionReference();

            if ($response->isSuccessful())
            {
                $payment = self::createPayment($invitation, $ref, $payerId);                

                Session::flash('message', trans('texts.applied_payment'));  
                return Redirect::to('view/' . $invitation->invitation_key);                
            }
            else
            {
                $errorMessage = trans('texts.payment_error') . "\n\n" . $response->getMessage();
                Session::flash('error', $errorMessage);  
                Utils::logError($errorMessage);
                return Redirect::to('view/' . $invitation->invitation_key);   
            }
        } 
        catch (\Exception $e) 
        {
            $errorMessage = trans('texts.payment_error');
            Session::flash('error', $errorMessage);  
            Utils::logError($errorMessage . "\n\n" . $e->getMessage());
            return Redirect::to('view/' . $invitation->invitation_key);
        }
    }


    public function store()
    {
        return $this->save();
    }

    public function update($publicId)
    {
        return $this->save($publicId);
    }

    private function save($publicId = null)
    {
        if ($errors = $this->paymentRepo->getErrors(Input::all())) 
        {
            $url = $publicId ? 'payments/' . $publicId . '/edit' : 'payments/create';
            return Redirect::to($url)
                ->withErrors($errors)
                ->withInput();
        } 
        else 
        {            
            $this->paymentRepo->save($publicId, Input::all());

            Session::flash('message', trans('texts.created_payment'));
            return Redirect::to('clients/' . Input::get('client'));
        }
    }

    public function bulk()
    {
        $action = Input::get('action');
        $ids = Input::get('id') ? Input::get('id') : Input::get('ids');
        $count = $this->paymentRepo->bulk($ids, $action);

        if ($count > 0)
        {
            $message = Utils::pluralize($action.'d_payment', $count);            
            Session::flash('message', $message);
        }
        
        return Redirect::to('payments');
    }
}