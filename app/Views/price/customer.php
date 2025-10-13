<div id="pre-loader" class="d-none">
     <div id="pre-loade" class="app-loader"><div class="loading"></div></div>
 </div>
<div id="page-content" class="page-wrapper clearfix grid-button">
  <div class="card">
    <div class="page-title clearfix notes-page-title">
      <h1><?php echo app_lang("view_price"); ?></h1>
    </div>

    <style>
        .new-input{
            border: none;
            background:#f3f3f3;
            cursor: pointer;
        }

        .badge-figure{
            background-color:rgb(149, 200, 254);
            color: #000 !important;
            border-radius: 15px;
            padding-left: 6px;
            padding-right: 6px;
            padding-top: 2px;
            padding-bottom: 2px;
        }
        .btn-save {
                padding: 5px 15px;
                color: white;
                border-radius: 5px;
                font-weight: bold;
                display: inline-block;
            }

        .clinic_name{
            padding: 5px;
            color:#07688f;
            background-color: #cdf0fe;
            border-radius: 20px;
        }

        .status-card {
                padding: 5px ;
                color: white;
                border-radius: 5px;
                font-weight: bold;
                display: inline-block;
            }
            .active-card {
                background-color: #f1fed7;

                color:rgb(31, 92, 4);
            }
            .inactive-card {
                background-color: #fbd7cb;

                color: #db2508 ;
            }

            .description-box {
                font-style: italic;
                color: #555;
                font-size: 10px;
                padding: 5px;
            
                background-color: #f9f9f9;
                border-radius: 5px;
                white-space: pre-wrap;
                max-width: 500px;
                text-align: center;
                display: flex;  
                justify-content: center; 
            }

        /* Estilo personalizado para el buscador */
    .search-container {
      max-width: 100%;
      margin: 0 auto;
      position: relative;
      
    }

    .search-input {
      padding-left: 2.5rem;
      border-radius: 5px;
      font-size: 1rem;
      background-color: #f1f1f1;
      transition: all 0.3s ease;
      border: none !important;
    }

    .search-input:focus {
      background-color: #ffffff;
      border-color: #007bff;
      box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    }

    .search-input::placeholder {
      color: #888;
      font-style: italic;
    }

    .search-icon {
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #007bff;
    }
    .span_category{
            padding: 4px;
            border-radius: 15px;
            font-size: 12px;
        }
        
    </style>
    <div class="card-body" >
        
        <div class="search-container">
            <!-- Input de búsqueda con icono -->
            <input class="form-control search-input mb-2" id="searchInput" type="text" placeholder="Buscar...">
            <i class="bi bi-search search-icon"></i> <!-- Icono de búsqueda -->
        </div>

        <div class="row">
            <div class="col-md-12 d-flex">

            <?php
                    $selected_clinic_id = isset($model_info) && $model_info !== null ? $model_info->clinic_id : '';
                    $clinic_options = ['' => app_lang("select_clinic_one")] + $clinic_options;
                    echo form_dropdown(
                    "clinic_select",
                    $clinic_options,
                    $selected_clinic_id,
                    'class="select_graph w-100 form-control me-2 " id="clinic_select" required aria-required="true" aria-label="' . app_lang('clinic_list') . '"'
                    ); ?>

            <button class="btn btn-default d-none w-25" style="background-color:#ccf0fe;" id="buttonModalCreate" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="far fa-save"></i> Crear </button>

            </div>

            
        </div>
        


        <table class="table mt-4 d-none" id="DataTable">
            <thead>
                <tr class="text-center">
                    <th>ID</th>
                    <th>Clinica</th>
                    <th>Tipo</th>
                    <th>Examen/Procedimiento</th>
                    <th>Descipción</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody id="servicesTableBody" class="text-center" >
                
            </tbody>
        </table>
    </div>
  </div>




  <!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#cdf0fe ;">
        <h5 class="modal-title " id="exampleModalLabel" style="font-weight: 500;">Crear Precio de Examen</h5>
        <button type="button" class="btn-close" id="closeModal" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
        <form action="" id="formServices">           
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label"> <span class="badge-figure"><i class="fas fa-clinic-medical"></i> Clinica</span></label>
                        <input type="text" id="clinic_input" style="border:none;font-size:14px;background-color:#f2f3f0;" readonly class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                        <input type="hidden" id="clinic_id" value="" >
                        <input type="hidden" id="user" value="<?php echo $login_user->first_name." ".$login_user->last_name; ?>" >
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mt-2">
                    <label for="exampleInputEmail1" class="form-label"> <span class="badge-figure"><i class="fas fa-heartbeat"></i> Examen</span></label>
                        <?php 
                            echo form_dropdown(
                                "list_service",
                                $list_service,
                                $selected_clinic_id,
                                'class="select_graph w-100 form-control new-input" id="exam_id" required aria-required="true" aria-label="' . app_lang('clinic_list') . '"'
                                ); 
                        ?>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                    <label for="exampleInputEmail1" class="form-label"> <span class="badge-figure"><i class="fas fa-text-height"></i> Descripción</span></label>
                        <textarea class="form-control" id="description" rows="5" style="height: auto;border:none;font-size:14px;background-color:#f2f3f0;font-size:12px;" ></textarea>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mt-2">
                    <label for="exampleInputEmail1" class="form-label"> <span class="badge-figure"><i class="fas fa-clinic-medical"></i> Precio</span></label>
                        <div class="input-group mb-3">
                        <span class="input-group-text" style="border:none;font-size:20px;" id="basic-addon1">$</span> 
                            <input type="number" id="price" style="border:none;background-color:#f2f3f0;text-align:center;font-size:20px;" class="form-control" aria-describedby="basic-addon1">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12 mt-2">
                    <button type="submit"  onclick="save()" id="createService" class="btn btn-default mb-2 " style="width:100%;border:2px solid #ddd;"><i class="fas fa-save"></i> <?php echo app_lang("save") ?></button>
                </div>
            </div>
        </form> 
      </div>

    </div>
  </div>
