@for ($date = $endDate; $date->diffInDays($startDate) > 0; $date->subDay())
    <?php
    $present = 0;
    $attendanceData = '';
    ?>

    @foreach($attendances as $attendance)
        @if($attendance->clock_in_date == $date->toDateString())
            <?php
            $present = 1;
            $attendanceData = $attendance;
            ?>
        @endif
    @endforeach
    @if($present == 1)
        <tr>
            <td>@lang('app.'.strtolower( $date->format("F") )) {{ $date->format('d, Y') }}</td>
            <td><label class="label label-success">@lang('modules.attendance.present')</label></td>
            <td>{{ $attendanceData->clock_in_time->timezone($global->timezone)->format('h:i A') }}</td>
            <td>@if(!is_null($attendanceData->clock_out_time)) {{ $attendanceData->clock_out_time->timezone($global->timezone)->format('h:i A') }} @endif</td>
            <td>
                <strong>@lang('modules.attendance.clock_in') IP: </strong> {{ $attendanceData->clock_in_ip }}<br>
                <strong>@lang('modules.attendance.clock_out') IP: </strong> {{ $attendanceData->clock_out_ip }}<br>
                <strong>@lang('modules.attendance.working_from'): </strong> {{ $attendanceData->working_from }}<br>
                @if($user->can('add_attendance'))
                    <a href="javascript:;" data-attendance-id="{{ $attendanceData->aId }}" class="delete-attendance btn btn-outline btn-danger btn-xs"><i class="fa fa-times"></i> @lang('app.delete')</a>
                @endif
            </td>
        </tr>
    @else
        <tr>
            <td>@lang('app.'.strtolower( $date->format("F") )) {{ $date->format('d, Y') }}</td>
            <td><label class="label label-danger">@lang('modules.attendance.absent')</label></td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
        </tr>
    @endif

@endfor

<?php
$present = 0;
$attendanceData = '';
$date = $endDate;
?>

@foreach($attendances as $attendance)
    @if($attendance->clock_in_date == $date->toDateString())
        <?php
        $present = 1;
        $attendanceData = $attendance;
        ?>
    @endif
@endforeach
@if($present == 1)
    <tr>
        <td>@lang('app.'.strtolower( $date->format("F") )) {{ $date->format('d, Y') }}</td>
        <td><label class="label label-success">@lang('modules.attendance.present')</label></td>
        <td>{{ $attendanceData->clock_in_time->timezone($global->timezone)->format('h:i A') }}</td>
        <td>@if(!is_null($attendanceData->clock_out_time)) {{ $attendanceData->clock_out_time->timezone($global->timezone)->format('h:i A') }} @endif</td>
        <td>
            <strong>@lang('modules.attendance.clock_in') IP: </strong> {{ $attendanceData->clock_in_ip }}<br>
            <strong>@lang('modules.attendance.clock_out') IP: </strong> {{ $attendanceData->clock_out_ip }}<br>
            <strong>@lang('modules.attendance.working_from'): </strong> {{ $attendanceData->working_from }}<br>
            @if($user->can('add_attendance'))
                <a href="javascript:;" data-attendance-id="{{ $attendanceData->aId }}" class="delete-attendance btn btn-outline btn-danger btn-xs"><i class="fa fa-times"></i> @lang('app.delete')</a>
            @endif
        </td>
    </tr>
@else
    <tr>
        <td>@lang('app.'.strtolower( $date->format("F") )) {{ $date->format('d, Y') }}</td>
        <td><label class="label label-danger">@lang('modules.attendance.absent')</label></td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
    </tr>
@endif
