<div class="panel panel-default">
    <div class="panel-heading "><i class="ti-search"></i> @lang('modules.tasks.taskDetail')
        <div class="panel-action">
            <a href="javascript:;" id="hide-edit-task-panel" class="close " data-dismiss="modal"><i class="ti-close"></i></a>
        </div>
    </div>

    @if($task->project->isProjectAdmin || $user->can('edit_projects'))
        <div class="panel-wrapper collapse in">
            <div class="panel-body">
                {!! Form::open(['id'=>'updateTask','class'=>'ajax-form','method'=>'PUT']) !!}
                {!! Form::hidden('project_id', $task->project_id) !!}

                <div class="form-body">
                    <div class="row">
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
                                <textarea id="description" name="description" class="form-control">{!! $task->description !!}</textarea>
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
                                <select class="form-control" name="user_id" id="user_id" >
                                    @foreach($task->project->members as $member)
                                        <option @if($task->user_id == $member->user->id) selected @endif
                                        value="{{ $member->user->id }}">{{ $member->user->name }}</option>
                                    @endforeach
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

    @else
        <div class="panel-wrapper collapse in">
            <div class="panel-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">@lang('app.title')</label>
                                <p>  {{ ucfirst($task->heading) }} </p>
                            </div>
                        </div>
                        <!--/span-->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">@lang('app.description')</label>
                                <p>  {!! ucfirst($task->description) !!} </p>
                            </div>
                        </div>
                        <!--/span-->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">@lang('app.dueDate')</label>
                                <p>  {{  $task->due_date->format('d-M-Y')  }} </p>
                            </div>
                        </div>
                        <!--/span-->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">@lang('modules.tasks.assignTo')</label>
                                <p>  {{  $task->user->name  }} </p>
                            </div>
                        </div>
                        <!--/span-->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">@lang('modules.tasks.priority')</label>
                                    <div  class="clearfix"></div>
                                    <label for="radio13" class="text-@if($task->priority == 'high')danger @elseif($task->priority == 'medium')warning @else success @endif ">
                                        @if($task->priority == 'high') @lang('modules.tasks.high') @elseif($task->priority == 'medium') @lang('modules.tasks.medium') @else @lang('modules.tasks.low') @endif</label>

                            </div>
                        </div>
                        <!--/span-->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>@lang('app.status')</label>
                                <div  class="clearfix"></div>
                                    <label for="radio13" class="text-@if($task->status == 'incomplete')danger @else success @endif ">
                                        @if($task->status == 'incomplete') @lang('app.incomplete') @else @lang('app.completed') @endif</label>

                            </div>
                        </div>
                        <!--/span-->
                    </div>
                    <!--/row-->

                </div>
                <div class="form-actions">
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    $('#hide-edit-task-panel').click(function () {
        newTaskpanel.addClass('hide').removeClass('show');
        taskListPanel.switchClass("col-md-6", "col-md-12", 1000, "easeInOutQuad");
    });
</script>
<script>
    //    update task
    $('#update-task').click(function () {
        $.easyAjax({
            url: '{{route('member.tasks.update', [$task->id])}}',
            container: '#updateTask',
            type: "POST",
            data: $('#updateTask').serialize(),
            success: function (data) {
                $('#task-list-panel ul.list-group').html(data.html);
            }
        })
    });

    jQuery('#due_date2').datepicker({
        autoclose: true,
        todayHighlight: true
    });
</script>