</div>




        

      



<script>

                             

    const optionsArray = [];    
    let selectedServiceId = null;
    let selectedClinicID = null;  
    
    document.addEventListener("DOMContentLoaded", function() {
        var selectElement = document.getElementById("exam_id"); 
        for (var x = 0; x < selectElement.options.length; x++) {
            var option = selectElement.options[x];
            optionsArray.push({
                value: option.value,
                text: option.text
            });
        }
        console.log(optionsArray);
    });

    window.onload = function() {
          const select = document.getElementById('clinic_select');
        
        select.value = select.options[1].value;

        const event = new Event('change');
        select.dispatchEvent(event);
    }


    document.getElementById('clinic_select').addEventListener('change', function(event) {
        // Capturamos el valor seleccionado
        const select = event.target;
        const text = select.options[select.selectedIndex].text;
        const selectedValue = select.value;

        selectedClinicID = selectedValue;
        console.log("la opcion elegida es : " + selectedValue);
      
        // Seleccionamos el input y asignamos el valor
        const inputField = document.getElementById('clinic_input');
        inputField.value = text;

        const clinic_id = document.getElementById('clinic_id');
        clinic_id.value = selectedValue;
        ReloadTable(selectedValue);
        
    });

    function ReloadTable(clinic_selected_id){
        modalShow();
        document.getElementById("description").value = "";
        document.getElementById("price").value = "";
        $.ajax({
            url: "<?php echo get_uri('price/listPriceCustomer'); ?>",
            type: "GET",
            data: {
                clinic_id: clinic_selected_id,
            },
            dataType: 'json',
            success: function(response){
               
                drawTable(response);  
                console.log(response);
                hideShow();
            },
            error:function(jqXHR, textStatus, errorThrown){
                console.log('Error en la consulta.');  
            }
        });
    }
    function getTextColor(hex) {
                 // Verifica si el color tiene 4 o 7 caracteres y lo expande si es necesario
                if (hex.length === 4) {
                    hex = "#" + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
                }

                if (hex.length === 7) {
                    let r = parseInt(hex.substring(1, 3), 16);
                    let g = parseInt(hex.substring(3, 5), 16);
                    let b = parseInt(hex.substring(5, 7), 16);

                    // Calcula la luminancia del color de fondo
                    let luminance = (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255;

                    // Si la luminancia es alta (fondo claro), retorna un color oscuro, de lo contrario, un color claro
                    return luminance > 0.5 ? "#222222" : "#FFFFFF"; // Negro oscuro o blanco puro
                }
                
                // Retorna un color por defecto en caso de un valor no válido
                return "#000000"; 
            }

 

    
    function modalShow(){
        document.getElementById('pre-loader').classList.remove('d-none');
    }

    function hideShow(){
        document.getElementById('pre-loader').classList.add('d-none');
    }

    function reload(){ //Recarga todo la tabla
        modalShow();
        const form = document.getElementById("formServices");
        form.exam_id.value = 1;
        form.price.value = 1;
        id = '';
        //document.getElementById('createService').classList.remove('d-none');
        //document.getElementById('editService').classList.add('d-none');
        //document.getElementById('reloadService').classList.add('d-none');
        //document.getElementById('pre-loader').classList.remove('d-none');
        ReloadTable(form.clinic_id.value);
        form.clinic_id.value = '';
        setTimeout(function(){
            hideShow();
        },1000);
    }

    

    function drawTable(data) {
        var servicesTableBody = document.getElementById('servicesTableBody');
        servicesTableBody.innerHTML = ''; // Borrar todas las filas dentro del tbody
        document.getElementById('DataTable').classList.remove('d-none');
     

        var selectElement = document.getElementById("exam_id"); 
        selectElement.innerHTML = '';
        console.log(optionsArray);
        optionsArray.forEach(function(optionData) {
            var newOption = document.createElement("option");
            newOption.value = optionData.value;
            newOption.textContent = optionData.text;
            selectElement.appendChild(newOption);  // Agregar la nueva opción al select
        });
      

        let clinicIdsFromData = data.map(service => service.service_id); 
            for (var x = 0;x < selectElement.options.length; x++) {
                var option = selectElement.options[x];
                var optionValue = option.value;

                // Si el clinic_id está en los datos, quita la clase d-none
                if (clinicIdsFromData.includes(optionValue)) {
                    option.classList.add("d-none");
                } else {
                    // Si no está, añade la clase d-none
                    option.classList.remove("d-none");
                }
            }
            document.getElementById("exam_id").value = 0; 
        
        

        if (data.length === 0) {
            var row = document.createElement('tr');
            var cellMessage = document.createElement('td');
            cellMessage.textContent = "No existen precios de los exámenes para esta clínica";
            cellMessage.setAttribute('colspan', '7'); // Ajustar el colspan al número de columnas
            cellMessage.classList.add('text-center', 'no-data-message'); // Agregar clases CSS para estilos
            row.appendChild(cellMessage);
            servicesTableBody.appendChild(row);
            return; // Salir de la función
        }


       
     

        let i = 0;
        data.forEach(function(service) {
            i++;
            var row = document.createElement('tr'); // Creamos una nueva fila
            row.classList.add('hover-pointer');

            row.setAttribute('class', 'fill_service_pointer');
            row.setAttribute('id_item', service.id); 
            row.setAttribute('clinic_id', service.clinic_id);
            row.setAttribute('service_id', service.service_id);
            row.setAttribute('price', service.price);
            row.setAttribute('observation', service.observation);
            row.setAttribute('state', service.state);
            row.setAttribute('assigned_by', service.assigned_by);

            // Columna ID
            var cellId = document.createElement('td');
            cellId.textContent = i;
            row.appendChild(cellId);

            // Columna Clinic ID
            var cellClinicId = document.createElement('td');
            cellClinicId.innerHTML = '<span><b class="clinic_name">'+service.clinic_name+'</b> </span>';
            row.appendChild(cellClinicId);

            // Columna Tipo ID
            var cellType = document.createElement('td');
            let textColor = getTextColor(service.color);
            cellType.innerHTML = '<span class="span_category" style="background-color:'+service.color+';color:'+textColor+'!important;" >'+service.category+' </span>';
            row.appendChild(cellType);

            // Columna Service ID
            var cellServiceId = document.createElement('td');
            cellServiceId.innerHTML = '<span>'+service.service_name+'</span>';
            row.appendChild(cellServiceId);


            var cellObservation = document.createElement('td');
            cellObservation.classList.add('d-flex','justify-content-center');
            // Crear un contenedor para la descripción
            var descContainer = document.createElement('div');
            descContainer.textContent = service.observation
                ? service.observation.charAt(0).toUpperCase() + service.observation.slice(1)
                : "No description"; // Manejo si está vacío

            // Asignar la clase CSS
            descContainer.classList.add('description-box');

            // Agregar el contenedor estilizado a la celda
            cellObservation.appendChild(descContainer);
            row.appendChild(cellObservation);

            // Columna Precio
            var cellPrice = document.createElement('td');
            // Formatear el precio en dólares con dos decimales
            cellPrice.textContent = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2
            }).format(service.price);

            row.appendChild(cellPrice);

            
            // Añadimos la fila a la tabla
            servicesTableBody.appendChild(row);
        });
    }
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('#DataTable tbody tr');

            rows.forEach(row => {
            const cells = row.getElementsByTagName('td');
            let match = false;

            // Comprobar si alguna celda contiene la cadena de búsqueda
            for (let i = 0; i < cells.length; i++) {
                if (cells[i].textContent.toLowerCase().includes(query)) {
                match = true;
                break;
                }
            }

            // Mostrar u ocultar la fila según el resultado de la búsqueda
            row.style.display = match ? '' : 'none';
            });
        });

</script>


</div>