@extends('layouts.member-app')

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
                <li><a href="{{ route('member.dashboard') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('member.attendances.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('modules.attendance.markAttendance')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/switchery/dist/switchery.min.css') }}">

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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">@lang('app.menu.attendance') @lang('app.date')</label>
                            <input type="text" class="form-control" name="attendance_date" id="attendance_date" value="{{ Carbon\Carbon::today()->format('m/d/Y') }}">
                        </div>
                    </div>
                </div>

                <table  id="attendance-table">

                </table>
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

<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/switchery/dist/switchery.min.js') }}"></script>

<script>

    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });

    jQuery('#attendance_date').datepicker({
        autoclose: true,
        todayHighlight: true,
        endDate: '+0d'
    }).on('changeDate', function (ev) {
        showTable();
    });

    var table;

    function showTable(){
        table = $('#attendance-table').dataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{!! route('member.attendances.data') !!}",
                data: function (d) {
                    d.date = $('#attendance_date').val();
                }
            },
            "bStateSave": true,
            language: {
                "url": "<?php echo __("app.datatable") ?>"
            },
            "aoColumns": [
                {'sClass': 'center', "bSortable": false}
            ],
            "fnDrawCallback": function (oSettings) {
                $(oSettings.nTHead).hide();
                $('.a-timepicker').timepicker({
                    minuteStep: 1
                });
                $('.b-timepicker').timepicker({
                    minuteStep: 1,
                    defaultTime: false
                });

                $('#attendance-table_wrapper').removeClass( 'form-inline' );

                // Switchery
                var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
                $('.js-switch').each(function() {
                    new Switchery($(this)[0], $(this).data());

                });

            },
            "destroy" : true
        });
    }

    $('#attendance-table').on('click', '.save-attendance', function () {
        var userId = $(this).data('user-id');
        var clockInTime = $('#clock-in-'+userId).val();
        var clockInIp = $('#clock-in-ip-'+userId).val();
        var clockOutTime = $('#clock-out-'+userId).val();
        var clockOutIp = $('#clock-out-ip-'+userId).val();
        var workingFrom = $('#working-from-'+userId).val();
        var date = $('#attendance_date').val();

        var late = 'no';
        if($('#late-'+userId).is(':checked')){
            late = 'yes';
        }
        var halfDay = 'no';
        if($('#halfday-'+userId).is(':checked')){
            halfDay = 'yes';
        }
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: '{{route('member.attendances.storeAttendance')}}',
            type: "POST",
            container: '#attendance-container-'+userId,
            data: {
                user_id: userId,
                clock_in_time: clockInTime,
                clock_in_ip: clockInIp,
                clock_out_time: clockOutTime,
                clock_out_ip: clockOutIp,
                late: late,
                half_day: halfDay,
                working_from: workingFrom,
                date: date,
                _token: token
            },
            success: function (response) {
                if(response.status == 'success'){
                    showTable();
                }
            }
        })
    })

    showTable();

</script>
@endpush