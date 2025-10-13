<head>
    <?php echo view('includes/meta'); ?>
    <?php echo view('includes/helper_js'); ?>
    <?php echo view('includes/plugin_language_js'); ?>

    <?php
    //We'll merge all css and js into sigle files. If you want to use the css separately, you can use it.

/*
    $css = array(
        "assets/js/datatable/datatables.min.css",
        "assets/js/datatable/css/responsive.dataTables.min.css",
        "assets/js/bootstrap-datepicker/css/datepicker3.css",
        "assets/js/bootstrap-timepicker/css/bootstrap-timepicker.min.css",
        "assets/js/dropzone/dropzone.min.css",
        "assets/js/magnific-popup/magnific-popup.css",
        "assets/js/perfect-scrollbar/perfect-scrollbar.css",
        "assets/js/awesomplete/awesomplete.css",
        "assets/js/atwho/css/jquery.atwho.min.css"
    );

    $scss = array(
        "assets/scss/style.scss"
    );

    $js = array(
        "assets/bootstrap/js/bootstrap.bundle.min.js",
        "assets/js/jquery-3.5.1.min.js",
        "assets/js/chartjs/chart.js",
        "assets/js/feather-icons/feather.min.js",
        "assets/js/jquery-validation/jquery.validate.min.js",
        "assets/js/jquery-validation/jquery.form.js",
        "assets/js/perfect-scrollbar/perfect-scrollbar.min.js",
        "assets/js/select2/select2.js",
        "assets/js/datatable/datatables.min.js",
        "assets/js/datatable/js/dataTables.responsive.min.js",
        "assets/js/datatable/js/dataTables.colReorder.min.js",
        "assets/js/datatable/TableTools/js/dataTables.buttons.min.js",
        "assets/js/datatable/TableTools/js/buttons.html5.min.js",
        "assets/js/datatable/TableTools/js/buttons.print.min.js",
        "assets/js/datatable/TableTools/js/jszip.min.js",
        "assets/js/bootstrap-datepicker/js/bootstrap-datepicker.js",
        "assets/js/bootstrap-timepicker/js/bootstrap-timepicker.min.js",
        "assets/js/fullcalendar/moment.min.js",
        "assets/js/dropzone/dropzone.min.js",
        "assets/js/magnific-popup/jquery.magnific-popup.min.js",
        "assets/js/sortable/sortable.min.js",
        "assets/js/atwho/caret/jquery.caret.min.js",
        "assets/js/atwho/js/jquery.atwho.min.js",
        "assets/js/notification_handler.js",
        "assets/js/general_helper.js",
        "assets/js/app.min.js"
    );

    //to merge all files into one, we'll use this helper
    helper('dev_tools');
    write_css($css);
    write_scss($scss);
    write_js($js);

*/

    $css_files = array(
        "assets/bootstrap/css/bootstrap.min.css",
        "assets/js/select2/select2.css", //don't combine this css because of the images path
        "assets/js/select2/select2-bootstrap.min.css",
        "assets/css/app_new.all.css",
        "assets/css/style_ghost.css",
    );

    if (app_lang("text_direction") == "rtl") {
        array_push($css_files, "assets/css/rtl.css");
    }

    array_push($css_files, "assets/css/custom_style.css"); //add to last. custom style should not be merged

    load_css($css_files);

    load_js(array(
        "assets/js/app.all.js",
        "assets/js/firebase-cofig.js?data=option58"
       //"assets/js/pwa-install2.js"
    ));

    ?>

    <?php echo view("includes/csrf_ajax"); ?>

    <?php app_hooks()->do_action('app_hook_head_extension'); ?>

    <?php echo view("includes/custom_head"); ?>

    <script>
        // Bandera para controlar si el bridge API de Tauri se ha cargado.
        window.isTauriApp = false;
        
        // Función para intentar cargar el script del bridge.
        function loadTauriBridge() {
            // La condición de detección es la clave:
            // Si __TAURI_INTERNALS__ está presente O si ya cargamos el bridge
            if (window.__TAURI_INTERNALS__ || window.__TAURI__) {
                console.log("✅ Entorno Tauri detectado. Intentando cargar el Bridge API.");
                
                // Si el bridge no está presente, lo inyectamos manualmente
                if (!window.__TAURI__) {
                    const tauriScript = document.createElement('script');
                    tauriScript.src = 'https://asset.localhost/tauri.js'; // Protocolo especial de Tauri
                    tauriScript.type = 'module';
                    tauriScript.onload = () => {
                        console.log("🎉 Bridge API de Tauri cargado exitosamente.");
                        window.isTauriApp = true;
                    };
                    tauriScript.onerror = () => {
                        console.error("❌ ERROR: No se pudo cargar tauri.js. Revisar CSP.");
                    }
                    document.head.appendChild(tauriScript);
                } else {
                    // Si __TAURI__ ya está, solo actualizamos la bandera
                    window.isTauriApp = true;
                }
                return true;
            } else {
                console.log("🌐 No detectado el entorno Tauri. Ejecutando como web estándar.");
                return false;
            }
        }

        // 1. Intentar la carga inmediatamente (para el caso más rápido)
        loadTauriBridge();

        // 2. Intentar de nuevo al cargar el DOM, por si el script interno se inyecta tarde
        document.addEventListener('DOMContentLoaded', loadTauriBridge);

        // 3. Intentar de nuevo 1 segundo después, como último recurso.
        setTimeout(loadTauriBridge, 1000); 

    </script>

    <script>
    function formatDateToISO(dateString) {
        const date = new Date(dateString);  // Asegúrate de que la fecha esté en formato UTC
        const year = date.getUTCFullYear();
        const month = String(date.getUTCMonth() + 1).padStart(2, '0');  // +1 porque los meses van de 0 a 11
        const day = String(date.getUTCDate()).padStart(2, '0');  // Obtiene el día en UTC
        console.log(`${year}-${month}-${day}`);
        return `${year}-${month}-${day}`;
    }

    function formatDateToUTC(dateString) {
        const date = new Date(dateString);
        date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function initializeUSDateInputs() {
        USDateInputs('.us-date-input-today');
        USDateInputs('.us-date-input');
    }

    function USDateInputs(class_input) {
        const dateInputs = document.querySelectorAll(class_input);
        const today = new Date();
        const formattedDate = today.toLocaleDateString('en-US');
        var data = {};
        /*if (class_input == ".us-date-input-today") {
            data = {
                dateFormat: "m/d/Y", // Formato MM/DD/YYYY
                locale: "en", // Idioma inglés
                maxDate: "today"
            };
        } else if(class_input == ".us-date-input-today-now"){
            data = {
                dateFormat: "m/d/Y", // Formato MM/DD/YYYY
                locale: "en", // Idioma inglés
                maxDate: "today",
                defaultDate: formattedDate
            };
        }
        else{
            data = {
                dateFormat: "m/d/Y", // Formato MM/DD/YYYY
                locale: "en", // Idioma inglés
            };
        }*/
        switch (class_input) {
            case ".us-date-input-today":
                data = {
                    dateFormat: "m/d/Y", // Formato MM/DD/YYYY
                    locale: "en", // Idioma inglés
                    maxDate: "today"
                };
                break;
            case ".us-date-input-today-now":
                data = {
                    dateFormat: "m/d/Y", // Formato MM/DD/YYYY
                    locale: "en", // Idioma inglés
                    maxDate: "today",
                    defaultDate: formattedDate
                };
                break;
            default:
                data = {
                    dateFormat: "m/d/Y", // Formato MM/DD/YYYY
                    locale: "en", // Idioma inglés
                };
        }
        dateInputs.forEach(input => {
            flatpickr(input, data);
        });
    }


    document.addEventListener('DOMContentLoaded', initializeUSDateInputs);
    </script>

   


</head>