<div id="pre-loader" class="d-none">
     <div id="pre-loade" class="app-loader"><div class="loading"></div></div>
 </div>
<div id="page-content" class="page-wrapper clearfix grid-button">
  <div class="card">
    <div class="page-title clearfix notes-page-title">
      <h1><?php echo app_lang("services_clinic"); ?></h1>
    </div>
    <div class="card-body " >
    
        <div class="d-flex">
            <div class="search-container w-75">
                <input class="form-control search-input mb-2" onkeyup="searchTable()" id="searchInput" type="text" placeholder="Buscar...">
                <i class="bi bi-search search-icon"></i> <!-- Icono de búsqueda -->
            </div>
            <div class="w-25 d-flex justify-content-end mb-2">
                <button class="btn btn-default w-50" onclick="buttonExam()" style="background-color:#e8ffa4;"><i class="fas fa-check-double"></i> Examen</button>
                <button class="btn btn-default w-50" onclick="buttonCategory()" style="background-color:#e0f2ff;"><i class="fas fa-list"></i> Categorias</button>
            </div>
        </div>
     

        <!--
        <form method="POST" id="formServices" action="" >
            <div class="row d-none">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="staticEmail2" class="sr-only">Nombre del servicio</label>
                        <input type="text"  class="form-control" placeholder="Nombre del servicio" id="nameService" value="">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="inputPassword2" class="sr-only"></label>
                        <select name=""  class="form-control" id="stateService" placeholder="Estado del servicio prestado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" onclick="save()" id="createService" style="background-color:#ccf0fe;" class="btn btn-default mb-2 w-100"><i class="fas fa-save"></i> <?php echo app_lang("save") ?></button>
                    <button type="submit" onclick="edit()" id="editService" class="btn btn-default mb-2 d-none"><i class="fas fa-edit"></i> <?php echo app_lang("save") ?></button>
                    <button type="button" onclick="reload()" id="reloadService" class="btn btn-default mb-2 d-none"><i class="fas fa-sync"></i></button>
                </div>
            </div>
        </form>
