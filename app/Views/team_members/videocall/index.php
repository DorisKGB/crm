<div class="tab-content">
    <div class="card rounded-bottom">
        <div class="card-body">
            
            <?php if(isset($vsee->id) && $vsee->id){ ?>
                <!-- Mensaje de éxito -->
                <!-- Mensaje de éxito mejorado -->
                <div class="video-success-banner">
                    <div class="video-success-content d-flex align-items-center">
                        <div class="video-icon-wrapper">
                            <span data-feather="check-circle" style="width: 28px; height: 28px; color: white;"></span>
                        </div>
                        <div>
                            <h3 class="video-success-title">¡Listo para hacer videollamadas!</h3>
                            <p class="video-success-subtitle">Sistema VSee configurado correctamente</p>
                        </div>
                    </div>
                </div>
                
                <style>
                .video-success-banner {
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    border: none;
                    border-radius: 16px;
                    box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);
                    position: relative;
                    overflow: hidden;
                    margin-bottom: 30px;
                }
                
                .video-success-banner::before {
                    content: '';
                    position: absolute;
                    top: -50%;
                    right: -50%;
                    width: 100%;
                    height: 200%;
                    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
                    transform: rotate(45deg);
                    animation: shimmer 3s infinite;
                }
                
                @keyframes shimmer {
                    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
                    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
                }
                
                .video-success-content {
                    position: relative;
                    z-index: 2;
                    padding: 24px;
                    color: white;
                }
                
                .video-icon-wrapper {
                    width: 56px;
                    height: 56px;
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-right: 20px;
                    animation: pulse 2s infinite;
                }
                
                @keyframes pulse {
                    0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
                    70% { box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
                }
                
                .video-success-title {
                    font-size: 24px;
                    font-weight: 700;
                    margin: 0;
                    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                
                .video-success-subtitle {
                    font-size: 16px;
                    opacity: 0.9;
                    margin: 4px 0 0 0;
                }
                
                </style>
                
                <!-- Campos de datos VSee -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="vsee_id" class="form-label">VSee ID</label>
                        <input type="text" class="form-control" id="vsee_id" name="vsee_id" 
                               value="<?php echo isset($vsee->vsee_id) ? $vsee->vsee_id : ''; ?>" readonly>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="vsee_username" class="form-label">VSee Username</label>
                        <input type="text" class="form-control" id="vsee_username" name="vsee_username" 
                               value="<?php echo isset($vsee->vsee_username) ? $vsee->vsee_username : ''; ?>" readonly>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="vsee_token" class="form-label">VSee Token</label>
                        <input type="text" class="form-control" id="vsee_token" name="vsee_token" 
                               value="<?php echo isset($vsee->vsee_token) ? $vsee->vsee_token : ''; ?>" readonly>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="vsee_room" class="form-label">VSee Room</label>
                        <input type="text" class="form-control" id="vsee_room" name="vsee_room" 
                               value="<?php echo isset($vsee->vsee_room) ? $vsee->vsee_room : ''; ?>" readonly>
                    </div>
                </div>
                
            <?php } else { ?>
                <!-- Botón para generar credenciales -->
                <style>
                    .video-warning-banner {
                        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
                        border: 2px solid #fde68a;
                        border-radius: 20px;
                        box-shadow: 0 12px 40px rgba(252, 211, 77, 0.15);
                        position: relative;
                        overflow: hidden;
                        margin-bottom: 32px;
                    }
                    
                    .video-warning-banner::before {
                        content: '';
                        position: absolute;
                        top: -50%;
                        right: -50%;
                        width: 100%;
                        height: 200%;
                        background: linear-gradient(45deg, transparent, rgba(252, 211, 77, 0.1), transparent);
                        transform: rotate(45deg);
                        animation: shimmer 4s infinite;
                    }
                    
                    .video-warning-content {
                        position: relative;
                        z-index: 2;
                        padding: 28px;
                        color: #92400e;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        text-align: center;
                    }
                    
                    .warning-icon-wrapper {
                        width: 60px;
                        height: 60px;
                        background: linear-gradient(135deg, #fbbf24, #f59e0b);
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-right: 20px;
                        box-shadow: 0 8px 20px rgba(251, 191, 36, 0.3);
                        animation: pulse-soft 3s infinite;
                    }
                    
                    @keyframes pulse-soft {
                        0% { box-shadow: 0 8px 20px rgba(251, 191, 36, 0.3); }
                        50% { box-shadow: 0 12px 30px rgba(251, 191, 36, 0.5); }
                        100% { box-shadow: 0 8px 20px rgba(251, 191, 36, 0.3); }
                    }
                    
                    .warning-title {
                        font-size: 22px;
                        font-weight: 600;
                        margin: 0 0 8px 0;
                        color: #78350f;
                        letter-spacing: -0.5px;
                    }
                    
                    .warning-subtitle {
                        font-size: 15px;
                        opacity: 0.8;
                        margin: 0;
                        color: #a16207;
                        line-height: 1.4;
                    }
                    
                    .generate-btn-custom {
                        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
                        border: none;
                        border-radius: 14px;
                        padding: 16px 32px;
                        font-size: 16px;
                        font-weight: 600;
                        color: white;
                        transition: all 0.3s ease;
                        box-shadow: 0 8px 25px rgba(251, 191, 36, 0.3);
                        letter-spacing: 0.5px;
                    }
                    
                    .generate-btn-custom:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 12px 35px rgba(251, 191, 36, 0.4);
                        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                        color: white;
                    }
                    
                    .generate-btn-custom:active {
                        transform: translateY(0);
                    }
                    
                    .warning-container {
                        background: linear-gradient(135deg, #fffbeb 0%, #fefce8 100%);
                        border-radius: 24px;
                        padding: 20px;
                        border: 1px solid #fde68a;
                    }
                </style>
                
                <div class="warning-container">
                    <div class="video-warning-banner">
                        <div class="video-warning-content">
                            <div class="warning-icon-wrapper">
                                <span data-feather="alert-triangle" style="width: 28px; height: 28px; color: white;"></span>
                            </div>
                            <div>
                                <h4 class="warning-title">Credenciales No Configuradas</h4>
                                <p class="warning-subtitle">Este usuario necesita credenciales VSee para realizar videollamadas</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="button" class="generate-btn-custom" id="generate-credentials-btn">
                            <span data-feather="key" style="width: 18px; height: 18px; margin-right: 8px;"></span> 
                            Generar Credenciales VSee
                        </button>
                    </div>
                </div>
                
               <script>
        $(document).ready(function() {
            console.log("jQuery cargado y DOM listo");
            
            $('#generate-credentials-btn').on('click', function(e) {
                showLoading();
                e.preventDefault();
                console.log("Click detectado con jQuery");
                
                var $btn = $(this);
                
                // Deshabilitar botón y mostrar loading
                $btn.prop('disabled', true);
                $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Generando...');
                
                // Llamada AJAX con jQuery
                $.ajax({
                    url: '<?php echo get_uri("team_members/registerVsee/" . $user_id); ?>',
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(data) {
                        console.log("Respuesta exitosa:", data);
                        if(data.success) {
                            // Mostrar mensaje de éxito y recargar
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                            // Restaurar botón
                            $btn.prop('disabled', false);
                            $btn.html('<span data-feather="key" class="icon-16 me-2"></span>Generar Credenciales');
                            feather.replace(); // Reinicializar iconos
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error AJAX:', {xhr: xhr, status: status, error: error});
                        console.error('Respuesta del servidor:', xhr.responseText);
                        
                        alert('Error al procesar la solicitud: ' + error);
                        
                        // Restaurar botón
                        $btn.prop('disabled', false);
                        $btn.html('<span data-feather="key" class="icon-16 me-2"></span>Generar Credenciales');
                        feather.replace(); // Reinicializar iconos
                    }
                });
            });
        });
        </script>
            <?php } ?>
            
        </div>
    </div>
</div>

