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
                <li class="active">@lang('app.edit')</li>
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
                <div class="panel-heading"> @lang('modules.tasks.updateTask')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'updateTask','class'=>'ajax-form','method'=>'PUT']) !!}

                        <div class="form-body">
                            <div class="row">

                               <!-- <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.project')</label>
                                        <select class="select2 form-control" data-placeholder="@lang("app.selectProject")" id="project_id" name="project_id">
                                            <option value=""></option>
                                            @foreach($projects as $project)
                                                <option
                                                        @if($project->id == $task->project_id) selected @endif
                                                        value="{{ $project->id }}">{{ ucwords($project->project_name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>-->

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.title')</label>
                                        <input type="text" id="heading" name="heading" class="form-control" value="{{ $task->heading }}">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.description')</label>
                                        <textarea id="description" name="description" class="form-control summernote">{{ $task->description }}</textarea>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.dueDate')</label>
                                        <input type="text" name="due_date" id="due_date2" class="form-control" value="{{ $task->due_date->format('m/d/Y') }}">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.tasks.assignTo')</label>
                                        <label class="control-label">@lang('modules.tasks.assignTo')</label>
                                        <select class="select2 form-control" data-placeholder="@lang('modules.tasks.chooseAssignee')" name="user_id" id="user_id" >
                                            @if(is_null($task->project_id))
                                                @foreach($employees as $employee)
                                                    <option @if($task->user_id == $employee->id) selected @endif
                                                    value="{{ $employee->id }}">{{ ucwords($employee->name) }}</option>
                                                @endforeach
                                            @else
                                                @foreach($task->project->members as $member)
                                                    <option @if($task->user_id == $member->user->id) selected @endif
                                                    value="{{ $member->user->id }}">{{ $member->user->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.tasks.priority')</label>

                                        <div class="radio radio-danger">
                                            <input type="radio" name="priority" id="radio13"
                                                   @if($task->priority == 'high') checked @endif
                                                   value="high">
                                            <label for="radio13" class="text-danger">
                                                @lang('modules.tasks.high') </label>
                                        </div>
                                        <div class="radio radio-warning">
                                            <input type="radio" name="priority"
                                                   @if($task->priority == 'medium') checked @endif
                                                   id="radio14" value="medium">
                                            <label for="radio14" class="text-warning">
                                                @lang('modules.tasks.medium') </label>
                                        </div>
                                        <div class="radio radio-success">
                                            <input type="radio" name="priority" id="radio15"
                                                   @if($task->priority == 'low') checked @endif
                                                   value="low">
                                            <label for="radio15" class="text-success">
                                                @lang('modules.tasks.low') </label>
                                        </div>
                                    </div>
                                </div>
                                 <!--/span-->
                                 <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <select name="remark" id="remark" class="form-control">
                                            <option value="">--</option>
                                            <option @if($task->remark == 'Poor') selected @endif value="Poor">Poor</option>
                                            <option @if($task->remark == 'Fair') selected @endif value="Fair">Fair</option>
                                            <option @if($task->remark == 'Good') selected @endif value="Good">Good</option>
                                            <option @if($task->remark == 'Very Good') selected @endif value="Very Good">Very Good</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>@lang('app.status')</label>
                                        <select name="status" id="status" class="form-control">
                                            <option @if($task->status == 'incomplete') selected @endif value="incomplete">@lang('app.incomplete')</option>
                                            <option @if($task->status == 'completed') selected @endif value="completed">@lang('app.completed')</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                            </div>
                            <!--/row-->

                        </div>
                        <div class="form-actions">
                            <button type="button" id="update-task" class="btn btn-success"><i class="fa fa-check"></i> @lang('app.save')</button>
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
    //    update task
    $('#update-task').click(function () {
        console.log($('#updateTask').serialize());
        $.easyAjax({
            url: '{{route('admin.all-tasks.update', [$task->id])}}',
            container: '#updateTask',
            type: "POST",
            data: $('#updateTask').serialize()
        })
    });

    jQuery('#due_date2').datepicker({
        autoclose: true,
        todayHighlight: true
    });

    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });

    $('.summernote').summernote({
        height: 200,                 // set editor height
        minHeight: null,             // set minimum height of editor
        maxHeight: null,             // set maximum height of editor
        focus: false                 // set focus to editable area after initializing summernote
    });

</script>
@endpush

