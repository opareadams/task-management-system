<link rel="stylesheet" href="{{ asset('plugins/bower_components/summernote/dist/summernote.css') }}">

<div class="rpanel-title"> @lang('app.task') <span><i class="ti-close right-side-toggle"></i></span> </div>
<div class="r-panel-body">

    <div class="row">
        <div class="col-xs-12">
            <h3>{{ ucwords($task->heading) }}</h3>
        </div>
        <div class="col-xs-6">
            <label for="">@lang('modules.tasks.assignTo')</label><br>
            @if($task->user->image)
                <img src="{{ asset('user-uploads/avatar/'.$task->user->image) }}" class="img-circle" width="30" alt="">
            @else
                <img src="{{ asset('default-profile-2.png') }}" class="img-circle" width="30" alt="">
            @endif

            {{ ucwords($task->user->name) }}
        </div>
        <div class="col-xs-6">
            <label for="">@lang('app.dueDate')</label><br>
            <span @if($task->due_date->isPast()) class="text-danger" @endif>{{ $task->due_date->format('d M, Y') }}</span>
        </div>
        <div class="col-xs-12 task-description">
            {!! ucfirst($task->description) !!}
        </div>

        @if($user->can('add_tasks'))
        <div class="col-xs-12 m-t-20 m-b-10">
            <a href="javascript:;" id="show-task-row" class="btn btn-xs btn-success btn-outline"><i class="fa fa-plus"></i> @lang('app.add') @lang('modules.tasks.subTask')</a>
        </div>
        @endif

        <div class="col-xs-12 m-t-20">
            <ul class="list-group" id="sub-task-list">
                @foreach($task->subtasks as $subtask)
                    <li class="list-group-item row">
                        <div class="col-xs-12">
                            <div class="checkbox checkbox-success checkbox-circle task-checkbox">
                                <input class="task-check" data-sub-task-id="{{ $subtask->id }}" id="checkbox{{ $subtask->id }}" type="checkbox"
                                       @if($subtask->status == 'complete') checked @endif>
                                <label for="checkbox{{ $subtask->id }}">&nbsp;</label>

                                <a href="#" class="text-muted @if($user->can('edit_tasks')) edit-sub-task" @endif data-name="title"  data-url="{{ route('member.sub-task.update', $subtask->id) }}" data-pk="{{ $subtask->id }}" data-type="text" data-value="{{ ucfirst($subtask->title) }}">{{ ucfirst($subtask->title) }}</a>
                            </div>
                        </div>
                            <div class="col-xs-11 text-right m-t-10">
                                <a href="#"  data-type="combodate" data-name="due_date" data-url="{{ route('member.sub-task.update', $subtask->id) }}"  data-emptytext="@lang('app.dueDate')" class="m-r-10 @if($user->can('edit_tasks')) edit-sub-task-date @endif"  data-format="YYYY-MM-DD" data-viewformat="DD/MM/YYYY" data-template="D / MMM / YYYY" data-value="@if($subtask->due_date){{ $subtask->due_date->format('Y-m-d') }}@endif" data-pk="{{ $subtask->id }}" data-title="@lang('app.dueDate')">@if($subtask->due_date){{ $subtask->due_date->format('d M, Y') }}@endif</a>
                            </div>
                        @if($user->can('delete_tasks'))
                            <div class="col-xs-1 m-t-10">
                                <a href="javascript:;" data-sub-task-id="{{ $subtask->id }}" class="btn btn-danger btn-xs delete-sub-task"><i class="fa fa-times"></i></a>
                            </div>
                        @endif
                    </li>
                @endforeach

            </ul>

            <div class="row b-all m-t-10 p-10"  id="new-sub-task" style="display: none">
                <div class="col-xs-11 ">
                    <a href="javascript:;" id="create-sub-task" data-name="title"  data-url="{{ route('member.sub-task.store') }}" class="text-muted" data-type="text"></a>
                </div>

                <div class="col-xs-1 text-right">
                    <a href="javascript:;" id="cancel-sub-task" class="btn btn-danger btn-xs"><i class="fa fa-times"></i></a>
                </div>
            </div>

        </div>

        <div class="col-xs-12 m-t-15">
            <h5>@lang('modules.tasks.comment')</h5>
        </div>

        <div class="col-xs-12" id="comment-container">
            <div id="comment-list">
                @forelse($task->comments as $comment)
                    <div class="row b-b m-b-5 font-12">
                        <div class="col-xs-8">
                            {!! ucfirst($comment->comment) !!} <br>
                            @if($comment->user_id == $user->id)
                                <a href="javascript:;" data-comment-id="{{ $comment->id }}" class="text-danger delete-task-comment">@lang('app.delete')</a>
                            @endif
                        </div>
                        <div class="col-xs-4 text-right">
                            {{ ucfirst($comment->created_at->diffForHumans()) }}
                        </div>
                        <div class="col-xs-12 text-right m-t-5 m-b-5">
                            &mdash; <i>{{ ucwords($comment->user->name) }}</i>
                        </div>
                    </div>
                @empty
                    <div class="col-xs-12">
                        @lang('messages.noRecordFound')
                    </div>
                @endforelse
            </div>
        </div>

        <!--loader image -->
        <div class="se-pre-con"></div>
        
        <div class="form-group" id="comment-box">
            
            <div class="col-xs-12">
                <textarea name="comment" id="task-comment" class="summernote" placeholder="@lang('modules.tasks.comment')">
                </textarea>
            </div> 

           <div class="col-xs-6" name="imageDiv" id="imageDiv" >

                <img style="width:70px" id="myimg" name="myimg" src="" data-filename="" />
              
              </div>

            <!--<input type="file" accept="image/*;capture=camera"> -->
            <input type="file" id="imageUpload" name="imageUpload" accept="image/*" capture="user">

            
            <div class="col-xs-3">
                <a href="javascript:;" id="submit-comment" class="btn btn-success"><i class="fa fa-send"></i> @lang('app.submit')</a>
            </div>
        </div>

    </div>

