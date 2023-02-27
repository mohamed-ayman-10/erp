<?php $__env->startPush('script-page'); ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Customer-Detail')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('customer.index')); ?>"><?php echo e(__('Customer')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e($customer['name']); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-btn'); ?>
    <div class="float-end">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create invoice')): ?>
            <a href="<?php echo e(route('invoice.create',$customer->id)); ?>" class="btn btn-sm btn-primary">
                <?php echo e(__('Create Invoice')); ?>

            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create proposal')): ?>
            <a href="<?php echo e(route('proposal.create',$customer->id)); ?>" class="btn btn-sm btn-primary">
                <?php echo e(__('Create Proposal')); ?>

            </a>

        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit customer')): ?>
            <a href="#" data-size="lg" data-url="<?php echo e(route('customer.edit',$customer['id'])); ?>" data-ajax-popup="true" title="<?php echo e(__('Edit Customer')); ?>" data-bs-toggle="tooltip" data-original-title="<?php echo e(__('Edit')); ?>" class="btn btn-sm btn-primary">
                <i class="ti ti-pencil"></i>
            </a>
        <?php endif; ?>


        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete customer')): ?>
            <?php echo Form::open(['method' => 'DELETE','class' => 'delete-form-btn', 'route' => ['customer.destroy', $customer['id']]]); ?>


            <a href="#" data-bs-toggle="tooltip" title="<?php echo e(__('Delete Customer')); ?>" data-confirm="<?php echo e(__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')); ?>" data-confirm-yes="document.getElementById('delete-form-<?php echo e($customer['id']); ?>').submit();" class="btn btn-sm btn-danger bs-pass-para">
                <i class="ti ti-trash text-white"></i>
            </a>
            <?php echo Form::close(); ?>


        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="card customer-detail-box customer_card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo e(__('Customer Info')); ?></h5>
                    <p class="card-text mb-0"><?php echo e($customer['name']); ?></p>
                    <p class="card-text mb-0"><?php echo e($customer['email']); ?></p>
                    <p class="card-text mb-0"><?php echo e($customer['contact']); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="card customer-detail-box customer_card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo e(__('Billing Info')); ?></h5>
                    <p class="card-text mb-0"><?php echo e($customer['billing_name']); ?></p>
                    <p class="card-text mb-0"><?php echo e($customer['billing_phone']); ?></p>
                    <p class="card-text mb-0"><?php echo e($customer['billing_address']); ?></p>
                    <p class="card-text mb-0"><?php echo e($customer['billing_city'].', '. $customer['billing_state'] .', '.$customer['billing_country']); ?></p>
                    <p class="card-text mb-0"><?php echo e($customer['billing_zip']); ?></p>
                </div>
            </div>

        </div>
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="card customer-detail-box customer_card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo e(__('Shipping Info')); ?></h5>
                    <p class="card-text mb-0"><?php echo e($customer['shipping_name']); ?></p>
                    <p class="card-text mb-0"><?php echo e($customer['shipping_phone']); ?></p>
                    <p class="card-text mb-0"><?php echo e($customer['shipping_address']); ?></p>
                    <p class="card-text mb-0"><?php echo e($customer['shipping_city'].', '. $customer['billing_state'] .', '.$customer['billing_country']); ?></p>
                    <p class="card-text mb-0"><?php echo e($customer['shipping_zip']); ?></p>
                </div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card pb-0">
                <div class="card-body">
                    <h5 class="card-title"><?php echo e(__('Company Info')); ?></h5>

                    <div class="row">
                        <?php
                            $totalInvoiceSum=$customer->customerTotalInvoiceSum($customer['id']);
                            $totalInvoice=$customer->customerTotalInvoice($customer['id']);
                            $averageSale=($totalInvoiceSum!=0)?$totalInvoiceSum/$totalInvoice:0;
                        ?>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0"><?php echo e(__('Customer Id')); ?></p>
                                <h6 class="report-text mb-3"><?php echo e(AUth::user()->customerNumberFormat($customer['customer_id'])); ?></h6>
                                <p class="card-text mb-0"><?php echo e(__('Total Sum of Invoices')); ?></p>
                                <h6 class="report-text mb-0"><?php echo e(\Auth::user()->priceFormat($totalInvoiceSum)); ?></h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0"><?php echo e(__('Date of Creation')); ?></p>
                                <h6 class="report-text mb-3"><?php echo e(\Auth::user()->dateFormat($customer['created_at'])); ?></h6>
                                <p class="card-text mb-0"><?php echo e(__('Quantity of Invoice')); ?></p>
                                <h6 class="report-text mb-0"><?php echo e($totalInvoice); ?></h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0"><?php echo e(__('Balance')); ?></p>
                                <h6 class="report-text mb-3"><?php echo e(\Auth::user()->priceFormat($customer['balance'])); ?></h6>
                                <p class="card-text mb-0"><?php echo e(__('Average Sales')); ?></p>
                                <h6 class="report-text mb-0"><?php echo e(\Auth::user()->priceFormat($averageSale)); ?></h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0"><?php echo e(__('Overdue')); ?></p>
                                <h6 class="report-text mb-3"><?php echo e(\Auth::user()->priceFormat($customer->customerOverdue($customer['id']))); ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style table-border-style">
                    <h5 class="d-inline-block mb-5"><?php echo e(__('Proposal')); ?></h5>

                    <div class="table-responsive">
                        <table class="table ">
                            <thead>
                            <tr>
                                <th><?php echo e(__('Proposal')); ?></th>
                                <th><?php echo e(__('Issue Date')); ?></th>
                                <th><?php echo e(__('Amount')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <?php if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal')): ?>
                                    <th width="10%"> <?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__currentLoopData = $customer->customerProposal($customer->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proposal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="Id">
                                        <a href="<?php echo e(route('proposal.show',\Crypt::encrypt($proposal->id))); ?>" class="btn btn-outline-primary"><?php echo e(AUth::user()->proposalNumberFormat($proposal->proposal_id)); ?>

                                        </a>
                                    </td>
                                    <td><?php echo e(Auth::user()->dateFormat($proposal->issue_date)); ?></td>
                                    <td><?php echo e(Auth::user()->priceFormat($proposal->getTotal())); ?></td>
                                    <td>
                                        <?php if($proposal->status == 0): ?>
                                            <span class="badge bg-primary p-2 px-3 rounded"><?php echo e(__(\App\Models\Proposal::$statues[$proposal->status])); ?></span>
                                        <?php elseif($proposal->status == 1): ?>
                                            <span class="badge bg-warning p-2 px-3 rounded"><?php echo e(__(\App\Models\Proposal::$statues[$proposal->status])); ?></span>
                                        <?php elseif($proposal->status == 2): ?>
                                            <span class="badge bg-danger p-2 px-3 rounded"><?php echo e(__(\App\Models\Proposal::$statues[$proposal->status])); ?></span>
                                        <?php elseif($proposal->status == 3): ?>
                                            <span class="badge bg-info p-2 px-3 rounded"><?php echo e(__(\App\Models\Proposal::$statues[$proposal->status])); ?></span>
                                        <?php elseif($proposal->status == 4): ?>
                                            <span class="badge bg-success p-2 px-3 rounded"><?php echo e(__(\App\Models\Proposal::$statues[$proposal->status])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal')): ?>
                                        <td class="Action">
                                            <span>
                                              <?php if($proposal->is_convert==0): ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('convert invoice')): ?>
                                                        <div class="action-btn bg-warning ms-2">
                                                        <?php echo Form::open(['method' => 'get', 'route' => ['proposal.convert', $proposal->id],'id'=>'proposal-form-'.$proposal->id]); ?>

                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="<?php echo e(__('Convert to Invoice')); ?>" title="<?php echo e(__('Convert to Invoice')); ?>" data-confirm="You want to confirm convert to invoice. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('proposal-form-<?php echo e($proposal->id); ?>').submit();">
                                                            <i class="ti ti-exchange text-white"></i>

                                                            </a>
                                                         <?php echo Form::close(); ?>

                                                    </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('convert invoice')): ?>
                                                        <div class="action-btn bg-warning ms-2">
                                                        <a href="<?php echo e(route('invoice.show',\Crypt::encrypt($proposal->converted_invoice_id))); ?>"
                                                           class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('Already convert to Invoice')); ?>" data-original-title="<?php echo e(__('Already convert to Invoice')); ?>" >
                                                            <i class="ti ti-file text-white"></i>
                                                        </a>
                                                    </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('duplicate proposal')): ?>
                                                    <div class="action-btn bg-success ms-2">
                                                    <?php echo Form::open(['method' => 'get', 'route' => ['proposal.duplicate', $proposal->id],'id'=>'duplicate-form-'.$proposal->id]); ?>


                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="<?php echo e(__('Duplicate')); ?>"  title="<?php echo e(__('Duplicate Proposal')); ?>" data-confirm="You want to confirm duplicate this invoice. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('duplicate-form-<?php echo e($proposal->id); ?>').submit();">
                                                        <i class="ti ti-copy text-white text-white"></i>

                                                        </a>
                                                         <?php echo Form::close(); ?>

                                                    </div>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show proposal')): ?>
                                                    <div class="action-btn bg-info ms-2">
                                                            <a href="<?php echo e(route('proposal.show',\Crypt::encrypt($proposal->id))); ?>" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('Show')); ?>" data-original-title="<?php echo e(__('Detail')); ?>">
                                                                <i class="ti ti-eye text-white text-white"></i>
                                                            </a>
                                                        </div>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit proposal')): ?>
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="<?php echo e(route('proposal.edit',\Crypt::encrypt($proposal->id))); ?>" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>" data-original-title="<?php echo e(__('Edit')); ?>">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete proposal')): ?>
                                                    <div class="action-btn bg-danger ms-2">
                                                        <?php echo Form::open(['method' => 'DELETE', 'route' => ['proposal.destroy', $proposal->id],'id'=>'delete-form-'.$proposal->id]); ?>


                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip"  title="Delete" data-original-title="<?php echo e(__('Delete')); ?>" data-confirm="<?php echo e(__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')); ?>" data-confirm-yes="document.getElementById('delete-form-<?php echo e($proposal->id); ?>').submit();">
                                                            <i class="ti ti-trash text-white text-white"></i>
                                                         </a>
                                                        <?php echo Form::close(); ?>

                                                    </div>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style table-border-style">
                    <h5 class=" d-inline-block mb-5"><?php echo e(__('Invoice')); ?></h5>

                    <div class="table-responsive">
                        <table class="table ">
                            <thead>
                            <tr>
                                <th><?php echo e(__('Invoice')); ?></th>
                                <th><?php echo e(__('Issue Date')); ?></th>
                                <th><?php echo e(__('Due Date')); ?></th>
                                <th><?php echo e(__('Due Amount')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <?php if(Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice')): ?>
                                    <th width="10%"> <?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__currentLoopData = $customer->customerInvoice($customer->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="Id">
                                        <a href="<?php echo e(route('invoice.show',\Crypt::encrypt($invoice->id))); ?>" class="btn btn-outline-primary"><?php echo e(AUth::user()->invoiceNumberFormat($invoice->invoice_id)); ?>

                                        </a>
                                    </td>
                                    <td><?php echo e(\Auth::user()->dateFormat($invoice->issue_date)); ?></td>
                                    <td>
                                        <?php if(($invoice->due_date < date('Y-m-d'))): ?>
                                            <p class="text-danger"> <?php echo e(\Auth::user()->dateFormat($invoice->due_date)); ?></p>
                                        <?php else: ?>
                                            <?php echo e(\Auth::user()->dateFormat($invoice->due_date)); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e(\Auth::user()->priceFormat($invoice->getDue())); ?></td>
                                    <td>
                                        <?php if($invoice->status == 0): ?>
                                            <span class="badge bg-primary p-2 px-3 rounded"><?php echo e(__(\App\Models\Invoice::$statues[$invoice->status])); ?></span>
                                        <?php elseif($invoice->status == 1): ?>
                                            <span class="badge bg-warning p-2 px-3 rounded"><?php echo e(__(\App\Models\Invoice::$statues[$invoice->status])); ?></span>
                                        <?php elseif($invoice->status == 2): ?>
                                            <span class="badge bg-danger p-2 px-3 rounded"><?php echo e(__(\App\Models\Invoice::$statues[$invoice->status])); ?></span>
                                        <?php elseif($invoice->status == 3): ?>
                                            <span class="badge bg-info p-2 px-3 rounded"><?php echo e(__(\App\Models\Invoice::$statues[$invoice->status])); ?></span>
                                        <?php elseif($invoice->status == 4): ?>
                                            <span class="badge bg-success p-2 px-3 rounded"><?php echo e(__(\App\Models\Invoice::$statues[$invoice->status])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if(Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice')): ?>
                                        <td class="Action">
                                            <span>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('duplicate invoice')): ?>
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-original-title="<?php echo e(__('Duplicate')); ?>" title="<?php echo e(__('Duplicate Invoice')); ?>" data-confirm="You want to confirm this action. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('duplicate-form-<?php echo e($invoice->id); ?>').submit();">
                                                        <i class="ti ti-copy text-white text-white"></i>
                                                        <?php echo Form::open(['method' => 'get', 'route' => ['invoice.duplicate', $invoice->id],'id'=>'duplicate-form-'.$invoice->id]); ?>

                                                            <?php echo Form::close(); ?>

                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show invoice')): ?>
                                                    <div class="action-btn bg-info ms-2">
                                                            <a href="<?php echo e(route('invoice.show',\Crypt::encrypt($invoice->id))); ?>" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('Show')); ?>" data-original-title="<?php echo e(__('Detail')); ?>">
                                                                <i class="ti ti-eye text-white text-white"></i>
                                                            </a>
                                                        </div>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit invoice')): ?>
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="<?php echo e(route('invoice.edit',\Crypt::encrypt($invoice->id))); ?>" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>" data-original-title="<?php echo e(__('Edit')); ?>">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete invoice')): ?>
                                                    <div class="action-btn bg-danger ms-2">
                                                        <?php echo Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id],'id'=>'delete-form-'.$invoice->id]); ?>


                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>" data-original-title="<?php echo e(__('Delete')); ?>" data-confirm="<?php echo e(__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')); ?>" data-confirm-yes="document.getElementById('delete-form-<?php echo e($invoice->id); ?>').submit();">
                                                            <i class="ti ti-trash text-white text-white"></i>
                                                        </a>
                                                        <?php echo Form::close(); ?>

                                                    </div>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\erpgo\resources\views/customer/show.blade.php ENDPATH**/ ?>