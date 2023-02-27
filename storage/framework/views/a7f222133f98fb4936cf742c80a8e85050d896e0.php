<?php echo e(Form::open(array('url' => 'pipelines'))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            <?php echo e(Form::label('name', __('Pipeline Name'),['class'=>'form-label'])); ?>

            <?php echo e(Form::text('name', '', array('class' => 'form-control','required'=>'required'))); ?>

        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="<?php echo e(__('Cancel')); ?>" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Create')); ?>" class="btn  btn-primary">
</div>
<?php echo e(Form::close()); ?>

<?php /**PATH C:\laragon\www\erpgo\resources\views/pipelines/create.blade.php ENDPATH**/ ?>