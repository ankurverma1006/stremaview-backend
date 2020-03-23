

<form action="{{  Setting::get('admin_delete_control') == YES ? '#' :  route('admin.coupons.save') }}" method="POST" class="form-horizontal" role="form">

	<input type="hidden" name="coupon_id" value="{{ $coupon_details->id }}">

	<div class="box-body">

		<div class="form-group">
			<label for = "title" class="col-sm-2 control-label"> * {{ tr('title') }}</label>

			<div class="col-sm-10">
				<input type="text" name="title" role="title" min="5" max="20" class="form-control" placeholder="{{ tr('enter_coupon_title') }}" value="{{ old('title') ?: $coupon_details->title  }}">
			</div>
		</div> 

		<div class="form-group">
			<label for="coupon_code" class="col-sm-2 control-label"> * {{ tr('coupon_code') }}</label>
			<div class="col-sm-10">
				<input type="text" name="coupon_code" min="5" max="10" class="form-control" placeholder="{{ tr('enter_coupon_code') }}" value="{{ old('coupon_code') ?: $coupon_details->coupon_code }}" title="{{ tr('validation') }}" pattern="[A-Z0-9]{1,10}"><p class="help-block"> {{ tr('note') }} : {{ tr('coupon_code_note') }}</p>
			</div>
		</div>

		@if($coupon_details->id == '')

		<div class="form-group floating-label">
			<label for = "amount_type" class="col-sm-2 control-label"> *
			{{tr('amount_type')}}</label>
			<div class="col-sm-10">
			    <select id ="amount_type" name="amount_type" class="form-control select2">
					<option value="" selected="">{{tr('select_option')}}</option>
					<option value="{{ PERCENTAGE }}">{{tr('percentage_amount')}}</option>
					<option value="{{ ABSOULTE }}">{{tr('absoulte_amount')}}</option>
				</select> 
			</div>
		</div>
		
		@else
		
		<div class="form-group floating-label">		
			<label for = "amount_type" class="col-sm-2 control-label"> * {{ tr('amount_type') }}</label>
			<div class="col-sm-10">
				<select id ="amount_type" name="amount_type" class="form-control select2">
					<option value="{{ PERCENTAGE }}" {{ $coupon_details->amount_type == 0 ?'selected="selected"':'' }}>{{ tr('percentage_amount') }}</option>
					<option value="{{ ABSOULTE }}" {{ $coupon_details->amount_type == 1 ?'selected="selected"':'' }}>{{ tr('absoulte_amount') }}</option>
				</select> 
			</div>
		</div>
		@endif
		<div class="form-group">
			<label for="amount" class="col-sm-2 control-label"> * {{ tr('amount') }}</label>
			<div class="col-sm-10">
				<input type="number" name="amount" min="1" max="5000" step="any" class="form-control" placeholder="{{ tr('amount') }}" value="{{ old('amount') ?: $coupon_details->amount }}">
			</div>
		</div>

		<div class="form-group">
			<label for="expiry_date" class="col-sm-2 control-label"> * {{ tr('expiry_date') }}</label>
			<div class="col-sm-10">
				<input type="text" id="expiry_date" name="expiry_date"  class="form-control" placeholder="{{ tr('expiry_date_coupon') }}" value="{{ old('expiry_date') ?: date('d-m-Y',strtotime($coupon_details->expiry_date)) }}" required readonly>
			</div>
		</div>

		<div class="form-group">
			<label for="no_of_users_limit" class="col-sm-2 control-label"> * {{ tr('no_of_users_limit') }}</label>
			<div class="col-sm-10">
				<input type="text" name="no_of_users_limit" class="form-control" placeholder="{{ tr('no_of_users_limit') }}" value="{{ $coupon_details->no_of_users_limit }}" required title="{{ tr('no_of_users_limit_notes') }}">
			</div>
		</div>

		<div class="form-group">
			<label for="per_users_limit" class="col-sm-2 control-label"> * {{ tr('per_users_limit') }}</label>
			<div class="col-sm-10">
				<input type="text" name="per_users_limit" class="form-control" placeholder="{{ tr('per_users_limit') }}" value="{{ $coupon_details->per_users_limit }}" required title="{{ tr('per_users_limit_notes') }}">
			</div>
		</div>
		
		<div class="form-group">
			<label for = "description" class="col-sm-2 control-label">{{ tr('description') }}</label>
			<div class="col-sm-10">
				<textarea name="description" class="form-control" max="255" style="resize: none;">{{  old('description') ?: $coupon_details->description }}</textarea>
			</div>
		</div>
		
	</div> 

    <div class="box-footer">

        <button type="reset" class="btn btn-danger">{{  tr('cancel') }}</button>        
        
        <button type="submit" class="btn btn-success pull-right" @if(Setting::get('admin_delete_control') == YES) disabled @endif) >{{  tr('submit')  }}</button>        
        
    </div>

</form>