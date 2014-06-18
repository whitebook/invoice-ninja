<?php

use ninja\mailers\ContactMailer as Mailer;
use ninja\repositories\InvoiceRepository;
use ninja\repositories\ClientRepository;
use ninja\repositories\TaxRateRepository;

class QuoteController extends \BaseController {

  protected $mailer;
  protected $invoiceRepo;
  protected $clientRepo;
  protected $taxRateRepo;

  public function __construct(Mailer $mailer, InvoiceRepository $invoiceRepo, ClientRepository $clientRepo, TaxRateRepository $taxRateRepo)
  {
    parent::__construct();

    $this->mailer = $mailer;
    $this->invoiceRepo = $invoiceRepo;
    $this->clientRepo = $clientRepo;
    $this->taxRateRepo = $taxRateRepo;
  } 

  public function index()
  {
    if (!Utils::isPro())
    {
      return Redirect::to('/invoices/create');
    }

    $data = [
      'title' => trans('texts.quotes'),
      'entityType'=>ENTITY_QUOTE, 
      'columns'=>Utils::trans(['checkbox', 'quote_number', 'client', 'quote_date', 'quote_total', 'due_date', 'status', 'action'])
    ];

    if (Invoice::scope()->where('is_recurring', '=', true)->count() > 0)
    {
      $data['secEntityType'] = ENTITY_RECURRING_INVOICE;
      $data['secColumns'] = Utils::trans(['checkbox', 'frequency', 'client', 'start_date', 'end_date', 'quote_total', 'action']);
    }

    return View::make('list', $data);
  }

  public function getDatatable($clientPublicId = null)
  {
    $accountId = Auth::user()->account_id;
    $search = Input::get('sSearch');
    
    return $this->invoiceRepo->getDatatable($accountId, $clientPublicId, ENTITY_QUOTE, $search);  
  }

  public function create($clientPublicId = 0)
  { 
    if (!Utils::isPro())
    {
      return Redirect::to('/invoices/create');
    }

    $client = null;
    $invoiceNumber = Auth::user()->account->getNextInvoiceNumber();
    $account = Account::with('country')->findOrFail(Auth::user()->account_id);

    if ($clientPublicId) 
    {
      $client = Client::scope($clientPublicId)->firstOrFail();
    }

    $data = array(
        'account' => $account,
        'invoice' => null,
        'data' => Input::old('data'), 
        'invoiceNumber' => $invoiceNumber,
        'method' => 'POST', 
        'url' => 'invoices',
        'title' => trans('texts.new_quote'),
        'client' => $client);
    $data = array_merge($data, self::getViewModel());       

    return View::make('invoices.edit', $data);
  }

  private static function getViewModel()
  {
    return [
      'entityType' => ENTITY_QUOTE,
      'account' => Auth::user()->account,
      'products' => Product::scope()->orderBy('id')->get(array('product_key','notes','cost','qty')),
      'countries' => Country::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
      'clients' => Client::scope()->with('contacts', 'country')->orderBy('name')->get(),
      'taxRates' => TaxRate::scope()->orderBy('name')->get(),
      'currencies' => Currency::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
      'sizes' => Size::remember(DEFAULT_QUERY_CACHE)->orderBy('id')->get(),
      'paymentTerms' => PaymentTerm::remember(DEFAULT_QUERY_CACHE)->orderBy('num_days')->get(['name', 'num_days']),
      'industries' => Industry::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),        
      'invoiceDesigns' => InvoiceDesign::remember(DEFAULT_QUERY_CACHE)->orderBy('id')->get(),
      'invoiceLabels' => Auth::user()->account->getInvoiceLabels()
    ];
  }

  public function bulk()
  {
    $action = Input::get('action');
    $ids = Input::get('id') ? Input::get('id') : Input::get('ids');
    $count = $this->invoiceRepo->bulk($ids, $action);

    if ($count > 0)   
    {
      $message = Utils::pluralize("{$action}d_quote", $count);      
      Session::flash('message', $message);
    }

    return Redirect::to('quotes');
  } 
}