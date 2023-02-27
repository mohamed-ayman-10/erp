<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Settings')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Settings')); ?></li>
<?php $__env->stopSection(); ?>

<?php
    $lang = \App\Models\Utility::getValByName('default_language');
   // $logo=asset(Storage::url('uploads/logo/'));
       $logo=\App\Models\Utility::get_file('uploads/logo');

    $logo_light = \App\Models\Utility::getValByName('logo_light');
    $logo_dark = \App\Models\Utility::getValByName('logo_dark');
    $company_favicon = \App\Models\Utility::getValByName('company_favicon');
    $setting = \App\Models\Utility::colorset();
    $mode_setting = \App\Models\Utility::mode_layout();
    $color = (!empty($setting['color'])) ? $setting['color'] : 'theme-3';
    $SITE_RTL= isset($setting['SITE_RTL'])?$setting['SITE_RTL']:'off';
?>


<?php
    $file_type = config('files_types');
    $setting = App\Models\Utility::settings();

   $local_storage_validation    = $setting['local_storage_validation'];
   $local_storage_validations   = explode(',', $local_storage_validation);

   $s3_storage_validation    = $setting['s3_storage_validation'];
   $s3_storage_validations   = explode(',', $s3_storage_validation);

   $wasabi_storage_validation    = $setting['wasabi_storage_validation'];
   $wasabi_storage_validations   = explode(',', $wasabi_storage_validation);

?>

<?php $__env->startPush('css-page'); ?>

    <?php if($color == 'theme-3'): ?>
        <style>
            .btn-check:checked + .btn-outline-primary, .btn-check:active + .btn-outline-primary,
            .btn-outline-primary:active, .btn-outline-primary.active, .btn-outline-primary.dropdown-toggle.show {
                color: #ffffff;
                background-color: #6fd943 !important;
                border-color: #6fd943 !important;
            }


            .btn-outline-primary:hover
            {
                color: #ffffff;
                background-color: #6fd943 !important;
                border-color: #6fd943 !important;
            }


            .btn[class*="btn-outline-"]:hover {

                border-color: #6fd943 !important;
            }
        </style>
    <?php endif; ?>
    <?php if($color == 'theme-2'): ?>
        <style>
            .btn-check:checked + .btn-outline-primary, .btn-check:active + .btn-outline-primary, .btn-outline-primary:active, .btn-outline-primary.active, .btn-outline-primary.dropdown-toggle.show {
                color: #ffffff;
                background: linear-gradient(141.55deg, rgba(240, 244, 243, 0) 3.46%, #4ebbd3 99.86%)#1f3996 !important;
                border-color: #4ebbd3 !important;

            }

            .btn-outline-primary:hover
            {
                color: #ffffff;
                background: linear-gradient(141.55deg, rgba(240, 244, 243, 0) 3.46%, #4ebbd3 99.86%)#1f3996 !important;
                border-color: #4ebbd3 !important;
            }
            .btn.btn-outline-primary{
                color: #1F3996;
                border-color: #4ebbd3 !important;
            }
        </style>
    <?php endif; ?>
    <?php if($color == 'theme-4'): ?>
        <style>
            .btn-check:checked + .btn-outline-primary, .btn-check:active + .btn-outline-primary, .btn-outline-primary:active, .btn-outline-primary.active, .btn-outline-primary.dropdown-toggle.show {
                color: #ffffff;
                background-color: #584ed2 !important;
                border-color: #584ed2 !important;

            }

            .btn-outline-primary:hover
            {
                color: #ffffff;
                background-color: #584ed2 !important;
                border-color: #584ed2 !important;
            }
            .btn.btn-outline-primary{
                color: #584ed2;
                border-color: #584ed2 !important;
            }
        </style>
    <?php endif; ?>
    <?php if($color == 'theme-1'): ?>
        <style>
            .btn-check:checked + .btn-outline-primary, .btn-check:active + .btn-outline-primary,
            .btn-outline-primary:active, .btn-outline-primary.active, .btn-outline-primary.dropdown-toggle.show {
                color: #ffffff;
                background: linear-gradient(141.55deg, rgba(81, 69, 157, 0) 3.46%, rgba(255, 58, 110, 0.6) 99.86%), #51459d !important;
                border-color: #51459d !important;
            }


            body.theme-1 .btn-outline-primary:hover
            {
                color: #ffffff;
                background: linear-gradient(141.55deg, rgba(81, 69, 157, 0) 3.46%, rgba(255, 58, 110, 0.6) 99.86%), #51459d !important;
                border-color: #51459d !important;
            }
        </style>
    <?php endif; ?>
<?php $__env->stopPush(); ?>


<?php $__env->startPush('script-page'); ?>
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300,
        })
        $(".list-group-item").click(function(){
            $('.list-group-item').filter(function(){
                return this.href == id;
            }).parent().removeClass('text-primary');
        });

        function check_theme(color_val) {
            $('#theme_color').prop('checked', false);
            $('input[value="' + color_val + '"]').prop('checked', true);
        }

        $(document).ready(function() {
            if ($('.gdpr_fulltime').is(':checked')) {
                $('.fulltime').show();
            } else {
                $('.fulltime').hide();
            }
            $('#gdpr_cookie').on('change', function() {
                if ($('.gdpr_fulltime').is(':checked')) {
                    $('.fulltime').show();
                } else {
                    $('.fulltime').hide();
                }
            });
        });

        // storage setting
        $(document).on('change','[name=storage_setting]',function(){
            if($(this).val() == 's3'){
                $('.s3-setting').removeClass('d-none');
                $('.wasabi-setting').addClass('d-none');
                $('.local-setting').addClass('d-none');
            }else if($(this).val() == 'wasabi'){
                $('.s3-setting').addClass('d-none');
                $('.wasabi-setting').removeClass('d-none');
                $('.local-setting').addClass('d-none');
            }else{
                $('.s3-setting').addClass('d-none');
                $('.wasabi-setting').addClass('d-none');
                $('.local-setting').removeClass('d-none');
            }
        });
    </script>

    <script>
        document.getElementById('logo_dark').onchange = function () {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image').src = src
        }
        document.getElementById('logo_light').onchange = function () {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('imae1').src = src
        }
        document.getElementById('favicon').onchange = function () {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image2').src = src
        }
    </script>

    <script type="text/javascript">

        $(document).on("click", '.send_email', function(e)
        {

            e.preventDefault();
            var title = $(this).attr('data-title');
            var size = 'md';
            var url = $(this).attr('data-url');

            if (typeof url != 'undefined') {
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-' + size);
                $("#commonModal").modal('show');


                $.post(url, {
                    _token:'<?php echo e(csrf_token()); ?>',
                    mail_driver: $("#mail_driver").val(),
                    mail_host: $("#mail_host").val(),
                    mail_port: $("#mail_port").val(),
                    mail_username: $("#mail_username").val(),
                    mail_password: $("#mail_password").val(),
                    mail_encryption: $("#mail_encryption").val(),
                    mail_from_address: $("#mail_from_address").val(),
                    mail_from_name: $("#mail_from_name").val(),

                }, function(data) {
                    $('#commonModal .body').html(data);
                });
            }
        });
        $(document).on('submit', '#test_email', function(e) {
            e.preventDefault();
            // $("#email_sending").show();
            var post = $(this).serialize();
            var url = $(this).attr('action');
            $.ajax({
                type: "post",
                url: url,
                data: post,
                cache: false,
                beforeSend: function() {
                    $('#test_email .btn-create').attr('disabled', 'disabled');
                },
                success: function(data) {
                    // console.log(data)
                    if (data.success) {
                        show_toastr('success', data.message, 'success');
                    } else {
                        show_toastr('error', data.message, 'error');
                    }
                    // $("#email_sending").hide();
                    $('#commonModal').modal('hide');


                },
                complete: function() {
                    $('#test_email .btn-create').removeAttr('disabled');
                },
            });
        });
    </script>