-->
        

            <style>
            .status-card {
                padding: 5px;
                color: white;
                border-radius: 5px;
                font-weight: bold;
                display: inline-block;
                margin: 3px;
            }
            .active-card {
                background-color: #f1fed7;
                font-size: 12px;
                color:rgb(31, 92, 4);
            }
            .inactive-card {
                background-color: #fbd7cb;
                font-size: 12px;
                color: #db2508 ;
            }
            .fill_service_pointer:hover{
                background-color: #f3f3f3;
                cursor: pointer;
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

    .spanTicket{
        padding: 8px;
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

        color-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .color-preview {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid #ccc;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            margin-bottom: 10px;
        }

        .color-preview:hover {
            transform: scale(1.1);
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        .color-label {
            font-size: 14px;
            font-weight: bold;
            color: #555;
        }

        .color-input {
            border: none;
            cursor: pointer;
            width: 50px;
            height: 50px;
            padding: 0;
            background: none;
        }

        /* Personalización del input color */
        .color-input::-webkit-color-swatch {
            border-radius: 10px;
            border: 2px solid #ddd;
        }

        .color-input::-moz-color-swatch {
            border-radius: 10px;
            border: 2px solid #ddd;
        }

        .span_category{
            padding: 4px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        </style>

        <table class="table table-sm mt-4">
            <thead >
                <tr class="text-center">
                    <th>ID</th>
                    <th>Categoria</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody id="servicesTableBody" class="text-center">
                
            </tbody>
        </table>

        <button type="button" class="btn btn-danger d-none" id="modalTrigger" data-bs-toggle="modal" data-bs-target="#confirmModal"></button>

        <!-- MODAL de confirmación -->
        <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Deseas eliminar este examen? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
                </div>
                </div>
            </div>
        </div>


        <!---MODAL CREAR EXAMEN--->
        <button type="button" class="btn btn-danger d-none" id="btnExamModal" data-bs-toggle="modal" data-bs-target="#examenModal"></button>
        <div class="modal fade" id="examenModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true" >
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Modulo Examen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form action="" id="formServices" class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="hidden" id="id_exam" name="id_exam">
                                <label for="staticEmail2" class="badge-figure"><i class="fas fa-check-double"></i> Nombre del servicio</label>
                                <input type="text"  class="form-control" placeholder="Nombre del servicio" id="nameService" value="">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="staticEmail2" class="badge-figure" ><i class="fas fa-check-double"></i> Categoria</label>
                                <?php
                                        echo form_dropdown(
                                        "category_id",
                                        $category_options,
                                        '',
                                        'class="select_graph w-100 form-control me-2 " id="category_id" required aria-required="true" aria-label="' . app_lang('clinic_list') . '"'
                                ); ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                            <label for="staticEmail2" class="badge-figure"><i class="fas fa-check-double"></i> Estado</label>
                                <select name=""  class="form-control" id="stateService" placeholder="Estado del servicio prestado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" onclick="save()" id="createService" style="background-color:#ccf0fe;" class="btn btn-default mb-2"><i class="fas fa-save"></i> <?php echo app_lang("save") ?></button>
                            <button type="submit" onclick="edit()" id="editService" style="background-color:#ccf0fe;" class="btn btn-default mb-2 d-none"><i class="fas fa-edit"></i> <?php echo app_lang("save") ?></button>
                            <button type="button" onclick="reload()" id="reloadService" class="btn btn-default mb-2 d-none"><i class="fas fa-sync"></i></button>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
        <!---MODAL CREAR CATEGORIA--->

        <!---MODAL CREAR EXAMEN--->
        <button type="button" class="btn btn-danger d-none" id="btncategoryModal" data-bs-toggle="modal" data-bs-target="#categoryModal"></button>
        <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog" style="max-width: 80vw;height: 80vh;display: flex; align-items: center;justify-content: center;">
                <div class="modal-content" style="width: 100%;height:100%;overflow:auto;">
                <div class="modal-header">
                    <h5 class="modal-title text-center" id="confirmModalLabel">Modulo Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">

                <div class="d-flex">
                    <div class="search-container w-75">
                        <input class="form-control search-input mb-2" id="searchInput1" onkeyup="searchCategoryTable()" type="text" placeholder="Buscar...">
                        <i class="bi bi-search search-icon"></i> <!-- Icono de búsqueda -->
                    </div>
                    <div class="w-25 d-flex justify-content-end mb-2">
                        <button class="btn btn-default w-100" onclick="confirmarCrearCategory()" style="background-color:#e8ffa4"><i class="fas fa-check-double"></i> Crear</button>
    
                    </div>
                </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-striped">
                                <thead class="text-center">
                                    <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Categoria</th>
                                    <th scope="col">Color</th>
                                    <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tableCategory" class="text-center">
                                  
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>
                </div>
            </div>
        </div>


        <!-- SEGUNDO MODAL -->
        <button type="button" class="btn btn-primary d-none" id="btnOpenSubModal" data-bs-toggle="modal" data-bs-target="#subModal">
    Abrir Segundo Modal
</button>
<div class="modal fade" id="subModal" tabindex="-1" aria-labelledby="subModalLabel" aria-hidden="true" >
    <div class="modal-dialog" style="max-width: 50vw; height: 50vh; display: flex; align-items: center; justify-content: center;">
        <div class="modal-content" style="width: 100%; height: 100%; overflow: auto; z-index: 1051;">
                    <div class="modal-header">
                        <h5 class="modal-title" id="subModalLabel">Modal Categoria</h5>
                        <button type="button"  class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
        
                        <form action="" id="formCategory" class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input type="hidden" id="category_id_data" >
                                    <label for="staticEmail2" class="badge-figure"><i class="fas fa-check-double"></i> Nombre de la Categoria</label>
                                    <input type="text"  class="form-control" placeholder="Nombre de la categoria" id="category_name" value="">
                                </div>
                            </div>
                
                            <div class="col-md-12">
                                <div class="color-container mb-3">
                                    <label class="color-label">Selecciona un color pastel:</label>
                                    <input type="color" id="category_color" class="color-input" value="#FFDDC1">

                                </div>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" onclick="saveCategory()" id="createCategory" style="background-color:#ccf0fe;" class="btn btn-default mb-2 w-100"><i class="fas fa-save"></i> <?php echo app_lang("save") ?></button>
                                <button type="submit" onclick="editCategoryNew()" id="editCategory" style="background-color:#ccf0fe;" class="btn btn-default mb-2 d-none"><i class="fas fa-edit"></i> <?php echo app_lang("save") ?></button>
                                <button type="button" onclick="reload()" id="reloadCategory" class="btn btn-default mb-2 d-none"><i class="fas fa-sync"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


      

        <!-- MODAL de confirmación -->
        <button type="button" class="btn btn-danger d-none" id="modalDeleteCategory" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal"></button>
        <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Deseas eliminar esta categoria? Esta acción no se puede deshacer.
                    <form action="" id="formDeleteCategory">
                        <input type="hidden" id="category_id_delete">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="deleteCategorySend()">Eliminar</button>
                </div>
                </div>
            </div>
        </div>



        <script>

            let id;
            let selectedServiceId = null;
            document.addEventListener("DOMContentLoaded", function(){
                ReloadTable();
                listCategory();
            }); 

            document.addEventListener('click', function(event) {
                // Verifica si el clic se hizo en un elemento dentro de un <tr> con la clase 'fill_service_pointer'
                const tr = event.target.closest('tr.fill_service_pointer');
                
                // Si se encontró un <tr> con esa clase
                if (tr) {
                
                    modalShow();
                    const form = document.getElementById("formServices");

                    // Obtener los atributos del <tr> o del elemento dentro de él
                    var idItem = tr.getAttribute('id_item'); // Atributo en el <tr>
                    var name = tr.getAttribute('name');     // Atributo en el <tr>
                    var state = tr.getAttribute('state');   // Atributo en el <tr>

                    //console.log(idItem, name, state); // Para depurar si los valores están correctos

                    form.nameService.value = name;
                    form.stateService.value = state;
                    id = idItem;
                    //document.getElementById('createService').classList.add('d-none');
                    //document.getElementById('editService').classList.remove('d-none');
    
                    hideShow();
                }
            });



            function ReloadTable(){
                $.ajax({
                    url: "<?php echo get_uri('price/listService'); ?>",
                    type: "GET",
                    dataType: 'json',
                    success: function(response){

                        drawTable(response);
                        
                    },
                    error:function(jqXHR, textStatus, errorThrown){
                        console.log('Error en la consulta.');
                    
                    }
                });
            }

            function drawTable(data){
                id = '';
                var servicesTableBody = document.getElementById('servicesTableBody');
                servicesTableBody.innerHTML = ''; // Borrar todas las filas dentro del tbody
                let i = 0;
                data.forEach(function(service) {
                    i++;
                    var row = document.createElement('tr'); // Creamos una nueva fila
                    row.classList.add('hover-pointer');

                    row.setAttribute('class', 'fill_service_pointer'); 
                    row.setAttribute('id_item', service.id); 
                    row.setAttribute('name', service.name);
                    row.setAttribute('state', service.state);

                    var cellId = document.createElement('td');
                    cellId.textContent = i;
                    row.appendChild(cellId);

                    var cellCategory = document.createElement('td');
                    var spanCategory = document.createElement('span');
                    spanCategory.textContent = service.category_name;
                    spanCategory.style.background = service.category_color;
                    var textColor = getTextColor(service.category_color);
                    console.log(textColor);
                    spanCategory.style.color = textColor;
                    spanCategory.classList.add('span_category');
                    cellCategory.appendChild(spanCategory);
                    row.appendChild(cellCategory);

                    var cellName = document.createElement('td');
                    cellName.textContent = service.name;
                    row.appendChild(cellName);

                    var cellState = document.createElement('td');
                    //cellState.textContent = service.state === "1" ? 'Activo' : 'Inactivo'; // Si el estado es 1, muestra 'Activo'
                    //row.appendChild(cellState);
                    var statusCard = document.createElement('span');
                    statusCard.classList.add('status-card');
                    if (service.state === "1") {
                        statusCard.classList.add('active-card');
                        statusCard.textContent = 'Activo';
                    } else {
                        statusCard.classList.add('inactive-card');
                        statusCard.textContent = 'Inactivo';
                    }
                    cellState.appendChild(statusCard);
                    row.appendChild(cellState);

                    //dibuja un boton
                    var cellDelete = document.createElement('td');
                    var buttonDelete = document.createElement('button');
                    buttonDelete.classList.add('btn','btn-danger','btn-sm');
                    buttonDelete.innerHTML = '<i class="far fa-trash-alt"></i>';
                    buttonDelete.onclick = function () {
                        confirmarEliminacion(service.id);
                    };


                    cellDelete.appendChild(buttonDelete);
                    row.appendChild(cellDelete);


                    var buttonEdit = document.createElement('button');
                    buttonEdit.classList.add('btn', 'btn-primary','ml-2','btn-sm');
                    buttonEdit.innerHTML = '<i class="far fa-edit"></i>';
                    buttonEdit.onclick = function () {
                        confirmarEdit(service.id,service.category_id,service.name,service.state);
                    };

                    cellDelete.appendChild(buttonEdit);
                
                    servicesTableBody.appendChild(row); // Añadimos la fila a la tabla
                });
                
            }

            function createService(name,category_id,state){
                modalShow();
                $.ajax({
                        url: "<?php echo get_uri('price/addService'); ?>",
                        type: "GET",
                        data: {
                            name: name,
                            category_id: category_id,
                            state: state,
                        },
                        dataType: 'json',
                        success: function(response){
                            $(".btn-close").click();
                            ReloadTable();
                            hideShow();
                        },
                        error:function(jqXHR, textStatus, errorThrown){
                            console.log('Error en la consulta.');
                        }
                });
            }

           function editService(id,name,category_id,state){
             modalShow();
                $.ajax({
                        url: "<?php echo get_uri('price/editService'); ?>",
                        type: "GET",
                        data: {
                            id: id,
                            category_id: category_id,
                            name: name,
                            state: state
                        },
                        dataType: 'json',
                        success: function(response){
                            $(".btn-close").click();
                            ReloadTable();
                            hideShow();
                        },
                        error:function(jqXHR, textStatus, errorThrown){
                            console.log('Error en la consulta.');
                        }
                    });
           }
                
           function deleteService(id){
            modalShow();
            const form = document.getElementById("formServices");
            $.ajax({
                url: "<?php echo get_uri('price/deleteService'); ?>",
                type: "GET",
                data: {
                    id: id,
                },
                dataType: 'json',
                success: function(response){
                    ReloadTable();
                    hideShow();
                },
                error:function(jqXHR, textStatus, errorThrown){
                    console.log('Error en la consulta.');
                }
            });
           }

           function save(){
                const form = document.getElementById("formServices");
                form.addEventListener("submit", function (event){
                    event.preventDefault();
                    //console.log('Service: ' + form.nameService.value);
                    //console.log('State: ' + form.stateService.value);
                    if(form.nameService.value == ""){
                        console.log('esta vacio');
                        return;
                    }
                    createService(form.nameService.value, form.category_id.value ,form.stateService.value);
                    form.nameService.value = "";
                });
           }

           function edit(){
            const form = document.getElementById("formServices");
                form.addEventListener("submit", function (event){
                    event.preventDefault();
                    if(form.nameService.value == ""){
                        return;
                    }
                    editService(form.id_exam.value,form.nameService.value, form.category_id.value ,form.stateService.value);
                    form.nameService.value = "";
                });
           }

           function reload(){
                modalShow();
                const form = document.getElementById("formServices");
                form.nameService.value = '';
                form.stateService.value = 1;
                id = '';
                document.getElementById('createService').classList.remove('d-none');
                document.getElementById('editService').classList.add('d-none');
                document.getElementById('reloadService').classList.add('d-none');
                document.getElementById('pre-loader').classList.remove('d-none');
                ReloadTable();
                setTimeout(function(){
                    hideShow();
                },1000);
           }

           function modalShow(){
            document.getElementById('pre-loader').classList.remove('d-none');
           }

           function hideShow(){
            document.getElementById('pre-loader').classList.add('d-none');
           }

            // Función para mostrar el modal de confirmación
            function confirmarEliminacion(serviceId) {
                selectedServiceId = serviceId; // Guardamos el ID del servicio a eliminar
                document.getElementById('modalTrigger').click(); // Dispara el modal
            }

            // Función para confirmar la eliminación (cuando el usuario presiona "Eliminar" en el modal)
            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                if (selectedServiceId !== null) {
                    console.log("Eliminando el examen con ID:", selectedServiceId);
                    // Aquí puedes agregar la lógica para eliminar el examen con AJAX o actualizar la tabla
                    deleteService(selectedServiceId);
                }
                var modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
                modal.hide(); // Cierra el modal después de confirmar la eliminación
            });


            function buttonExam(){

                document.getElementById("id_exam").value = '';
                document.getElementById('category_id').selectedIndex = 0; 
                document.getElementById('nameService').value= name;
                document.getElementById('stateService').selectedIndex = 0; 

                $("#createService").removeClass('d-none');
                $("#editService").addClass('d-none');
                $("#btnExamModal").click();
            }

            function buttonCategory(){
                $("#btncategoryModal").click();
            }

            function confirmarEdit(id,category_id,name,state) {
                $("#createService").addClass('d-none');
                $("#editService").removeClass('d-none');

                //selectedClinicID = clinic_id;
                document.getElementById("id_exam").value = id;
                document.getElementById('category_id').value= category_id;
                document.getElementById('nameService').value= name;
                document.getElementById('stateService').value= state;

                $("#btnExamModal").click();
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

            function drawTableCategory(data){
                id = '';
                var servicesTableBody = document.getElementById('tableCategory');
                servicesTableBody.innerHTML = ''; // Borrar todas las filas dentro del tbody
                let i = 0;

                var fomService = document.getElementById('formServices');
                    fomService.category_id.innerHTML = '';

                data.forEach(function(category) {

                    //agregando categorias nuevas
                    var option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.category;
                    fomService.category_id.appendChild(option);

                    i++;
                    var row = document.createElement('tr'); // Creamos una nueva fila
                    row.classList.add('hover-pointer');

                    row.setAttribute('class', 'fill_category_pointer'); 
                    row.setAttribute('id_item', category.id); 
                    row.setAttribute('name', category.category);
                    row.setAttribute('color', category.color);
                    row.setAttribute('state', category.state);

                    var cellId = document.createElement('td');
                    cellId.textContent = i;
                    row.appendChild(cellId);


                    var cellName = document.createElement('td');
                    cellName.textContent = category.category;
                    row.appendChild(cellName);

                    var cellColor = document.createElement('td');
                    let bgColor = category.color; // Color de fondo
                    let textColor = getTextColor(category.color);
                    cellColor.innerHTML = `<div style="width: 20px; height: 20px; border-radius: 50%; background-color: ${bgColor} !important; display: inline-block; text-align: center; line-height: 20px;"></div>`;
                    row.appendChild(cellColor);

                    //dibuja un boton
                    var cellDelete = document.createElement('td');
                    var buttonDelete = document.createElement('button');
                    buttonDelete.classList.add('btn','btn-danger','btn-sm');
                    buttonDelete.innerHTML = '<i class="far fa-trash-alt"></i>';
                    buttonDelete.onclick = function () {
                        confirmarDeleteCategory(category.id);
                    };


                    cellDelete.appendChild(buttonDelete);
                    row.appendChild(cellDelete);


                    var buttonEdit = document.createElement('button');
                    buttonEdit.classList.add('btn', 'btn-primary','ml-2','btn-sm');
                    buttonEdit.innerHTML = '<i class="far fa-edit"></i>';
                    buttonEdit.onclick = function () {
                        confirmarEditCategory(category.id,category.category,category.color);
                    };

                    cellDelete.appendChild(buttonEdit);
                
                    servicesTableBody.appendChild(row); // Añadimos la fila a la tabla
                });
                
            }


            function listCategory(){ 
                $.ajax({
                    url: "<?php echo get_uri('price/listCategory'); ?>",
                    type: "GET",
                    dataType: 'json',
                    success: function(response){
                        drawTableCategory(response);
                    },
                    error:function(jqXHR, textStatus, errorThrown){
                        console.log('Error en la consulta.');
                    
                    }
                });
            }

            function saveCategory(){
                const form = document.getElementById("formCategory");
                form.addEventListener("submit", function (event){
                    event.preventDefault();
                    if(form.category_name.value == ""){
                        return;
                    }
                    createCategorySend(form.category_name.value, form.category_color.value);
                    form.category_name.value = "";
                });
           }

           function editCategoryNew(){
            const form = document.getElementById("formCategory");
                form.addEventListener("submit", function (event){
                    event.preventDefault();
                    if(form.category_name.value == ""){
                        return;
                    }
                    editCategorySend(form.category_id_data.value,form.category_name.value, form.category_color.value);
                    form.category_name.value = "";
                });
           }

           function confirmarDeleteCategory(id){
            const form = document.getElementById("formDeleteCategory");
            form.category_id_delete.value = id;
            $("#modalDeleteCategory").click();
           }

           function createCategorySend(name,color){
                modalShow();
                $.ajax({
                        url: "<?php echo get_uri('price/createCategory'); ?>",
                        type: "GET",
                        data: {
                            name: name,
                            color: color,
                        },
                        dataType: 'json',
                        success: function(response){
                            $(".btn-close").click();
                            ReloadTable();
                            hideShow();
                            listCategory();
                            $("#btncategoryModal").click();
                        },
                        error:function(jqXHR, textStatus, errorThrown){
                            console.log('Error en la consulta.');
                        }
                });
            }

            function confirmarEditCategory(id,name,color) {
                $("#createCategory").addClass('d-none');
                $("#editCategory").removeClass('d-none');

                //selectedClinicID = clinic_id;
                document.getElementById("category_id_data").value = id;
                document.getElementById('category_name').value= name;
                document.getElementById('category_color').value= color;
                $(".btn-close").click();
                $("#btnOpenSubModal").click();
            } 

           function editCategorySend(id,name,color){
             modalShow();
                $.ajax({
                        url: "<?php echo get_uri('price/editCategory'); ?>",
                        type: "GET",
                        data: {
                            id: id,
                            name: name,
                            color: color,
                        },
                        dataType: 'json',
                        success: function(response){
                            $(".btn-close").click(); 
                            ReloadTable();
                            hideShow();
                            listCategory();
                            $("#btncategoryModal").click();
                        },
                        error:function(jqXHR, textStatus, errorThrown){
                            console.log('Error en la consulta.');
                        }
                    });
           }
                
           function deleteCategorySend(){
            const form = document.getElementById("formDeleteCategory");
            modalShow();
            $.ajax({
                url: "<?php echo get_uri('price/deleteCategory'); ?>",
                type: "GET",
                data: {
                    id: form.category_id_delete.value,
                },
                dataType: 'json',
                success: function(response){
                    ReloadTable();
                    hideShow();
                    listCategory();
                    $(".btn-close").click();
                    $("#btncategoryModal").click();
                },
                error:function(jqXHR, textStatus, errorThrown){
                    console.log('Error en la consulta.');
                }
            });
           }

           function confirmarCrearCategory(){
            $("#createCategory").removeClass('d-none');
            $("#editCategory").addClass('d-none');
            $("#btnOpenSubModal").click();
           }

        

        </script>



        <!-- Script para el filtro -->
        <script>
        function searchTable() {
            let input = document.getElementById('searchInput');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('servicesTableBody');
            let rows = table.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName('td');
                if (cells.length > 3) {
                    let categoryText = cells[1].querySelector('span') ? cells[1].querySelector('span').textContent.toLowerCase() : cells[1].textContent.toLowerCase();
                    let nameText = cells[2].querySelector('span') ? cells[2].querySelector('span').textContent.toLowerCase() : cells[2].textContent.toLowerCase();
                    let statusText = cells[3].querySelector('span') ? cells[3].querySelector('span').textContent.toLowerCase() : cells[3].textContent.toLowerCase();

                    if (categoryText.includes(filter) || nameText.includes(filter) || statusText.includes(filter)) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }

        function searchCategoryTable() {
            let input = document.getElementById('searchInput1');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('tableCategory');
            let rows = table.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName('td');
                if (cells.length > 1) {
                    let idText = cells[0].textContent.toLowerCase();
                    let categoryText = cells[1].textContent.toLowerCase();

                    if (idText.includes(filter) || categoryText.includes(filter)) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }

        </script>
   
  </div>
</div>