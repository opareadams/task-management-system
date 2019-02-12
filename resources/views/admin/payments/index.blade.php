@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">{{ $pageTitle }}</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.1.1/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <a href="{{ route('admin.payments.create') }}" class="btn btn-outline btn-success btn-sm">@lang('modules.payments.addPayment') <i class="fa fa-plus" aria-hidden="true"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            {!! Form::open(['id'=>'importExcel','class'=>'ajax-form','method'=>'POST']) !!}
                            <div class="form-group">
                                <div class="col-md-12 text-right">
                                    <div class="checkbox checkbox-info">
                                        <input id="calculate-task-progress" name="currency_character" value="true"
                                               type="checkbox">
                                        <label for="calculate-task-progress">@lang('modules.payments.firstCharacter')</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                        <div class="form-control" data-trigger="fileinput">
                                            <i class="glyphicon glyphicon-file fileinput-exists"></i>
                                            <span class="fileinput-filename"></span>
                                        </div>
                                        <span class="input-group-addon btn btn-default btn-file">
                                            <span class="fileinput-new"><i class="fa fa-file-excel-o text-success"></i> @lang('modules.payments.import')</span>
                                                <span class="fileinput-exists">@lang('app.change')</span>
                                                <input type="file" name="import_file" id="import_file">
                                                </span>
                                        <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">@lang('app.remove')</a>
                                        <a href="javascript:;" id="import-excel" class="input-group-addon btn btn-success fileinput-exists text-white" data-dismiss="fileinput">@lang('app.submit')</a>
                                    </div>

                                    <a href="{{ route('admin.payments.downloadSample') }}" class="btn btn-success"><i class="fa fa-download"></i> @lang('app.sampleFile')</a>

                                </div>
                            </div>

                            {!! Form::close() !!}

                        </div>

                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover toggle-circle default footable-loaded footable" id="invoice-table">
                        <thead>
                        <tr>
                            <th>@lang('app.id')</th>
                            <th>@lang('modules.invoices.amount')</th>
                            <th>@lang('modules.payments.paidOn')</th>
                            <th>@lang('app.status')</th>
                            <th>@lang('app.action')</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- .row -->

@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.1.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.1.1/js/responsive.bootstrap.min.js"></script>
<script>
    $(function() {
        var table = $('#invoice-table').dataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: '{!! route('admin.payments.data') !!}',
            "order": [[ 0, "desc" ]],
            language: {
                "url": "<?php echo __("app.datatable") ?>"
            },
            "fnDrawCallback": function( oSettings ) {
                $("body").tooltip({
                    selector: '[data-toggle="tooltip"]'
                });
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'amount', name: 'amount' },
                { data: 'paid_on', name: 'paid_on' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', width: '10%' }
            ]
        });

        $('body').on('click', '.sa-params', function(){
            var id = $(this).data('payment-id');
            swal({
                title: "Are you sure?",
                text: "You will not be able to recover the deleted payment record!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel please!",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {

                    var url = "{{ route('admin.payments.destroy',':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                            url: url,
                            data: {'_token': token, '_method': 'DELETE'},
                        success: function (response) {
                            if (response.status == "success") {
                                $.unblockUI();
//                                    swal("Deleted!", response.message, "success");
                                table._fnDraw();
                            }
                        }
                    });
                }
            });
        });

        $('#import-excel').click(function () {
            $.easyAjax({
                url: '{{route('admin.payments.importExcel')}}',
                container: '#importExcel',
                type: "POST",
                redirect: true,
                file: (document.getElementById("import_file").files.length == 0) ? false : true
            })
        });


    });

</script>
@endpush