<?php $__env->stopPush(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Settings')); ?></li>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#brand-settings" class="list-group-item list-group-item-action border-0"><?php echo e(__('Brand Settings')); ?>

                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#email-settings" class="list-group-item list-group-item-action border-0"><?php echo e(__('Email Settings')); ?>

                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#payment-settings" class="list-group-item list-group-item-action border-0"><?php echo e(__('Payment Settings')); ?>

                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#pusher-settings" class="list-group-item list-group-item-action border-0"><?php echo e(__('Pusher Settings')); ?>

                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#recaptcha_settings" class="list-group-item list-group-item-action border-0"><?php echo e(__('ReCaptcha Settings')); ?>

                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#storage-settings" class="list-group-item list-group-item-action border-0"><?php echo e(__('Storage Settings')); ?>

                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <!--Site Settings-->
                    <div id="brand-settings" class="card">

                        <div class="card-header">
                            <h5><?php echo e(__('Brand Settings')); ?></h5>
                        </div>
                        <?php echo e(Form::model($settings, ['url' => 'systems', 'method' => 'POST', 'enctype' => 'multipart/form-data'])); ?>


                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-md-6">
                                    <div class="card logo_card">
                                        <div class="card-header">
                                            <h5><?php echo e(__('Logo dark')); ?></h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class=" setting-card">
                                                <div class="logo-content mt-4">
                                                    <img id="image" src="<?php echo e($logo.'/'.(isset($logo_dark) && !empty($logo_dark)?$logo_dark:'logo-dark.png')); ?>"
                                                         class="big-logo">
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="logo_dark">
                                                        <div class=" bg-primary company_logo_update"> <i
                                                                class="ti ti-upload px-1"></i><?php echo e(__('Choose file here')); ?>

                                                        </div>

                                                        <input type="file" name="logo_dark" id="logo_dark" class="form-control file" data-filename="logo_dark">
                                                    </label>
                                                </div>
                                                <?php $__errorArgs = ['logo_dark'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <div class="row">
                                                    <span class="invalid-logo" role="alert">
                                                        <strong class="text-danger"><?php echo e($message); ?></strong>
                                                    </span>
                                                    </div>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-md-6">
                                    <div class="card logo_card">
                                        <div class="card-header">
                                            <h5><?php echo e(__('Logo Light')); ?></h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class=" setting-card">
                                                <div class="logo-content mt-4">
                                                    <img id="image1" src="<?php echo e($logo.'/'.(isset($logo_light) && !empty($logo_light)?$logo_light:'logo-light.png')); ?>"
                                                         class="big-logo img_setting">
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="logo_light">
                                                        <div class=" bg-primary dark_logo_update"> <i class="ti ti-upload px-1">
                                                            </i><?php echo e(__('Choose file here')); ?>

                                                        </div>
                                                        <input type="file" name="logo_light" id="logo_light" class="form-control file" data-filename="logo_light">


                                                    </label>
                                                </div>
                                                <?php $__errorArgs = ['logo_light'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="row">
                                                    <span class="invalid-logo" role="alert">
                                                        <strong class="text-danger"><?php echo e($message); ?></strong>
                                                    </span>
                                                </div>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-md-6">
                                    <div class="card logo_card">
                                        <div class="card-header">
                                            <h5><?php echo e(__('Favicon')); ?></h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class=" setting-card">
                                                <div class="logo-content mt-4">
                                                    <img id="image2" src="<?php echo e($logo.'/'.(isset($company_favicon) && !empty($company_favicon)?$company_favicon:'favicon.png')); ?>" width="50px"
                                                         class="img_setting">
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="favicon">
                                                        <div class="bg-primary company_favicon_update"> <i
                                                                class="ti ti-upload px-1"></i><?php echo e(__('Choose file here')); ?>

                                                        </div>


                                                        <input type="file" class="form-control file"  id="favicon" name="favicon"
                                                               data-filename="favicon">
                                                    </label>
                                                </div>
                                                <?php $__errorArgs = ['favicon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="row">
                                                    <span class="invalid-logo" role="alert">
                                                            <strong class="text-danger"><?php echo e($message); ?></strong>
                                                    </span>
                                                </div>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row ">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?php echo e(Form::label('title_text',__('Title Text'),array('class'=>'form-label'))); ?>

                                            <?php echo e(Form::text('title_text',null,array('class'=>'form-control','placeholder'=>__('Title Text')))); ?>

                                            <?php $__errorArgs = ['title_text'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-title_text" role="alert">
                                                     <strong class="text-danger"><?php echo e($message); ?></strong>
                                                 </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?php echo e(Form::label('footer_text',__('Footer Text'),['class'=>'form-label'])); ?>

                                            <?php echo e(Form::text('footer_text',Utility::getValByName('footer_text'),array('class'=>'form-control','placeholder'=>__('Enter Footer Text')))); ?>

                                            <?php $__errorArgs = ['footer_text'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-footer_text" role="alert">
                                            <strong class="text-danger"><?php echo e($message); ?></strong>
                                        </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?php echo e(Form::label('default_language',__('Default Language'),['class'=>'form-label text-dark'])); ?>

                                            <div class="changeLanguage">
                                                <select name="default_language" id="default_language" class="form-control select">
                                                    <?php $__currentLoopData = \App\Models\Utility::languages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option <?php if($lang == $language): ?> selected <?php endif; ?> value="<?php echo e($language); ?>">
                                                            <?php echo e(Str::upper($language)); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <?php $__errorArgs = ['default_language'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-default_language" role="alert">
                                            <strong class="text-danger"><?php echo e($message); ?></strong>
                                        </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-3">
                                        <div class="custom-control custom-switch">
                                                <label class="text-dark mb-1 mt-3" for="SITE_RTL"><?php echo e(__('Enable RTL')); ?></label>
                                                <div class="">
                                                    <input type="checkbox" name="SITE_RTL" id="SITE_RTL" data-toggle="switchbutton"  data-onstyle="primary"  <?php echo e($settings['SITE_RTL'] == 'on' ? 'checked="checked"' : ''); ?>>
                                                    <label class="custom-control-label" for="SITE_RTL"></label>
                                                </div>
                                            </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label class="text-dark mb-1 mt-3" for="display_landing_page"><?php echo e(__('Enable Landing Page')); ?></label>
                                            <div class="form-check form-switch d-inline-block">
                                                <input type="checkbox" name="display_landing_page" class="form-check-input" id="display_landing_page" data-toggle="switchbutton" <?php echo e((Utility::getValByName('display_landing_page') == 'on') ? 'checked' : ''); ?> data-onstyle="primary">
                                                <label class="form-check-label" for="display_landing_page"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label class="text-dark mb-1 mt-3" for="signup_button"><?php echo e(__('Enable Sign-Up Page')); ?></label>
                                            <div class="">
                                                <input type="checkbox" name="enable_signup" id="enable_signup" data-toggle="switchbutton"  <?php echo e($settings['enable_signup'] == 'on' ? 'checked="checked"' : ''); ?> data-onstyle="primary">
                                                <label class="form-check-label" for="enable_signup"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <?php echo e(Form::label('gdpr_cookie', 'GDPR Cookie', ['class' => 'form-label text-dark mb-1 mt-3'])); ?>

                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" data-toggle="switchbutton" data-onstyle="primary" class="form-check-label gdpr_fulltime gdpr_type"
                                                       name="gdpr_cookie" id="gdpr_cookie"<?php echo e(isset($settings['gdpr_cookie']) && $settings['gdpr_cookie'] == 'on' ? 'checked="checked"' : ''); ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-12">
                                        <?php echo e(Form::label('cookie_text',__('GDPR Cookie Text'),array('class'=>'fulltime col-form-label text-dark text-dark') )); ?>

                                        <?php echo Form::textarea('cookie_text',$settings['cookie_text'], ['class'=>'form-control fulltime','rows'=>'4']); ?>

                                    </div>
                                </div>

                                <h4 class="small-title"><?php echo e(__('Theme Customizer')); ?></h4>
                                <div class="setting-card setting-logo-box p-3">
                                    <div class="row">
                                        <div class="col-lg-4 col-xl-4 col-md-4">
                                            <h6 class="mt-2">
                                                <i data-feather="credit-card" class="me-2"></i><?php echo e(__('Primary color settings')); ?>

                                            </h6>

                                            <hr class="my-2" />
                                            <div class="theme-color themes-color">
                                                <a href="#!" class="<?php echo e(($settings['color'] == 'theme-1') ? 'active_color' : ''); ?>" data-value="theme-1" onclick="check_theme('theme-1')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-1" style="display: none;">
                                                <a href="#!" class="<?php echo e(($settings['color'] == 'theme-2') ? 'active_color' : ''); ?> " data-value="theme-2" onclick="check_theme('theme-2')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-2" style="display: none;">
                                                <a href="#!" class="<?php echo e(($settings['color'] == 'theme-3') ? 'active_color' : ''); ?>" data-value="theme-3" onclick="check_theme('theme-3')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-3" style="display: none;">
                                                <a href="#!" class="<?php echo e(($settings['color'] == 'theme-4') ? 'active_color' : ''); ?>" data-value="theme-4" onclick="check_theme('theme-4')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-4" style="display: none;">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xl-4 col-md-4">
                                            <h6 class="mt-2">
                                                <i data-feather="layout" class="me-2"></i><?php echo e(__('Sidebar settings')); ?>

                                            </h6>
                                            <hr class="my-2" />
                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="form-check-input" id="cust-theme-bg" name="cust_theme_bg" <?php echo e(!empty($settings['cust_theme_bg']) && $settings['cust_theme_bg'] == 'on' ? 'checked' : ''); ?>/>
                                                <label class="form-check-label f-w-600 pl-1" for="cust-theme-bg"
                                                ><?php echo e(__('Transparent layout')); ?></label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xl-4 col-md-4">
                                            <h6 class="mt-2">
                                                <i data-feather="sun" class="me-2"></i><?php echo e(__('Layout settings')); ?>

                                            </h6>
                                            <hr class="my-2" />
                                            <div class="form-check form-switch mt-2">
                                                <input type="checkbox" class="form-check-input" id="cust-darklayout" name="cust_darklayout"<?php echo e(!empty($settings['cust_darklayout']) && $settings['cust_darklayout'] == 'on' ? 'checked' : ''); ?> />
                                                <label class="form-check-label f-w-600 pl-1" for="cust-darklayout"><?php echo e(__('Dark Layout')); ?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="form-group">
                                        <input class="btn btn-print-invoice btn-primary m-r-10" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                                <?php echo e(Form::close()); ?>

                            </div>
                        </div>
                    </div>
                    <!--Email Settings-->
                    <div id="email-settings" class="card">
                        <div class="card-header">
                            <h5><?php echo e(__('Email Settings')); ?></h5>
                        </div>
                        <div class="card-body">
                            <?php echo e(Form::open(['route' => 'email.settings', 'method' => 'post'])); ?>

                            <?php echo csrf_field(); ?>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?php echo e(Form::label('mail_driver', __('Mail Driver'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::text('mail_driver', env('MAIL_DRIVER'), ['class' => 'form-control', 'placeholder' => __('Enter Mail Driver')])); ?>

                                        <?php $__errorArgs = ['mail_driver'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-mail_driver" role="alert">
                                                <strong class="text-danger"><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?php echo e(Form::label('mail_host', __('Mail Host'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::text('mail_host', env('MAIL_HOST'), ['class' => 'form-control ', 'placeholder' => __('Enter Mail Host')])); ?>

                                        <?php $__errorArgs = ['mail_host'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-mail_driver" role="alert">
                                                <strong class="text-danger"><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?php echo e(Form::label('mail_port', __('Mail Port'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::text('mail_port', env('MAIL_PORT'), ['class' => 'form-control', 'placeholder' => __('Enter Mail Port')])); ?>

                                        <?php $__errorArgs = ['mail_port'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-mail_port" role="alert">
                                                <strong class="text-danger"><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?php echo e(Form::label('mail_username', __('Mail Username'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::text('mail_username', env('MAIL_USERNAME'), ['class' => 'form-control', 'placeholder' => __('Enter Mail Username')])); ?>

                                        <?php $__errorArgs = ['mail_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-mail_username" role="alert">
                                                <strong class="text-danger"><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?php echo e(Form::label('mail_password', __('Mail Password'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::text('mail_password', env('MAIL_PASSWORD'), ['class' => 'form-control', 'placeholder' => __('Enter Mail Password')])); ?>

                                        <?php $__errorArgs = ['mail_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-mail_password" role="alert">
                                                <strong class="text-danger"><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?php echo e(Form::label('mail_encryption', __('Mail Encryption'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::text('mail_encryption', env('MAIL_ENCRYPTION'), ['class' => 'form-control', 'placeholder' => __('Enter Mail Encryption')])); ?>

                                        <?php $__errorArgs = ['mail_encryption'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-mail_encryption" role="alert">
                                                <strong class="text-danger"><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?php echo e(Form::label('mail_from_address', __('Mail From Address'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::text('mail_from_address', env('MAIL_FROM_ADDRESS'), ['class' => 'form-control', 'placeholder' => __('Enter Mail From Address')])); ?>

                                        <?php $__errorArgs = ['mail_from_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-mail_from_address" role="alert">
                                                <strong class="text-danger"><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?php echo e(Form::label('mail_from_name', __('Mail From Name'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::text('mail_from_name', env('MAIL_FROM_NAME'), ['class' => 'form-control', 'placeholder' => __('Enter Mail From Name')])); ?>

                                        <?php $__errorArgs = ['mail_from_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-mail_from_name" role="alert">
                                                <strong class="text-danger"><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-footer d-flex justify-content-end">
                                    <div class="form-group me-2">
                                        <a href="#" data-url="<?php echo e(route('test.mail')); ?>"
                                           data-title="<?php echo e(__('Send Test Mail')); ?>" class="btn btn-primary send_email ">
                                            <?php echo e(__('Send Test Mail')); ?>

                                        </a>
                                    </div>


                                    <div class="form-group">
                                        <input class="btn btn-primary" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                            </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                    </div>

                    <!--Payment Settings-->
                    <div id="payment-settings" class="card">
                        <div class="card-header">
                            <h5><?php echo e(__('Payment Settings')); ?></h5>
                            <small class="text-muted"><?php echo e(__('These details will be used to collect subscription plan payments.Each subscription plan will have a payment button based on the below configuration.')); ?></small>

                        </div>
                        <div class="card-body">
                            <?php echo e(Form::open(['route' => 'payment.settings', 'method' => 'post'])); ?>

                            <?php echo csrf_field(); ?>
                            <div class="faq justify-content-center">
                                <div class="col-sm-12 col-md-10 col-xxl-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <?php echo e(Form::label('currency_symbol', __('Currency Symbol *'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Form::text('currency_symbol', env('CURRENCY_SYMBOL'), ['class' => 'form-control', 'required', 'placeholder' => __('Enter Currency Symbol')])); ?>

                                                <?php $__errorArgs = ['currency_symbol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-currency_symbol" role="alert">
                                                    <strong class="text-danger"><?php echo e($message); ?></strong>
                                                </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <?php echo e(Form::label('currency', __('Currency *'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Form::text('currency', env('CURRENCY'), ['class' => 'form-control font-style', 'required', 'placeholder' => __('Enter Currency')])); ?>

                                                <small> <?php echo e(__('Note: Add currency code as per three-letter ISO code.')); ?><br>
                                                    <a href="https://stripe.com/docs/currencies"
                                                       target="_blank"><?php echo e(__('You can find out how to do that here.')); ?></a></small> <br>
                                                <?php $__errorArgs = ['currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-currency" role="alert">
                                                    <strong class="text-danger"><?php echo e($message); ?></strong>
                                                </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion accordion-flush" id="accordionExample">

                                        <!-- Strip -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-2">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> <?php echo e(__('Stripe')); ?>

                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse1" class="accordion-collapse collapse"aria-labelledby="heading-2-2"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">

                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_stripe_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_stripe_enabled" id="is_stripe_enabled"<?php echo e((isset($admin_payment_setting['is_stripe_enabled']) && $admin_payment_setting['is_stripe_enabled'] == 'on') ? 'checked' : ''); ?>>
                                                                <label class="custom-control-label form-label" for="is_stripe_enabled"><?php echo e(__('Enable ')); ?></label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="stripe_key" class="col-form-label"><?php echo e(__('Stripe Key')); ?></label>
                                                                <input class="form-control" placeholder="<?php echo e(__('Stripe Key')); ?>" name="stripe_key" type="text" value="<?php echo e((!isset($admin_payment_setting['stripe_key']) || is_null($admin_payment_setting['stripe_key'])) ? '' : $admin_payment_setting['stripe_key']); ?>" id="stripe_key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="stripe_secret" class="col-form-label"><?php echo e(__('Stripe Secret')); ?></label>
                                                                <input class="form-control " placeholder="<?php echo e(__('Stripe Secret')); ?>" name="stripe_secret" type="text" value="<?php echo e((!isset($admin_payment_setting['stripe_secret']) || is_null($admin_payment_setting['stripe_secret'])) ? '' : $admin_payment_setting['stripe_secret']); ?>" id="stripe_secret">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Paypal -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-3">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> <?php echo e(__('Paypal')); ?>

                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse2" class="accordion-collapse collapse"aria-labelledby="heading-2-3"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>



                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_paypal_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_paypal_enabled" id="is_paypal_enabled"  <?php echo e(isset($admin_payment_setting['is_paypal_enabled']) && $admin_payment_setting['is_paypal_enabled'] == 'on' ? 'checked="checked"' : ''); ?>><label class="custom-control-label form-label" for="is_paypal_enabled"><?php echo e(__('Enable ')); ?></label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <label class="paypal-label col-form-label" for="paypal_mode"><?php echo e(__('Paypal Mode')); ?></label> <br>
                                                            <div class="d-flex">
                                                                <div class="mr-2" style="margin-right: 15px;">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">







                                                                            <label class="form-check-label text-dark <?php echo e(isset($admin_payment_setting['paypal_mode']) && $admin_payment_setting['paypal_mode'] == 'sandbox' ? 'active' : ''); ?>">
                                                                                <input type="radio" name="paypal_mode" value="sandbox" class="form-check-input"
                                                                                <?php echo e(!isset($admin_payment_setting['paypal_mode']) || $admin_payment_setting['paypal_mode'] == '' || $admin_payment_setting['paypal_mode'] == 'sandbox' ? 'checked="checked"' : ''); ?>" >
                                                                                <?php echo e(__('Sandbox')); ?>

                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mr-2">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">






                                                                            <label class="form-check-label text-dark <?php echo e(isset($admin_payment_setting['paypal_mode']) && $admin_payment_setting['paypal_mode'] == 'live' ? 'active' : ''); ?>">
                                                                                <input type="radio" name="paypal_mode" value="live" class="form-check-input"
                                                                                    <?php echo e(isset($admin_payment_setting['paypal_mode']) && $admin_payment_setting['paypal_mode'] == 'live' ? 'checked="checked"' : ''); ?>>
                                                                                <?php echo e(__('Live')); ?>

                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paypal_client_id" class="col-form-label"><?php echo e(__('Client ID')); ?></label>
                                                                <input type="text" name="paypal_client_id" id="paypal_client_id" class="form-control" value="<?php echo e((!isset($admin_payment_setting['paypal_client_id']) || is_null($admin_payment_setting['paypal_client_id'])) ? '' : $admin_payment_setting['paypal_client_id']); ?>" placeholder="<?php echo e(__('Client ID')); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paypal_secret_key" class="col-form-label"><?php echo e(__('Secret Key')); ?></label>
                                                                <input type="text" name="paypal_secret_key" id="paypal_secret_key" class="form-control" value="<?php echo e((!isset($admin_payment_setting['paypal_secret_key']) || is_null($admin_payment_setting['paypal_secret_key'])) ? '' : $admin_payment_setting['paypal_secret_key']); ?>" placeholder="<?php echo e(__('Secret Key')); ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Paystack -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-4">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="true" aria-controls="collapse3">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> <?php echo e(__('Paystack')); ?>

                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse3" class="accordion-collapse collapse"aria-labelledby="heading-2-4"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_paystack_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_paystack_enabled" id="is_paystack_enabled" <?php echo e((isset($admin_payment_setting['is_paystack_enabled']) && $admin_payment_setting['is_paystack_enabled'] == 'on') ? 'checked' : ''); ?>>
                                                                <label class="custom-control-label form-label" for="is_paystack_enabled"><?php echo e(__('Enable ')); ?></label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paypal_client_id" class="col-form-label"><?php echo e(__('Public Key')); ?></label>
                                                                <input type="text" name="paystack_public_key" id="paystack_public_key" class="form-control" value="<?php echo e((!isset($admin_payment_setting['paystack_public_key']) || is_null($admin_payment_setting['paystack_public_key'])) ? '' : $admin_payment_setting['paystack_public_key']); ?>" placeholder="<?php echo e(__('Public Key')); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paystack_secret_key" class="col-form-label"><?php echo e(__('Secret Key')); ?></label>
                                                                <input type="text" name="paystack_secret_key" id="paystack_secret_key" class="form-control" value="<?php echo e((!isset($admin_payment_setting['paystack_secret_key']) || is_null($admin_payment_setting['paystack_secret_key'])) ? '' : $admin_payment_setting['paystack_secret_key']); ?>" placeholder="<?php echo e(__('Secret Key')); ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- FLUTTERWAVE -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-5">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="true" aria-controls="collapse4">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> <?php echo e(__('Flutterwave')); ?>

                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse4" class="accordion-collapse collapse"aria-labelledby="heading-2-5"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_flutterwave_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_flutterwave_enabled" id="is_flutterwave_enabled" <?php echo e((isset($admin_payment_setting['is_flutterwave_enabled']) && $admin_payment_setting['is_flutterwave_enabled'] == 'on') ? 'checked' : ''); ?>>
                                                                <label class="custom-control-label form-label" for="is_flutterwave_enabled"><?php echo e(__('Enable ')); ?></label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paypal_client_id" class="col-form-label"><?php echo e(__('Public Key')); ?></label>
                                                                <input type="text" name="flutterwave_public_key" id="flutterwave_public_key" class="form-control" value="<?php echo e((!isset($admin_payment_setting['flutterwave_public_key']) || is_null($admin_payment_setting['flutterwave_public_key'])) ? '' : $admin_payment_setting['flutterwave_public_key']); ?>" placeholder="Public Key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paystack_secret_key" class="col-form-label"><?php echo e(__('Secret Key')); ?></label>
                                                                <input type="text" name="flutterwave_secret_key" id="flutterwave_secret_key" class="form-control" value="<?php echo e((!isset($admin_payment_setting['flutterwave_secret_key']) || is_null($admin_payment_setting['flutterwave_secret_key'])) ? '' : $admin_payment_setting['flutterwave_secret_key']); ?>" placeholder="Secret Key">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Razorpay -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-6">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="true" aria-controls="collapse5">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> <?php echo e(__('Razorpay')); ?>

                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse5" class="accordion-collapse collapse"aria-labelledby="heading-2-6"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_razorpay_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_razorpay_enabled" id="is_razorpay_enabled" <?php echo e(isset($admin_payment_setting['is_razorpay_enabled']) && $admin_payment_setting['is_razorpay_enabled'] == 'on' ? 'checked="checked"' : ''); ?>>
                                                                <label class="custom-control-label form-label" for="is_razorpay_enabled"><?php echo e(__('Enable ')); ?></label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paypal_client_id" class="col-form-label"><?php echo e(__('Public Key')); ?></label>

                                                                <input type="text" name="razorpay_public_key" id="razorpay_public_key" class="form-control" value="<?php echo e((!isset($admin_payment_setting['razorpay_public_key']) || is_null($admin_payment_setting['razorpay_public_key'])) ? '' : $admin_payment_setting['razorpay_public_key']); ?>" placeholder="Public Key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paystack_secret_key" class="col-form-label"><?php echo e(__('Secret Key')); ?></label>
                                                                <input type="text" name="razorpay_secret_key" id="razorpay_secret_key" class="form-control" value="<?php echo e((!isset($admin_payment_setting['razorpay_secret_key']) || is_null($admin_payment_setting['razorpay_secret_key'])) ? '' : $admin_payment_setting['razorpay_secret_key']); ?>" placeholder="Secret Key">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>



                                        <!-- Mercado Pago-->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-8">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="true" aria-controls="collapse7">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> <?php echo e(__('Mercado Pago')); ?>

                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse7" class="accordion-collapse collapse"aria-labelledby="heading-2-8"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_mercado_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_mercado_enabled" id="is_mercado_enabled" <?php echo e((isset($admin_payment_setting['is_mercado_enabled']) && $admin_payment_setting['is_mercado_enabled'] == 'on') ? 'checked' : ''); ?>>
                                                                <label class="custom-control-label form-label" for="is_mercado_enabled"><?php echo e(__('Enable')); ?></label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 ">
                                                            <label class="coingate-label col-form-label" for="mercado_mode"><?php echo e(__('Mercado Mode')); ?></label> <br>
                                                            <div class="d-flex">
                                                                <div class="mr-2" style="margin-right: 15px;">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labeltext-dark">
                                                                                <input type="radio" name="mercado_mode" value="sandbox" class="form-check-input" <?php echo e(isset($admin_payment_setting['mercado_mode']) && $admin_payment_setting['mercado_mode'] == '' || isset($admin_payment_setting['mercado_mode']) && $admin_payment_setting['mercado_mode'] == 'sandbox' ? 'checked="checked"' : ''); ?>>
                                                                                <?php echo e(__('Sandbox')); ?>

                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mr-2">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labeltext-dark">
                                                                                <input type="radio" name="mercado_mode" value="live" class="form-check-input" <?php echo e(isset($admin_payment_setting['mercado_mode']) && $admin_payment_setting['mercado_mode'] == 'live' ? 'checked="checked"' : ''); ?>>
                                                                                <?php echo e(__('Live')); ?>

                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="mercado_access_token" class="col-form-label"><?php echo e(__('Access Token')); ?></label>
                                                                <input type="text" name="mercado_access_token" id="mercado_access_token" class="form-control" value="<?php echo e(isset($admin_payment_setting['mercado_access_token']) ? $admin_payment_setting['mercado_access_token']:''); ?>" placeholder="<?php echo e(__('Access Token')); ?>"/>
                                                                <?php if($errors->has('mercado_secret_key')): ?>
                                                                    <span class="invalid-feedback d-block">
                                                                            <?php echo e($errors->first('mercado_access_token')); ?>

                                                                        </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Paytm -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-7">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="true" aria-controls="collapse6">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> <?php echo e(__('Paytm')); ?>

                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse6" class="accordion-collapse collapse"aria-labelledby="heading-2-7"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>

                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_paytm_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_paytm_enabled" id="is_paytm_enabled" <?php echo e(isset($admin_payment_setting['is_paytm_enabled']) && $admin_payment_setting['is_paytm_enabled'] == 'on' ? 'checked="checked"' : ''); ?>>
                                                                <label class="custom-control-label form-label" for="is_paytm_enabled"><?php echo e(__('Enable ')); ?></label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <label class="paypal-label col-form-label" for="paypal_mode"><?php echo e(__('Paytm Environment')); ?></label> <br>
                                                            <div class="d-flex">
                                                                <div class="mr-2" style="margin-right: 15px;">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labeltext-dark">

                                                                                <input type="radio" name="paytm_mode" value="local" class="form-check-input" <?php echo e(!isset($admin_payment_setting['paytm_mode']) || $admin_payment_setting['paytm_mode'] == '' || $admin_payment_setting['paytm_mode'] == 'local' ? 'checked="checked"' : ''); ?>>

                                                                                <?php echo e(__('Local')); ?>

                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mr-2">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labeltext-dark">
                                                                                <input type="radio" name="paytm_mode" value="production" class="form-check-input" <?php echo e(isset($admin_payment_setting['paytm_mode']) && $admin_payment_setting['paytm_mode'] == 'production' ? 'checked="checked"' : ''); ?>>

                                                                                <?php echo e(__('Production')); ?>

                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="paytm_public_key" class="col-form-label"><?php echo e(__('Merchant ID')); ?></label>
                                                                <input type="text" name="paytm_merchant_id" id="paytm_merchant_id" class="form-control" value="<?php echo e((!isset($admin_payment_setting['paytm_merchant_id']) || is_null($admin_payment_setting['paytm_merchant_id'])) ? '' : $admin_payment_setting['paytm_merchant_id']); ?>" placeholder="Merchant ID">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="paytm_secret_key" class="col-form-label"><?php echo e(__('Merchant Key')); ?></label>
                                                                <input type="text" name="paytm_merchant_key" id="paytm_merchant_key" class="form-control" value="<?php echo e((!isset($admin_payment_setting['paytm_merchant_key']) || is_null($admin_payment_setting['paytm_merchant_key'])) ? '' : $admin_payment_setting['paytm_merchant_key']); ?>" placeholder="Merchant Key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="paytm_industry_type" class="col-form-label"><?php echo e(__('Industry Type')); ?></label>
                                                                <input type="text" name="paytm_industry_type" id="paytm_industry_type" class="form-control" value="<?php echo e((!isset($admin_payment_setting['paytm_industry_type']) || is_null($admin_payment_setting['paytm_industry_type'])) ? '' : $admin_payment_setting['paytm_industry_type']); ?>" placeholder="Industry Type">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mollie -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-9">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="true" aria-controls="collapse8">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> <?php echo e(__('Mollie')); ?>

                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse8" class="accordion-collapse collapse"aria-labelledby="heading-2-9"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_mollie_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_mollie_enabled" id="is_mollie_enabled" <?php echo e((isset($admin_payment_setting['is_mollie_enabled']) && $admin_payment_setting['is_mollie_enabled'] == 'on') ? 'checked' : ''); ?>>
                                                                <label class="custom-control-label form-label" for="is_mollie_enabled"><?php echo e(__('Enable ')); ?></label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="mollie_api_key" class="col-form-label"><?php echo e(__('Mollie Api Key')); ?></label>
                                                                <input type="text" name="mollie_api_key" id="mollie_api_key" class="form-control" value="<?php echo e((!isset($admin_payment_setting['mollie_api_key']) || is_null($admin_payment_setting['mollie_api_key'])) ? '' : $admin_payment_setting['mollie_api_key']); ?>" placeholder="Mollie Api Key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="mollie_profile_id" class="col-form-label"><?php echo e(__('Mollie Profile ID')); ?></label>
                                                                <input type="text" name="mollie_profile_id" id="mollie_profile_id" class="form-control" value="<?php echo e((!isset($admin_payment_setting['mollie_profile_id']) || is_null($admin_payment_setting['mollie_profile_id'])) ? '' : $admin_payment_setting['mollie_profile_id']); ?>" placeholder="Mollie Profile Id">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="mollie_partner_id" class="col-form-label"><?php echo e(__('Mollie Partner ID')); ?></label>
                                                                <input type="text" name="mollie_partner_id" id="mollie_partner_id" class="form-control" value="<?php echo e((!isset($admin_payment_setting['mollie_partner_id']) || is_null($admin_payment_setting['mollie_partner_id'])) ? '' : $admin_payment_setting['mollie_partner_id']); ?>" placeholder="Mollie Partner Id">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Skrill -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-10">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="true" aria-controls="collapse9">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> <?php echo e(__('Skrill')); ?>

                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse9" class="accordion-collapse collapse"aria-labelledby="heading-2-10"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_skrill_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_skrill_enabled" id="is_skrill_enabled" <?php echo e((isset($admin_payment_setting['is_skrill_enabled']) && $admin_payment_setting['is_skrill_enabled'] == 'on') ? 'checked' : ''); ?>>
                                                                <label class="custom-control-label form-label" for="is_skrill_enabled"><?php echo e(__('Enable')); ?></label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="mollie_api_key" class="col-form-label"><?php echo e(__('Skrill Email')); ?></label>
                                                                <input type="text" name="skrill_email" id="skrill_email" class="form-control" value="<?php echo e((!isset($admin_payment_setting['skrill_email']) || is_null($admin_payment_setting['skrill_email'])) ? '' : $admin_payment_setting['skrill_email']); ?>" placeholder="Enter Skrill Email">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- CoinGate -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-11">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse10" aria-expanded="true" aria-controls="collapse10">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> <?php echo e(__('CoinGate')); ?>

                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse10" class="accordion-collapse collapse"aria-labelledby="heading-2-11"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_coingate_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_coingate_enabled" id="is_coingate_enabled" <?php echo e((isset($admin_payment_setting['is_coingate_enabled']) && $admin_payment_setting['is_coingate_enabled'] == 'on') ? 'checked' : ''); ?>>
                                                                <label class="custom-control-label form-label" for="is_coingate_enabled"><?php echo e(__('Enable ')); ?></label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <label class="col-form-label" for="coingate_mode"><?php echo e(__('CoinGate Mode')); ?></label> <br>
                                                            <div class="d-flex">
                                                                <div class="mr-2" style="margin-right: 15px;">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labeltext-dark">

                                                                                <input type="radio" name="coingate_mode" value="sandbox" class="form-check-input" <?php echo e(!isset($admin_payment_setting['coingate_mode']) || $admin_payment_setting['coingate_mode'] == '' || $admin_payment_setting['coingate_mode'] == 'sandbox' ? 'checked="checked"' : ''); ?>>

                                                                                <?php echo e(__('Sandbox')); ?>

                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mr-2">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labeltext-dark">
                                                                                <input type="radio" name="coingate_mode" value="live" class="form-check-input" <?php echo e(isset($admin_payment_setting['coingate_mode']) && $admin_payment_setting['coingate_mode'] == 'live' ? 'checked="checked"' : ''); ?>>
                                                                                <?php echo e(__('Live')); ?>

                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="coingate_auth_token" class="col-form-label"><?php echo e(__('CoinGate Auth Token')); ?></label>
                                                                <input type="text" name="coingate_auth_token" id="coingate_auth_token" class="form-control" value="<?php echo e((!isset($admin_payment_setting['coingate_auth_token']) || is_null($admin_payment_setting['coingate_auth_token'])) ? '' : $admin_payment_setting['coingate_auth_token']); ?>" placeholder="CoinGate Auth Token">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- PaymentWall -->
                                        <div class="accordion-item card">
                                            <h2 class="accordion-header" id="heading-2-12">
                                                <button class="accordion-button"  type="button" data-bs-toggle="collapse" data-bs-target="#collapse11" aria-expanded="true" aria-controls="collapse11">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ti ti-credit-card text-primary"></i> <?php echo e(__('PaymentWall')); ?>

                                                        </span>
                                                </button>
                                            </h2>
                                            <div id="collapse11" class="accordion-collapse collapse"aria-labelledby="heading-2-12"data-bs-parent="#accordionExample" >
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 py-2">
                                                        </div>
                                                        <div class="col-6 py-2 text-end">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input type="hidden" name="is_paymentwall_enabled" value="off">
                                                                <input type="checkbox" class="form-check-input" name="is_paymentwall_enabled" id="is_paymentwall_enabled" <?php echo e((isset($admin_payment_setting['is_paymentwall_enabled']) && $admin_payment_setting['is_paymentwall_enabled'] == 'on') ? 'checked' : ''); ?>>
                                                                <label class="custom-control-label form-label" for="is_paymentwall_enabled"><?php echo e(__('Enable')); ?></label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paymentwall_public_key" class="col-form-label"><?php echo e(__('Public Key')); ?></label>
                                                                <input type="text" name="paymentwall_public_key"
                                                                       id="paymentwall_public_key"
                                                                       class="form-control"
                                                                       value="<?php echo e(!isset($admin_payment_setting['paymentwall_public_key']) || is_null($admin_payment_setting['paymentwall_public_key']) ? '' : $admin_payment_setting['paymentwall_public_key']); ?>"
                                                                       placeholder="<?php echo e(__('Public Key')); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="paymentwall_private_key" class="col-form-label"><?php echo e(__('Private Key')); ?></label>
                                                                <input type="text" name="paymentwall_private_key"
                                                                       id="paymentwall_private_key"
                                                                       class="form-control"
                                                                       value="<?php echo e(!isset($admin_payment_setting['paymentwall_private_key']) || is_null($admin_payment_setting['paymentwall_private_key']) ? '' : $admin_payment_setting['paymentwall_private_key']); ?>"
                                                                       placeholder="<?php echo e(__('Private Key')); ?>">
                                                            </div>
                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="form-group">
                                    <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>

                    <!--Pusher Settings-->
                    <div id="pusher-settings" class="card">
                        <div class="card-header">
                            <h5><?php echo e(__('Pusher Settings')); ?></h5>
                        </div>
                        <div class="card-body">
                            <?php echo e(Form::model($settings,array('route'=>'pusher.setting','method'=>'post'))); ?>

                            <?php echo csrf_field(); ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?php echo e(Form::label('pusher_app_id',__('Pusher App Id'),array('class'=>'form-label'))); ?>

                                        <?php echo e(Form::text('pusher_app_id',env('PUSHER_APP_ID'),array('class'=>'form-control font-style'))); ?>

                                        <?php $__errorArgs = ['pusher_app_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-pusher_app_id" role="alert">
                                                    <strong class="text-danger"><?php echo e($message); ?></strong>
                                                </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?php echo e(Form::label('pusher_app_key',__('Pusher App Key'),array('class'=>'form-label'))); ?>

                                        <?php echo e(Form::text('pusher_app_key',env('PUSHER_APP_KEY'),array('class'=>'form-control font-style'))); ?>

                                        <?php $__errorArgs = ['pusher_app_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-pusher_app_key" role="alert">
                                                    <strong class="text-danger"><?php echo e($message); ?></strong>
                                                </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?php echo e(Form::label('pusher_app_secret',__('Pusher App Secret'),array('class'=>'form-label'))); ?>

                                        <?php echo e(Form::text('pusher_app_secret',env('PUSHER_APP_SECRET'),array('class'=>'form-control font-style'))); ?>

                                        <?php $__errorArgs = ['pusher_app_secret'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-pusher_app_secret" role="alert">
                                                    <strong class="text-danger"><?php echo e($message); ?></strong>
                                                </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?php echo e(Form::label('pusher_app_cluster',__('Pusher App Cluster'),array('class'=>'form-label'))); ?>

                                        <?php echo e(Form::text('pusher_app_cluster',env('PUSHER_APP_CLUSTER'),array('class'=>'form-control font-style'))); ?>

                                        <?php $__errorArgs = ['pusher_app_cluster'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="invalid-pusher_app_cluster" role="alert">
                                                    <strong class="text-danger"><?php echo e($message); ?></strong>
                                                </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="form-group">
                                    <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                </div>
                            </div>

                            <?php echo e(Form::close()); ?>

                        </div>
                    </div>

                    <!--ReCaptcha Settings-->
                    <div id="recaptcha_settings" class="card">
                        <form method="POST" action="<?php echo e(route('recaptcha.settings.store')); ?>" accept-charset="UTF-8">
                            <?php echo csrf_field(); ?>
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="mb-2"><?php echo e(__('ReCaptcha Settings')); ?></h5>
                                        <a href="https://phppot.com/php/how-to-get-google-recaptcha-site-and-secret-key/"
                                           target="_blank" class="text-dark">
                                            <small>(<?php echo e(__('How to Get Google reCaptcha Site and Secret key')); ?>)</small>
                                        </a>
                                    </div>
                                    <div class="col switch-width text-end">
                                        <div class="form-group mb-0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" data-toggle="switchbutton" data-onstyle="primary" class="" name="recaptcha_module"
                                                       id="recaptcha_module"  <?php echo e(env('RECAPTCHA_MODULE') == 'on' ? 'checked="checked"' : ''); ?>>
                                                <label class="custom-control-label" for="recaptcha_module"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="google_recaptcha_key" class="form-label"><?php echo e(__('Google Recaptcha Key')); ?></label>
                                                <input class="form-control" placeholder="<?php echo e(__('Enter Google Recaptcha Key')); ?>" name="google_recaptcha_key" type="text" value="<?php echo e(env('NOCAPTCHA_SITEKEY')); ?>" id="google_recaptcha_key">
                                            </div>
                                        </div>
                                    <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="google_recaptcha_secret" class="form-label"><?php echo e(__('Google Recaptcha Secret')); ?></label>
                                                <input class="form-control" placeholder="<?php echo e(__('Enter Google Recaptcha Secret')); ?>" name="google_recaptcha_secret" type="text" value="<?php echo e(env('NOCAPTCHA_SECRET')); ?>" id="google_recaptcha_secret">
                                            </div>
                                        </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="form-group">
                                        <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                            </div>
                        <?php echo e(Form::close()); ?>

                    </div>

                    <!-- Storage Settings -->
                    <div id="storage-settings" class="card mb-3">
                        <?php echo e(Form::open(array('route' => 'storage.setting.store', 'enctype' => "multipart/form-data"))); ?>

                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-10 col-md-10 col-sm-10">
                                    <h5 class=""><?php echo e(__('Storage Settings')); ?></h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="pe-2">
                                    <input type="radio" class="btn-check" name="storage_setting" id="local-outlined" autocomplete="off" <?php echo e($setting['storage_setting'] == 'local'?'checked':''); ?> value="local" checked>
                                    <label class="btn btn-outline-primary" for="local-outlined"><?php echo e(__('Local')); ?></label>
                                </div>
                                <div  class="pe-2">
                                    <input type="radio" class="btn-check" name="storage_setting" id="s3-outlined" autocomplete="off" <?php echo e($setting['storage_setting']=='s3'?'checked':''); ?>  value="s3">
                                    <label class="btn btn-outline-primary" for="s3-outlined"> <?php echo e(__('AWS S3')); ?></label>
                                </div>
                                <div  class="pe-2">
                                    <input type="radio" class="btn-check" name="storage_setting" id="wasabi-outlined" autocomplete="off" <?php echo e($setting['storage_setting']=='wasabi'?'checked':''); ?> value="wasabi">
                                    <label class="btn btn-outline-primary" for="wasabi-outlined"><?php echo e(__('Wasabi')); ?></label>
                                </div>
                            </div>
                            <div  class="mt-2">
                                <div class="local-setting row <?php echo e($setting['storage_setting']=='local'?' ':'d-none'); ?>">
                                    
                                    <div class="form-group col-8 switch-width">
                                        <?php echo e(Form::label('local_storage_validation',__('Only Upload Files'),array('class'=>' form-label'))); ?>

                                        <select name="local_storage_validation[]" class="select2"  id="local_storage_validation"  multiple>
                                            <?php $__currentLoopData = $file_type; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option <?php if(in_array($f, $local_storage_validations)): ?> selected <?php endif; ?>><?php echo e($f); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label class="form-label" for="local_storage_max_upload_size"><?php echo e(__('Max upload size ( In KB)')); ?></label>
                                            <input type="number" name="local_storage_max_upload_size" class="form-control" value="<?php echo e((!isset($setting['local_storage_max_upload_size']) || is_null($setting['local_storage_max_upload_size'])) ? '' : $setting['local_storage_max_upload_size']); ?>" placeholder="<?php echo e(__('Max upload size')); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="s3-setting row <?php echo e($setting['storage_setting']=='s3'?' ':'d-none'); ?>">
                                    <div class=" row ">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_key"><?php echo e(__('S3 Key')); ?></label>
                                                <input type="text" name="s3_key" class="form-control" value="<?php echo e((!isset($setting['s3_key']) || is_null($setting['s3_key'])) ? '' : $setting['s3_key']); ?>" placeholder="<?php echo e(__('S3 Key')); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_secret"><?php echo e(__('S3 Secret')); ?></label>
                                                <input type="text" name="s3_secret" class="form-control" value="<?php echo e((!isset($setting['s3_secret']) || is_null($setting['s3_secret'])) ? '' : $setting['s3_secret']); ?>" placeholder="<?php echo e(__('S3 Secret')); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_region"><?php echo e(__('S3 Region')); ?></label>
                                                <input type="text" name="s3_region" class="form-control" value="<?php echo e((!isset($setting['s3_region']) || is_null($setting['s3_region'])) ? '' : $setting['s3_region']); ?>" placeholder="<?php echo e(__('S3 Region')); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_bucket"><?php echo e(__('S3 Bucket')); ?></label>
                                                <input type="text" name="s3_bucket" class="form-control" value="<?php echo e((!isset($setting['s3_bucket']) || is_null($setting['s3_bucket'])) ? '' : $setting['s3_bucket']); ?>" placeholder="<?php echo e(__('S3 Bucket')); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_url"><?php echo e(__('S3 URL')); ?></label>
                                                <input type="text" name="s3_url" class="form-control" value="<?php echo e((!isset($setting['s3_url']) || is_null($setting['s3_url'])) ? '' : $setting['s3_url']); ?>" placeholder="<?php echo e(__('S3 URL')); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_endpoint"><?php echo e(__('S3 Endpoint')); ?></label>
                                                <input type="text" name="s3_endpoint" class="form-control" value="<?php echo e((!isset($setting['s3_endpoint']) || is_null($setting['s3_endpoint'])) ? '' : $setting['s3_endpoint']); ?>" placeholder="<?php echo e(__('S3 Bucket')); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-8 switch-width">
                                            <?php echo e(Form::label('s3_storage_validation',__('Only Upload Files'),array('class'=>' form-label'))); ?>

                                            <select name="s3_storage_validation[]" class="select2" id="s3_storage_validation" multiple>
                                                <?php $__currentLoopData = $file_type; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option <?php if(in_array($f, $s3_storage_validations)): ?> selected <?php endif; ?>><?php echo e($f); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_max_upload_size"><?php echo e(__('Max upload size ( In KB)')); ?></label>
                                                <input type="number" name="s3_max_upload_size" class="form-control" value="<?php echo e((!isset($setting['s3_max_upload_size']) || is_null($setting['s3_max_upload_size'])) ? '' : $setting['s3_max_upload_size']); ?>" placeholder="<?php echo e(__('Max upload size')); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="wasabi-setting row <?php echo e($setting['storage_setting']=='wasabi'?' ':'d-none'); ?>">
                                    <div class=" row ">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_key"><?php echo e(__('Wasabi Key')); ?></label>
                                                <input type="text" name="wasabi_key" class="form-control" value="<?php echo e((!isset($setting['wasabi_key']) || is_null($setting['wasabi_key'])) ? '' : $setting['wasabi_key']); ?>" placeholder="<?php echo e(__('Wasabi Key')); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_secret"><?php echo e(__('Wasabi Secret')); ?></label>
                                                <input type="text" name="wasabi_secret" class="form-control" value="<?php echo e((!isset($setting['wasabi_secret']) || is_null($setting['wasabi_secret'])) ? '' : $setting['wasabi_secret']); ?>" placeholder="<?php echo e(__('Wasabi Secret')); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_region"><?php echo e(__('Wasabi Region')); ?></label>
                                                <input type="text" name="wasabi_region" class="form-control" value="<?php echo e((!isset($setting['wasabi_region']) || is_null($setting['wasabi_region'])) ? '' : $setting['wasabi_region']); ?>" placeholder="<?php echo e(__('Wasabi Region')); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="wasabi_bucket"><?php echo e(__('Wasabi Bucket')); ?></label>
                                                <input type="text" name="wasabi_bucket" class="form-control" value="<?php echo e((!isset($setting['wasabi_bucket']) || is_null($setting['wasabi_bucket'])) ? '' : $setting['wasabi_bucket']); ?>" placeholder="<?php echo e(__('Wasabi Bucket')); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="wasabi_url"><?php echo e(__('Wasabi URL')); ?></label>
                                                <input type="text" name="wasabi_url" class="form-control" value="<?php echo e((!isset($setting['wasabi_url']) || is_null($setting['wasabi_url'])) ? '' : $setting['wasabi_url']); ?>" placeholder="<?php echo e(__('Wasabi URL')); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="wasabi_root"><?php echo e(__('Wasabi Root')); ?></label>
                                                <input type="text" name="wasabi_root" class="form-control" value="<?php echo e((!isset($setting['wasabi_root']) || is_null($setting['wasabi_root'])) ? '' : $setting['wasabi_root']); ?>" placeholder="<?php echo e(__('Wasabi Bucket')); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-8 switch-width">
                                            <?php echo e(Form::label('wasabi_storage_validation',__('Only Upload Files'),array('class'=>'form-label'))); ?>


                                            <select name="wasabi_storage_validation[]" class="select2" id="wasabi_storage_validation" multiple>
                                                <?php $__currentLoopData = $file_type; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option <?php if(in_array($f, $wasabi_storage_validations)): ?> selected <?php endif; ?>><?php echo e($f); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label class="form-label" for="wasabi_root"><?php echo e(__('Max upload size ( In KB)')); ?></label>
                                                <input type="number" name="wasabi_max_upload_size" class="form-control" value="<?php echo e((!isset($setting['wasabi_max_upload_size']) || is_null($setting['wasabi_max_upload_size'])) ? '' : $setting['wasabi_max_upload_size']); ?>" placeholder="<?php echo e(__('Max upload size')); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                            </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                    </div>
                </div>
            </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Bdrango\Desktop\main_file\resources\views/settings/index.blade.php ENDPATH**/ ?>