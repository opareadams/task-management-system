<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><i class="ti-check-box"></i> @lang("modules.taskCalendar.taskDetail")</h4>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">@lang("app.title")</label>
                    <p>  {{ ucfirst($task->heading) }} </p>
                </div>
            </div>
            <!--/span-->
            @if(!is_null($task->project_id))
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">@lang("app.project")</label>
                    <p>  {{ ucwords($task->project->project_name) }} </p>
                </div>
            </div>
            <!--/span-->
            @endif

            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">@lang("app.description")</label>
                    <p>  {!!  ucfirst($task->description)  !!} </p>
                </div>
            </div>
            <!--/span-->
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">@lang("app.dueDate")</label>
                    <p>  {{  $task->due_date->format('d-M-Y')  }} </p>
                </div>
            </div>
            <!--/span-->
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">@lang("modules.tasks.assignTo")</label>
                    <p>  {{  $task->user->name  }} </p>
                </div>
            </div>
            <!--/span-->
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">@lang("modules.tasks.priority")</label>
                    <div  class="clearfix"></div>
                    <label for="radio13" class="text-@if($task->priority == 'high')danger @elseif($task->priority == 'medium')warning @else success @endif ">
                        @if($task->priority == 'high')@lang("modules.tasks.high") @elseif($task->priority == 'medium')@lang("modules.tasks.medium") @else @lang("modules.tasks.low") @endif</label>

                </div>
            </div>
            <!--/span-->
            <div class="col-md-12">
                <div class="form-group">
                    <label>@lang('app.status')</label>
                    <div  class="clearfix"></div>
                    <label for="radio13" class="text-@if($task->status == 'incomplete')danger @else success @endif ">
                        @if($task->status == 'incomplete')@lang("app.incomplete") @else @lang("app.completed") @endif</label>

                </div>
            </div>
            <!--/span-->
            <div class="col-md-12">
                <div class="form-group">
                    <a href="{{ route('admin.all-tasks.edit', $task->id) }}" class="btn btn-info"><i class="fa fa-edit"></i> @lang('app.edit')</a>

                </div>
            </div>
            <!--/span-->
        </div>
        <!--/row-->
    </div>
</div>