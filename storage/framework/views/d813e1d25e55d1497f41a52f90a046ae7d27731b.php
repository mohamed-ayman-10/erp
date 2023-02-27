<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Dashboard')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
    <script>

        (function () {
            var etitle;
            var etype;
            var etypeclass;
            var calendar = new FullCalendar.Calendar(document.getElementById('event_calendar'), {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridDay,timeGridWeek,dayGridMonth'
                },
                buttonText: {
                    timeGridDay: "<?php echo e(__('Day')); ?>",
                    timeGridWeek: "<?php echo e(__('Week')); ?>",
                    dayGridMonth: "<?php echo e(__('Month')); ?>"
                },
                themeSystem: 'bootstrap',
                navLinks: true,
                droppable: true,
                selectable: true,
                selectMirror: true,
                editable: true,
                dayMaxEvents: true,
                handleWindowResize: true,
                events: <?php echo json_encode($arrEvents); ?>,
                locale: '<?php echo e(basename(App::getLocale())); ?>',
                dayClick: function (e) {
                    var t = moment(e).toISOString();
                    $("#new-event").modal("show"), $(".new-event--title").val(""), $(".new-event--start").val(t), $(".new-event--end").val(t)
                },
                eventResize: function (event) {
                    var eventObj = {
                        start: event.start.format(),
                        end: event.end.format(),
                    };
                },
                viewRender: function (t) {
                    e.fullCalendar("getDate").month(), $(".fullcalendar-title").html(t.title)
                },
                eventClick: function (e, t) {
                    var title = e.title;
                    var url = e.url;

                    if (typeof url != 'undefined') {
                        $("#commonModal .modal-title").html(title);
                        $("#commonModal .modal-dialog").addClass('modal-md');
                        $("#commonModal").modal('show');
                        $.get(url, {}, function (data) {
                            $('#commonModal .modal-body').html(data);
                        });
                        return false;
                    }
                }
            });
            calendar.render();
        })();
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('HRM')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php if(\Auth::user()->type != 'client' && \Auth::user()->type != 'company'): ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-xxl-6">
                        <div class="card">
                                <div class="card-header">
                                    <h4><?php echo e(__('Mark Attandance')); ?></h4>
                                </div>
                                <div class="card-body dash-card-body">
                                    <p class="text-muted pb-0-5"><?php echo e(__('My Office Time: '.$officeTime['startTime'].' to '.$officeTime['endTime'])); ?></p>
                                    <center>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <?php echo e(Form::open(array('url'=>'attendanceemployee/attendance','method'=>'post'))); ?>

                                                <?php if(empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00'): ?>
                                                    <button type="submit" value="0" name="in" id="clock_in" class="btn btn-success "><?php echo e(__('CLOCK IN')); ?></button>
                                                <?php else: ?>
                                                    <button type="submit" value="0" name="in" id="clock_in" class="btn btn-success disabled" disabled><?php echo e(__('CLOCK IN')); ?></button>
                                                <?php endif; ?>
                                                <?php echo e(Form::close()); ?>

                                            </div>
                                            <div class="col-md-6 ">
                                                <?php if(!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00'): ?>
                                                    <?php echo e(Form::model($employeeAttendance,array('route'=>array('attendanceemployee.update',$employeeAttendance->id),'method' => 'PUT'))); ?>

                                                    <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger"><?php echo e(__('CLOCK OUT')); ?></button>
                                                <?php else: ?>
                                                    <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger disabled" disabled><?php echo e(__('CLOCK OUT')); ?></button>
                                                <?php endif; ?>
                                                <?php echo e(Form::close()); ?>

                                            </div>
                                        </div>
                                    </center>

                                </div>
                            </div>
                        <div class="card ">
                                <div class="card-header">
                                    <h4><?php echo e(__('Event View')); ?></h4>
                                </div>
                                <div class="card-body dash-card-body">
                                    <div class="overflow-hidden widget-calendar">
                                                <div class="calendar e-height" data-toggle="event_calendar" id="event_calendar"></div>
                                            </div>
                                </div>
                            </div>
                    </div>
                    <div class="col-xxl-6">
                        <div class="card list_card">
                            <div class="card-header">
                                <h4><?php echo e(__('Announcement List')); ?></h4>
                            </div>
                            <div class="card-body dash-card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                        <tr>
                                            <th><?php echo e(__('Title')); ?></th>
                                            <th><?php echo e(__('Start Date')); ?></th>
                                            <th><?php echo e(__('End Date')); ?></th>
                                            <th><?php echo e(__('description')); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td><?php echo e($announcement->title); ?></td>
                                                <td><?php echo e(\Auth::user()->dateFormat($announcement->start_date)); ?></td>
                                                <td><?php echo e(\Auth::user()->dateFormat($announcement->end_date)); ?></td>
                                                <td><?php echo e($announcement->description); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="4">
                                                    <div class="text-center">
                                                        <h6><?php echo e(__('There is no Announcement List')); ?></h6>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card list_card">
                            <div class="card-header">
                                <h4><?php echo e(__('Meeting List')); ?></h4>
                            </div>
                            <div class="card-body dash-card-body">
                                <?php if(count($meetings) > 0): ?>
                                    <div class="table-responsive">

                                        <table class="table align-items-center">

                                            <thead>
                                            <tr>
                                                <th><?php echo e(__('Meeting title')); ?></th>
                                                <th><?php echo e(__('Meeting Date')); ?></th>
                                                <th><?php echo e(__('Meeting Time')); ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $meetings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $meeting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($meeting->title); ?></td>
                                                    <td><?php echo e(\Auth::user()->dateFormat($meeting->date)); ?></td>
                                                    <td><?php echo e(\Auth::user()->timeFormat($meeting->time)); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>

                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="p-2">
                                        <?php echo e(__('No meeting scheduled yet.')); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
    <div class="row">
        <div class="col-xxl-12">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo e(__("Today's Not Clock In")); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-12">
                            <div class="row g-3 flex-nowrap team-lists horizontal-scroll-cards">
                                <?php $__currentLoopData = $notClockIns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notClockIn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <div class="col-auto">
                                        <img src="<?php echo e((!empty($notClockIn->user))? $notClockIn->user->profile : asset(Storage::url('uploads/avatar/avatar.png'))); ?>" alt="">

                                        <p class="mt-2"><?php echo e($notClockIn->name); ?></p>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="row">
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo e(__('Event')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div id='event_calendar' class='calendar'></div>
                        </div>
                    </div>

                </div>
                <div class="col-md-3">
                    <div class="col-xxl-12">
                        <div class="card">
                            <div class="card-body">
                                <h5><?php echo e(__('Staff')); ?></h5>
                                <div class="row  mt-4">
                                    <div class="col-md-6 col-sm-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-users"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Total Staff')); ?></p>
                                                <h4 class="mb-0 text-success"><?php echo e($countUser +   $countClient); ?></h4>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6 my-3 my-sm-0">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="theme-avtar bg-info">
                                                <i class="ti ti-user"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Employee')); ?></p>
                                                <h4 class="mb-0 text-primary"><?php echo e($countUser); ?></h4>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="theme-avtar bg-danger">
                                                <i class="ti ti-user"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Client')); ?></p>
                                                <h4 class="mb-0 text-danger"><?php echo e($countClient); ?></h4>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-12">
                        <div class="card">
                            <div class="card-body">
                                <h5><?php echo e(__('Job')); ?></h5>
                                <div class="row  mt-4">
                                    <div class="col-md-6 col-sm-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-award"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Total Jobs')); ?></p>
                                                <h4 class="mb-0 text-success"><?php echo e($activeJob + $inActiveJOb); ?></h4>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6 my-3 my-sm-0">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="theme-avtar bg-info">
                                                <i class="ti ti-check"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Active Job')); ?></p>
                                                <h4 class="mb-0 text-primary"><?php echo e($activeJob); ?></h4>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="theme-avtar bg-danger">
                                                <i class="ti ti-x"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Inactive Job ')); ?></p>
                                                <h4 class="mb-0 text-danger"><?php echo e($inActiveJOb); ?></h4>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-12">
                        <div class="card">
                            <div class="card-body">
                                <h5><?php echo e(__('Training')); ?></h5>
                                <div class="row  mt-4">
                                    <div class="col-md-6 col-sm-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-users"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Total Training')); ?></p>
                                                <h4 class="mb-0 text-success"><?php echo e($onGoingTraining +   $doneTraining); ?></h4>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6 my-3 my-sm-0">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="theme-avtar bg-info">
                                                <i class="ti ti-user"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Trainer')); ?></p>
                                                <h4 class="mb-0 text-primary"><?php echo e($countTrainer); ?></h4>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="theme-avtar bg-danger">
                                                <i class="ti ti-user-check"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Active Training')); ?></p>
                                                <h4 class="mb-0 text-danger"><?php echo e($onGoingTraining); ?></h4>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="theme-avtar bg-secondary">
                                                <i class="ti ti-user-minus"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Done Training')); ?></p>
                                                <h4 class="mb-0 text-secondary"><?php echo e($doneTraining); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">

                            <h5><?php echo e(__('Announcement List')); ?></h5>
                        </div>
                        <div class="card-body" style="min-height: 295px;">
                            <div class="table-responsive">
                                <?php if(count($announcements) > 0): ?>
                                    <table class="table align-items-center">
                                        <thead>
                                        <tr>
                                            <th><?php echo e(__('Title')); ?></th>
                                            <th><?php echo e(__('Start Date')); ?></th>
                                            <th><?php echo e(__('End Date')); ?></th>

                                        </tr>
                                        </thead>
                                        <tbody class="list">
                                        <?php $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($announcement->title); ?></td>
                                                <td><?php echo e(\Auth::user()->dateFormat($announcement->start_date)); ?></td>
                                                <td><?php echo e(\Auth::user()->dateFormat($announcement->end_date)); ?></td>

                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="p-2">
                                        No accouncement present yet.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo e(__('Meeting schedule')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <?php if(count($meetings) > 0): ?>
                                    <table class="table align-items-center">
                                        <thead>
                                        <tr>
                                            <th><?php echo e(__('Title')); ?></th>
                                            <th><?php echo e(__('Date')); ?></th>
                                            <th><?php echo e(__('Time')); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody class="list">
                                        <?php $__currentLoopData = $meetings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $meeting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($meeting->title); ?></td>
                                                <td><?php echo e(\Auth::user()->dateFormat($meeting->date)); ?></td>
                                                <td><?php echo e(\Auth::user()->timeFormat($meeting->time)); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="p-2">
                                        No meeting scheduled yet.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ sample-page ] end -->
    </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Bdrango\Desktop\main_file\resources\views/dashboard/dashboard.blade.php ENDPATH**/ ?>