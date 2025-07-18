<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('AccountController@postDeposit'), 'method' => 'post', 'id' => 'deposit_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'account.deposit' )</h4>
    </div>

    <div class="modal-body">
            <div class="form-group">
                <strong>@lang('account.selected_account')</strong>: 
                {{$account->name}}
                {!! Form::hidden('account_id', $account->id) !!}
            </div>

            <div class="form-group">
                {!! Form::label('account_id', __( 'lang_v1.deposit_to' ) .":") !!}
                {!! Form::select('account_id', $from_accounts, $account->id, ['class' => 'form-control' ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('amount', __( 'sale.amount' ) .":*") !!}
                {!! Form::text('amount', 0, ['class' => 'form-control input_number', 'required','placeholder' => __( 'sale.amount' ) ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('from_account', __( 'account.deposit_from' ) .":") !!}
                {!! Form::select('from_account', $from_accounts, null, ['class' => 'form-control', 'placeholder' => __('messages.please_select') ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('operation_date', __( 'messages.date' ) .":*") !!}
                <div class="input-group date">
                  {!! Form::text('operation_date', null, ['class' => 'form-control', 'required','placeholder' => __( 'messages.date' ), 'id'=>'od_datetimepicker' ]) !!}
                  <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                  </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]) !!}
            </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.submit' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
  $(document).ready( function(){
    $('#od_datetimepicker').datetimepicker({
      format: moment_date_format + ' ' + moment_time_format
    });
  });
</script>