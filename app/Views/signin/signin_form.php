<div class="card form-signin-bg mb15 rounded-custom py-2 py-md-5">
    
    <div class="card-header text-center bg-transparent border border-0 p-0 header-with-blue-line">

        <?php if (get_setting("show_logo_in_signin_page") === "yes") { ?>
            <div class="d-flex justify-content-center">
                 <img class="p-1 form-signin-max-w-img"  src="<?php echo base_url('files/system/logo_signin_color.png'); ?>" />
            </div>

            

        <?php } else { ?>

            <h2><?php echo app_lang('signin'); ?></h2>

        <?php } ?>

    </div>

    <div class="card-body px-5 py-1">

        <?php echo form_open("signin/authenticate", array("id" => "signin-form", "class" => "general-form", "role" => "form")); ?>



        <?php

        $session = \Config\Services::session();

        $signin_validation_errors = $session->getFlashdata("signin_validation_errors");

        if ($signin_validation_errors && is_array($signin_validation_errors)) {

            ?>

            <div class="alert alert-danger" role="alert">

                <?php foreach ($signin_validation_errors as $validation_error) { ?>

                    <i data-feather="alert-circle" class="icon-16"></i>

                    <?php echo $validation_error; ?>

                    <br />

                <?php } ?>

            </div>

        <?php } ?>

        <div class="m-4">

            <?php

            echo form_input(array(

                "id" => "email",
                "name" => "email",
                "class" => "form-signin-input",
                "placeholder" => app_lang('email'),
                "autofocus" => true,
                "autocomplete" => "off",
                "data-rule-required" => true,
                "data-msg-required" => app_lang("field_required"),
                "data-rule-email" => true,
                "data-msg-email" => app_lang("enter_valid_email"),

            ));

            ?>

        </div>

        <div class="m-4 pb-3">

            <?php

            echo form_password(array(

                "id" => "password",

                "name" => "password",

                "class" => "form-signin-input",

                "placeholder" => app_lang('password'),

                "data-rule-required" => true,

                "data-msg-required" => app_lang("field_required")

            ));

            ?>

        </div>

        <input type="hidden" name="redirect" value="<?php

        if (isset($redirect)) {

            echo $redirect;

        }

        ?>" />
        <?php echo view("signin/re_captcha"); ?>
        <button class="w-100 form-signin-btn text-white border border-0 p-4 rounded-custom text-uppercase fw-bold" type="submit"><?php echo app_lang('signin'); ?></button>

      <!--         
        <?php echo form_close(); ?>
        <div class="mt5"><?php echo anchor("signin/request_reset_password", app_lang("forgot_password")); ?></div>
        <?php if (!get_setting("disable_client_signup")) { ?>
            <div class="mt20"><?php echo app_lang("you_dont_have_an_account") ?> &nbsp; <?php echo anchor("signup", app_lang("signup")); ?></div>
        <?php } ?> -->

        <?php

        app_hooks()->do_action('app_hook_signin_extension');

        ?>

    </div>

</div>





<script type="text/javascript">

    $(document).ready(function () {

        $("#signin-form").appForm({ajaxSubmit: false, isModal: false});

    });

</script>    