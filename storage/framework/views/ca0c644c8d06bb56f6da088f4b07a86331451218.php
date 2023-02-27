<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Deals')); ?> <?php if($pipeline): ?> - <?php echo e($pipeline->name); ?> <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css-page'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/summernote/summernote-bs4.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startPush('script-page'); ?>
    <script src="<?php echo e(asset('css/summernote/summernote-bs4.js')); ?>"></script>
    <script>
        $(document).on("change", ".change-pipeline select[name=default_pipeline_id]", function () {
            $('#change-pipeline').submit();
        });
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Lead')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('action-btn'); ?>
    <div class="float-end">
        <a href="<?php echo e(route('deals.index')); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Kanban View')); ?>" class="btn btn-sm btn-primary">
            <i class="ti ti-layout-grid"></i>
        </a>
        <a href="#" data-size="lg" data-url="<?php echo e(route('deals.create')); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Create New Deal')); ?>" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php if($pipeline): ?>
        <div class="row">
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <small class="text-muted"><?php echo e(__('Total Deals')); ?></small>
                                <h3 class="m-0"><?php echo e($cnt_deal['total']); ?></h3>
                            </div>
                            <div class="col-auto">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <small class="text-muted"><?php echo e(__('This Month Total Deals')); ?></small>
                                <h3 class="m-0"><?php echo e($cnt_deal['this_month']); ?></h3>
                            </div>
                            <div class="col-auto">
                                <div class="theme-avtar bg-primary">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <small class="text-muted"><?php echo e(__('This Week Total Deals')); ?></small>
                                <h3 class="m-0"><?php echo e($cnt_deal['this_week']); ?></h3>
                            </div>
                            <div class="col-auto">
                                <div class="theme-avtar bg-warning">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <small class="text-muted"><?php echo e(__('Last 30 Days Total Deals')); ?></small>
                                <h3 class="m-0"><?php echo e($cnt_deal['last_30days']); ?></h3>
                            </div>
                            <div class="col-auto">
                                <div class="theme-avtar bg-danger">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th><?php echo e(__('Name')); ?></th>
                                    <th><?php echo e(__('Price')); ?></th>
                                    <th><?php echo e(__('Stage')); ?></th>
                                    <th><?php echo e(__('Tasks')); ?></th>
                                    <th><?php echo e(__('Users')); ?></th>
                                    <th width="300px"><?php echo e(__('Action')); ?></th>

                                </tr>
                                </thead>
                                <tbody>
                                <?php if(count($deals) > 0): ?>
                                    <?php $__currentLoopData = $deals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($deal->name); ?></td>
                                            <td><?php echo e(\Auth::user()->priceFormat($deal->price)); ?></td>
                                            <td><?php echo e($deal->stage->name); ?></td>
                                            <td><?php echo e(count($deal->tasks)); ?>/<?php echo e(count($deal->complete_tasks)); ?></td>
                                            <td>
                                                <?php $__currentLoopData = $deal->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <a href="#" class="btn btn-sm mr-1 p-0 rounded-circle">
                                                        <img alt="image" data-toggle="tooltip" data-original-title="<?php echo e($user->name); ?>" <?php if($user->avatar): ?> src="<?php echo e(asset('/storage/uploads/avatar/'.$user->avatar)); ?>" <?php else: ?> src="<?php echo e(asset('/storage/uploads/avatar/avatar.png')); ?>" <?php endif; ?> class="rounded-circle " width="25" height="25">
                                                    </a>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </td>
                                            <?php if(\Auth::user()->type != 'Client'): ?>
                                                <td class="Action">
                                                    <span>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view deal')): ?>
                                                            <?php if($deal->is_active): ?>
                                                                <div class="action-btn bg-warning ms-2">
                                                                <a href="<?php echo e(route('deals.show',$deal->id)); ?>" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-size="xl" data-bs-toggle="tooltip" title="<?php echo e(__('View')); ?>" data-title="<?php echo e(__('Lead Detail')); ?>">
                                                                    <i class="ti ti-eye text-white"></i>
                                                                </a>
                                                            </div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit deal')): ?>
                                                            <div class="action-btn bg-info ms-2">
                                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="<?php echo e(URL::to('deals/'.$deal->id.'/edit')); ?>" data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>" data-title="<?php echo e(__('Lead Edit')); ?>">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete deal')): ?>
                                                            <div class="action-btn bg-danger ms-2">
                                                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['deals.destroy', $deal->id],'id'=>'delete-form-'.$deal->id]); ?>

                                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>"><i class="ti ti-trash text-white"></i></a>

                                                                <?php echo Form::close(); ?>

                                                             </div>
                                                        <?php endif; ?>
                                                    </span>

                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <tr class="font-style">
                                        <td colspan="6" class="text-center"><?php echo e(__('No data available in table')); ?></td>
                                    </tr>
                                <?php endif; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\erpgo\resources\views/deals/list.blade.php ENDPATH**/ ?>