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
                <li><a href="{{ route('admin.all-tasks.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('app.addNew')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/summernote/dist/summernote.css') }}">
<style>
    .panel-black .panel-heading a, .panel-inverse .panel-heading a {
        color: unset!important;
    }
</style>
@endpush

@section('content')

    <div class="row">
        <div class="col-md-8">

            <div class="panel panel-inverse">
                <div class="panel-heading"> @lang('modules.tasks.newTask')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'storeTask','class'=>'ajax-form','method'=>'POST']) !!}

                        <div class="form-body">
                            <div class="row">

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label hidden class="control-label">@lang('app.project')</label>
                                        <select class="select2 form-control" data-placeholder="@lang("app.selectProject")" id="project_id" name="project_id">
                                            <option value=""></option>
                                            @foreach($projects as $project)
                                                <option
                                                value="{{ $project->id }}">{{ ucwords($project->project_name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.title')</label>
                                        <input type="text" id="heading" name="heading" class="form-control" >
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.description')</label>
                                        <textarea id="description" name="description" class="form-control summernote"></textarea>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.dueDate')</label>
                                        <input type="text" name="due_date" id="due_date2" class="form-control">
                                    </div>
                                </div>

                                 <!--/span-->
                                 <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.tasks.repeat')</label>
                                        <select class="select2 form-control" data-placeholder="@lang('modules.tasks.chooseRepeatPeriod')" name="repeat" id="repeat" >
                                            <option value=""></option>
                                            <option value="@lang('modules.tasks.never')">@lang('modules.tasks.never')</option>
                                            <option value="@lang('modules.tasks.daily')">@lang('modules.tasks.daily')</option>
                                            <option value="@lang('modules.tasks.weekly')">@lang('modules.tasks.weekly')</option>
                                            <option value="@lang('modules.tasks.monthly')">@lang('modules.tasks.monthly')</option>
                                            <option value="@lang('modules.tasks.yearly')">@lang('modules.tasks.yearly')</option>
                                            
                                        </select>
                                    </div>
                                </div> 

                               <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label" id="until_label">@lang('app.untilDate')</label>
                                        <input type="text" name="until_date" id="until_date2" class="form-control">
                                    </div>
                                </div> 

                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.tasks.assignmentType')</label>

                                        <div class="radio radio-danger">
                                            <input type="radio" name="assignmentType" id="individual"
                                                    value="individual">
                                            <label for="individual" class="text-danger">
                                                @lang('modules.tasks.individual') </label>
                                        </div>
                                        <div class="radio radio-warning">
                                            <input type="radio" name="assignmentType"
                                                   id="roleGroup" value="roleGroup">
                                            <label for="roleGroup" class="text-warning">
                                                @lang('modules.tasks.roleGroup') </label>
                                        </div>
                                        <div class="radio radio-success">
                                            <input type="radio" name="assignmentType" id="locationGroup"
                                                   value="locationGroup">
                                            <label for="locationGroup" class="text-success">
                                                @lang('modules.tasks.locationGroup') </label>
                                        </div>
                                    </div>
                                </div>

                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label  id='individual_label' class="control-label">@lang('modules.tasks.assignTo') @lang('modules.tasks.individual')</label>
                                        <select class="select2 form-control" data-placeholder="@lang('modules.tasks.chooseAssignee')" name="user_id" id="user_id" >
                                            <option value=""></option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" data-fbasetoken="{{ $employee->firebase_token }}">{{ ucwords($employee->name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                                 <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label id='team_label' class="control-label">@lang('modules.tasks.assignTo') @lang('modules.tasks.roleGroup')</label>
                                        <select name="team_id" id="team_id" class="form-control">
                                                <option value=""> -- </option>
                                                @foreach($teams as $team)
                                                    <option value="{{ $team->id }}">{{ ucwords($team->team_name) }}</option>
                                                @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                        <div class="form-group">
                                            <label id='team2_label' class="control-label">@lang('modules.tasks.assignTo') @lang('modules.tasks.locationGroup')</label>
                                            <select name="team2_id" id="team2_id" class="form-control">
                                                    <option value=""> -- </option>
                                                    @foreach($teams2 as $team2)
                                                        <option value="{{ $team2->id }}">{{ ucwords($team2->team2_name) }}</option>
                                                    @endforeach
                                            </select>
                                        </div>
                                    </div>

                                <!--/span-->
                                

                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.tasks.priority')</label>

                                        <div class="radio radio-danger">
                                            <input type="radio" name="priority" id="radio13"
                                                   value="high">
                                            <label for="radio13" class="text-danger">
                                                @lang('modules.tasks.high') </label>
                                        </div>
                                        <div class="radio radio-warning">
                                            <input type="radio" name="priority"
                                                   id="radio14"  value="medium">
                                            <label for="radio14" class="text-warning">
                                                @lang('modules.tasks.medium') </label>
                                        </div>
                                        <div class="radio radio-success">
                                            <input type="radio" name="priority" id="radio15"
                                                   value="low">
                                            <label for="radio15" class="text-success">
                                                @lang('modules.tasks.low') </label>
                                        </div>
                                    </div>
                                </div>
                                <!--/span-->

                            </div>
                            <!--/row-->
                              
                        </div>
                        <div class="form-actions">
                            <button type="button" id="store-task" class="btn btn-success"><i class="fa fa-check"></i> @lang('app.save')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- .row -->

@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/summernote/dist/summernote.min.js') }}"></script>

<script>
//at page load, hide until date
 $("#until_date2").hide();
 $("#until_label").hide();
 $("#project_id").hide();

//hide role group label and field
 $('#team_label').hide();
 $('#team_id').hide();

  $('#team2_label').hide();
  $('#team2_id').hide();

 $('#user_id').hide();
 $('#individual_label').hide();


//to hide and display until date field
$('#repeat').click(function () {

    if($('#repeat').val() !== 'Never'){
        $("#until_date2").show();
        $("#until_label").show();
    }
    if($('#repeat').val() === 'Never'){
        $("#until_date2").hide();
        $("#until_label").hide();
    }
});

$('#roleGroup').click(function(){
    $('#user_id').val("1");
    $('#team_label').show();
    $('#team_id').show();

    $('#individual_label').hide();
    $('#user_id').hide();

    $('#team2_label').hide();
    $('#team2_id').hide();
});

$('#locationGroup').click(function(){
    $('#user_id').val("1");
    $('#team2_label').show();
    $('#team2_id').show();

    $('#individual_label').hide();
    $('#user_id').hide();

    $('#team_label').hide();
    $('#team_id').hide();
});

$('#individual').click(function(){
    $('#individual_label').show();
    $('#user_id').show(); 

    $('#team_label').hide();
    $('#team_id').hide();

    $('#team2_label').hide();
    $('#team2_id').hide();
});



function formatDate (unformattedDate){
    var formattedDate = new Date(unformattedDate);
    var d = formattedDate.getDate();
    var m =  formattedDate.getMonth();
    m += 1;  // JavaScript months are 0-11
    var y = formattedDate.getFullYear();

    return m + "/" + d + "/" + y;
}

/*function dispatchNotification(){
    $.ajax({
            url: 'https://fcm.googleapis.com/fcm/send',
            type: 'POST',
            // dataType: 'json',
            headers: {
                'Authorization': 'key=AIzaSyDmlO8naLCTyaUgMAI--wHfuFsFUCuQj5c',
                'Content-Type': 'application/json'
            },
            //contentType: 'application/json; charset=utf-8',
            data: JSON.stringify({
                "to" : $('#user_id').find('option:selected').attr('data-fbasetoken') ,
                "collapse_key" : "type_a",
                "notification" : {
                    "body" : "You have a new task",
                    "title": "Title of Your Notification"
                }
            }),
            success: function (result) {
            console.log('notification Success'+result);
            },
            error: function (error) {
                console.log('notification failed'+error);
            }
        });
}*/


    //    update task
    $('#store-task').click(function () {
         
           if($('#repeat').val() === 'Never'){ //if repeat is never , post data as it is
              
               
                $.easyAjax({
                    url: '{{route('admin.all-tasks.store')}}',
                    container: '#storeTask',
                    type: "POST",
                    data: $('#storeTask').serialize(),
                    success: function (result) {
                        console.log('post was Successful!');

                        //dispatchNotification();
                        },
                    error: function (error) {
                        console.log('post failed');
                    }
                }) 
           
             }

             else if($('#repeat').val() === 'Daily'){ //if repeat is daily, generate dates and post


                var startDate = new Date($('#due_date2').val());
                var endDate = new Date($('#until_date2').val()); 
        

                var getDateArray = function(start, end) {
                    var arr = new Array();
                    var dt = new Date(start);
                    while (dt <= end) {
                        arr.push(new Date(dt));
                        dt.setDate(dt.getDate() + 1);
                    }
                    return arr;
                }

                var dateArr = getDateArray(startDate, endDate);

                for (var i = 0; i < dateArr.length; i++) {
                  console.log( $('#due_date2').val());
                   $('#due_date2').val(formatDate(dateArr[i]));
                   $.easyAjax({
                        url: '{{route('admin.all-tasks.store')}}',
                        container: '#storeTask',
                        type: "POST",
                        data: $('#storeTask').serialize()
                    })
                }
                

             }

            else if($('#repeat').val() === 'Weekly'){ //if repeat is weekly, generate dates and post


                var startDate = new Date($('#due_date2').val());
                var endDate = new Date($('#until_date2').val()); 


                var getDateArray = function(start, end) {
                    var arr = new Array();
                    var dt = new Date(start);
                    while (dt <= end) {
                        arr.push(new Date(dt));
                        dt.setDate(dt.getDate() + 7); //add 7 days to the date
                    }
                    return arr;
                }

                var dateArr = getDateArray(startDate, endDate);

                for (var i = 0; i < dateArr.length; i++) {
                    console.log( $('#due_date2').val());
                    $('#due_date2').val(formatDate(dateArr[i]));
                    $.easyAjax({
                            url: '{{route('admin.all-tasks.store')}}',
                            container: '#storeTask',
                            type: "POST",
                            data: $('#storeTask').serialize()
                        })
                }
                
            }

            else if($('#repeat').val() === 'Monthly'){ //if repeat is Monthly, generate dates and post


                var startDate = new Date($('#due_date2').val());
                var endDate = new Date($('#until_date2').val()); 


                var getDateArray = function(start, end) {
                    var arr = new Array();
                    var dt = new Date(start);
                    while (dt <= end) {
                        arr.push(new Date(dt));
                        dt.setMonth(dt.getMonth() + 1); //add 1 month to the date
                    }
                    return arr;
                }

                var dateArr = getDateArray(startDate, endDate);

                for (var i = 0; i < dateArr.length; i++) {
                    console.log( $('#due_date2').val());
                    $('#due_date2').val(formatDate(dateArr[i]));
                    $.easyAjax({
                            url: '{{route('admin.all-tasks.store')}}',
                            container: '#storeTask',
                            type: "POST",
                            data: $('#storeTask').serialize()
                        })
                }
                
            }

            else if($('#repeat').val() === 'Yearly'){ //if repeat is Yearly, generate dates and post


                var startDate = new Date($('#due_date2').val());
                var endDate = new Date($('#until_date2').val()); 


                var getDateArray = function(start, end) {
                    var arr = new Array();
                    var dt = new Date(start);
                    while (dt <= end) {
                        arr.push(new Date(dt));
                        dt.setFullYear(dt.getFullYear() + 1); //add 1 year to the date
                    }
                    return arr;
                }

                var dateArr = getDateArray(startDate, endDate);

                for (var i = 0; i < dateArr.length; i++) {
                    console.log( $('#due_date2').val());
                    $('#due_date2').val(formatDate(dateArr[i]));
                    $.easyAjax({
                            url: '{{route('admin.all-tasks.store')}}',
                            container: '#storeTask',
                            type: "POST",
                            data: $('#storeTask').serialize()
                        })
                }

            }

      


/*
        $.easyAjax({
            url: '{{route('admin.all-tasks.store')}}',
            container: '#storeTask',
            type: "POST",
            data: $('#storeTask').serialize()
        }) */
    });

    jQuery('#due_date2').datepicker({
        autoclose: true,
        todayHighlight: true
    });

    jQuery('#until_date2').datepicker({
        autoclose: true,
        todayHighlight: true
    });

    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });

    $('#project_id').change(function () {
        var id = $(this).val();
        var url = '{{route('admin.all-tasks.members', ':id')}}';
        url = url.replace(':id', id);

        $.easyAjax({
            url: url,
            type: "GET",
            redirect: true,
            success: function (data) {
                $('#user_id').html(data.html);
            }
        })
    });

    $('.summernote').summernote({
        height: 200,                 // set editor height
        minHeight: null,             // set minimum height of editor
        maxHeight: null,             // set maximum height of editor
        focus: false                 // set focus to editable area after initializing summernote
    });

</script>
@endpush

