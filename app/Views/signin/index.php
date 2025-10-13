<!DOCTYPE html>

<html lang="en">

    <head>

        <?php echo view('includes/head'); ?>

    </head>

    <body class="body-bg-gradient">
         
          <img class="fullscreen-bg d-none" src="<?php echo base_url('files/system/img-bg-pagelogin.webp'); ?>" />
          <video autoplay muted loop class="fullscreen-bg">
                <source src="<?php echo base_url('files/system/fondo.mp4'); ?>" type="video/mp4">
                Tu navegador no soporta la etiqueta de video.
            </video>
        <?php

        if (get_setting("show_background_image_in_signin_page") === "yes") {
            $background_url = get_file_from_setting("signin_page_background");
            ?>
        <?php } ?>





        <div class="scrollable-page d-flex flex-column justify-content-center align-items-center vh-100">
            <div class="form-signin">
                <?php
                if (isset($form_type) && $form_type == "request_reset_password") {
                    echo view("signin/reset_password_form");
                } else if (isset($form_type) && $form_type == "new_password") {
                    echo view('signin/new_password_form');
                } else {
                    echo view("signin/signin_form");
                }
                ?>
            </div>
   
        </div>
        <script>

            $(document).ready(function () {

                initScrollbar('.scrollable-page', {

                    setHeight: $(window).height() - 50

                });

            });

        </script>



        <?php echo view("includes/footer"); ?>

    </body>

</html>