<?php echo e(Form::model($stage, array('route' => array('stages.update', $stage->id), 'method' => 'PUT'))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            <?php echo e(Form::label('name', __('Stage Name'),['class'=>'form-label'])); ?>

            <?php echo e(Form::text('name', null, array('class' => 'form-control','required'=>'required'))); ?>

        </div>
        <div class="form-group col-12">
            <?php echo e(Form::label('pipeline_id', __('Pipeline'),['class'=>'form-label'])); ?>

            <?php echo e(Form::select('pipeline_id', $pipelines,null, array('class' => 'form-control select2','required'=>'required'))); ?>

        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="<?php echo e(__('Cancel')); ?>" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Update')); ?>" class="btn  btn-primary">
</div>

<?php echo e(Form::close()); ?>

<?php /**PATH C:\laragon\www\erpgo\resources\views/stages/edit.blade.php ENDPATH**/ ?>