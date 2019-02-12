@extends('layouts.client-app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-6 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }} #{{ $project->id }} - <span
                        class="font-bold">{{ ucwords($project->project_name) }}</span></h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-6 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('client.dashboard.index') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('client.projects.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('modules.projects.files')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')

<link rel="stylesheet" href="{{ asset('plugins/bower_components/dropzone-master/dist/dropzone.css') }}">
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">

            <section>
                <div class="sttabs tabs-style-line">
                    <div class="white-box">
                        <nav>
                            <ul>
                                <li><a href="{{ route('client.projects.show', $project->id) }}"><span>@lang('modules.projects.overview')</span></a></li>
                                @if(\App\ModuleSetting::checkModule('employees'))
                                <li><a href="{{ route('client.project-members.show', $project->id) }}"><span>@lang('modules.projects.members')</span></a></li>
                                @endif

                                @if($project->client_view_task == 'enable' && \App\ModuleSetting::checkModule('tasks'))
                                    <li><a href="{{ route('client.tasks.edit', $project->id) }}"><span>@lang('app.menu.tasks')</span></a></li>
                                @endif

                                <li class="tab-current"><a href="{{ route('client.files.show', $project->id) }}"><span>@lang('modules.projects.files')</span></a></li>

                                @if(\App\ModuleSetting::checkModule('timelogs'))
                                <li><a href="{{ route('client.time-log.show', $project->id) }}"><span>@lang('app.menu.timeLogs')</span></a></li>
                                @endif

                                @if(\App\ModuleSetting::checkModule('invoices'))
                                <li><a href="{{ route('client.project-invoice.show', $project->id) }}"><span>@lang('app.menu.invoices')</span></a></li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                    <div class="content-wrap">
                        <section id="section-line-3" class="show">
                            <div class="row">
                                <div class="col-md-12" id="files-list-panel">
                                    <div class="white-box">
                                        <h2>@lang('modules.projects.files')</h2>

                                        <div class="row m-b-10">
                                            <div class="col-md-12">
                                                <a href="javascript:;" id="show-dropzone"
                                                   class="btn btn-success btn-outline"><i class="ti-upload"></i> @lang('modules.projects.uploadFile')</a>
                                            </div>
                                        </div>

                                        <div class="row m-b-20 hide" id="file-dropzone">
                                            <div class="col-md-12">
                                                <form action="{{ route('client.files.store') }}" class="dropzone"
                                                      id="file-upload-dropzone">
                                                    {{ csrf_field() }}

                                                    {!! Form::hidden('project_id', $project->id) !!}

                                                    <div class="fallback">
                                                        <input name="file" type="file" multiple/>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <ul class="list-group" id="files-list">
                                            @forelse($project->files as $file)
                                                <li class="list-group-item">
                                                    <div class="row">
                                                        <div class="col-md-9">
                                                            {{ $file->filename }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <a target="_blank"
                                                               @if(!is_null($file->dropbox_link))
                                                               href="{{ $file->dropbox_link }}"
                                                               @else
                                                               href="{{ asset('user-uploads/project-files/'.$project->id.'/'.$file->hashname) }}"
                                                               @endif
                                                               data-toggle="tooltip" data-original-title="View"
                                                               class="btn btn-info btn-circle"><i
                                                                        class="fa fa-search"></i></a>
                                                            &nbsp;&nbsp;
                                                            <a
                                                                    @if(!is_null($file->dropbox_link))
                                                                    href="{{ str_replace('dl=0', 'dl=1', $file->dropbox_link) }}"
                                                                    @else
                                                                    href="{{ route('client.files.download', $file->id) }}"
                                                                    @endif

                                                                    data-toggle="tooltip" data-original-title="Download"
                                                                    class="btn btn-inverse btn-circle"><i
                                                                        class="fa fa-download"></i></a>
                                                            <span class="m-l-10">{{ $file->created_at->diffForHumans() }}</span>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="list-group-item">
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            @lang('messages.noFileUploaded')
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforelse

                                        </ul>
                                    </div>
                                </div>

                            </div>
                        </section>

                    </div><!-- /content -->
                </div><!-- /tabs -->
            </section>
        </div>


    </div>
    <!-- .row -->

@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/dropzone-master/dist/dropzone.js') }}"></script>
<script>
    $('#show-dropzone').click(function () {
        $('#file-dropzone').toggleClass('hide show');
    });

    $("body").tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    // "myAwesomeDropzone" is the camelized version of the HTML element's ID
    Dropzone.options.fileUploadDropzone = {
        paramName: "file", // The name that will be used to transfer the file
//        maxFilesize: 2, // MB,
        dictDefaultMessage: "@lang('modules.projects.dropFile')",
        accept: function (file, done) {
            done();
        },
        init: function () {
            this.on("success", function (file, response) {
                console.log(response);
                $('#files-list-panel ul.list-group').html(response.html);
            })
        }
    };


</script>
@endpush