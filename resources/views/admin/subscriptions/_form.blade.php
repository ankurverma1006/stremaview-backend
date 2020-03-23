
<form class="form-horizontal" action="{{  Setting::get('admin_delete_control') == YES ? '#' : route('admin.subscriptions.save')  }}" method="POST" enctype="multipart/form-data" role="form">

    <input type="hidden" name="subscription_id" value="{{ $subscription_details->id }}">

    <div class="box-body">

        <div class="form-group">
            <label for="title" class="col-sm-2 control-label">*{{ tr('title') }}</label>

            <div class="col-sm-10">
                <input type="text" required name="title" class="form-control" id="title" value="{{ isset($subscription_details) ? $subscription_details->title : old('title') }}" placeholder="{{ tr('title') }}" pattern = "[a-zA-Z,0-9\s\-\.]{2,100}">
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="col-sm-2 control-label">*{{ tr('description') }}</label>

            <div class="col-sm-10">
                <textarea class="form-control" name="description" required placeholder="{{ tr('description') }}">{{ isset($subscription_details) ? $subscription_details->description : old('description') }}</textarea>
            </div>

        </div>

        <div class="form-group">
            <label for="plan" class="col-sm-2 control-label">*{{ tr('no_of_months') }}</label>

            <div class="col-sm-10">
                <input type="number" min="1" max="12" pattern="[0-9][0-2]{2}"  required name="plan" class="form-control" id="plan" value="{{ isset($subscription_details) ? $subscription_details->plan : old('plan') }}" title="{{ tr('please_enter_plan_month') }}" placeholder="{{ tr('no_of_months') }}">
            </div>
        </div>
        
        <div class="form-group">
            <label for="amount" class="col-sm-2 control-label">*{{ tr('no_of_account') }}</label>

            <div class="col-sm-10">
                <input type="text" required name="no_of_account" class="form-control" id="manage_account_count" placeholder="{{ tr('manage_account_count') }}" pattern="[0-9]{1,}" value="{{ $subscription_details->no_of_account }}">
            </div>
        </div>

        <div class="form-group">
            <label for="amount" class="col-sm-2 control-label">*{{ tr('amount') }}</label>

            <div class="col-sm-10">
                <input type="number" required value="{{ isset($subscription_details) ? $subscription_details->amount : old('amount') }}" name="amount" class="form-control" id="amount" placeholder="{{ tr('amount') }}" step="any">
            </div>
        </div>

    </div>

    <div class="box-footer">
        <button type="reset" class="btn btn-danger">{{  tr('cancel')  }}</button>
        
        <button type="submit" class="btn btn-success pull-right" @if(Setting::get('admin_delete_control') == YES) disabled @endif) >{{  tr('submit')  }}</button>        
    </div>
</form>