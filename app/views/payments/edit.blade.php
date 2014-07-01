@extends('header')


@section('onReady')
	$('input#name').focus();
@stop

@section('content')

	
	{{ Former::open($url)->addClass('col-md-10 col-md-offset-1 warn-on-exit')->method($method)->rules(array(
		'client' => 'required',
		'invoice' => 'required',		
		'amount' => 'required',		
	)); }}
	
	<div class="row">
		<div class="col-md-8">

			{{ Former::select('client')->addOption('', '')->addGroupClass('client-select') }}
			{{ Former::select('invoice')->addOption('', '')->addGroupClass('invoice-select') }}
			{{ Former::text('amount') }}
			{{ Former::select('payment_type_id')->addOption('','')
				->fromQuery($paymentTypes, 'name', 'id') }}			
			{{ Former::text('payment_date')->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT))->append('<i class="glyphicon glyphicon-calendar"></i>') }}
			{{ Former::text('transaction_reference') }}
			{{-- Former::select('currency_id')->addOption('','')
				->fromQuery($currencies, 'name', 'id')->select(Session::get(SESSION_CURRENCY, DEFAULT_CURRENCY)) --}}

		</div>
		<div class="col-md-6">

		</div>
	</div>

	<center class="buttons">
        {{ Button::lg_primary_submit_success(trans('texts.save'))->append_with_icon('floppy-disk') }}
         {{ Button::lg_default_link('payments/' . ($payment ? $payment->public_id : ''), trans('texts.cancel'))->append_with_icon('remove-circle'); }}
	</center>

	{{ Former::close() }}

	<script type="text/javascript">

	var invoices = {{ $invoices }};
	var clients = {{ $clients }};

	$(function() {

		populateInvoiceComboboxes({{ $clientPublicId }}, {{ $invoicePublicId }});

		$('#payment_type_id').combobox();
		$('#payment_date').datepicker('update', new Date());

	});

	</script>

@stop