</div>



<!--<script src="{{ asset('plugins/bower_components/moment/moment.js') }}"></script>
<script src="{{ asset('plugins/bower_components/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/summernote/dist/summernote.min.js') }}"></script>-->
<script>




    $('.summernote').summernote({
       // airMode: true,
       // dialogsInBody: true,
        height: 100,                 // set editor height
        minHeight: null,             // set minimum height of editor
        maxHeight: null,             // set maximum height of editor
        focus: false,                 // set focus to editable area after initializing summernote,
        toolbar:false
       /* toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']]
        ]*/
    });


    $('#create-sub-task').editable({
        send: 'always',
        type: 'text',
        emptytext: 'Enter task',
        mode: 'inline',
        params: {
            task_id: '{{ $task->id }}',
            '_token':  '{{ csrf_token() }}'
        },
        success: function(response) {
            if(response.status == 'success'){
                $('#sub-task-list').html(response.view);

                $('.edit-sub-task').editable({
                    type: 'text',
                    emptytext: 'Enter task',
                    mode: 'inline',
                    validate: function(value) {
                        if ($.trim(value) == '') return 'This field is required';
                    }
                });

                $('.edit-sub-task-date').editable({
                    mode: 'inline',
                    combodate: {
                        minYear: '{{ \Carbon\Carbon::now()->subYear(20)->year }}',
                        maxYear: '{{ \Carbon\Carbon::now()->year }}'
                    },
                    params: {
                        task_id: '{{ $task->id }}',
                        '_method': 'PUT',
                        '_token':  '{{ csrf_token() }}'
                    },
                });

                $('#new-sub-task').hide();
            }
        },
        validate: function(value) {
            if ($.trim(value) == '') return 'This field is required';
        }
    });

    $('.edit-sub-task').editable({
        send: 'always',
        type: 'text',
        emptytext: 'Enter task',
        mode: 'inline',
        params: {
            task_id: '{{ $task->id }}',
            '_method': 'PUT',
            '_token':  '{{ csrf_token() }}'
        },
        success: function(response) {
            if(response.status == 'success'){
                $('#sub-task-list').html(response.view);

                reinitializeList();
            }
        },
        validate: function(value) {
            if ($.trim(value) == '') return 'This field is required';
        }
    });

    $('.edit-sub-task-date').editable({
        send: 'always',
        type: 'text',
        emptytext: 'Enter task',
        mode: 'inline',
        combodate: {
            minYear: '{{ \Carbon\Carbon::now()->subYear(20)->year }}',
            maxYear: '{{ \Carbon\Carbon::now()->year }}'
        },
        params: {
            task_id: '{{ $task->id }}',
            '_method': 'PUT',
            '_token':  '{{ csrf_token() }}'
        },
        success: function(response) {
            if(response.status == 'success'){
                $('#sub-task-list').html(response.view);

                reinitializeList();
            }
        },
        validate: function(value) {
            if ($.trim(value) == '') return 'This field is required';
        }
    });

    function reinitializeList() {
        $('.edit-sub-task').editable({
            type: 'text',
            emptytext: 'Enter task',
            mode: 'inline',
            validate: function(value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            params: {
                task_id: '{{ $task->id }}',
                '_method': 'PUT',
                '_token':  '{{ csrf_token() }}'
            }
        });

        $('.edit-sub-task-date').editable({
            mode: 'inline',
            combodate: {
                minYear: '{{ \Carbon\Carbon::now()->subYear(20)->year }}',
                maxYear: '{{ \Carbon\Carbon::now()->year }}'
            },
            params: {
                task_id: '{{ $task->id }}',
                '_method': 'PUT',
                '_token':  '{{ csrf_token() }}'
            }
        });

        $('#new-sub-task').hide();
    }

    $('#show-task-row').click(function () {
        $('#new-sub-task').show();
    })

    $('#cancel-sub-task').click(function () {
        $('#new-sub-task').hide();
    })

    $('body').on('click', '.delete-sub-task', function () {
        var id = $(this).data('sub-task-id');
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover the deleted sub task!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel please!",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {

                var url = "{{ route('member.sub-task.destroy',':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token': token, '_method': 'DELETE'},
                    success: function (response) {
                        if (response.status == "success") {
                            $('#sub-task-list').html(response.view);
                            reinitializeList();
                        }
                    }
                });
            }
        });
    });

    //    change sub task status
    $('#sub-task-list').on('click', '.task-check', function () {
        if ($(this).is(':checked')) {
            var status = 'complete';
        }else{
            var status = 'incomplete';
        }

        var id = $(this).data('sub-task-id');
        var url = "{{route('member.sub-task.changeStatus')}}";
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: url,
            type: "POST",
            data: {'_token': token, subTaskId: id, status: status},
            success: function (response) {
                if (response.status == "success") {
                    $('#sub-task-list').html(response.view);
                    reinitializeList();
                }
            }
        })
    });

    /***** Javascript to set textArea (comment) with image from "choose file" ********/

    document.getElementById('imageUpload').onchange = function () { //set up a common class
    readURL(this);
    }; 

   function readURL(input) {

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                document.getElementById('myimg').setAttribute('src',e.target.result);
                document.getElementById('myimg').setAttribute('data-filename',input.files[0].name);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

   
/***************************************************************************/

    


    $('#submit-comment').click(function () {
      //  console.log($('#task-comment').val());
       // console.log($('#imageDiv').html());

        /*removed id attribute because the img tag returned in the comments carry them which 
        attempts to be overridden when u are posting another picture */
        $('#myimg').removeAttr("id");


        var comment = $('#task-comment').val() + $('#imageDiv').html();
        console.log(comment);

        var token = '{{ csrf_token() }}';
        $.easyAjax({
            url: '{{ route("member.task-comment.store") }}',
            type: "POST",
            data: {'_token': token, comment: comment, taskId: '{{ $task->id }}'},
            success: function (response) {
                if (response.status == "success") {
                    $('#comment-list').html(response.view);
                    $('#task-comment').val('');
                }
            }
        })

        $("img[name*='myimg']").attr("id","myimg");

        document.getElementById('myimg').setAttribute('src',' ');


    })

    $('body').on('click', '.delete-task-comment', function () {
        var commentId = $(this).data('comment-id');
        var token = '{{ csrf_token() }}';

        var url = '{{ route("member.task-comment.destroy", ':id') }}';
        url = url.replace(':id', commentId);

        $.easyAjax({
            url: url,
            type: "POST",
            data: {'_token': token, '_method': 'DELETE', commentId: commentId},
            success: function (response) {
                if (response.status == "success") {
                    $('#comment-list').html(response.view);
                }
            }
        })
    })


</script>