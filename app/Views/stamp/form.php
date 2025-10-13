<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-signature"></i> <?php echo app_lang("menu_stamp"); ?></h4>
        </div>
        <div class="card-body">
            <style>
                .tam-doc-group {
                    display: flex;
                }

                .tam-doc-group label {
                    padding-left: 5px;
                    padding-right: 5px;
                }

                .tam-doc-group label i {
                    font-size: 25px;
                }

                .cards-container {
                    padding: 20px;
                }

                .loader {
                    display: none;
                    text-align: center;
                }

                .listStamp {
                    height: 80vh;
                    overflow-y: scroll;
                }

                .list-stamp p {
                    padding: 0 !important;
                    margin: 0 !important;
                }

                .colorFechaHora {
                    color: rgb(189, 189, 189);
                }

                /* Estilos para la selección de plantilla */
                .template-item {
                    cursor: pointer;
                    margin: 10px;
                    text-align: center;
                    width: 120px;
                }

                .form-group input[type="text"],
                .form-group select,
                .form-group textarea,
                #search {
                    width: 100%;
                    padding: 8px;
                    box-sizing: border-box;
                    border: none;
                    background-color: #f5f5f5;
                    outline: none;
                }

                .card {
                    border-radius: 15px !important;
                }

                .form-group label {
                    display: block;
                    font-weight: bold;
                    margin-bottom: 5px;
                }

                #cardStamp {
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    padding: 10px;
                    border-radius: 15px;
                }

                /* Regla para zoom en las imágenes de previsualización y descripción */
                #template-preview-image-container img,
                .zoomable {
                    transition: transform 0.3s ease;
                    cursor: zoom-in;
                }

                #template-preview-image-container img:hover,
                .zoomable:hover {
                    transform: scale(1.2);
                }

                .marker {
                    position: absolute;
                    width: 12px;
                    height: 12px;
                    background: red;
                    border-radius: 50%;
                    transform: translate(-50%, -50%);
                    pointer-events: none;
                    z-index: 10;
                }

                /* Contenedor de cada plantilla */
                .template-item {
                    flex: 0 0 calc(25% - 1rem);
                    margin: 0.5rem;
                    padding: 1rem;
                    text-align: center;
                    border: 1px solid #e0e0e0;
                    border-radius: 0.5rem;
                    transition: box-shadow 0.2s ease;
                    background: #fff;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                }

                .template-item:hover {
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                }

                .template-item img {
                    width: 100px;
                    height: auto;
                    margin-bottom: 0.5rem;
                    border-radius: 0.25rem;
                }

                .template-item .template-title {
                    font-size: 1rem;
                    font-weight: 600;
                    margin-bottom: 0.75rem;
                    color: #333;
                }

                .template-item .btn-group-template {
                    display: flex;
                    flex-direction: column;
                    gap: 0.5rem;
                    width: 100%;
                }

                .template-item .btn-group-template .btn {
                    padding: 0.4rem 0.6rem;
                    font-size: 0.875rem;
                }
            </style>
            <form method="post" id="formTimbrado">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="proveedor_id"><?php echo app_lang('clinic_emit_document') ?> </label>
                    <select name="clinic_select" class="form-control" id="clinic_select"></select>
                </div>
                <div class="form-group d-none">
                    <label for="proveedor_id"><?php echo app_lang('select_provider_text') ?> </label>
                    <select name="proveedor_id" class="form-control" id="proveedor_id">
                        <?php foreach ($providers as $provider) { ?>
                            <option value="<?php echo $provider->id ?>"><?php echo $provider->name ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tipo_documento"><?php echo app_lang('tam_doc_text') ?></label>
                    <div class="tam-doc">
                        <div class="tam-doc-group">
                            <input type="radio" name="size" id="size1"
                                value="<?php echo app_lang('tam_carta_text') ?>">
                            <label for="size1"> <i class="far fa-sticky-note"></i>
                                <?php echo app_lang('tam_carta_text') ?></label>
                        </div>
                        <div class="tam-doc-group">
                            <input type="radio" name="size" id="size2"
                                value="<?php echo app_lang('tam_oficio_text') ?>">
                            <label for="size2"> <i class="far fa-sticky-note"></i>
                                <?php echo app_lang('tam_oficio_text') ?></label>
                        </div>
                        <div class="tam-doc-group">
                            <input type="radio" name="size" id="size3"
                                value="<?php echo app_lang('tam_a4_text') ?>">
                            <label for="size3"> <i class="far fa-sticky-note"></i>
                                <?php echo app_lang('tam_a4_text') ?></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="contenido"><?php echo app_lang('description_document_timbrar_text') ?></label>
                    <textarea name="contenido" style="height: auto" class="form-control" id="contenido" rows="6"
                        placeholder=""></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <?php echo app_lang('template_electronic_firm') ?>
                    </label>

                    <div class="my-2">
                        <button type="button" id="choose-template-btn" class="btn-rubymed btn-rubymed-success">
                            <b><i class="fas fa-arrow-up"></i> Elegir Plantilla</b>
                        </button>

                        <a class="btn-rubymed btn-rubymed-warning"
                            href="<?php echo get_uri('stamptemplate') ?>"><i class="fas fa-plus"></i>
                            Agregar</a>
                    </div>

                    <input type="text" readonly id="selected-template-name" class="form-control" placeholder=""
                        value="">
                    <!-- Contenedor para mostrar la imagen pequeña de la plantilla seleccionada -->
                    <div id="selected-template-image-container"></div>
                </div>
                <div class="form-group">
                    <button class="btn-rubymed btn-rubymed-primary-in" type="submit">
                        <i class="fas fa-arrow-right"></i> Solicitar timbre
                    </button>
                </div>
            </form>
            <div class="loader" id="loader-laboral">
                <p>Cargando...</p>
            </div>
        </div>
    </div>
</div>