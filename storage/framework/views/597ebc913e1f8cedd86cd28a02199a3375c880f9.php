
<table class="table">
    <thead>
    <tr>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th colspan="2">
            <div class="timelogged">
                <p> <?php echo e(__('Time Logged')); ?> : <span class="text-muted"> <?php echo e($calculatedtotaltaskdatetime . __(' Hours')); ?> </span> </p>
            </div>
        </th>
    </tr>
    <tr>
        <th></th>
        <?php $__currentLoopData = $days['datePeriod']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $perioddate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <th scope="col" class="heading"><span><?php echo e($perioddate->format('D')); ?></span><span><?php echo e($perioddate->format('d M')); ?></span></th>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <th class="text-center"><?php echo e(__('Total')); ?></th>
    </tr>
    </thead>
    <tbody>

    <?php if(isset($allProjects) && $allProjects == true): ?>
        <?php $__currentLoopData = $timesheetArray; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $timesheet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td colspan="9"><span class="project-name font-weight-700"><?php echo e($timesheet['project_name']); ?></span></td>
            </tr>
            <?php $__currentLoopData = $timesheet['taskArray']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $taskTimesheet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <?php $__currentLoopData = $taskTimesheet['dateArray']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dateTimeArray): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="timesheet-user">
                        
                        <td><span class="task-name pl-3"><?php echo e($taskTimesheet['task_name']); ?></span></td>
                        <?php $__currentLoopData = $dateTimeArray['week']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dateSubArray): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="text-center"><span class="task-time day-time" data-type="<?php echo e($dateSubArray['type']); ?>" data-user-id="<?php echo e($dateTimeArray['user_id']); ?>" data-project-id="<?php echo e($timesheet['project_id']); ?>" data-task-id="<?php echo e($taskTimesheet['task_id']); ?>" data-date="<?php echo e($dateSubArray['date']); ?>" data-ajax-timesheet-popup="true" data-url="<?php echo e($dateSubArray['url']); ?>"><?php echo e($dateSubArray['time'] != '00:00' ? $dateSubArray['time'] : '-'); ?></span></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td class="text-center"><span class="total-task-time day-time"><?php echo e($dateTimeArray['totaltime']); ?></span></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>

    <?php $__currentLoopData = $timesheetArray; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $timesheet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="">
            <td><span class="task-name"><?php echo e($timesheet['task_name']); ?></span></td>
            <?php $__currentLoopData = $timesheet['dateArray']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $datetime): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td><span class="task-time day-time" data-type="<?php echo e($datetime['type']); ?>" data-task-id="<?php echo e($timesheet['task_id']); ?>" data-date="<?php echo e($datetime['date']); ?>" data-ajax-timesheet-popup="true" data-url="<?php echo e($datetime['url']); ?>"><?php echo e($datetime['time'] != '00:00' ? $datetime['time'] : '-'); ?></span></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <td class="text-center"><span class="total-task-time day-time"><?php echo e($timesheet['totaltime']); ?></span></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
    </tbody>
    <tfoot>
    <tr class="">
        <td class="font-weight-700"><?php echo e(__('Total')); ?></td>
        <?php $__currentLoopData = $totalDateTimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $totaldatetime): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <td class="total-date-time text-center" style="padding: 3px 19px !important;"><?php echo e($totaldatetime != '00:00' ? $totaldatetime : '-'); ?></td>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        
        <td class="text-center">
            <div class="total-value" style="padding: 3px 19px !important;">
                <?php echo e($calculatedtotaltaskdatetime); ?>

            </div>
        </td>

    </tr>
    </tfoot>
</table>
<?php /**PATH C:\Users\Bdrango\Desktop\main_file\resources\views/projects/timesheets/week.blade.php ENDPATH**/ ?>