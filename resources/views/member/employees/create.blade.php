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
                <li><a href="{{ route('member.employees.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('app.addNew')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">

            <div class="panel panel-inverse">
                <div class="panel-heading"> @lang('modules.employees.createTitle')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'createEmployee','class'=>'ajax-form','method'=>'POST']) !!}
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6 ">
                                        <div class="form-group">
                                            <label>@lang('modules.employees.employeeName')</label>
                                            <input type="text" name="name" id="name" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('modules.employees.employeeEmail')</label>
                                            <input type="email" name="email" id="email" class="form-control">
                                            <span class="help-block">@lang('modules.employees.emailNote')</span>
                                        </div>
                                    </div>
                                    <!--/span-->
                                </div>
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('modules.employees.employeePassword')</label>
                                            <input type="password" style="display: none">
                                            <input type="password" name="password" id="password" class="form-control">
                                            <span class="help-block"> @lang('modules.employees.passwordNote') </span>
                                        </div>
                                    </div>
                                    <!--/span-->

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('app.mobile')</label>
                                            <input type="tel" name="mobile" id="mobile"  class="form-control">
                                        </div>
                                    </div>
                                    <!--/span-->
                                </div>
                                <!--/row-->

                                <div class="row">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label"><i class="fa fa-slack"></i> @lang('modules.employees.slackUsername')</label>
                                            <div class="input-group"> <span class="input-group-addon">@</span>
                                                <input type="text" id="slack_username" name="slack_username" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <!--/span-->

                                </div>
                                <!--/row-->

                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label class="control-label">@lang('app.address')</label>
                                            <textarea name="address"  id="address"  rows="5" class="form-control"></textarea>
                                        </div>
                                    </div>

                                </div>
                                <!--/span-->

                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('modules.employees.jobTitle')</label>
                                            <input type="text" name="job_title" id="job_title" class="form-control" >
                                        </div>
                                    </div>
                                    <!--/span-->

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('modules.employees.hourlyRate')</label>
                                            <input type="number" min="0" name="hourly_rate" id="hourly_rate" class="form-control">
                                        </div>
                                    </div>
                                    <!--/span-->
                                </div>
                                <!--/row-->

                                <div class="row">
                                    @if(isset($fields))
                                        @foreach($fields as $field)
                                            <div class="col-md-6">
                                                <label>{{ ucfirst($field->label) }}</label>
                                                <div class="form-group">
                                                    @if( $field->type == 'text')
                                                        <input type="text" name="custom_fields_data[{{$field->name.'_'.$field->id}}]" class="form-control" placeholder="{{$field->label}}" value="{{$editUser->custom_fields_data['field_'.$field->id] or ''}}">
                                                    @elseif($field->type == 'password')
                                                        <input type="password" name="custom_fields_data[{{$field->name.'_'.$field->id}}]" class="form-control" placeholder="{{$field->label}}" value="{{$editUser->custom_fields_data['field_'.$field->id] or ''}}">
                                                    @elseif($field->type == 'number')
                                                        <input type="number" name="custom_fields_data[{{$field->name.'_'.$field->id}}]" class="form-control" placeholder="{{$field->label}}" value="{{$editUser->custom_fields_data['field_'.$field->id] or ''}}">

                                                    @elseif($field->type == 'textarea')
                                                        <textarea name="custom_fields_data[{{$field->name.'_'.$field->id}}]" class="form-control" id="{{$field->name}}" cols="3">{{$editUser->custom_fields_data['field_'.$field->id] or ''}}</textarea>

                                                    @elseif($field->type == 'radio')
                                                        <div class="radio-list">
                                                            @foreach($field->values as $key=>$value)
                                                            <label class="radio-inline @if($key == 0) p-0 @endif">
                                                                <div class="radio radio-info">
                                                                    <input type="radio" name="custom_fields_data[{{$field->name.'_'.$field->id}}]" id="optionsRadios{{$key.$field->id}}" value="{{$value}}" @if(isset($editUser) && $editUser->custom_fields_data['field_'.$field->id] == $value) checked @elseif($key==0) checked @endif>>
                                                                    <label for="optionsRadios{{$key.$field->id}}">{{$value}}</label>
                                                                </div>
                                                            </label>
                                                            @endforeach
                                                        </div>
                                                    @elseif($field->type == 'select')
                                                        {!! Form::select('custom_fields_data['.$field->name.'_'.$field->id.']',
                                                                $field->values,
                                                                 isset($editUser)?$editUser->custom_fields_data['field_'.$field->id]:'',['class' => 'form-control gender'])
                                                         !!}

                                                    @elseif($field->type == 'checkbox')
                                                        <div class="mt-checkbox-inline">
                                                            @foreach($field->values as $key => $value)
                                                                <label class="mt-checkbox mt-checkbox-outline">
                                                                    <input name="custom_fields_data[{{$field->name.'_'.$field->id}}][]" type="checkbox" value="{{$key}}"> {{$value}}
                                                                    <span></span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    @elseif($field->type == 'date')
                                                        <input type="text" class="form-control date-picker" size="16" name="custom_fields_data[{{$field->name.'_'.$field->id}}]"
                                                                value="{{ isset($editUser->dob)?Carbon\Carbon::parse($editUser->dob)->format('Y-m-d'):Carbon\Carbon::now()->format('m/d/Y')}}">
                                                    @endif
                                                    <div class="form-control-focus"> </div>
                                                    <span class="help-block"></span>

                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                </div>

                            </div>
                            <div class="form-actions">
                                <button type="submit" id="save-form" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.save')</button>
                                <button type="reset" class="btn btn-default">@lang('app.reset')</button>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- .row -->

@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script>

    $(".date-picker").datepicker({
        todayHighlight: true,
        autoclose: true
    });

    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route('member.employees.store')}}',
            container: '#createEmployee',
            type: "POST",
            redirect: true,
            data: $('#createEmployee').serialize()
        })
    });
</script>
@endpush

