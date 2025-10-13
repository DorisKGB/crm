<?php

$user_id = $this->login_user->id ?? null;
load_js([
    "assets/js/session_heartbeat.js",       // Sistema de heartbeat primero
]);

/**
 * Notifica a la aplicación de escritorio sobre una llamada entrante
 * 
 * @param string $caller_name Nombre de quien llama
 * @param string $call_id ID único de la llamada
 * @param string $caller_image URL de la imagen del llamador (opcional)
 * @param int $time_remaining Tiempo restante en segundos (opcional)
 * @return bool true si la notificación se envió correctamente
 */
function notifyDesktopCall($caller_name, $call_id, $caller_image = null, $time_remaining = null) {
    // URL del servidor HTTP interno de la aplicación de escritorio
    $desktop_url = 'http://127.0.0.1:8080/call';
    
    // Datos a enviar
    $data = [
        'caller_name' => $caller_name,
        'call_id' => $call_id,
        'caller_image' => $caller_image,
        'time_remaining' => $time_remaining
    ];
    
    // Configurar la petición HTTP
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data),
            'timeout' => 5 // 5 segundos de timeout
        ]
    ];
    
    $context = stream_context_create($options);
    
    try {
        // Enviar la petición
        $result = file_get_contents($desktop_url, false, $context);
        
        if ($result !== false) {
            $response = json_decode($result, true);
            if (isset($response['success']) && $response['success']) {
                log_message('info', "Notificación de llamada enviada a escritorio: {$caller_name}");
                return true;
            }
        }
        
        log_message('error', "Error enviando notificación de llamada a escritorio: {$caller_name}");
        return false;
        
    } catch (Exception $e) {
        log_message('error', "Excepción enviando notificación de llamada: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica si la aplicación de escritorio está ejecutándose
 * 
 * @return bool true si la aplicación está disponible
 */
function isDesktopAppRunning() {
    $health_url = 'http://127.0.0.1:8080/health';
    
    $options = [
        'http' => [
            'method' => 'GET',
            'timeout' => 2 // 2 segundos de timeout
        ]
    ];
    
    $context = stream_context_create($options);
    
    try {
        $result = file_get_contents($health_url, false, $context);
        return $result !== false;
    } catch (Exception $e) {
        return false;
    }
}
?>
<!-- ========== WIDGET DE ESTADO DE LLAMADAS ========== -->
<?php include_once(APPPATH . 'Views/includes/call_status_widget.php'); ?>

<!-- ========== BURBUJA DE LLAMADAS - SISTEMA FLOTANTE ========== -->
<div id="calls-bubble-system" class="calls-bubble-system">
    
    <!-- Burbuja flotante de llamadas -->
    <div id="calls-bubble-icon" class="calls-bubble-wrapper">
        <div class="calls-bubble-icon" data-status="available" onclick="toggleCallsModal()">
            <i class="fas fa-phone calls-bubble-phone-icon"></i>
            <div class="calls-bubble-notification-dot" id="calls-bubble-notification" style="display: none;">
                <span class="calls-bubble-notification-count" id="calls-bubble-count">0</span>
            </div>
            <div class="calls-bubble-status-indicator available" id="calls-bubble-status"></div>
        </div>
    </div>

    <!-- Modal del sistema de llamadas -->
    <div class="calls-modal-overlay" id="calls-modal-overlay">
        <div class="calls-modal-container">
            <!-- Header -->
            <div class="calls-modal-header">
                <div class="calls-modal-header-left">
                    <div class="calls-modal-logo">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="calls-modal-title-section">
                        <h4 class="calls-modal-title"><?php echo app_lang('calls_system_title') ?: 'Sistema de Comunicación'; ?></h4>
                        <small class="calls-modal-subtitle"><?php echo app_lang('calls_system_subtitle') ?: 'Profesional disponible'; ?></small>
                    </div>
                </div>
                <div class="calls-modal-header-right">
                    <div class="calls-status-section d-none">
                        <div class="calls-status-display">
                            <div class="calls-status-dot available" id="calls-user-status-dot"></div>
                            <span class="calls-status-text" id="calls-user-status-text"><?php echo app_lang('available') ?: 'Disponible'; ?></span>
                        </div>
                        <select class="calls-status-selector d-none" id="calls-status-selector">
                            <option value="available"><?php echo app_lang('available') ?: 'Disponible'; ?></option>
                            <option value="busy"><?php echo app_lang('busy') ?: 'Ocupado'; ?></option>
                            <option value="do_not_disturb"><?php echo app_lang('do_not_disturb') ?: 'No molestar'; ?></option>
                        </select>
                    </div>
                    <div class="calls-modal-close" onclick="closeCallsModal()">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            </div>

            <!-- Controles de búsqueda -->
            <div class="calls-controls-section">
                <div class="calls-search-wrapper">
                    <div class="calls-search-input-wrapper">
                        <i class="fas fa-search calls-search-icon"></i>
                        <input type="text" class="calls-search-input" id="calls-search-input" 
                               placeholder="<?php echo app_lang('search_medical_staff') ?: 'Buscar personal médico por nombre o clínica...'; ?>">
                    </div>
                    <div class="calls-filter-buttons mt-2">
                        <button class="calls-filter-btn calls-active" data-filter="all" onclick="CallsBubbleSystem.setFilter('all')">
                            <i class="fas fa-users"></i> Todos
                        </button>
                        <button class="calls-filter-btn d-none" data-filter="available" onclick="CallsBubbleSystem.setFilter('available')">
                            <i class="fas fa-circle"></i> Disponibles
                        </button>
                        <button class="calls-filter-btn d-none" data-filter="busy" onclick="CallsBubbleSystem.setFilter('busy')">
                            <i class="fas fa-clock"></i> Ocupados
                        </button>
                        <button class="calls-filter-btn" data-filter="history" onclick="CallsBubbleSystem.showHistoryView()">
                            <i class="fas fa-history"></i> Historial
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cuerpo principal -->
            <div class="calls-modal-body">
                <div class="calls-main-layout">
                    <!-- Sidebar con clínicas -->
                    <div class="calls-sidebar">
                        <div class="calls-clinics-list" id="calls-clinics-list">
                            <!-- Se cargará dinámicamente -->
                            <div class="calls-loading-skeleton">
                                <div class="calls-skeleton-item"></div>
                                <div class="calls-skeleton-item"></div>
                                <div class="calls-skeleton-item"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de contactos -->
                    <div class="calls-content">
                        <div class="calls-contacts-panel">
                            <!-- Estado vacío inicial -->
                            <div class="calls-empty-state" id="calls-empty-state">
                                <i class="fas fa-hospital-user calls-empty-icon"></i>
                                <h5 class="calls-empty-title"><?php echo app_lang('select_clinic') ?: 'Selecciona una clínica'; ?></h5>
                                <p class="calls-empty-message"><?php echo app_lang('select_clinic_message') ?: 'Elige una clínica para ver sus contactos médicos'; ?></p>
                            </div>

                            <!-- Panel con contactos (oculto inicialmente) -->
                            <div id="calls-contacts-container" style="display: none;">
                                <div class="calls-contacts-header">
                                    <h4 class="calls-contacts-title" id="calls-contacts-title"><?php echo app_lang('clinic') ?: 'Clínica'; ?></h4>
                                    <small class="calls-contacts-count" id="calls-contacts-count">0 <?php echo app_lang('professionals') ?: 'profesionales'; ?></small>
                                </div>
                                
                                <div class="calls-local-search">
                                    <div style="position: relative;">
                                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280;"></i>
                                        <input type="text" class="calls-local-search-input" id="calls-local-search" 
                                               placeholder="<?php echo app_lang('search_in_clinic') ?: 'Buscar en esta clínica...'; ?>">
                                    </div>
                                </div>
                                
                                <div class="calls-contacts-list" id="calls-contacts-list">
                                    <!-- Se cargarán los contactos dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales adicionales para llamadas -->
    
    <!-- Modal de llamada saliente -->
    <div class="calls-outgoing-modal" id="calls-outgoing-modal" style="display: none;">
        <div class="calls-call-modal-content">
            <div class="calls-call-header">
                <div class="calls-call-avatar-section">
                    <div class="calls-call-avatar-wrapper">
                        <div class="calls-pulse-rings">
                            <div class="calls-ring calls-ring-1"></div>
                            <div class="calls-ring calls-ring-2"></div>
                            <div class="calls-ring calls-ring-3"></div>
                        </div>
                        <img id="calls-calling-user-image" src="" class="calls-call-avatar" alt="Usuario">
                    </div>
                    <div class="calls-call-info">
                        <h3 id="calls-calling-user-name" class="calls-call-name">Llamando...</h3>
                        <p class="calls-call-subtitle"><?php echo app_lang('medical_staff') ?: 'Personal médico'; ?></p>
                        <div class="calls-timer">
                            <span id="calls-call-timer">00:00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="calls-call-actions">
                <button class="calls-call-btn calls-btn-end" onclick="hangupCallsBubbleCall()">
                    <i class="fas fa-phone-slash"></i>
                    <span><?php echo app_lang('hang_up') ?: 'Colgar'; ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de llamada entrante -->
    <div class="calls-incoming-modal" id="calls-incoming-modal" style="display: none;">
        <div class="calls-call-modal-content">
            <div class="calls-call-header">
                <div class="calls-call-avatar-section">
                    <div class="calls-call-avatar-wrapper">
                        <div class="calls-wave-rings">
                            <div class="calls-wave calls-wave-1"></div>
                            <div class="calls-wave calls-wave-2"></div>
                            <div class="calls-wave calls-wave-3"></div>
                        </div>
                        <img id="calls-incoming-user-image" src="" class="calls-call-avatar" alt="Usuario">
                    </div>
                    <div class="calls-call-info">
                        <h3 id="calls-incoming-user-name" class="calls-call-name"><?php echo app_lang('incoming_call') ?: 'Llamada entrante'; ?></h3>
                        <p class="calls-call-subtitle"><?php echo app_lang('wants_to_connect') ?: 'Desea conectarse contigo'; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="calls-call-actions calls-two-buttons">
                <button class="calls-call-btn calls-btn-decline" onclick="rejectCallsBubbleCall()">
                    <i class="fas fa-times"></i>
                    <span><?php echo app_lang('decline') ?: 'Rechazar'; ?></span>
                </button>
                <button class="calls-call-btn calls-btn-accept" onclick="acceptCallsBubbleCall()">
                    <i class="fas fa-phone"></i>
                    <span><?php echo app_lang('answer') ?: 'Contestar'; ?></span>
                </button>
            </div>
        </div>
    </div>


   
</div>
 <!-- Modal de permisos de popup -->
    <div class="popup-permission-modal" id="popup-permission-modal">
        <div class="popup-permission-content">
            <div class="popup-permission-header">
                <i class="fas fa-ban popup-permission-icon"></i>
                <h3 class="popup-permission-title">Ventanas Emergentes Bloqueadas</h3>
                <p class="popup-permission-subtitle">Para realizar videollamadas necesitas habilitar las ventanas emergentes</p>
            </div>
            
            <div class="popup-permission-body">
                <div class="popup-why-section">
                    <h4 class="popup-why-title">
                        <i class="fas fa-info-circle"></i>
                        ¿Por qué necesito esto?
                    </h4>
                    <p class="popup-why-text">
                        Las videollamadas se abren en una ventana separada para ofrecerte la mejor experiencia. 
                        Tu navegador está bloqueando estas ventanas por seguridad, pero puedes permitirlas fácilmente.
                    </p>
                </div>
                
                <div class="popup-browser-instructions">
                    <h4 class="popup-browser-title">
                        <i class="fas fa-tools"></i>
                        Cómo habilitar ventanas emergentes
                    </h4>
                    
                    <div class="popup-browser-card">
                        <h5 class="popup-browser-name">
                            <i class="fab fa-chrome"></i>
                            Google Chrome / Microsoft Edge
                        </h5>
                        <img src="<?= base_url('assets/images/llamadas.png') ?>" width="100%">
                        <ol class="popup-steps-list">
                            <li>Haz clic en el ícono <strong>de ventana</strong> junto a la estrella (arriba derecha)</li>
                            <li>Busca "Ventanas emergentes y redirecciones"</li>
                            <li>Cambia de "Bloquear" a <strong>"Permitir"</strong></li>
                            <li>Recarga esta página</li>
                        </ol>
                        <div class="popup-visual-hint">
                            💡 <strong>Tip:</strong> También aparece un ícono de popup bloqueado en la barra de direcciones
                        </div>
                    </div>
                  
                </div>
            </div>
            
            <div class="popup-permission-footer">
                <button class="popup-test-btn d-none" onclick="testPopupPermission()">
                    <i class="fas fa-check-circle"></i>
                    Probar de Nuevo
                </button>
                <button class="popup-close-btn" onclick="closePopupModal()">
                    Cerrar
                </button>
                <div class="popup-reload-hint">
                    Después de cambiar la configuración, haz clic en "Probar de Nuevo"
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Popups bloqueados → Sugerir colgar -->
<div class="calls-incoming-modal" id="popup-blocked-hangup-modal" style="display:none;">
  <div class="calls-call-modal-content">
    <div class="calls-call-header">
      <div class="calls-call-avatar-section">
        <div class="calls-call-avatar-wrapper">
          <div class="calls-wave-rings">
            <div class="calls-wave calls-wave-1"></div>
            <div class="calls-wave calls-wave-2"></div>
            <div class="calls-wave calls-wave-3"></div>
          </div>
          <img id="popup-blocked-avatar" src="" class="calls-call-avatar" alt="Usuario">
        </div>
        <div class="calls-call-info">
          <h3 class="calls-call-name">Ventanas emergentes bloqueadas</h3>
          <p class="calls-call-subtitle" id="popup-blocked-subtitle">
            No se pudo abrir la videollamada. Cuelga para que el otro usuario no te espere.
          </p>
          <small style="display:block;color:#9ca3af;margin-top:6px">
            Habilita las ventanas emergentes y reintenta aceptar.
          </small>
        </div>
      </div>
    </div>

    <div class="calls-call-actions calls-two-buttons">
      <button class="calls-call-btn calls-btn-decline d-none" onclick="closePopupBlockedHangupModal()">
        <i class="fas fa-times"></i>
        <span>Seguir aquí</span>
      </button>
      <button class="calls-call-btn calls-btn-end" id="popup-blocked-hangup-btn">
        <i class="fas fa-phone-slash"></i>
        <span>Colgar ahora</span>
      </button>
    </div>

    <div id="popup-blocked-countdown" style="text-align:center;color:#9ca3af;margin-top:10px;display:none;"></div>
  </div>
</div>

<!-- Modal: Error de conexión en videollamada -->
<div class="calls-incoming-modal" id="connection-error-modal" style="display:none;">
  <div class="calls-call-modal-content">
    <div class="calls-call-header">
      <div class="calls-call-avatar-section">
        <div class="calls-call-avatar-wrapper">
          <div class="calls-error-icon">
            <i class="fas fa-wifi connection-error-icon"></i>
            <div class="connection-error-pulse"></div>
          </div>
        </div>
        <div class="calls-call-info">
          <h3 class="calls-call-name">Error de Conexión</h3>
          <p class="calls-call-subtitle">
            Se ha detectado un problema con tu conexión a internet durante la videollamada.
          </p>
          <div class="connection-error-instructions">
            <div class="error-instruction-item">
              <i class="fas fa-times-circle text-danger"></i>
              <span>Cierra esta ventana</span>
            </div>
            <div class="error-instruction-item">
              <i class="fas fa-external-link-alt text-primary"></i>
              <span>Abre una nueva ventana</span>
            </div>
            <div class="error-instruction-item">
              <i class="fas fa-redo text-success"></i>
              <span>Vuelve a intentar la llamada</span>
            </div>
          </div>
          <small style="display:block;color:#9ca3af;margin-top:12px;text-align:center;">
            Si el problema persiste, por favor contacta a soporte técnico
          </small>
        </div>
      </div>
    </div>

    <div class="calls-call-actions calls-two-buttons">
      <button class="calls-call-btn calls-btn-secondary" onclick="closeConnectionErrorModal()">
        <i class="fas fa-times"></i>
        <span>Cerrar</span>
      </button>
      <button class="calls-call-btn calls-btn-primary" onclick="retryConnection()">
        <i class="fas fa-redo"></i>
        <span>Reintentar</span>
      </button>
    </div>
  </div>
</div>

<!-- Modal: Usuario ocupado quiere hacer llamada -->
<div class="calls-incoming-modal" id="user-busy-modal" style="display:none;">
  <div class="calls-call-modal-content">
    <div class="calls-call-header">
      <div class="calls-call-avatar-section">
        <div class="calls-call-avatar-wrapper">
          <div class="calls-busy-icon">
            <i class="fas fa-user-clock busy-status-icon"></i>
            <div class="busy-pulse"></div>
          </div>
        </div>
        <div class="calls-call-info">
          <h3 class="calls-call-name">Estado Ocupado</h3>
          <p class="calls-call-subtitle">
            Actualmente tienes el estado "Ocupado". Revisa si tienes una videollamada en curso.
          </p>
          <div class="busy-user-instructions">
            <div class="busy-instruction-item">
              <i class="fas fa-search text-primary"></i>
              <span>Verifica si tienes una videollamada activa</span>
            </div>
            <div class="busy-instruction-item">
              <i class="fas fa-times-circle text-danger"></i>
              <span>Cierra cualquier ventana de videollamada</span>
            </div>
            <div class="busy-instruction-item">
              <i class="fas fa-check-circle text-success"></i>
              <span>Marca como "Disponible" para recibir llamadas</span>
            </div>
          </div>
          <small style="display:block;color:#9ca3af;margin-top:12px;text-align:center;">
            Si no tienes ninguna videollamada abierta, marca aquí como disponible
          </small>
        </div>
      </div>
    </div>

    <div class="calls-call-actions calls-two-buttons">
      <button class="calls-call-btn calls-btn-secondary" onclick="closeUserBusyModal()">
        <i class="fas fa-times"></i>
        <span>Cancelar</span>
      </button>
      <button class="calls-call-btn calls-btn-success" onclick="markAsAvailable()">
        <i class="fas fa-check"></i>
        <span>Marcar Disponible</span>
      </button>
    </div>
  </div>
</div>

<!-- Modal: Usuario receptor ocupado -->
<div class="calls-incoming-modal" id="recipient-busy-modal" style="display:none;">
  <div class="calls-call-modal-content">
    <div class="calls-call-header">
      <div class="calls-call-avatar-section">
        <div class="calls-call-avatar-wrapper">
          <div class="calls-busy-icon">
            <i class="fas fa-phone-slash recipient-busy-icon"></i>
            <div class="recipient-busy-pulse"></div>
          </div>
          <img id="recipient-busy-avatar" src="" class="calls-call-avatar" alt="Usuario ocupado" style="display:none;">
        </div>
        <div class="calls-call-info">
          <h3 class="calls-call-name">Usuario Ocupado</h3>
          <p class="calls-call-subtitle" id="recipient-busy-subtitle">
            Ahora mismo no puedes hacer una llamada porque este usuario está en una videollamada.
          </p>
          <div class="recipient-busy-instructions">
            <div class="busy-instruction-item">
              <i class="fas fa-clock text-warning"></i>
              <span>Espera a que termine su llamada actual</span>
            </div>
            <div class="busy-instruction-item">
              <i class="fas fa-redo text-primary"></i>
              <span>Intenta llamar nuevamente en unos minutos</span>
            </div>
          </div>
          <small style="display:block;color:#9ca3af;margin-top:12px;text-align:center;">
            El usuario estará disponible cuando termine su llamada actual
          </small>
        </div>
      </div>
    </div>

    <div class="calls-call-actions calls-one-button">
      <button class="calls-call-btn calls-btn-primary" onclick="closeRecipientBusyModal()">
        <i class="fas fa-check"></i>
        <span>Entendido</span>
      </button>
    </div>
  </div>
</div>


<!-- ========== MODAL DE LLAMADAS PERDIDAS ========== -->
<div id="missed-calls-modal" class="missed-calls-modal-overlay" style="display: none;">
    <div class="missed-calls-modal-container">
        <div class="missed-calls-modal-header">
            <div class="missed-calls-modal-icon">
                <i class="fas fa-phone-slash"></i>
            </div>
            <div class="missed-calls-modal-title-section">
                <h3 class="missed-calls-modal-title"><?php echo app_lang('missed_calls_dashboard_title') ?: 'Llamadas Perdidas'; ?></h3>
                <p class="missed-calls-modal-subtitle"><?php echo app_lang('missed_calls_dashboard_subtitle') ?: 'Tienes llamadas pendientes que requieren tu atención'; ?></p>
            </div>
        </div>
        
        <div class="missed-calls-modal-body">
            <div id="missed-calls-loading" class="missed-calls-loading">
                <div class="missed-calls-spinner"></div>
                <p><?php echo app_lang('missed_call_loading') ?: 'Cargando llamadas perdidas...'; ?></p>
            </div>
            
            <div id="missed-calls-content" class="missed-calls-content" style="display: none;">
                <div id="missed-calls-list" class="missed-calls-list">
                    <!-- Las llamadas perdidas se cargarán aquí dinámicamente -->
                </div>
                
                <div id="no-missed-calls" class="no-missed-calls" style="display: none;">
                    <div class="no-missed-calls-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4><?php echo app_lang('no_missed_calls') ?: '¡Excelente!'; ?></h4>
                    <p><?php echo app_lang('no_missed_calls_message') ?: 'No tienes llamadas perdidas pendientes'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="missed-calls-modal-footer">
            <button id="acknowledge-all-btn" class="missed-calls-btn missed-calls-btn-primary" onclick="acknowledgeAllMissedCalls()" style="display: none;">
                <i class="fas fa-check-double"></i>
                <span><?php echo app_lang('accept_all_missed_calls') ?: 'Aceptar Todas'; ?></span>
            </button>
        </div>
    </div>
</div>

<script type="text/javascript">

// Simple TimezoneHelper for compatibility
if (typeof window.TimezoneHelper === 'undefined') {
    window.TimezoneHelper = {
        now: function() {
            return new Date().toISOString();
        },
        minutesAgo: function(minutes) {
            const date = new Date();
            date.setMinutes(date.getMinutes() - minutes);
            return date.toISOString();
        }
    };
}

window.CallsBubbleSystem = {
    config: {
        userId: <?= json_encode($user_id ?? null) ?>,
        apiEndpoints: <?= json_encode([
        'getUsers'      => get_uri("calls_system/get_users_grouped_by_clinics"),
        'initiateCall'  => get_uri("calls_system/initiate_call"),
        'rejectCallPopupBlocked' => get_uri("calls_system/reject_call_popup_blocked"),
        'answerCall'    => get_uri("calls_system/answer_call"),
        'endCall'       => get_uri("calls_system/end_call"),
        'checkIncoming' => get_uri("calls_system/check_incoming_calls"),
        'updateStatus'  => get_uri("calls_system/update_user_status"),
        'getUserStatus' => get_uri("calls_system/get_user_status"),
        'getCallHistory' => get_uri("calls_system/get_call_history"),
        'checkCallStatus'=> get_uri("calls_system/check_call_status"),
        ], JSON_UNESCAPED_SLASHES) ?>,
        audioFiles: <?= json_encode([
        'ringTone' => base_url("assets/sounds/incoming-call.mp3"),
        'dialTone' => base_url("assets/sounds/outgoing-call.mp3"),
        ], JSON_UNESCAPED_SLASHES) ?>,
        texts: <?= json_encode([
        'calling'         => app_lang("calling") ?: "Llamando...",
        'connecting'      => app_lang("connecting") ?: "Conectando...",
        'callAccepted'    => app_lang("call_accepted") ?: "Llamada aceptada",
        'callRejected'    => app_lang("call_rejected") ?: "Llamada rechazada",
        'callEnded'       => app_lang("call_ended") ?: "Llamada finalizada",
        'userBusy'        => app_lang("user_busy") ?: "Usuario ocupado",
        'connectionError' => app_lang("connection_error") ?: "Error de conexión",
        'statusUpdated'   => app_lang("status_updated") ?: "Estado actualizado",
        ''
        ], JSON_UNESCAPED_UNICODE) ?>,
        csrf: {
            name: '<?= csrf_token() ?>',
            hash: '<?= csrf_hash() ?>'
        }
    },
    callWindow: null,
    currentCallId: null,
    incomingCallId: null,
    modalOpen: false,
    data: { clinics: [], administrative_staff: [] },
    selected: { type: null, index: null },
    filterActive: 'all',
    audioContext: null,
    ringTone: null,
    dialTone: null,
    historyData: {
        calls: [],
        currentPage: 1,
        totalPages: 1,
        loading: false
    },
    
    // Inicialización
    init: function() {
        console.log('🔄 Inicializando sistema de llamadas burbuja...');
        
        this.setupEventListeners();
        this.initializeAudio();
        this.setupAudioUnlock();
        this.setupSessionHandling();
        this.loadUsers();
        this.checkUserStatus();
        this.startIncomingCallsCheck();
        
        // Verificar disponibilidad cada 5 segundos para mayor reactividad
        setInterval(() => this.updateUserAvailability(), 5000);
        
        // Verificar el estado del usuario actual más frecuentemente
        setInterval(() => this.checkUserStatus(), 3000);
        
        console.log('✅ Sistema de llamadas burbuja inicializado');

        setInterval(() => {
            this.validateCurrentView();
        }, 2000);
    },
    
    // Configurar event listeners
    setupEventListeners: function() {
        const self = this;
        
        // Búsqueda global
        const searchInput = document.getElementById('calls-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                self.filterUsers(this.value);
            });
        }
        
        // Filtros
        document.querySelectorAll('.calls-filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // ✅ REMOVER clase activa de todos
                document.querySelectorAll('.calls-filter-btn').forEach(b => b.classList.remove('calls-active'));
                this.classList.add('calls-active');
                
                const filter = this.getAttribute('data-filter');
                self.filterActive = filter;
                
                // ✅ MANEJAR vistas de forma GARANTIZADA
                if (filter === 'history') {
                    self.showHistoryView();
                } else {
                    self.forceShowContactsView();
                    
                    // ✅ AGREGAR ESTA PARTE:
                    if (!self.selected.type || self.selected.index === null) {
                        // ✅ SELECCIONAR AUTOMÁTICAMENTE LA PRIMERA CLÍNICA DISPONIBLE
                        const mainClinics = self.data.clinics.filter(c => !self.isAlliedClinic(c.clinic_name));
                        const alliedClinics = self.data.clinics.filter(c => self.isAlliedClinic(c.clinic_name));
                        
                        if (mainClinics.length > 0) {
                            self.selectClinic('main', 0);
                        } else if (alliedClinics.length > 0) {
                            self.selectClinic('allied', 0);
                        } else if (self.data.administrative_staff.length > 0) {
                            self.selectClinic('admin', 0);
                        } else {
                            self.showEmptyState();
                        }
                    } else {
                        self.selectClinic(self.selected.type, self.selected.index);
                    }
                    
                    self.applyFilter(filter);
                }
            });
        });
        
        // Cambio de estado
        const statusSelector = document.getElementById('calls-status-selector');
        if (statusSelector) {
            statusSelector.addEventListener('change', function() {
                self.updateStatus(this.value);
            });
        }
        
        // Búsqueda local
        const localSearch = document.getElementById('calls-local-search');
        if (localSearch) {
            localSearch.addEventListener('input', function() {
                self.filterLocalContacts(this.value);
            });
        }
        
        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && self.modalOpen) {
                self.closeModal();
            }
        });
        
        // Cerrar modal al hacer click fuera
        document.addEventListener('click', function(e) {
            const overlay = document.getElementById('calls-modal-overlay');
            if (e.target === overlay && self.modalOpen) {
                self.closeModal();
            }
        });
    },

    validateCurrentView: function() {
        // ✅ FUNCIÓN DE SEGURIDAD para validar estado
        const historyContainer = document.querySelector('.calls-history-header');
        const contactsContainer = document.getElementById('calls-contacts-container');
        const emptyState = document.getElementById('calls-empty-state');
        
        if (this.currentView === 'history') {
            // ✅ Debe mostrar historial
            if (historyContainer && historyContainer.parentElement) {
                // Ya está en historial, todo correcto
            } else {
                // ✅ FORZAR vista de historial
                this.showHistoryView();
            }
        } else {
            // ✅ Debe mostrar contactos
            if (historyContainer && historyContainer.parentElement) {
                // ✅ FORZAR salida de historial
                this.forceShowContactsView();
                if (this.selected.type && this.selected.index !== null) {
                    this.selectClinic(this.selected.type, this.selected.index);
                } else {
                    this.showEmptyState();
                }
            }
        }
    },
    
    // Inicializar audio
    initializeAudio: function() {
        try {
            this.ringTone = new Audio(this.config.audioFiles.ringTone);
            this.ringTone.loop = true;
            this.ringTone.volume = 0.4;
            
            this.dialTone = new Audio(this.config.audioFiles.dialTone);
            this.dialTone.loop = true;
            this.dialTone.volume = 0.3;
        } catch (error) {
            console.warn('Archivos de audio no disponibles:', error);
        }
    },
    
    // Cargar usuarios
    loadUsers: function() {
        const self = this;
        this.showLoadingSkeleton();
        
        fetch(this.config.apiEndpoints.getUsers, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                self.displayUsers(data.data);
            } else {
                self.showError(data.message || 'Error al cargar personal médico');
            }
        })
        .catch(error => {
            console.error('Error cargando usuarios:', error);
            self.showError('Error de conexión al cargar usuarios');
        });
    },
    
    // Mostrar skeleton de carga
    showLoadingSkeleton: function() {
        const clinicsList = document.getElementById('calls-clinics-list');
        if (clinicsList) {
            clinicsList.innerHTML = `
                <div style="padding: 20px;">
                    <div style="background: #f1f5f9; height: 20px; border-radius: 8px; margin-bottom: 15px; animation: calls-pulse 1.5s infinite;"></div>
                    <div style="background: #f1f5f9; height: 60px; border-radius: 12px; margin-bottom: 12px; animation: calls-pulse 1.5s infinite;"></div>
                    <div style="background: #f1f5f9; height: 60px; border-radius: 12px; margin-bottom: 12px; animation: calls-pulse 1.5s infinite;"></div>
                    <div style="background: #f1f5f9; height: 60px; border-radius: 12px; animation: calls-pulse 1.5s infinite;"></div>
                </div>
                <style>
                    @keyframes calls-pulse {
                        0%, 100% { opacity: 1; }
                        50% { opacity: 0.5; }
                    }
                </style>
            `;
        }
    },
    
    // Mostrar usuarios
    /*displayUsers: function(data) {
        this.data = data;
        
        const mainClinics = data.clinics.filter(c => !this.isAlliedClinic(c.clinic_name));
        const alliedClinics = data.clinics.filter(c => this.isAlliedClinic(c.clinic_name));
        
        this.renderClinics({
            main: mainClinics,
            allied: alliedClinics,
            adminCount: data.administrative_staff.length
        });
        
        // Seleccionar primera clínica por defecto
        if (mainClinics.length) {
            this.selectClinic('main', 0);
        } else if (alliedClinics.length) {
            this.selectClinic('allied', 0);
        } else if (data.administrative_staff.length) {
            this.selectClinic('admin', 0);
        } else {
            this.showEmptyState();
        }
    },*/

    displayUsers: function(data) {
        // Filtrar personal administrativo - quitar usuarios que contengan "clinica" en el nombre
        data.administrative_staff = data.administrative_staff.filter(user => 
            !user.name.toLowerCase().includes('clinica')
        );
        
        this.data = data;
        
        const mainClinics = data.clinics.filter(c => !this.isAlliedClinic(c.clinic_name));
        const alliedClinics = data.clinics.filter(c => this.isAlliedClinic(c.clinic_name));
        
        this.renderClinics({
            main: mainClinics,
            allied: [],
            adminCount: data.administrative_staff.length
        });
        
        // Seleccionar primera clínica por defecto
        if (mainClinics.length) {
            this.selectClinic('main', 0);
        } else if (alliedClinics.length) {
            this.selectClinic('allied', 0);
        } else if (data.administrative_staff.length) {
            this.selectClinic('admin', 0);
        } else {
            this.showEmptyState();
        }
    },

    // Verificar si es clínica aliada
    isAlliedClinic: function(name = '') {
        const normalized = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        return normalized.includes('clinica');
    },
    
    // Renderizar clínicas
    renderClinics: function({ main, allied, adminCount }) {
        const container = document.getElementById('calls-clinics-list');
        if (!container) return;
        
        container.innerHTML = '';
        
        // Sección clínicas principales
        if (main.length > 0) {
            container.innerHTML += `
                <div class="calls-section-title">
                    <i class="fas fa-hospital" style="color: #3b82f6;"></i>
                    <?php echo app_lang('main_clinics') ?: 'Clínicas Principales'; ?>
                </div>
            `;
            
            main.forEach((clinic, index) => {
                container.innerHTML += this.createClinicRow(clinic, 'main', index);
            });
        }
        
        // Sección clínicas aliadas
        /*if (allied.length > 0) {
            container.innerHTML += `
                <div class="calls-section-title" style="margin-top: 25px;">
                    <i class="fas fa-handshake" style="color: #10b981;"></i>
                    <?php echo app_lang('allied_clinics') ?: 'Clínicas Aliadas'; ?>
                </div>
            `;
            
            allied.forEach((clinic, index) => {
                container.innerHTML += this.createClinicRow(clinic, 'allied', index);
            });
        }*/
        
        // Sección personal administrativo
        if (adminCount > 0) {
            container.innerHTML += `
                <div class="calls-section-title" style="margin-top: 25px;">
                    <i class="fas fa-users" style="color: #f59e0b;"></i>
                    <?php echo app_lang('administrative_staff') ?: 'Personal Administrativo'; ?>
                </div>
                <div class="calls-clinic-row" data-kind="admin" data-index="0" onclick="CallsBubbleSystem.selectClinic('admin', 0)">
                    <div class="calls-clinic-meta">
                        <div class="calls-clinic-icon" style="background: #f59e0b;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="calls-clinic-info">
                            <h6><?php echo app_lang('administrative_staff') ?: 'Personal Administrativo'; ?></h6>
                            <small>${adminCount} <?php echo app_lang('available_contacts') ?: 'contactos disponibles'; ?></small>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-chevron-right" style="color: #9ca3af;"></i>
                    </div>
                </div>
            `;
        }
    },
    
    // Crear fila de clínica
    createClinicRow: function(clinic, kind, index) {
        return `
            <div class="calls-clinic-row" data-kind="${kind}" data-index="${index}" onclick="CallsBubbleSystem.selectClinic('${kind}', ${index})">
                <div class="calls-clinic-meta">
                    <div class="calls-clinic-icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <div class="calls-clinic-info">
                        <h6>${clinic.clinic_name}</h6>
                        <small>${clinic.users.length} <?php echo app_lang('available_contacts') ?: 'contactos disponibles'; ?></small>
                    </div>
                </div>
                <div>
                    <i class="fas fa-chevron-right" style="color: #9ca3af;"></i>
                </div>
            </div>
        `;
    },
    
    // Seleccionar clínica
    selectClinic: function(kind, index) {
        this.selected = { type: kind, index: index };
        
        // Actualizar estado visual
        document.querySelectorAll('.calls-clinic-row').forEach(row => {
            row.classList.remove('calls-active');
        });
        
        const selectedRow = document.querySelector(`.calls-clinic-row[data-kind="${kind}"][data-index="${index}"]`);
        if (selectedRow) {
            selectedRow.classList.add('calls-active');
        }
        
        let title = '', users = [];
        
        if (kind === 'main' || kind === 'allied') {
            const list = (kind === 'main') 
                ? this.data.clinics.filter(c => !this.isAlliedClinic(c.clinic_name))
                : this.data.clinics.filter(c => this.isAlliedClinic(c.clinic_name));
            
            const clinic = list[index];
            if (!clinic) {
                this.showEmptyState();
                return;
            }
            
            title = clinic.clinic_name;
            users = clinic.users || [];
        } else if (kind === 'admin') {
            title = '<?php echo app_lang("administrative_staff") ?: "Personal Administrativo"; ?>';
            users = this.data.administrative_staff || [];
        }
        
        this.showContacts(title, users);
    },
    
    // Mostrar contactos
    showContacts: function(title, users) {
        // ✅ FORZAR vista de contactos antes de mostrar contenido
        this.currentView = 'contacts';
        
        const emptyState = document.getElementById('calls-empty-state');
        const contactsContainer = document.getElementById('calls-contacts-container');
        const contactsTitle = document.getElementById('calls-contacts-title');
        const contactsCount = document.getElementById('calls-contacts-count');
        const contactsList = document.getElementById('calls-contacts-list');
        
        if (emptyState) emptyState.style.display = 'none';
        if (contactsContainer) contactsContainer.style.display = 'block';
        if (contactsTitle) contactsTitle.textContent = title;
        if (contactsCount) contactsCount.textContent = `${users.length} <?php echo app_lang("professionals") ?: "profesionales"; ?>`;
        
        if (contactsList) {
            contactsList.innerHTML = users.map(user => this.createContactCard(user)).join('');
        }
        
        // ✅ VALIDAR que no hay contenido de historial residual
        const historyContent = document.querySelector('.calls-history-header');
        if (historyContent && historyContent.parentElement === contactsContainer) {
            contactsContainer.innerHTML = `
                <div class="calls-contacts-header">
                    <h4 class="calls-contacts-title" id="calls-contacts-title">${title}</h4>
                    <small class="calls-contacts-count" id="calls-contacts-count">${users.length} profesionales</small>
                </div>
                <div class="calls-local-search">
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280;"></i>
                        <input type="text" class="calls-local-search-input" id="calls-local-search" 
                            placeholder="Buscar en esta clínica...">
                    </div>
                </div>
                <div class="calls-contacts-list" id="calls-contacts-list">
                    ${users.map(user => this.createContactCard(user)).join('')}
                </div>
            `;
        }
        
        // Aplicar filtro activo
        setTimeout(() => this.applyFilter(this.filterActive), 100);
    },
    
    // Crear tarjeta de contacto
    createContactCard: function(user) {
        // Determinar estado visual basado en conexión y disponibilidad
        let statusClass, statusText;
        
        if (!user.is_online) {
            // Usuario desconectado
            statusClass = 'offline';
            statusText = '<?php echo app_lang("offline") ?: "Desconectado"; ?>';
        } else if (user.available) {
            // Usuario en línea y disponible
            statusClass = 'available';
            statusText = '<?php echo app_lang("available") ?: "Disponible"; ?>';
        } else {
            // Usuario en línea pero ocupado
            statusClass = 'busy';
            if (user.status === 'busy') {
                statusText = '<?php echo app_lang("busy") ?: "Ocupado"; ?>';
            } else if (user.status === 'do_not_disturb') {
                statusText = '<?php echo app_lang("do_not_disturb") ?: "No molestar"; ?>';
            } else {
                statusText = '<?php echo app_lang("in_consultation") ?: "En consulta"; ?>';
            }
        }
    
        return `
            <div class="calls-contact-card-mini" data-user-id="${user.id}" data-user-status="${user.status}" data-is-online="${user.is_online}" data-available="${user.available}">
                <div class="calls-contact-avatar-wrapper">
                    <img src="${user.image}" alt="${user.name}" class="calls-contact-avatar-mini">
                    <span class="calls-contact-dot ${statusClass}"></span>
                </div>
                <div class="calls-contact-info-mini">
                    <h6>${user.name}</h6>
                    <div><small>${statusText}</small></div>
                </div>
                <button class="calls-contact-btn-big ${statusClass}" 
                        onclick="CallsBubbleSystem.initiateCall(${user.id}, '${user.name}', '${user.image}')"
                        ${!user.available ? 'disabled' : ''}>
                    <i class="fas fa-${user.available ? 'phone' : 'clock'}"></i>
                </button>
            </div>
        `;
    },



    
    // Iniciar llamada
    initiateCall: async function(userId, userName, userImage) {
        console.log('Iniciando llamada, verificando estado...');
        
        // Verificar si el usuario actual está en una llamada activa
        const hasActiveCall = this.currentCallId || this.incomingCallId || (this.callWindow && !this.callWindow.closed);
        const currentStatus = document.getElementById('calls-user-status-selector')?.value;
        
        if (hasActiveCall || currentStatus === 'busy' || currentStatus === 'do_not_disturb') {
            console.log('Usuario está ocupado o en llamada, mostrando modal...');
            if (typeof showUserBusyModal === 'function') {
                showUserBusyModal();
            } else {
                this.showNotification('No puedes hacer llamadas mientras estás ocupado o en otra llamada', 'warning');
            }
            return;
        }
        
        console.log('Estado verificado, verificando popup...');
        
        try {
            // FORZAR SIEMPRE la validación, sin importar si funciona o no
            const popupValidator = new PopupPermissionValidator();
            
            // Primer test rápido
            const quickTest = await popupValidator.checkPopupPermission();
            
            if (!quickTest) {
                // Si falla el test rápido, MOSTRAR EL MODAL INMEDIATAMENTE
                console.log('Test rápido falló, mostrando modal...');
                const permissionResult = await popupValidator.showPopupInstructions();
                
                if (!permissionResult.isConfirmed) {
                    this.showNotification('Se requieren ventanas emergentes para realizar llamadas', 'warning');
                    return;
                }
                
                // Verificar nuevamente después de las instrucciones
                const retestResult = await popupValidator.checkPopupPermission();
                if (!retestResult) {
                    this.showNotification('Las ventanas emergentes siguen bloqueadas', 'error');
                    return;
                }
            }

            // Continuar con el flujo normal solo si los permisos están OK
            //this.callWindow = window.open('about:blank', '_blank');

            if (this.isSafari()) {
                this.callWindow = window.open('about:blank', 'VseeCall', 'fullscreen=yes,width=' + screen.width + ',height=' + screen.height + ',left=0,top=0');

            } else {
                this.callWindow = window.open('about:blank', '_blank', 'fullscreen=yes,width=' + screen.width + ',height=' + screen.height + ',left=0,top=0');

            }

            if (!this.callWindow) {
                // Último recurso: mostrar modal de nuevo
                const finalValidator = new PopupPermissionValidator();
                await finalValidator.showPopupInstructions();
                this.showNotification('El navegador bloqueó la ventana emergente. Configura los permisos y vuelve a intentar.', 'error');
                return;
            }

            // Resto del código de iniciar llamada...
            this.createCallerReactivePopup(this.callWindow, {
                title: `Llamando a ${userName}`,
                subtitle: 'Esperando a que conteste…'
            });
            this.__dialedName = userName;
            try { this.callWindow.blur(); window.focus(); } catch(_){}

            this.showOutgoingCallModal(userName, userImage);
            this.startDialTone();

            const fd = new FormData();
            fd.append('receiver_id', userId);
            fd.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

            fetch(this.config.apiEndpoints.initiateCall, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.currentCallId = data.call_id;
                    this.startCallStatusCheck();
                    this.showNotification(this.config.texts.calling + ' ' + userName + '...', 'info');
                } else {
                    if (this.callWindow && !this.callWindow.closed) this.callWindow.close();
                    this.callWindow = null;
                    this.endCall();
                    if (data.user_busy) {
                        // Mostrar modal de receptor ocupado en lugar de solo notificación
                        if (typeof showRecipientBusyModal === 'function') {
                            showRecipientBusyModal(userName, userImage);
                        } else {
                            this.showNotification(userName + ' ' + this.config.texts.userBusy, 'warning');
                        }
                    } else {
                        this.showNotification(data.message || 'Error al iniciar llamada', 'error');
                    }
                }
            })
            .catch(err => {
                if (this.callWindow && !this.callWindow.closed) this.callWindow.close();
                this.callWindow = null;
                console.error('Error iniciando llamada:', err);
                this.endCall();
                
                // Mostrar modal de error de conexión en lugar de solo notificación
                if (typeof showConnectionErrorModal === 'function') {
                    showConnectionErrorModal();
                } else {
                    this.showNotification(this.config.texts.connectionError, 'error');
                }
            });

        } catch (error) {
            console.error('Error en validación de popup:', error);
            this.showNotification('Error al preparar la llamada', 'error');
        }
    },
    
    // Otros métodos...
    toggleModal: function() {
        if (this.modalOpen) {
            this.closeModal();
        } else {
            this.openModal();
        }
    },
    
    openModal: function() {
        const overlay = document.getElementById('calls-modal-overlay');
        if (overlay) {
            overlay.style.display = 'flex';
            setTimeout(() => {
                overlay.classList.add('calls-show');
            }, 10);
            
            this.modalOpen = true;
            this.clearBubbleNotifications();
            
            if (this.data.clinics.length === 0) {
                this.loadUsers();
            }
        }
    },
    
    closeModal: function() {
        const overlay = document.getElementById('calls-modal-overlay');
        if (overlay) {
            overlay.classList.remove('calls-show');
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
            
            this.modalOpen = false;
        }
    },
    
    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `calls-notification ${type}`;
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="${icons[type]}" style="font-size: 20px; color: ${colors[type]};"></i>
                <span style="flex: 1; font-weight: 500;">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #6b7280;">×</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 4000);
    },

    startCallStatusCheck: function () {
        // ✅ CAMBIAR: Verificar si hay cualquier tipo de llamada activa
        const callId = this.currentCallId || this.incomingCallId;
        if (!callId) return;

        const self = this;
        const T = "<?= csrf_token() ?>";
        const H = "<?= csrf_hash() ?>";

        const check = () => {
            // ✅ MEJORADO: Usar la variable callId que puede ser cualquiera de las dos
            const activeCallId = self.currentCallId || self.incomingCallId;
            if (!activeCallId) {
                // Si no hay llamada activa, detener verificación
                if (self.__statusTimer) {
                    clearInterval(self.__statusTimer);
                    self.__statusTimer = null;
                }
                if (self.__callTimeout) {
                    clearTimeout(self.__callTimeout);
                    self.__callTimeout = null;
                }
                return;
            }

            const fd = new FormData();
            fd.append('call_id', activeCallId);  // ✅ CAMBIAR: Usar activeCallId
            fd.append(T, H);

            fetch(self.config.apiEndpoints.checkCallStatus, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data) return;
                if (data.success) {
                    self.handleCallStatusUpdate(data); // ← Ahora SÍ se ejecutará para receiver
                } else {
                    self.endCall();
                }
            })
            .catch(() => {});
        };

        this.__statusTimer && clearInterval(this.__statusTimer);
        this.__statusTimer = setInterval(check, 2000);

        // ✅ MEJORADO: timeout de seguridad (2 min) - funciona para emisor y receptor
        this.__callTimeout = setTimeout(() => {
            self.__statusTimer && clearInterval(self.__statusTimer);
            // Verificar cualquier llamada activa (emisor o receptor)
            if (self.currentCallId || self.incomingCallId) {
                console.log('⏰ Timeout de llamada alcanzado - terminando llamada automáticamente');
                self.endCall();
                self.showNotification('Llamada sin respuesta - tiempo agotado', 'warning');
            }
        }, 120000);
    },

    isSafari: function() {
        return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    },

    // 🔔 FUNCIÓN PARA NOTIFICAR A LA APLICACIÓN DE ESCRITORIO
    notifyDesktopApp: function(callerName, callerImage) {
        try {
            // Verificar si la aplicación de escritorio está disponible
            fetch('http://127.0.0.1:8080/health', {
                method: 'GET',
                mode: 'no-cors',
                cache: 'no-store'
            })
            .then(() => {
                // Si la aplicación está disponible, enviar la notificación
                const callData = {
                    caller_name: callerName || 'Usuario desconocido',
                    call_id: this.incomingCallId || 'unknown_' + Date.now(),
                    caller_image: callerImage || null,
                    time_remaining: null
                };

                fetch('http://127.0.0.1:8080/call', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(callData),
                    mode: 'no-cors'
                })
                .then(() => {
                    console.log('✅ Notificación enviada a aplicación de escritorio:', callerName);
                })
                .catch(error => {
                    console.log('⚠️ Error enviando notificación a escritorio:', error);
                });
            })
            .catch(() => {
                console.log('⚠️ Aplicación de escritorio no disponible');
            });
        } catch (error) {
            console.log('⚠️ Error verificando aplicación de escritorio:', error);
        }
    },

    // 🧪 FUNCIÓN DE PRUEBA PARA NOTIFICAR A LA APLICACIÓN DE ESCRITORIO
    testDesktopNotification: function(callerName = 'Usuario de Prueba') {
        console.log('🧪 Probando notificación de escritorio para:', callerName);
        this.notifyDesktopApp(callerName, null);
        return 'Notificación de prueba enviada para: ' + callerName;
    },


    showIncomingCallModal: function (name, image) {
        const m = document.getElementById('calls-incoming-modal');
        if (m) m.style.display = 'block';
        const n = document.getElementById('calls-incoming-user-name');
        const img = document.getElementById('calls-incoming-user-image');
        if (n && name) n.textContent = name;
        
        // 🔔 NOTIFICAR A LA APLICACIÓN DE ESCRITORIO
        this.notifyDesktopApp(name, image);
        if (img && image) img.src = image;

        this.startRingTone();
        
        // ✅ AGREGAR: Iniciar verificación de estado para llamada entrante
        this.startCallStatusCheck();
    },

    handleCallStatusUpdate: function (statusData) {
        const isCaller = !!this.currentCallId;
        
        switch (statusData.call_status) {
            case 'pending':
            case 'ringing':
                if (isCaller) {
                    this.updateCallerPopup('wait', {
                        title: 'Llamando a ' + (this.__dialedName || 'usuario'),
                        subtitle: 'Esperando respuesta...'
                    });
                }
                break;

           case 'in_progress':
            if ((statusData.vsee_url || statusData.caller_vsee_url) && !this._vseeNavigated) {
                this._vseeNavigated = true; // Flag para evitar múltiples navegaciones
                
                this.stopAllTones();
                this.showNotification('Llamada conectada - abriendo videollamada...', 'success');

                if (isCaller) {
                    this.updateCallerPopup('ok', {
                        title: '¡Conectado!',
                        subtitle: 'Abriendo videollamada...',
                        autoClose: 3
                    });
                    
                    const urlToUse = statusData.caller_vsee_url || statusData.vsee_url;
                    setTimeout(() => {
                        try { 
                            this.callWindow.location.href = urlToUse; 
                            this.callWindow.focus(); 
                        } catch(e){ 
                            window.open(urlToUse, 'VseeCall', 'popup=yes,width=980,height=720,noopener=0');
                        }
                    }, 2000);
                } else {
                    // código del receiver...
                }
                
                setTimeout(() => {
                    this.endCall();
                }, 5000);
            }
            break;

            case 'rejected':
                this.stopAllTones();
                if (isCaller) {
                    this.updateCallerPopup('err', {
                        title: 'Llamada rechazada',
                        subtitle: 'El usuario rechazó la llamada',
                        autoClose: 4,
                        showActions: true
                    });
                }
                this.endCall();
                this.showNotification('Llamada rechazada', 'warning');
                break;

            case 'missed':
                console.log('📞 MISSED: Llamada no contestada detectada - Role:', isCaller ? 'Emisor' : 'Receptor');
                this.stopAllTones();
                if (isCaller) {
                    console.log('📞 MISSED: Mostrando popup de error para emisor');
                    this.updateCallerPopup('err', {
                        title: 'Sin respuesta',
                        subtitle: 'El usuario no contestó la llamada',
                        autoClose: 4,
                        showActions: true
                    });
                }
                // ✅ MEJORADO: Asegurar que ambos participantes terminen la llamada
                console.log('📞 MISSED: Terminando llamada para', isCaller ? 'emisor' : 'receptor');
                this.endCall();
                this.showNotification('Llamada no contestada', 'warning');
                
                // ✅ NUEVO: Limpiar cualquier timeout de verificación pendiente
                if (this.__statusTimer) {
                    clearInterval(this.__statusTimer);
                    this.__statusTimer = null;
                }
                break;

            case 'failed':
                this.stopAllTones();
                if (isCaller) {
                    this.updateCallerPopup('err', {
                        title: 'Llamada fallida',
                        subtitle: 'Error de conexión',
                        autoClose: 4,
                        showActions: true
                    });
                }
                this.endCall();
                this.showNotification('La llamada ha fallado', 'error');
                break;
        }
    },

    checkIncomingCalls: function () {
        if (this.currentCallId || this.incomingCallId) return;

        const url = this.config.apiEndpoints.checkIncoming + '?t=' + Date.now();
        fetch(url, { method: 'GET', cache: 'no-store', credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            const list = (data && data.success && Array.isArray(data.incoming_calls)) ? data.incoming_calls : [];
            if (list.length) {
                const c = list[0]; // toma la primera pendiente
                this.incomingCallId = c.call_id;
                this.showIncomingCallModal(c.caller_name, c.caller_image);
                
                // 🔔 NOTIFICAR INMEDIATAMENTE A LA APLICACIÓN DE ESCRITORIO
                this.notifyDesktopApp(c.caller_name, c.caller_image);
            }
        })
        .catch(() => {});
    },

    startDialTone: function () {
        if (!this.dialTone) return;
        try {
            this.dialTone.currentTime = 0;
            const p = this.dialTone.play();
            if (p && p.catch) p.catch(()=>{ /* bloqueado por autoplay, no romper */});
        } catch(e) {}
    },
    startRingTone: function () {
        if (!this.ringTone) return;
        try {
            if (!this.__audioUnlocked) this.showEnableSoundBanner();
            this.ringTone.currentTime = 0;
            const p = this.ringTone.play();
            if (p && p.catch) p.catch(()=> this.showEnableSoundBanner());
        } catch(e) {
            this.showEnableSoundBanner();
        }
    },
    stopAllTones: function () {
        try {
            if (this.dialTone) { this.dialTone.pause(); this.dialTone.currentTime = 0; }
            if (this.ringTone) { this.ringTone.pause(); this.ringTone.currentTime = 0; }
        } catch(e) {}
    },

    checkUserStatus: function () {
        // Lee estado del usuario y actualiza el dot/texto
        const url = this.config.apiEndpoints.getUserStatus + '?user_id=' + encodeURIComponent(this.config.userId || '');
        return fetch(url, { method: 'GET', cache: 'no-store', credentials: 'include' })
        .then(r => r.ok ? r.json() : null)
        .then(res => {
            const dot  = document.getElementById('calls-user-status-dot');
            const text = document.getElementById('calls-user-status-text');
            const status = (res && res.success && res.data && res.data.status) ? String(res.data.status) : 'available';
            if (dot) { dot.classList.remove('available','busy','do_not_disturb'); dot.classList.add(status); }
            if (text) {
                const map = { available: 'Disponible', busy: 'Ocupado', do_not_disturb: 'No molestar' };
                text.textContent = map[status] || status;
            }
        }).catch(() => {});
    },

    startIncomingCallsCheck: function () {
        if (this.incomingTimer) clearInterval(this.incomingTimer);
        this.__incomingTimer = setInterval(() => {
            const url = this.config.apiEndpoints.checkIncoming + '?t=' + Date.now();
            fetch(url, { method: 'GET', cache: 'no-store', credentials: 'same-origin' })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                const list = (data && data.success && Array.isArray(data.incoming_calls)) ? data.incoming_calls : [];
                if (list.length && !this.incomingCallId && !this.currentCallId) {
                    const c = list[0];
                    this.incomingCallId = c.call_id;
                    this.showIncomingCallModal(c.caller_name, c.caller_image);
                    
                    // 🔔 NOTIFICAR INMEDIATAMENTE A LA APLICACIÓN DE ESCRITORIO
                    this.notifyDesktopApp(c.caller_name, c.caller_image);
                }
            }).catch(() => {});
        }, 4000);
    },

    updateUserAvailability: function () {
        // Evitar múltiples actualizaciones simultáneas
        if (this._isUpdatingUsers) {
            return;
        }
        
        // Actualizar estado de todos los usuarios en tiempo real
        const self = this;
        this._isUpdatingUsers = true;
        
        // Mostrar indicador sutil de actualización
        this.showUpdateIndicator();
        
        // Actualizar estados de otros usuarios
        fetch(this.config.apiEndpoints.getUsers, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            cache: 'no-store'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Actualizar solo los estados sin recrear toda la interfaz
                self.updateUserStatuses(data.data);
            }
        })
        .catch(error => {
            console.warn('Error actualizando disponibilidad de usuarios:', error);
        })
        .finally(() => {
            self._isUpdatingUsers = false;
            self.hideUpdateIndicator();
            // Aplicar filtros visuales
            self.applyFilter(self.filterActive || 'all');
        });
    },

    // Nueva función para actualizar estados de usuarios sin recrear interfaz
    updateUserStatuses: function (data) {
        const self = this;
        
        // Filtrar personal administrativo
        data.administrative_staff = data.administrative_staff.filter(user => 
            !user.name.toLowerCase().includes('clinica')
        );
        
        // Actualizar datos internos
        this.data = data;
        
        // Actualizar todas las tarjetas de usuario existentes
        const allUsers = [];
        
        // Agregar usuarios de clínicas principales
        data.clinics.filter(c => !this.isAlliedClinic(c.clinic_name)).forEach(clinic => {
            allUsers.push(...clinic.users.map(u => ({...u, clinic: clinic.clinic_name, type: 'main'})));
        });
        
        // Agregar usuarios de clínicas aliadas
        data.clinics.filter(c => this.isAlliedClinic(c.clinic_name)).forEach(clinic => {
            allUsers.push(...clinic.users.map(u => ({...u, clinic: clinic.clinic_name, type: 'allied'})));
        });
        
        // Agregar personal administrativo
        allUsers.push(...data.administrative_staff.map(u => ({...u, type: 'admin'})));
        
        // Actualizar cada tarjeta existente solo si hay cambios
        allUsers.forEach(user => {
            const card = document.querySelector(`.calls-contact-card-mini[data-user-id="${user.id}"]`);
            if (card) {
                // Verificar si realmente hay cambios antes de actualizar
                const currentOnline = card.getAttribute('data-is-online');
                const currentAvailable = card.getAttribute('data-available');
                const currentStatus = card.getAttribute('data-user-status');
                
                const hasChanges = (
                    currentOnline !== String(user.is_online) ||
                    currentAvailable !== String(user.available) ||
                    currentStatus !== String(user.status)
                );
                
                if (!hasChanges) {
                    return; // Sin cambios, saltar actualización
                }
                // Actualizar atributos de datos
                card.setAttribute('data-is-online', user.is_online);
                card.setAttribute('data-available', user.available);
                card.setAttribute('data-user-status', user.status);
                
                // Actualizar indicador visual de estado
                const dot = card.querySelector('.calls-contact-dot');
                if (dot) {
                    // Remover todas las clases de estado
                    dot.classList.remove('available', 'busy', 'do_not_disturb', 'offline');
                    
                    // Determinar nueva clase de estado
                    let statusClass;
                    if (!user.is_online) {
                        statusClass = 'offline';
                    } else if (user.available) {
                        statusClass = 'available';
                    } else {
                        if (user.status === 'do_not_disturb') {
                            statusClass = 'do_not_disturb';
                        } else {
                            statusClass = 'busy';
                        }
                    }
                    
                    // Aplicar nueva clase
                    dot.classList.add(statusClass);
                }
                
                // Actualizar texto de estado
                const statusText = card.querySelector('.calls-contact-status-mini');
                if (statusText) {
                    let newStatusText;
                    if (!user.is_online) {
                        newStatusText = '<?php echo app_lang("offline") ?: "Desconectado"; ?>';
                    } else if (user.available) {
                        newStatusText = '<?php echo app_lang("available") ?: "Disponible"; ?>';
                    } else {
                        if (user.status === 'do_not_disturb') {
                            newStatusText = '<?php echo app_lang("do_not_disturb") ?: "No molestar"; ?>';
                        } else {
                            newStatusText = '<?php echo app_lang("in_consultation") ?: "En consulta"; ?>';
                        }
                    }
                    statusText.textContent = newStatusText;
                }

                 // Actualizar botón de llamada
                const callButton = card.querySelector('.calls-contact-btn-big');
                if (callButton) {
                    // Remover todas las clases de estado del botón
                    callButton.classList.remove('available', 'busy', 'do_not_disturb', 'offline');
                    
                    // Determinar nueva clase de estado para el botón
                    let buttonStatusClass;
                    if (!user.is_online) {
                        buttonStatusClass = 'offline';
                    } else if (user.available) {
                        buttonStatusClass = 'available';
                    } else {
                        if (user.status === 'do_not_disturb') {
                            buttonStatusClass = 'do_not_disturb';
                        } else {
                            buttonStatusClass = 'busy';
                        }
                    }
                    
                    // Aplicar nueva clase al botón
                    callButton.classList.add(buttonStatusClass);
                    
                    // Actualizar estado disabled del botón
                    if (user.available) {
                        callButton.removeAttribute('disabled');
                        callButton.innerHTML = '<i class="fas fa-phone"></i>';
                    } else {
                        callButton.setAttribute('disabled', 'disabled');
                        callButton.innerHTML = '<i class="fas fa-clock"></i>';
                    }
                }
            }
        });
        
        // Contar actualizaciones realizadas
        const updatedCount = allUsers.filter(user => {
            const card = document.querySelector(`.calls-contact-card-mini[data-user-id="${user.id}"]`);
            if (!card) return false;
            
            const currentOnline = card.getAttribute('data-is-online');
            const currentAvailable = card.getAttribute('data-available');
            const currentStatus = card.getAttribute('data-user-status');
            
            return (
                currentOnline !== String(user.is_online) ||
                currentAvailable !== String(user.available) ||
                currentStatus !== String(user.status)
            );
        }).length;
        
        if (updatedCount > 0) {
            console.log(`📡 ${updatedCount} estados de usuarios actualizados en tiempo real`);
        }
    },

    // Mostrar indicador sutil de actualización
    showUpdateIndicator: function() {
        const bubble = document.querySelector('.calls-bubble-icon');
        if (bubble) {
            bubble.classList.add('calls-updating');
        }
    },

    // Ocultar indicador de actualización
    hideUpdateIndicator: function() {
        const bubble = document.querySelector('.calls-bubble-icon');
        if (bubble) {
            bubble.classList.remove('calls-updating');
        }
    },

    applyFilter: function (filter) {
        // Filtra cards por disponibilidad: all | available | busy | offline
        const cards = document.querySelectorAll('.calls-contact-card-mini, .calls-contact-card');
        cards.forEach(card => {
            const isOnline = card.dataset.isOnline === 'true';
            const isAvailable = card.dataset.available === 'true';
            
            let visible = true;
            if (filter === 'available') {
                visible = isOnline && isAvailable;
            } else if (filter === 'busy') {
                visible = isOnline && !isAvailable;
            } else if (filter === 'offline') {
                visible = !isOnline;
            }
            // filter === 'all' muestra todos
            
            card.style.display = visible ? '' : 'none';
        });
    },

    clearBubbleNotifications: function () {
        const dot = document.getElementById('calls-bubble-notification');
        const count = document.getElementById('calls-bubble-count');
        if (dot) dot.style.display = 'none';
        if (count) count.textContent = '0';
    },

    filterUsers: function(query) {
        // Búsqueda global mejorada - busca en TODOS los datos cargados
        query = (query || '').trim().toLowerCase();
        
        if (!query) {
            // Si no hay query, mostrar la selección actual
            this.showCurrentSelection();
            return;
        }
        
        // Buscar en TODOS los datos disponibles
        let allMatches = [];
        
        // 1. Buscar en clínicas principales
        const mainClinics = this.data.clinics.filter(c => !this.isAlliedClinic(c.clinic_name));
        mainClinics.forEach(clinic => {
            // Buscar por nombre de clínica
            if (clinic.clinic_name.toLowerCase().includes(query)) {
                clinic.users.forEach(user => allMatches.push({user, clinicName: clinic.clinic_name, type: 'main'}));
            } else {
                // Buscar por nombre de usuarios dentro de la clínica
                clinic.users.forEach(user => {
                    if (user.name.toLowerCase().includes(query)) {
                        allMatches.push({user, clinicName: clinic.clinic_name, type: 'main'});
                    }
                });
            }
        });
        
        // 2. Buscar en clínicas aliadas
        const alliedClinics = this.data.clinics.filter(c => this.isAlliedClinic(c.clinic_name));
        alliedClinics.forEach(clinic => {
            // Buscar por nombre de clínica
            if (clinic.clinic_name.toLowerCase().includes(query)) {
                clinic.users.forEach(user => allMatches.push({user, clinicName: clinic.clinic_name, type: 'allied'}));
            } else {
                // Buscar por nombre de usuarios dentro de la clínica
                clinic.users.forEach(user => {
                    if (user.name.toLowerCase().includes(query)) {
                        allMatches.push({user, clinicName: clinic.clinic_name, type: 'allied'});
                    }
                });
            }
        });
        
        // 3. Buscar en personal administrativo (filtrar usuarios con "clinica" en el nombre)
        const filteredAdminStaff = this.data.administrative_staff.filter(user => 
            !user.name.toLowerCase().includes('clinica')
        );
        
        filteredAdminStaff.forEach(user => {
            if (user.name.toLowerCase().includes(query)) {
                allMatches.push({user, clinicName: 'Personal Administrativo', type: 'admin'});
            }
        });
        
        // Mostrar resultados de búsqueda global
        this.showGlobalSearchResults(allMatches, query);
    },

    showHistoryView: function() {
        this.currentView = 'history';
        
        // Actualizar botones de filtro
        document.querySelectorAll('.calls-filter-btn').forEach(btn => {
            btn.classList.remove('calls-active');
        });
        
        // Agregar clase activa al botón de historial
        const historyBtn = document.querySelector('[data-filter="history"]');
        if (historyBtn) historyBtn.classList.add('calls-active');
        
        // Mostrar panel de historial
        this.renderHistoryView();
        this.loadCallHistory();
    },

    forceShowContactsView: function() {
        // ✅ FORZAR vista de contactos sin importar el estado actual
        this.currentView = 'contacts';
        
        // ✅ LIMPIAR filtro de historial si está activo
        document.querySelectorAll('.calls-filter-btn').forEach(btn => {
            btn.classList.remove('calls-active');
            if (btn.getAttribute('data-filter') === 'all') {
                btn.classList.add('calls-active');
            }
        });
        
        // ✅ ESTABLECER filtro activo
        this.filterActive = 'all';
        
        // ✅ ASEGURAR que el panel de contactos esté visible
        const emptyState = document.getElementById('calls-empty-state');
        const contactsContainer = document.getElementById('calls-contacts-container');
        
        if (emptyState) emptyState.style.display = 'none';
        if (contactsContainer) contactsContainer.style.display = 'block';
    },

    /**
     * Mostrar vista de contactos
     */
    showContactsView: function() {
        this.currentView = 'contacts';
        this.showCurrentSelection();
    },

    /**
     * Cargar historial de llamadas
     */
    loadCallHistory: function(page = 1, filters = {}) {
        if (this.historyData.loading) return;
        
        this.historyData.loading = true;
        const self = this;
        
        const params = new URLSearchParams({
            page: page,
            limit: 20,
            status: filters.status || 'all',
            date_range: filters.dateRange || 'all',
            search: filters.search || ''
        });
        
        fetch(`${this.config.apiEndpoints.getCallHistory}?${params}`, {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            self.historyData.loading = false;
            
            if (data.success) {
                if (page === 1) {
                    self.historyData.calls = data.data.calls;
                } else {
                    self.historyData.calls = [...self.historyData.calls, ...data.data.calls];
                }
                
                self.historyData.currentPage = data.data.pagination.current_page;
                self.historyData.totalPages = data.data.pagination.total_pages;
                
                self.renderHistoryList();
            } else {
                self.showNotification(data.message || 'Error al cargar historial', 'error');
            }
        })
        .catch(err => {
            self.historyData.loading = false;
            console.error('Error cargando historial:', err);
            // Mostrar modal de error de conexión para errores de historial
            if (typeof showConnectionErrorModal === 'function') {
                showConnectionErrorModal();
            } else {
                self.showNotification('Error de conexión', 'error');
            }
        });
    },

    /** Renderizar vista de historial*/
    renderHistoryView: function() {
        const emptyState = document.getElementById('calls-empty-state');
        const contactsContainer = document.getElementById('calls-contacts-container');
        
        if (emptyState) emptyState.style.display = 'none';
        if (contactsContainer) {
            contactsContainer.style.display = 'block';
            contactsContainer.innerHTML = `
                <div class="calls-history-header">
                    <h4>Historial de Llamadas</h4>
                    <div class="calls-history-filters">
                        <select id="calls-history-status-filter" class="calls-history-filter-select">
                            <option value="all">Todos los estados</option>
                            <option value="completed">Completadas</option>
                            <option value="missed">No contestadas</option>
                            <option value="rejected">Rechazadas</option>
                            <option value="failed">Fallidas</option>
                        </select>
                        <select id="calls-history-date-filter" class="calls-history-filter-select">
                            <option value="all">Todas las fechas</option>
                            <option value="today">Hoy</option>
                            <option value="yesterday">Ayer</option>
                            <option value="week">Última semana</option>
                            <option value="month">Último mes</option>
                        </select>
                    </div>
                </div>
                <div class="calls-history-search">
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280;"></i>
                        <input type="text" id="calls-history-search" class="calls-history-search-input" 
                            placeholder="Buscar en historial...">
                    </div>
                </div>
                <div class="calls-history-list" id="calls-history-list">
                    <!-- Se cargarán las llamadas aquí -->
                </div>
            `;
            
            // Configurar event listeners para filtros
            this.setupHistoryFilters();
        }
    },

    /** Configurar filtros de historial*/
    setupHistoryFilters: function() {
        const self = this;
        
        // Filtro por estado
        const statusFilter = document.getElementById('calls-history-status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                self.loadCallHistory(1, {
                    status: this.value,
                    dateRange: document.getElementById('calls-history-date-filter').value,
                    search: document.getElementById('calls-history-search').value
                });
            });
        }
        
        // Filtro por fecha
        const dateFilter = document.getElementById('calls-history-date-filter');
        if (dateFilter) {
            dateFilter.addEventListener('change', function() {
                self.loadCallHistory(1, {
                    status: document.getElementById('calls-history-status-filter').value,
                    dateRange: this.value,
                    search: document.getElementById('calls-history-search').value
                });
            });
        }
        
        // Búsqueda
        const searchInput = document.getElementById('calls-history-search');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    self.loadCallHistory(1, {
                        status: document.getElementById('calls-history-status-filter').value,
                        dateRange: document.getElementById('calls-history-date-filter').value,
                        search: this.value
                    });
                }, 500);
            });
        }
    },

    /**
     * Renderizar lista de historial
     */
    renderHistoryList: function() {
        const container = document.getElementById('calls-history-list');
        if (!container) return;
        
        if (this.historyData.calls.length === 0) {
            container.innerHTML = `
                <div class="calls-history-empty">
                    <i class="fas fa-phone-slash" style="font-size: 48px; color: #9ca3af; margin-bottom: 16px;"></i>
                    <h5>No hay llamadas</h5>
                    <p>No se encontraron llamadas con los filtros seleccionados</p>
                </div>
            `;
            return;
        }
        
        const callsHtml = this.historyData.calls.map(call => this.createHistoryCallCard(call)).join('');
        
        container.innerHTML = callsHtml;
        
        // Agregar botón "Cargar más" si hay más páginas
        if (this.historyData.currentPage < this.historyData.totalPages) {
            container.innerHTML += `
                <div class="calls-history-load-more">
                    <button class="calls-load-more-btn" onclick="CallsBubbleSystem.loadMoreHistory()">
                        <i class="fas fa-chevron-down"></i>
                        Cargar más llamadas
                    </button>
                </div>
            `;
        }
    },

    /**
     * Crear tarjeta de historial de llamada
     */
    createHistoryCallCard: function(call) {
        const directionIcon = call.call_type === 'outgoing' ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
        const directionColor = call.call_type === 'outgoing' ? '#3b82f6' : '#10b981';
        const directionText = call.call_type === 'outgoing' ? 'Saliente' : 'Entrante';
        
        return `
            <div class="calls-history-card">
                <div class="calls-history-card-header">
                    <div class="calls-history-avatar-wrapper">
                        <img src="${call.other_user_image}" alt="${call.other_user_name}" class="calls-history-avatar">
                        <div class="calls-history-direction-badge" style="background: ${directionColor};">
                            <i class="${directionIcon}"></i>
                        </div>
                    </div>
                    <div class="calls-history-info">
                        <h6>${call.other_user_name}</h6>
                        <div class="calls-history-meta">
                            <span class="calls-history-date">${call.date_formatted} • ${call.time_formatted}</span>
                            <span class="calls-history-type">${directionText}</span>
                        </div>
                        <div class="calls-history-status">
                            <i class="${call.status_icon}" style="color: ${call.status_color};"></i>
                            <span style="color: ${call.status_color};">${call.status_text}</span>
                            ${call.duration_text ? `<span class="calls-history-duration">• ${call.duration_text}</span>` : ''}
                        </div>
                    </div>
                </div>
                <div class="calls-history-actions">
                    <button class="calls-history-action-btn" onclick="CallsBubbleSystem.callFromHistory(${call.other_user_id || 'null'}, '${call.other_user_name}', '${call.other_user_image}')">
                        <i class="fas fa-phone"></i>
                    </button>
                </div>
            </div>
        `;
    },

    /**
     * Cargar más llamadas
     */
    loadMoreHistory: function() {
        const filters = {
            status: document.getElementById('calls-history-status-filter')?.value || 'all',
            dateRange: document.getElementById('calls-history-date-filter')?.value || 'all',
            search: document.getElementById('calls-history-search')?.value || ''
        };
        
        this.loadCallHistory(this.historyData.currentPage + 1, filters);
    },

    /**
     * Iniciar llamada desde historial
     */
    callFromHistory: function(userId, userName, userImage) {
        if (userId) {
            this.showContactsView();
            this.initiateCall(userId, userName, userImage);
        }
    },


    showGlobalSearchResults: function(matches, query) {
        const emptyState = document.getElementById('calls-empty-state');
        const contactsContainer = document.getElementById('calls-contacts-container');
        const contactsTitle = document.getElementById('calls-contacts-title');
        const contactsCount = document.getElementById('calls-contacts-count');
        const contactsList = document.getElementById('calls-contacts-list');
        
        if (emptyState) emptyState.style.display = 'none';
        if (contactsContainer) contactsContainer.style.display = 'block';
        if (contactsTitle) contactsTitle.textContent = `Resultados para: "${query}"`;
        if (contactsCount) contactsCount.textContent = `${matches.length} ${matches.length === 1 ? 'resultado' : 'resultados'}`;
        
        if (contactsList) {
            if (matches.length === 0) {
                contactsList.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                        <h5>No se encontraron resultados</h5>
                        <p>Intenta con otros términos de búsqueda</p>
                    </div>
                `;
            } else {
                contactsList.innerHTML = matches.map(match => this.createGlobalSearchCard(match)).join('');
            }
        }
        
        // Aplicar filtro activo a los resultados
        setTimeout(() => this.applyFilter(this.filterActive), 100);
    },

    createGlobalSearchCard: function(match) {
        const {user, clinicName, type} = match;
        
        // Determinar estado visual basado en conexión y disponibilidad
        let statusClass, statusText;
        
        if (!user.is_online) {
            // Usuario desconectado
            statusClass = 'offline';
            statusText = '<?php echo app_lang("offline") ?: "Desconectado"; ?>';
        } else if (user.available) {
            // Usuario en línea y disponible
            statusClass = 'available';
            statusText = '<?php echo app_lang("available") ?: "Disponible"; ?>';
        } else {
            // Usuario en línea pero ocupado
            statusClass = 'busy';
            if (user.status === 'busy') {
                statusText = '<?php echo app_lang("busy") ?: "Ocupado"; ?>';
            } else if (user.status === 'do_not_disturb') {
                statusText = '<?php echo app_lang("do_not_disturb") ?: "No molestar"; ?>';
            } else {
                statusText = '<?php echo app_lang("in_consultation") ?: "En consulta"; ?>';
            }
        }
    
        const typeIcon = type === 'admin' ? 'fas fa-user-tie' : 'fas fa-hospital';
        const typeColor = type === 'admin' ? '#f59e0b' : (type === 'allied' ? '#10b981' : '#3b82f6');
    
        return `
            <div class="calls-contact-card-mini" data-user-id="${user.id}" data-user-status="${user.status}">
                <div class="calls-contact-avatar-wrapper">
                    <img src="${user.image}" alt="${user.name}" class="calls-contact-avatar-mini">
                    <span class="calls-contact-dot ${statusClass}"></span>
                </div>
                <div class="calls-contact-info-mini">
                    <h6>${user.name}</h6>
                    <small>
                        <i class="${typeIcon}" style="color:${typeColor}; margin-right:4px;"></i>
                        ${clinicName}
                    </small>
                    <div><small>${statusText}</small></div>
                </div>
                <button class="calls-contact-btn-big ${statusClass}" 
                        onclick="CallsBubbleSystem.initiateCall(${user.id}, '${user.name}', '${user.image}')"
                        ${!user.available ? 'disabled' : ''}>
                    <i class="fas fa-${user.available ? 'phone' : 'clock'}"></i>
                </button>
            </div>
        `;
    },
    // NUEVA función para mostrar la selección actual cuando no hay búsqueda
    showCurrentSelection: function() {
        if (this.selected.type && this.selected.index !== null) {
            this.selectClinic(this.selected.type, this.selected.index);
        } else {
            this.showEmptyState();
        }
    },

    filterLocalContacts: function (query) {
        // Buscador local por clínica: reutiliza el global
        this.filterUsers(query);
    },

    showOutgoingCallModal: function (name, image) {
        const m = document.getElementById('calls-outgoing-modal');
        if (m) m.style.display = 'block';
        const n = document.getElementById('calls-calling-user-name');
        const img = document.getElementById('calls-calling-user-image');
        if (n)  n.textContent = name || this.config.texts.calling || 'Llamando...';
        if (img && image) img.src = image;
    },

    setupAudioUnlock: function () {
        if (this.__audioUnlockInstalled) return;
        this.__audioUnlockInstalled = true;

        const self = this;
        const unlock = () => {
            ['ringTone','dialTone'].forEach(k => {
                const a = self[k];
                if (!a) return;
                try {
                    // SOLUCIÓN PARA SAFARI: Verificar si es Safari y usar volumen 0
                    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
                    const originalVolume = a.volume;
                    
                    // En Safari, usar volumen 0 completamente para evitar sonido audible
                    a.volume = isSafari ? 0 : 0.0001;
                    a.muted = true; // AGREGAR muted para mayor seguridad
                    
                    const playPromise = a.play();
                    if (playPromise && playPromise.catch) {
                        playPromise.catch(() => {
                            // Silenciar cualquier error de reproducción
                        });
                    }
                    
                    // Pausar más rápido en Safari
                    const pauseDelay = isSafari ? 50 : 120;
                    setTimeout(() => { 
                        try {
                            a.pause(); 
                            a.currentTime = 0; 
                            a.volume = originalVolume;
                            a.muted = false; // Restaurar muted
                        } catch(e) {}
                    }, pauseDelay);
                    
                } catch(e) {
                    // Error silencioso
                }
            });
            
            self.__audioUnlocked = true;
            self.hideEnableSoundBanner();
            
            // Remover event listeners
            document.removeEventListener('click', unlock);
            document.removeEventListener('touchstart', unlock);
            document.removeEventListener('keydown', unlock);
        };

        // Mostrar banner solo si realmente hay archivos de audio
        if (this.ringTone || this.dialTone) {
            this.showEnableSoundBanner();
        }
        
        document.addEventListener('click', unlock);
        document.addEventListener('touchstart', unlock);
        document.addEventListener('keydown', unlock);
    },

    // TAMBIÉN agregar una versión mejorada de showEnableSoundBanner
    showEnableSoundBanner: function () {
        if (document.getElementById('enable-sound-banner')) return;
        
        // Detectar Safari para mostrar mensaje más específico
        const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        const bannerText = isSafari ? 
            '🔔 Activar sonido para llamadas (Safari)' : 
            '🔔 Activa el sonido de llamadas';
        
        const el = document.createElement('div');
        el.id = 'enable-sound-banner';
        el.style.cssText = 'position:fixed;bottom:18px;left:18px;z-index:9998;background:#111;color:#fff;padding:10px 14px;border-radius:8px;display:flex;gap:10px;align-items:center;box-shadow:0 6px 16px rgba(0,0,0,.25);max-width:300px';
        el.innerHTML = `<span>${bannerText}</span><button id="enable-sound-btn" style="background:#10b981;color:#fff;border:0;padding:6px 10px;border-radius:6px;cursor:pointer">Activar</button>`;
        
        document.body.appendChild(el);
        
        document.getElementById('enable-sound-btn').addEventListener('click', () => {
            this.__audioUnlocked = true;
            this.hideEnableSoundBanner();
        });
    },

    hideEnableSoundBanner: function () {
        const el = document.getElementById('enable-sound-banner');
        if (el) el.remove();
    },

    endCall: function () {
        console.log('📞 END CALL: Terminando llamada - currentCallId:', this.currentCallId, 'incomingCallId:', this.incomingCallId);
        this._vseeNavigated = false; // Resetear flag
        // ✅ DETENER TODOS LOS SONIDOS
        this.stopAllTones();
        
        // ✅ CERRAR MODALES
        const outgoingModal = document.getElementById('calls-outgoing-modal');
        const incomingModal = document.getElementById('calls-incoming-modal');
        if (outgoingModal) outgoingModal.style.display = 'none';
        if (incomingModal) incomingModal.style.display = 'none';
        
        // ✅ LIMPIAR IDs DE LLAMADA
        this.currentCallId = null;
        this.incomingCallId = null;
        
        // ✅ DETENER TIMER DE VERIFICACIÓN DE ESTADO
        if (this.__statusTimer) {
            clearInterval(this.__statusTimer);
            this.__statusTimer = null;
        }
        
        // ✅ DETENER TIMEOUT DE SEGURIDAD
        if (this.__callTimeout) {
            clearTimeout(this.__callTimeout);
            this.__callTimeout = null;
        }
        
        // ✅ NOTIFICAR A TAURI QUE TERMINÓ LA VIDELLAMADA (restaurar ventana)
        this.notifyDesktopCallEnded();
        
        // ✅ SI ESTAMOS EN TAURI Y LA PÁGINA ESTÁ EN BLANCO, REDIRIGIR A LA PÁGINA PRINCIPAL
        if (this.detectTauriEnvironment() && this.isPageBlank()) {
            console.log('🖥️ Detectado: Página en blanco en Tauri, redirigiendo a página principal');
            this.redirectToMainPage();
        }
    },
    
    // Configurar manejo de sesión
    setupSessionHandling: function() {
        const self = this;
        
        // Escuchar eventos de sesión activa
        window.addEventListener('sessionActive', function(event) {
            console.log('🔄 Sesión renovada - actualizando tokens CSRF');
            if (event.detail && event.detail.csrf_hash) {
                self.config.csrf.hash = event.detail.csrf_hash;
            }
        });
        
        // Verificar sesión antes de hacer llamadas
        this.originalInitiateCall = this.initiateCall;
        this.initiateCall = function(userId, userName, userImage) {
            self.checkSessionBeforeCall(() => {
                self.originalInitiateCall(userId, userName, userImage);
            });
        };
        
        // Verificar sesión antes de aceptar llamadas
        this.originalAcceptCall = this.acceptCall;
        this.acceptCall = function() {
            self.checkSessionBeforeCall(() => {
                self.originalAcceptCall();
            });
        };
    },
    
    // Verificar sesión antes de realizar acciones críticas
    checkSessionBeforeCall: function(callback) {
        if (window.sessionHeartbeat && window.sessionHeartbeat.isSessionActive()) {
            callback();
            return;
        }
        
        // Forzar verificación de sesión
        if (window.sessionHeartbeat) {
            window.sessionHeartbeat.forceHeartbeat();
        }
        
        // Verificar sesión con el servidor
        fetch('/heartbeat/check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                callback();
            } else {
                this.showNotification('Sesión expirada. Recargando página...', 'error');
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error verificando sesión:', error);
            this.showNotification('Error de conexión. Verificando...', 'warning');
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        });
    },

    acceptCall: function() {
        console.log("🟢 acceptCall INICIO - incomingCallId:", this.incomingCallId);
        
        if (!this.incomingCallId) {
            console.log("❌ acceptCall: No hay incomingCallId!");
            return;
        }

        // ⭐ PRESERVAR EL CALL ID AL INICIO
        const preservedCallId = this.incomingCallId;
        const preservedUserName = document.getElementById('calls-incoming-user-name')?.textContent || 'usuario';
        const preservedUserImage = (document.getElementById('calls-incoming-user-image') || {}).src || '';
        
        console.log("💾 Call ID preservado:", preservedCallId);

        // 🔍 DETECTAR SI ESTAMOS EN TAURI
        const isRunningInTauri = this.detectTauriEnvironment();
        console.log("🖥️ Ejecutándose en Tauri:", isRunningInTauri);

        // Si estamos en Tauri, manejar de manera diferente
        if (isRunningInTauri) {
            this.acceptCallInTauri(preservedCallId, preservedUserName, preservedUserImage);
            return;
        }

        // Variable para controlar si ya se manejó el bloqueo de popup
        let popupBlockHandled = false;
        const self = this; // ⭐ PRESERVAR REFERENCIA

        try {
            //this.callWindow = window.open('about:blank', 'VseeCall', 'popup=yes,width=980,height=720,noopener=0');
            if (this.isSafari()) {
                this.callWindow = window.open('about:blank', 'VseeCall', 'popup=yes,width=980,height=720,noopener=0');
            } else {
                this.callWindow = window.open('about:blank', 'VseeCall', 'popup=yes,width=980,height=720,noopener=0');
            }

            // VERIFICACIÓN INMEDIATA: Si el popup está bloqueado
            if (!this.callWindow) {
                console.log('🚫 Popup bloqueado inmediatamente');
                popupBlockHandled = true;
                this.stopAllTones();
                
                // ⭐ USAR DATOS PRESERVADOS
                setTimeout(() => {
                    self.showPopupBlockedHangupModalWithId(preservedCallId, {
                        name: preservedUserName,
                        image: preservedUserImage
                    });
                }, 100);
                return;
            }

            // VERIFICACIÓN DIFERIDA: Si se abrió pero no es accesible
            setTimeout(() => {
                if (popupBlockHandled) return;
                
                try {
                    // Verificaciones múltiples más estrictas
                    if (!self.callWindow || self.callWindow.closed) {
                        console.log('🚫 Popup cerrado después de abrir');
                        popupBlockHandled = true;
                        self.stopAllTones();
                        // ⭐ USAR DATOS PRESERVADOS
                        self.showPopupBlockedHangupModalWithId(preservedCallId, {
                            name: preservedUserName,
                            image: preservedUserImage
                        });
                        return;
                    }
                    
                    // Verificación adicional: intentar enfocar y verificar dimensiones
                    try {
                        self.callWindow.focus();
                        
                        const width = self.callWindow.outerWidth || self.callWindow.innerWidth;
                        const height = self.callWindow.outerHeight || self.callWindow.innerHeight;
                        
                        if (width < 100 || height < 100) {
                            console.log('🚫 Popup tiene dimensiones inválidas:', width, 'x', height);
                            popupBlockHandled = true;
                            self.stopAllTones();
                            // ⭐ USAR DATOS PRESERVADOS
                            self.showPopupBlockedHangupModalWithId(preservedCallId, {
                                name: preservedUserName,
                                image: preservedUserImage
                            });
                            return;
                        }
                    } catch (focusError) {
                        console.log('🚫 Error enfocando popup:', focusError);
                        popupBlockHandled = true;
                        self.stopAllTones();
                        // ⭐ USAR DATOS PRESERVADOS
                        self.showPopupBlockedHangupModalWithId(preservedCallId, {
                            name: preservedUserName,
                            image: preservedUserImage
                        });
                        return;
                    }
                    
                    // Test final: verificar si podemos escribir en el popup
                    self.callWindow.document.title = 'Test';
                    console.log('✅ Popup verificado exitosamente');
                    
                } catch (e) {
                    console.log('🚫 Error accediendo al popup');
                    if (!popupBlockHandled) {
                        popupBlockHandled = true;
                        self.stopAllTones();
                        // ⭐ USAR DATOS PRESERVADOS
                        self.showPopupBlockedHangupModalWithId(preservedCallId, {
                            name: preservedUserName,
                            image: preservedUserImage
                        });
                    }
                    return;
                }
            }, 100);

        } catch (error) {
            console.log('🚫 Error creando popup');
            if (!popupBlockHandled) {
                popupBlockHandled = true;
                this.stopAllTones();
                
                // ⭐ USAR DATOS PRESERVADOS
                setTimeout(() => {
                    self.showPopupBlockedHangupModalWithId(preservedCallId, {
                        name: preservedUserName,
                        image: preservedUserImage
                    });
                }, 50);
            }
            return;
        }

        // Si llegamos aquí, el popup está OK inicialmente - continuar con flujo normal
        this.createPopupChecklist(this.callWindow, {
            title: 'Comprobando servicios…',
            steps: ['Micrófono disponible','Cámara disponible','Conexión estable','Permisos confirmados','Todo listo para la llamada']
        }, preservedCallId);  // ⭐ PASAR EL CALL ID PRESERVADO

        // Silenciar tonos y mostrar estado
        this.stopAllTones();
        this.showNotification('Llamada aceptada - Preparando videollamada...', 'success');

        // Aceptar en backend - ⭐ USAR CALL ID PRESERVADO
        const fd = new FormData();
        fd.append('call_id', preservedCallId);
        fd.append('action', 'accept');
        fd.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

        fetch(this.config.apiEndpoints.answerCall, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            const url = data && data.receiver_vsee_url;
            if (data && data.success && url) {
                // Tras el checklist, navegar a VSee
                this.runChecklistAndNavigate(this.callWindow, url, { openInNewTab: true });
                this.endCall();
            } else {
                if (this.callWindow && !this.callWindow.closed) this.callWindow.close();
                this.callWindow = null;
                this.showNotification((data && data.message) || 'No se pudo aceptar la llamada', 'error');
                this.endCall();
            }
        })
        .catch(() => {
            if (this.callWindow && !this.callWindow.closed) this.callWindow.close();
            this.callWindow = null;
            
            // Mostrar modal de error de conexión en lugar de solo notificación
            if (typeof showConnectionErrorModal === 'function') {
                showConnectionErrorModal();
            } else {
                this.showNotification('Error de conexión', 'error');
            }
            this.endCall();
        });
    },

    showPopupBlockedHangupModalWithId: function(callId, opts = {}) {
        const modal = document.getElementById('popup-blocked-hangup-modal');
        if (!modal) {
            console.log("Modal no encontrado");
            return;
        }

        console.log("Entrando al modal bloqueado con call ID:", callId);

        // Opcionales: nombre/imagen del otro usuario
        const avatar = document.getElementById('popup-blocked-avatar');
        if (avatar && opts.image) avatar.src = opts.image;
        const subtitle = document.getElementById('popup-blocked-subtitle');
        if (subtitle && opts.name) {
            subtitle.textContent = `No se pudo abrir la videollamada con ${opts.name}. ` +
                                `Cuelga para que no te espere y vuelve a intentar.`;
        }

        modal.style.display = 'block';

        const self = this;
        
        modal.onclick = function(event) {
            if (event.target.id === 'popup-blocked-hangup-btn' || 
                event.target.closest('#popup-blocked-hangup-btn') ||
                (event.target.classList.contains('calls-btn-end'))) {
                
                console.log("Botón presionado!");
                console.log("Call ID recibido:", callId);
                
                event.preventDefault();
                event.stopPropagation();
                
                if (callId) {
                    console.log("Enviando request de rechazo con call ID:", callId);
                    
                    const fd = new FormData();
                    fd.append('call_id', callId);
                    fd.append('reason', 'popup_blocked');
                    fd.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

                    fetch(self.config.apiEndpoints.rejectCallPopupBlocked, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    })
                    .then(r => r.json())
                    .then(data => {
                        console.log("Respuesta del servidor:", data);
                        if (data && data.success) {
                            self.showNotification('Llamada rechazada por popup bloqueado', 'info');
                        } else {
                            self.showNotification((data && data.message) || 'No se pudo rechazar', 'error');
                        }
                    })
                    .catch(error => {
                        console.error("Error en request:", error);
                        self.showNotification('Error de conexión al rechazar', 'error');
                    })
                    .finally(() => {
                        self.closePopupBlockedHangupModal();
                        self.endCall();
                    });
                    
                } else {
                    console.log("No hay call ID disponible");
                    self.showNotification('No hay llamada activa para rechazar', 'warning');
                    self.closePopupBlockedHangupModal();
                    self.endCall();
                }
            }
        };

        // Auto-cancelar si el usuario no hace nada
        this.startPopupBlockedAutoCancel(20, callId);
    },

    autoCancelCallDueToBlocking: function() {
        console.log('🔄 Iniciando cancelación automática de llamada...');
        
        if (!this.incomingCallId) {
            console.log('❌ No hay ID de llamada entrante para cancelar');
            return;
        }

        // Enviar rechazo automático al backend
        const fd = new FormData();
        fd.append('call_id', this.incomingCallId);
        fd.append('action', 'reject');
        fd.append('auto_reject_reason', 'popup_blocked'); // Nuevo campo para identificar el motivo
        fd.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

        fetch(this.config.apiEndpoints.rejectCallPopupBlocked, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Llamada cancelada automáticamente en el backend');
                this.showNotification('Llamada cancelada: ventanas emergentes bloqueadas', 'warning');
            } else {
                console.log('❌ Error cancelando llamada automáticamente:', data.message);
                this.showNotification('Error cancelando llamada automáticamente', 'error');
            }
        })
        .catch(error => {
            console.error('❌ Error de red cancelando llamada:', error);
            this.showNotification('Error de conexión cancelando llamada', 'error');
        })
        .finally(() => {
            // Limpiar estado local siempre
            this.endCall();
        });
    },

    showPopupBlockedNotice: function() {
        console.log('📱 Mostrando modal de popup bloqueado...');
        
        const modal = document.getElementById('popup-permission-modal');
        if (modal) {
            // Personalizar mensaje para llamadas entrantes
            const title = modal.querySelector('.popup-permission-title');
            const subtitle = modal.querySelector('.popup-permission-subtitle');
            
            if (title) {
                title.textContent = 'Llamada Cancelada: Ventanas Bloqueadas';
            }
            
            if (subtitle) {
                subtitle.textContent = 'La llamada se canceló automáticamente porque las ventanas emergentes están bloqueadas';
            }
            
            // Mostrar modal
            modal.style.display = 'flex';
            
            // Enfocar en el botón de prueba
            setTimeout(() => {
                const testBtn = modal.querySelector('.popup-test-btn');
                if (testBtn) {
                    testBtn.textContent = 'Configurar y Reintentar';
                    testBtn.focus();
                }
            }, 300);
        } else {
            // Fallback si no existe el modal
            alert('La llamada se canceló porque las ventanas emergentes están bloqueadas.\n\nPor favor, habilita las ventanas emergentes en tu navegador y vuelve a intentar.');
        }
    },

    rejectCall: function() {
        if (!this.incomingCallId) return;

        const fd = new FormData();
        fd.append('call_id', this.incomingCallId);
        fd.append('action', 'reject');
        fd.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

        fetch(this.config.apiEndpoints.answerCall, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        })
        .then(() => {
            this.endCall();
            this.showNotification('Llamada rechazada', 'info');
        })
        .catch(() => {
            this.endCall();
            this.showNotification('Error al rechazar la llamada', 'error');
        });
    },

    createPopupLoader(win, text = 'Conectando videollamada…') {
        if (!win) return;
        const html = `<!doctype html>
        <html lang="es"><head><meta charset="utf-8">
        <title>${text}</title>
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <style>
            html,body{height:100%;margin:0;background:#0f172a;color:#fff;font-family:system-ui,Segoe UI,Roboto,Arial}
            .wrap{min-height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:14px}
            .spinner{width:48px;height:48px;border:4px solid rgba(255,255,255,.2);border-top-color:#fff;border-radius:50%;
                    animation:spin 1s linear infinite}
            @keyframes spin{to{transform:rotate(360deg)}}
            .hint{opacity:.7;font-size:14px}
        </style></head>
        <body>
            <div class="wrap">
            <div class="spinner"></div>
            <div>${text}</div>
            <div class="hint">Puedes volver a esta pestaña sin cerrar esta ventana.</div>
            </div>
        </body></html>`;
        try {
            win.document.open();
            win.document.write(html);
            win.document.close();
        } catch (e) {/* ignorar */}
    },

    createPopupChecklist: function (win, opts, preservedCallId = null) {
        console.log("Call ID Create Popup Check List " + preservedCallId);
        if (!win) return;
        opts = opts || {};
        var title = opts.title || 'Comprobando servicios…';
        var steps = Array.isArray(opts.steps) ? opts.steps : [];

        var stepsHtml = steps.map(function (t, i) {
            return '<li class="step" data-i="' + i + '">'
                + '<div class="icon">✓</div><div class="txt">' + t + '</div>'
                + '</li>';
        }).join('');

        var html =
        '<!doctype html>'
        + '<html lang="es"><head><meta charset="utf-8">'
        + '<meta name="viewport" content="width=device-width,initial-scale=1">'
        + '<title>' + title + '</title>'
        + '<style>'
        + ':root{--bg:#0f172a;--fg:#e5e7eb;--muted:#94a3b8;--accent:#22c55e}'
        + 'html,body{height:100%;margin:0;background:var(--bg);color:var(--fg);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial}'
        + '.wrap{min-height:100%;display:flex;align-items:center;justify-content:center}'
        + '.card{width:min(520px,92vw);background:#111827;border:1px solid #1f2937;border-radius:16px;padding:22px 20px;box-shadow:0 10px 30px rgba(0,0,0,.3)}'
        + 'h1{font-size:18px;margin:0 0 6px 0}'
        + 'p{margin:0 0 16px 0;color:var(--muted);font-size:14px}'
        + 'ul{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px}'
        + 'li{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;background:#0b1220;border:1px solid #111827}'
        + '.icon{width:22px;height:22px;border-radius:50%;border:2px solid #334155;display:grid;place-items:center;font-size:14px;color:#334155;transition:.25s}'
        + '.txt{flex:1}'
        + '.ok .icon{border-color:var(--accent);color:#0b1220;background:var(--accent)}'
        + '.ok{border-color:#134e4a;background:#052e2b}'
        + '.spinner{width:46px;height:46px;border:4px solid rgba(255,255,255,.15);border-top-color:#fff;border-radius:50%;margin:16px auto 0;animation:spin 1s linear infinite}'
        + '.hint{margin-top:12px;text-align:center;color:#9ca3af;font-size:12px}'
        + '@keyframes spin{to{transform:rotate(360deg)}}'
        + '</style>'
        + '</head><body>'
        + '<div class="wrap"><div class="card">'
        + '<h1>' + title + '</h1>'
        + '<p>Estamos verificando que todo esté listo. Esto tomará unos segundos…</p>'
        + '<ul id="steps">' + stepsHtml + '</ul>'
        + '<div class="spinner" id="spinner"></div>'
        + '<div class="hint">No cierres esta ventana.</div>'
        + '</div></div>'
        + '<script>'
        + 'window.__preservedCallId=' + JSON.stringify(preservedCallId) + ';'  // ⭐ PRESERVAR CALL ID
        + 'window.__proceed=function(){'
        + '  var target=window.__targetURL||"";'
        + '  var openNew=!!window.__openNew;'
        + '  if(!target){try{window.close()}catch(e){} return;}'
        + '  try{'
        + '    if(openNew){'
        + '      var vseeWindow = null;'
        + '      try{'
        + '        if(window.opener){'
        + '          vseeWindow = window.opener.open(target,"_blank");'
        + '        }'
        + '      }catch(e){'
        + '        console.log("Error abriendo VSee:", e);'
        + '      }'
        + '      if(!vseeWindow){'
        + '        console.log("VSee popup bloqueado");'
        + '        if(window.opener && window.opener.CallsBubbleSystem){'
        + '          try{'
        + '            var name = "usuario";'
        + '            var image = "";'
        + '            try{'
        + '              var nameEl = window.opener.document.getElementById("calls-incoming-user-name");'
        + '              var imgEl = window.opener.document.getElementById("calls-incoming-user-image");'
        + '              if(nameEl) name = nameEl.textContent || "usuario";'
        + '              if(imgEl) image = imgEl.src || "";'
        + '            }catch(e){}'
        + '            if(window.__preservedCallId){'  // ⭐ USAR FUNCIÓN CON ID
        + '              window.opener.CallsBubbleSystem.showPopupBlockedHangupModalWithId(window.__preservedCallId, {'
        + '                name: name,'
        + '                image: image'
        + '              });'
        + '            }else{'  // ⭐ FALLBACK
        + '              window.opener.CallsBubbleSystem.showPopupBlockedHangupModal({'
        + '                name: name,'
        + '                image: image'
        + '              });'
        + '            }'
        + '          }catch(e){'
        + '            console.log("Error mostrando modal:", e);'
        + '          }'
        + '        }'
        + '        try{window.close()}catch(e){}'
        + '        return;'
        + '      }'
        + '      try{window.close()}catch(e){}'
        + '    }else{'
        + '      try{location.replace(target)}catch(e){location.href=target}'
        + '    }'
        + '  }catch(e){'
        + '    console.log("Error general:", e);'
        + '    location.href=target;'
        + '  }'
        + '};'
        + 'function start(){'
        + '  var items=[].slice.call(document.querySelectorAll(".step"));'
        + '  var i=0;'
        + '  function tick(){'
        + '    if(i<items.length){items[i].classList.add("ok");i++;setTimeout(tick,450)}'
        + '    else{setTimeout(function(){window.__proceed&&window.__proceed()},300)}'
        + '  }'
        + '  setTimeout(tick,350);'
        + '}'
        + 'if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",start)}else{start()}'
        + '</scr' + 'ipt>'
        + '</body></html>';

        try { win.document.open(); win.document.write(html); win.document.close(); } catch(e) {}
    },

    createCallerReactivePopup: function (win, { title='Llamando…', subtitle='Esperando a que la otra persona conteste' } = {}) {
        if (!win) return;
        const html = `<!doctype html>
        <html lang="es"><head><meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>${title}</title>
        <style>
            :root{--bg:#0f172a;--fg:#e5e7eb;--muted:#94a3b8;--ok:#22c55e;--err:#ef4444;--warn:#f59e0b}
            html,body{height:100%;margin:0;background:var(--bg);color:var(--fg);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial}
            .wrap{min-height:100%;display:flex;align-items:center;justify-content:center;position:relative}
            .card{width:min(420px,92vw);background:#111827;border:1px solid #1f2937;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.4);position:relative;overflow:hidden}
            h1{font-size:20px;margin:0 0 8px 0;font-weight:600}
            p{margin:0;color:var(--muted);font-size:15px;line-height:1.4}
            .status-row{display:flex;align-items:center;gap:12px;margin:16px 0;padding:12px;background:#0b1220;border-radius:8px;border:1px solid #1f2937}
            .status-icon{width:32px;height:32px;border-radius:50%;display:grid;place-items:center;border:2px solid #334155;position:relative;transition:all 0.3s ease}
            .status-icon.wait{border-color:#64748b;animation:pulse 2s infinite}
            .status-icon.ok{background:var(--ok);border-color:var(--ok);color:#0b1220}
            .status-icon.err{background:var(--err);border-color:var(--err);color:white}
            .spinner{width:20px;height:20px;border:2px solid rgba(255,255,255,.2);border-top-color:#fff;border-radius:50%;animation:spin 1s linear infinite}
            .status-text{flex:1;font-weight:500}
            .actions{margin-top:20px;display:flex;gap:12px;justify-content:center}
            .btn{padding:12px 24px;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600;transition:all 0.2s ease;display:flex;align-items:center;gap:8px}
            .btn.danger{background:var(--err);color:white}
            .btn.danger:hover{background:#dc2626;transform:translateY(-1px)}
            .auto-close-info{text-align:center;margin-top:12px;font-size:12px;color:var(--muted);font-style:italic}
            .hide{display:none}
            @keyframes spin{to{transform:rotate(360deg)}}
            @keyframes pulse{0%,100%{opacity:1}50%{opacity:0.5}}
        </style></head><body>
        <div class="wrap">
            <div class="card">
                <h1 id="title">${title}</h1>
                <div class="status-row">
                    <div class="status-icon wait" id="statusIcon">
                        <div class="spinner" id="spinner"></div>
                        <span id="iconText" class="hide">✓</span>
                    </div>
                    <div class="status-text">
                        <p id="subtitle">${subtitle}</p>
                    </div>
                </div>
                <div class="actions" id="actions">
                    <button class="btn danger" onclick="cancelCall()">
                        <i class="fas fa-phone-slash"></i>
                        Cancelar llamada
                    </button>
                </div>
                <div class="auto-close-info hide" id="autoCloseInfo"></div>
            </div>
        </div>
        <script>
        (function(){
            let autoCloseTimer = null;
            
            function updateIcon(type) {
                const icon = document.getElementById('statusIcon');
                const spinner = document.getElementById('spinner');
                const iconText = document.getElementById('iconText');
                
                icon.className = 'status-icon ' + type;
                
                if (type === 'wait') {
                    spinner.classList.remove('hide');
                    iconText.classList.add('hide');
                } else {
                    spinner.classList.add('hide');
                    iconText.classList.remove('hide');
                    
                    if (type === 'ok') iconText.textContent = '✓';
                    else if (type === 'err') iconText.textContent = '✕';
                }
            }
            
            function startAutoClose(seconds) {
                if (autoCloseTimer) clearInterval(autoCloseTimer);
                
                const info = document.getElementById('autoCloseInfo');
                info.classList.remove('hide');
                
                let remaining = seconds;
                
                const updateCountdown = () => {
                    info.textContent = \`Cerrando automáticamente en \${remaining} segundos...\`;
                    remaining--;
                    
                    if (remaining < 0) {
                        clearInterval(autoCloseTimer);
                        try { window.close(); } catch(e) {}
                    }
                };
                
                updateCountdown();
                autoCloseTimer = setInterval(updateCountdown, 1000);
            }
            
            // Función para cancelar llamada desde el popup
            window.cancelCall = function() {
                try {
                    const callId = window.opener?.CallsBubbleSystem?.currentCallId;
                    // Notificar a la ventana padre que se cancela la llamada
                    if (window.opener && window.opener.hangupCallsBubbleCall) {
                        window.opener.hangupCallsBubbleCall();
                    }
                    // Cerrar esta ventana
                    window.close();
                } catch(e) {
                    console.error('Error cancelando llamada:', e);
                    window.close();
                }
            };
            
            // API pública
            window.setCallStatus = function(type, title, subtitle, options = {}) {
                if (title) document.getElementById('title').textContent = title;
                if (subtitle) document.getElementById('subtitle').textContent = subtitle;
                
                updateIcon(type);
                
                // Ocultar botón de cancelar si la llamada ya no está en espera
                const actions = document.getElementById('actions');
                if (type !== 'wait') {
                    actions.classList.add('hide');
                }
                
                if (options.autoClose) {
                    startAutoClose(options.autoClose);
                }
                
                if (title) document.title = title;
            };
            
            // Detectar si la ventana padre se cierra
            if (window.opener) {
                const checkParent = setInterval(() => {
                    if (!window.opener || window.opener.closed) {
                        clearInterval(checkParent);
                        window.setCallStatus('err', 'Conexión perdida', 'La ventana principal se cerró', {
                            autoClose: 3
                        });
                    }
                }, 1000);
            }
            
        })();
        </scr` + `ipt></body></html>`;
        
        try { 
            win.document.open(); 
            win.document.write(html); 
            win.document.close(); 
        } catch(e) {
            console.error('Error creando popup reactivo:', e);
        }
    },

    updateCallerPopup: function(type, {title, subtitle, autoClose, showActions} = {}) {
        const w = this.callWindow;
        if (!w || w.closed) return;
        
        try { 
            if (w.setCallStatus) {
                w.setCallStatus(type, title || '', subtitle || '', {
                    autoClose: autoClose || 0,
                    showActions: showActions || false
                });
            }
        } catch(e) {
            console.warn('Error actualizando popup:', e);
        }
    },

    runChecklistAndNavigate: function (win, url, opts) {
        if (!win) return;
        opts = opts || {};
        
        const self = this;
        const preservedCallId = this.incomingCallId;
        
        try {
            win.__targetURL = url;
            //win.__openNew = !!opts.openInNewTab;
            win.__openNew = this.isSafari() ? false : !!opts.openInNewTab;
            
            // AGREGAR: Función de verificación de popup para VSee
            win.__checkVseePopup = function() {
                if (win.__openNew && win.opener) {
                    try {
                        const vseeWindow = win.opener.open(win.__targetURL, "_blank");
                        if (!vseeWindow) {
                            // VSee popup bloqueado - notificar al sistema principal
                            win.opener.CallsBubbleSystem.showPopupBlockedHangupModal({
                                name: win.opener.document.getElementById('calls-incoming-user-name')?.textContent || 'usuario',
                                image: (win.opener.document.getElementById('calls-incoming-user-image') || {}).src || ''
                            });
                            return false;
                        }
                        return true;
                    } catch(e) {
                        // Error abriendo VSee - notificar al sistema principal
                        win.opener.CallsBubbleSystem.showPopupBlockedHangupModal({
                            name: win.opener.document.getElementById('calls-incoming-user-name')?.textContent || 'usuario',
                            image: (win.opener.document.getElementById('calls-incoming-user-image') || {}).src || ''
                        });
                        return false;
                    }
                }
                return true;
            };
            
        } catch(e) {}
        
        this.createPopupChecklist(win, {
            title: 'Servicios comprobados…',
            steps: [
                'Micrófono disponible',
                'Cámara disponible', 
                'Conexión estable',
                'Permisos confirmados',
                'Todo listo para la llamada'
            ]
       }, preservedCallId);
    },

    showPopupBlockedNotice: function () {
        const modal = document.getElementById('popup-permission-modal');
        if (modal) {
            modal.style.display = 'flex';
        }
    },

    showPopupBlockedHangupModal: function(opts = {}) {
        const modal = document.getElementById('popup-blocked-hangup-modal');
        if (!modal) return;

        console.log("Entrando al modal bloqueado");

        // ⭐ PRESERVAR EL CALL ID ANTES DE QUE SE PIERDA
        this._preservedCallId = this.incomingCallId || this.currentCallId;
        console.log("Call ID preservado:", this._preservedCallId);

        // Opcionales: nombre/imagen del otro usuario
        const avatar = document.getElementById('popup-blocked-avatar');
        if (avatar && opts.image) avatar.src = opts.image;
        const subtitle = document.getElementById('popup-blocked-subtitle');
        if (subtitle && opts.name) {
            subtitle.textContent = `No se pudo abrir la videollamada con ${opts.name}. ` +
                                `Cuelga para que no te espere y vuelve a intentar.`;
        }

        modal.style.display = 'block';

        const self = this;
        
        modal.onclick = function(event) {
            if (event.target.id === 'popup-blocked-hangup-btn' || 
                event.target.closest('#popup-blocked-hangup-btn') ||
                (event.target.classList.contains('calls-btn-end'))) {
                
                console.log("Botón presionado!");
                
                event.preventDefault();
                event.stopPropagation();
                
                // ⭐ USAR EL CALL ID PRESERVADO
                const callId = self._preservedCallId || self.incomingCallId || self.currentCallId;
                console.log("Call ID a usar:", callId);
                
                if (callId) {
                    console.log("Enviando request de rechazo");
                    
                    const fd = new FormData();
                    fd.append('call_id', callId);
                    fd.append('reason', 'popup_blocked');
                    fd.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

                    fetch(self.config.apiEndpoints.rejectCallPopupBlocked, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    })
                    .then(r => r.json())
                    .then(data => {
                        console.log("Respuesta del servidor:", data);
                        if (data && data.success) {
                            self.showNotification('Llamada rechazada por popup bloqueado', 'info');
                        } else {
                            self.showNotification((data && data.message) || 'No se pudo rechazar', 'error');
                        }
                    })
                    .catch(error => {
                        console.error("Error en request:", error);
                        self.showNotification('Error de conexión al rechazar', 'error');
                    })
                    .finally(() => {
                        // Limpiar call ID preservado
                        self._preservedCallId = null;
                        self.closePopupBlockedHangupModal();
                        self.endCall();
                    });
                    
                } else {
                    console.log("No hay call ID disponible");
                    self.showNotification('No hay llamada activa para rechazar', 'warning');
                    self.closePopupBlockedHangupModal();
                    self.endCall();
                }
            }
        };

        // Auto-cancelar si el usuario no hace nada
        this.startPopupBlockedAutoCancel(20);
    },

  // Y también agregar este método:
  closePopupBlockedHangupModal: function() {
      const modal = document.getElementById('popup-blocked-hangup-modal');
      if (modal) modal.style.display = 'none';
      this.stopPopupBlockedAutoCancel();
  },

  // Y estos métodos para el auto-cancel:
    startPopupBlockedAutoCancel: function(seconds, preservedCallId = null) {
        this.stopPopupBlockedAutoCancel();
        const label = document.getElementById('popup-blocked-countdown');
        if (!label) return;

        // Usar el call ID preservado o intentar obtener el actual
        const callIdToUse = preservedCallId || this.incomingCallId || this.currentCallId;
        console.log("Auto-cancel iniciado con call ID:", callIdToUse);

        const self = this;
        let left = seconds;
        label.style.display = 'block';
        label.textContent = `Colgando automáticamente en ${left} s...`;

        this.__popupBlockedTimer = setInterval(async () => {
            left--;
            if (left <= 0) {
                clearInterval(self.__popupBlockedTimer);
                self.__popupBlockedTimer = null;

                if (callIdToUse) {
                    console.log("Auto-cancel: enviando request con call ID:", callIdToUse);
                    const fd = new FormData();
                    fd.append('call_id', callIdToUse);
                    fd.append('reason', 'popup_blocked');  // ✅ CORREGIDO
                    fd.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");
                    try {
                        await fetch(self.config.apiEndpoints.rejectCallPopupBlocked, {
                            method: 'POST',
                            body: fd,
                            credentials: 'same-origin'
                        });
                        console.log("Auto-cancel: request enviado exitosamente");
                    } catch(error) {
                        console.error("Auto-cancel error:", error);
                    }
                } else if (self.currentCallId) {
                    console.log("Auto-cancel: usando hangupCallsBubbleCall para currentCallId");
                    hangupCallsBubbleCall();
                } else {
                    console.log("Auto-cancel: no hay call ID disponible");
                }

                self.closePopupBlockedHangupModal();
                self.endCall();
                self.showNotification('Llamada colgada automáticamente (pop-ups bloqueados)', 'warning');
            } else {
                label.textContent = `Colgando automáticamente en ${left} s...`;
            }
        }, 1000);
    },

  stopPopupBlockedAutoCancel: function() {
      const label = document.getElementById('popup-blocked-countdown');
      if (label) label.style.display = 'none';
      if (this.__popupBlockedTimer) {
          clearInterval(this.__popupBlockedTimer);
          this.__popupBlockedTimer = null;
      }
  },

  // Función para detectar si estamos ejecutándose en Tauri
  detectTauriEnvironment: function() {
    // Verificar si existe el objeto window.__TAURI__ (API de Tauri)
    if (window.__TAURI__) {
      console.log('🖥️ Detectado: Ejecutándose en Tauri (API disponible)');
      return true;
    }
    
    // Verificar user agent para detectar Tauri
    const userAgent = navigator.userAgent || '';
    if (userAgent.includes('Tauri') || userAgent.includes('tauri')) {
      console.log('🖥️ Detectado: Ejecutándose en Tauri (User Agent)');
      return true;
    }
    
    // Verificar características específicas de Tauri
    // Tauri tiene un user agent específico y ciertas características
    if (userAgent.includes('WebKit') && 
        !userAgent.includes('Chrome') && 
        !userAgent.includes('Safari') && 
        !userAgent.includes('Mobile') &&
        window.location.protocol === 'https:') {
      console.log('🖥️ Detectado: Posiblemente ejecutándose en Tauri (características WebKit sin Chrome/Safari)');
      return true;
    }
    
    // Verificar si estamos en localhost (típico de desarrollo Tauri)
    if (window.location.hostname === 'localhost' || 
        window.location.hostname === '127.0.0.1') {
      console.log('🖥️ Detectado: Ejecutándose en localhost (probablemente Tauri en desarrollo)');
      return true;
    }
    
    console.log('🌐 Ejecutándose en navegador web normal');
    return false;
  },

  // Función para manejar la aceptación de llamada cuando estamos en Tauri
  acceptCallInTauri: function(preservedCallId, preservedUserName, preservedUserImage) {
    console.log('🖥️ Aceptando llamada en Tauri - abriendo videollamada en la misma ventana');
    
    // Guardar la URL original para poder volver después
    this.originalPageUrl = window.location.href;
    console.log('💾 URL original guardada:', this.originalPageUrl);
    
    // Silenciar tonos y mostrar estado
    this.stopAllTones();
    this.showNotification('Llamada aceptada - Abriendo videollamada...', 'success');

    // Aceptar en backend
    const fd = new FormData();
    fd.append('call_id', preservedCallId);
    fd.append('action', 'accept');
    fd.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

    fetch(this.config.apiEndpoints.answerCall, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
        const url = data && data.receiver_vsee_url;
        if (data && data.success && url) {
            console.log('✅ URL de videollamada obtenida, redirigiendo en la misma ventana:', url);
            
            // Configurar monitoreo antes de redirigir
            this.setupReturnFromVideoCall();
            
            // En lugar de abrir una ventana emergente, redirigir en la misma ventana
            // NO llamar endCall aquí porque la página va a cambiar
            window.location.href = url;
        } else {
            console.log('❌ Error obteniendo URL de videollamada:', data);
            this.showNotification((data && data.message) || 'No se pudo aceptar la llamada', 'error');
            this.endCall();
        }
    })
    .catch(error => {
        console.log('❌ Error de conexión:', error);
        this.showNotification('Error de conexión', 'error');
        this.endCall();
    });
  },

  // Función para notificar a la aplicación Tauri que terminó la videollamada
  notifyDesktopCallEnded: function() {
    const desktop_url = 'http://127.0.0.1:8080/call-ended';
    
    fetch(desktop_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Aplicación Tauri notificada - ventana restaurada');
        } else {
            console.log('⚠️ Error notificando fin de llamada a Tauri:', data.error || 'Error desconocido');
        }
    })
    .catch(error => {
        console.log('⚠️ Error de conexión con aplicación Tauri al terminar llamada:', error);
        // No es crítico si falla
    });
  },

  // Función para detectar si la página está en blanco
  isPageBlank: function() {
    const body = document.body;
    if (!body) return true;
    
    // Verificar si el body está vacío o solo tiene elementos mínimos
    const children = body.children;
    if (children.length === 0) return true;
    
    // Verificar si solo hay elementos de script o meta
    let meaningfulElements = 0;
    for (let i = 0; i < children.length; i++) {
      const tagName = children[i].tagName.toLowerCase();
      if (tagName !== 'script' && tagName !== 'meta' && tagName !== 'link') {
        meaningfulElements++;
      }
    }
    
    return meaningfulElements === 0;
  },

  // Función para redirigir a la página principal
  redirectToMainPage: function() {
    console.log('🔄 Redirigiendo a la página principal de Tauri');
    
    // Usar la URL original guardada si está disponible
    if (this.originalPageUrl) {
      console.log('🔄 Usando URL original guardada:', this.originalPageUrl);
      setTimeout(() => {
        window.location.href = this.originalPageUrl;
      }, 1000);
      return;
    }
    
    // Fallback: Intentar redirigir a la página principal
    // Usar la URL base sin parámetros de videollamada
    const baseUrl = window.location.origin + window.location.pathname;
    const urlWithoutParams = baseUrl.split('?')[0];
    
    // Redirigir después de un pequeño delay para asegurar que la llamada termine
    setTimeout(() => {
      window.location.href = urlWithoutParams;
    }, 1000);
  },

  // Función específica para configurar el retorno desde videollamada
  setupReturnFromVideoCall: function() {
    console.log('📹 Configurando retorno desde videollamada');
    
    // Guardar información en localStorage para persistir entre páginas
    localStorage.setItem('tauri_video_call_active', 'true');
    localStorage.setItem('tauri_original_url', this.originalPageUrl || window.location.href);
    
    // Configurar un timeout de seguridad para volver automáticamente
    // Si la videollamada dura más de 30 minutos, volver automáticamente
    setTimeout(() => {
      if (localStorage.getItem('tauri_video_call_active') === 'true') {
        console.log('⏰ Timeout de videollamada - volviendo automáticamente');
        this.forceReturnFromVideoCall();
      }
    }, 30 * 60 * 1000); // 30 minutos
  },

  // Función para forzar el retorno desde videollamada
  forceReturnFromVideoCall: function() {
    console.log('🔄 Forzando retorno desde videollamada');
    
    // Limpiar flags de localStorage
    localStorage.removeItem('tauri_video_call_active');
    
    // Notificar a Tauri que terminó la videollamada
    this.notifyDesktopCallEnded();
    
    // Obtener URL original
    const originalUrl = localStorage.getItem('tauri_original_url');
    if (originalUrl) {
      localStorage.removeItem('tauri_original_url');
      console.log('🔄 Redirigiendo a URL original:', originalUrl);
      window.location.href = originalUrl;
    } else {
      // Fallback a página principal
      this.redirectToMainPage();
    }
  },

  // Función para configurar el monitoreo de fin de videollamada
  setupVideoCallMonitoring: function() {
    // Solo configurar si estamos en Tauri
    if (!this.detectTauriEnvironment()) return;

    console.log('🖥️ Configurando monitoreo de videollamada para Tauri');

    // Verificar si hay una videollamada activa desde localStorage
    const videoCallActive = localStorage.getItem('tauri_video_call_active') === 'true';
    
    // Detectar si estamos en una página de videollamada
    const isVideoCallPage = this.isVideoCallPage();
    
    if (isVideoCallPage || videoCallActive) {
      console.log('📹 Detectado: Página de videollamada o videollamada activa, configurando monitoreo');
      
      // Configurar listener para detectar cuando se cierra la videollamada
      this.setupVideoCallEndDetection();
      
      // Configurar monitoreo más agresivo para el receptor
      this.setupAggressiveVideoCallMonitoring();
    }
  },

  // Función para detectar si estamos en una página de videollamada
  isVideoCallPage: function() {
    const url = window.location.href.toLowerCase();
    const hostname = window.location.hostname.toLowerCase();
    
    // Detectar URLs comunes de videollamada
    const videoCallPatterns = [
      'vsee',
      'zoom',
      'teams',
      'meet',
      'webex',
      'gotomeeting',
      'videollamada',
      'video-call',
      'call'
    ];
    
    return videoCallPatterns.some(pattern => url.includes(pattern));
  },

  // Función para configurar la detección del fin de videollamada
  setupVideoCallEndDetection: function() {
    const self = this;
    
    // Listener para detectar cuando la página se va a cerrar o cambiar
    window.addEventListener('beforeunload', function() {
      console.log('📹 Videollamada terminando - notificando a Tauri');
      self.notifyDesktopCallEnded();
      // Limpiar flags de localStorage
      localStorage.removeItem('tauri_video_call_active');
      localStorage.removeItem('tauri_original_url');
    });

    // Listener para detectar cuando la página pierde el foco (posible fin de videollamada)
    window.addEventListener('blur', function() {
      setTimeout(() => {
        if (document.hidden || !document.hasFocus()) {
          console.log('📹 Página perdió foco - posible fin de videollamada');
          // No hacer nada inmediatamente, solo registrar
        }
      }, 1000);
    });

    // Listener para detectar cuando la página vuelve a tener foco
    window.addEventListener('focus', function() {
      console.log('📹 Página recuperó foco');
    });

    // Monitoreo periódico para detectar cambios en la página
    setInterval(() => {
      if (self.isPageBlank()) {
        console.log('📹 Página en blanco detectada - redirigiendo a página principal');
        self.forceReturnFromVideoCall();
      }
    }, 5000); // Verificar cada 5 segundos
  },

  // Función para monitoreo agresivo de videollamada (específico para el receptor)
  setupAggressiveVideoCallMonitoring: function() {
    const self = this;
    
    console.log('📹 Configurando monitoreo agresivo de videollamada');
    
    // Monitoreo más frecuente (cada 2 segundos)
    const aggressiveMonitor = setInterval(() => {
      // Verificar si la videollamada sigue activa
      const videoCallActive = localStorage.getItem('tauri_video_call_active') === 'true';
      
      if (!videoCallActive) {
        console.log('📹 Videollamada ya no está activa, limpiando monitoreo');
        clearInterval(aggressiveMonitor);
        return;
      }
      
      // Verificar si la página está en blanco o vacía
      if (self.isPageBlank()) {
        console.log('📹 Página en blanco detectada en monitoreo agresivo');
        self.forceReturnFromVideoCall();
        clearInterval(aggressiveMonitor);
        return;
      }
      
      // Verificar si estamos en una página de error o de fin de videollamada
      const currentUrl = window.location.href.toLowerCase();
      const endCallPatterns = [
        'call-ended',
        'call-complete',
        'meeting-ended',
        'session-ended',
        'goodbye',
        'thanks'
      ];
      
      if (endCallPatterns.some(pattern => currentUrl.includes(pattern))) {
        console.log('📹 Detectado fin de videollamada por URL');
        self.forceReturnFromVideoCall();
        clearInterval(aggressiveMonitor);
        return;
      }
      
    }, 2000); // Verificar cada 2 segundos
    
    // Limpiar el intervalo después de 35 minutos como medida de seguridad
    setTimeout(() => {
      clearInterval(aggressiveMonitor);
      console.log('📹 Monitoreo agresivo limpiado por timeout');
    }, 35 * 60 * 1000);
  }
};

function closePopupModal() {
    console.log('🔒 Cerrando modal popup...');
    const modal = document.getElementById('popup-permission-modal');
    if (modal) {
        modal.style.display = 'none';
    }
    
    // Resolver cualquier promesa pendiente
    if (window.__currentPopupResolve) {
        window.__currentPopupResolve(false);
        delete window.__currentPopupResolve;
    }
}



function testPopupPermission() {
    console.log('🧪 Probando permisos popup...');
    const testWindow = window.open('', 'test', 'width=1,height=1,left=-1000,top=-1000');
    
    setTimeout(() => {
        if (testWindow && !testWindow.closed) {
            testWindow.close();
            closePopupModal();
            CallsBubbleSystem.showNotification('¡Ventanas emergentes habilitadas correctamente!', 'success');
            
            // Resolver promesa
            if (window.__currentPopupResolve) {
                window.__currentPopupResolve(true);
                delete window.__currentPopupResolve;
            }
        } else {
            CallsBubbleSystem.showNotification('Las ventanas emergentes siguen bloqueadas. Verifica la configuración.', 'warning');
        }
    }, 100);
}

function showPopupBlockedHangupModal(opts = {}) {
  const modal = document.getElementById('popup-blocked-hangup-modal');
  if (!modal) return;

  // Opcionales: nombre/imagen del otro usuario (para un toque más humano)
  const avatar = document.getElementById('popup-blocked-avatar');
  if (avatar && opts.image) avatar.src = opts.image;
  const subtitle = document.getElementById('popup-blocked-subtitle');
  if (subtitle && opts.name) {
    subtitle.textContent = `No se pudo abrir la videollamada con ${opts.name}. ` +
                           `Cuelga para que no te espere y vuelve a intentar.`;
  }

  modal.style.display = 'block';

  // Botón "Colgar ahora"
  const btn = document.getElementById('popup-blocked-hangup-btn');
  if (btn) {
    btn.onclick = async () => {
      // Si el receptor “aceptó” pero no se pudo abrir popup, mejor rechazamos/cerramos la llamada
      if (CallsBubbleSystem.incomingCallId) {
        // Rechazar (receiver)
        const fd = new FormData();
        fd.append('call_id', CallsBubbleSystem.incomingCallId);
        fd.append('action', 'reject');
        fd.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

        try {
          const r = await fetch(CallsBubbleSystem.config.apiEndpoints.answerCall, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
          });
          const data = await r.json().catch(()=>null);
          if (data && data.success) {
            CallsBubbleSystem.showNotification('Llamada colgada', 'info');
          } else {
            CallsBubbleSystem.showNotification((data && data.message) || 'No se pudo colgar', 'error');
          }
        } catch {
          CallsBubbleSystem.showNotification('Error de conexión al colgar', 'error');
        }
      } else if (CallsBubbleSystem.currentCallId) {
        // Si fueras caller (por si reutilizas el modal)
        hangupCallsBubbleCall();
      }

      // Cerrar modal y limpiar estado local
      closePopupBlockedHangupModal();
      CallsBubbleSystem.endCall();
    };
  }

  // Auto-cancelar si el usuario no hace nada (p. ej. 20 s)
  startPopupBlockedAutoCancel(20);
}

function closePopupBlockedHangupModal() {
  const modal = document.getElementById('popup-blocked-hangup-modal');
  if (modal) modal.style.display = 'none';
  stopPopupBlockedAutoCancel();
}

let __popupBlockedTimer = null;
function startPopupBlockedAutoCancel(seconds) {
  stopPopupBlockedAutoCancel();
  const label = document.getElementById('popup-blocked-countdown');
  if (!label) return;

  let left = seconds;
  label.style.display = 'block';
  label.textContent = `Colgando automáticamente en ${left} s...`;

  __popupBlockedTimer = setInterval(async () => {
    left--;
    if (left <= 0) {
      clearInterval(__popupBlockedTimer);
      __popupBlockedTimer = null;

      // Igual que el botón "Colgar ahora"
      if (CallsBubbleSystem.incomingCallId) {
        const fd = new FormData();
        fd.append('call_id', CallsBubbleSystem.incomingCallId);
        fd.append('action', 'reject');
        fd.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");
        try {
          await fetch(CallsBubbleSystem.config.apiEndpoints.answerCall, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
          });
        } catch {}
      } else if (CallsBubbleSystem.currentCallId) {
        hangupCallsBubbleCall();
      }

      closePopupBlockedHangupModal();
      CallsBubbleSystem.endCall();
      CallsBubbleSystem.showNotification('Llamada colgada automáticamente (pop-ups bloqueados)', 'warning');
    } else {
      label.textContent = `Colgando automáticamente en ${left} s...`;
    }
  }, 1000);
}
function stopPopupBlockedAutoCancel() {
  const label = document.getElementById('popup-blocked-countdown');
  if (label) label.style.display = 'none';
  if (__popupBlockedTimer) {
    clearInterval(__popupBlockedTimer);
    __popupBlockedTimer = null;
  }
}

// Funciones para el modal de error de conexión
function showConnectionErrorModal() {
  console.log('🔌 Mostrando modal de error de conexión...');
  const modal = document.getElementById('connection-error-modal');
  if (modal) {
    modal.style.display = 'block';
    // Registrar el error para analytics/debugging
    console.warn('Error de conexión en videollamada detectado');
  }
}

function closeConnectionErrorModal() {
  console.log('🔌 Cerrando modal de error de conexión...');
  const modal = document.getElementById('connection-error-modal');
  if (modal) {
    modal.style.display = 'none';
  }
  // Finalizar la llamada cuando se cierra el modal
  if (CallsBubbleSystem) {
    CallsBubbleSystem.endCall();
  }
}

function retryConnection() {
  console.log('🔌 Reintentando conexión...');
  closeConnectionErrorModal();
  
  // Mostrar notificación de reintento
  if (CallsBubbleSystem) {
    CallsBubbleSystem.showNotification('Reintentando conexión...', 'info');
    
    // Si hay una llamada activa, intentar reconectar
    if (CallsBubbleSystem.currentCallId) {
      // Esperar un momento y luego intentar abrir nueva ventana
      setTimeout(() => {
        try {
          // Cerrar ventana anterior si existe
          if (CallsBubbleSystem.callWindow && !CallsBubbleSystem.callWindow.closed) {
            CallsBubbleSystem.callWindow.close();
          }
          
          // Abrir nueva ventana de llamada
          const retryUrl = CallsBubbleSystem.callWindow ? CallsBubbleSystem.callWindow.location.href : 'about:blank';
          CallsBubbleSystem.callWindow = window.open(retryUrl, '_blank');
          
          if (!CallsBubbleSystem.callWindow) {
            CallsBubbleSystem.showNotification('No se pudo abrir nueva ventana. Verifica los permisos de popup.', 'error');
          } else {
            CallsBubbleSystem.showNotification('Nueva ventana abierta. Intenta conectar de nuevo.', 'success');
          }
        } catch (error) {
          console.error('Error al reintentar conexión:', error);
          CallsBubbleSystem.showNotification('Error al reintentar. Contacta soporte técnico.', 'error');
        }
      }, 1000);
    }
  }
}

// Funciones para el modal de usuario ocupado
function showUserBusyModal() {
  console.log('🔄 Mostrando modal de usuario ocupado...');
  const modal = document.getElementById('user-busy-modal');
  if (modal) {
    modal.style.display = 'block';
    
    // Verificar si hay una llamada activa para personalizar el mensaje
    const hasActiveCall = CallsBubbleSystem.currentCallId || 
                         CallsBubbleSystem.incomingCallId || 
                         (CallsBubbleSystem.callWindow && !CallsBubbleSystem.callWindow.closed);
    
    const titleElement = modal.querySelector('.calls-call-name');
    const subtitleElement = modal.querySelector('.calls-call-subtitle');
    
    if (hasActiveCall) {
      // Caso: Hay una videollamada activa
      if (titleElement) titleElement.textContent = 'Videollamada en Curso';
      if (subtitleElement) {
        subtitleElement.textContent = 'Tienes una videollamada activa. Para hacer otra llamada, primero debes terminar la llamada actual.';
      }
      
      // Actualizar instrucciones para llamada activa
      const instructionsContainer = modal.querySelector('.busy-user-instructions');
      if (instructionsContainer) {
        instructionsContainer.innerHTML = `
          <div class="busy-instruction-item">
            <i class="fas fa-phone-slash text-danger"></i>
            <span>Finaliza tu videollamada actual</span>
          </div>
          <div class="busy-instruction-item">
            <i class="fas fa-window-close text-warning"></i>
            <span>Cierra la ventana de videollamada</span>
          </div>
          <div class="busy-instruction-item">
            <i class="fas fa-phone text-success"></i>
            <span>Luego podrás hacer otra llamada</span>
          </div>
        `;
      }
      
      // Actualizar nota inferior
      const smallNote = modal.querySelector('small');
      if (smallNote) {
        smallNote.textContent = 'No puedes tener múltiples videollamadas simultáneas';
      }
      
    } else {
      // Caso: Estado ocupado por configuración manual
      if (titleElement) titleElement.textContent = 'Estado Ocupado';
      if (subtitleElement) {
        subtitleElement.textContent = 'Actualmente tienes el estado "Ocupado". Revisa si tienes una videollamada en curso.';
      }
      
      // Restaurar instrucciones originales para estado ocupado
      const instructionsContainer = modal.querySelector('.busy-user-instructions');
      if (instructionsContainer) {
        instructionsContainer.innerHTML = `
          <div class="busy-instruction-item">
            <i class="fas fa-search text-primary"></i>
            <span>Verifica si tienes una videollamada activa</span>
          </div>
          <div class="busy-instruction-item">
            <i class="fas fa-times-circle text-danger"></i>
            <span>Cierra cualquier ventana de videollamada</span>
          </div>
          <div class="busy-instruction-item">
            <i class="fas fa-check-circle text-success"></i>
            <span>Marca como "Disponible" para recibir llamadas</span>
          </div>
        `;
      }
      
      // Restaurar nota original
      const smallNote = modal.querySelector('small');
      if (smallNote) {
        smallNote.textContent = 'Si no tienes ninguna videollamada abierta, marca aquí como disponible';
      }
    }
    
    console.warn('Usuario intenta hacer llamada estando ocupado o en llamada activa');
  }
}

function closeUserBusyModal() {
  console.log('🔄 Cerrando modal de usuario ocupado...');
  const modal = document.getElementById('user-busy-modal');
  if (modal) {
    modal.style.display = 'none';
  }
}

function markAsAvailable() {
  console.log('🔄 Marcando usuario como disponible...');
  closeUserBusyModal();
  
  // Cambiar el estado del usuario a disponible
  if (CallsBubbleSystem) {
    const statusSelector = document.getElementById('calls-user-status-selector');
    const statusDot = document.getElementById('calls-user-status-dot');
    const statusText = document.getElementById('calls-user-status-text');
    
    if (statusSelector) {
      statusSelector.value = 'available';
      // Trigger change event para actualizar estado en el servidor
      statusSelector.dispatchEvent(new Event('change'));
    }
    
    // Actualizar indicadores visuales
    if (statusDot) {
      statusDot.className = 'calls-status-dot available';
    }
    if (statusText) {
      statusText.textContent = 'Disponible';
    }
    
    CallsBubbleSystem.showNotification('Estado cambiado a "Disponible". Ahora puedes hacer llamadas.', 'success');
  }
}

// Funciones para el modal de receptor ocupado
function showRecipientBusyModal(recipientName, recipientImage) {
  console.log('📞 Mostrando modal de receptor ocupado...');
  const modal = document.getElementById('recipient-busy-modal');
  if (modal) {
    modal.style.display = 'block';
    
    // Actualizar información del usuario ocupado
    const subtitle = document.getElementById('recipient-busy-subtitle');
    if (subtitle) {
      subtitle.textContent = `${recipientName || 'Este usuario'} está actualmente en una videollamada y no puede recibir llamadas.`;
    }
    
    // Mostrar avatar si está disponible
    const avatar = document.getElementById('recipient-busy-avatar');
    if (avatar && recipientImage) {
      avatar.src = recipientImage;
      avatar.style.display = 'block';
      // Ocultar el icono cuando se muestra el avatar
      const iconContainer = modal.querySelector('.calls-busy-icon');
      if (iconContainer) {
        iconContainer.style.display = 'none';
      }
    }
    
    console.warn(`Intento de llamada a usuario ocupado: ${recipientName}`);
  }
}

function closeRecipientBusyModal() {
  console.log('📞 Cerrando modal de receptor ocupado...');
  const modal = document.getElementById('recipient-busy-modal');
  if (modal) {
    modal.style.display = 'none';
    
    // Resetear el modal para el próximo uso
    const avatar = document.getElementById('recipient-busy-avatar');
    if (avatar) {
      avatar.style.display = 'none';
      avatar.src = '';
    }
    
    // Mostrar el icono de nuevo
    const iconContainer = modal.querySelector('.calls-busy-icon');
    if (iconContainer) {
      iconContainer.style.display = 'flex';
    }
  }
}

class PopupPermissionValidator {
    constructor() {
        this.permissionGranted = false;
        this.testWindow = null;
    }

    async checkPopupPermission() {
        return new Promise((resolve) => {
            console.log('🔍 Verificando permisos de popup...');
            
            try {
                this.testWindow = window.open('', 'test', 'width=1,height=1,left=-1000,top=-1000');
                
                setTimeout(() => {
                    if (this.testWindow && !this.testWindow.closed) {
                        try {
                            this.testWindow.close();
                            this.permissionGranted = true;
                            console.log('✅ Popup permitido');
                            resolve(true);
                        } catch(e) {
                            console.log('❌ Error cerrando popup:', e);
                            resolve(false);
                        }
                    } else {
                        console.log('❌ Popup bloqueado');
                        this.permissionGranted = false;
                        resolve(false);
                    }
                }, 100);
            } catch(e) {
                console.log('❌ Error abriendo popup:', e);
                resolve(false);
            }
        });
    }

    async showPopupInstructions() {
        console.log('🔧 Mostrando modal de instrucciones...');
        
        return new Promise((resolve) => {
            // FORZAR mostrar el modal
            const modal = document.getElementById('popup-permission-modal');
            if (modal) {
                console.log('📱 Modal encontrado, mostrando...');
                modal.style.setProperty('display', 'flex', 'important');
                modal.style.setProperty('z-index', '9998', 'important');
            } else {
                console.error('❌ Modal no encontrado');
                resolve({ isConfirmed: false });
                return;
            }
            
            // Configurar resolvers únicos
            const modalResolve = (confirmed) => {
                console.log(`🔧 Modal resuelto: ${confirmed}`);
                resolve({ isConfirmed: confirmed });
            };
            
            // Guardar en window con timestamp para evitar conflictos
            const timestamp = Date.now();
            window[`__popupResolve_${timestamp}`] = modalResolve;
            window.__currentPopupResolve = modalResolve;
            
            // Timeout de seguridad (30 segundos)
            setTimeout(() => {
                if (window.__currentPopupResolve === modalResolve) {
                    console.log('⏰ Timeout del modal');
                    modalResolve(false);
                    delete window.__currentPopupResolve;
                }
            }, 30000);
        });
    }

    async validateAndRequest() {
        console.log('🚀 Iniciando validación de popup...');
        
        const hasPermission = await this.checkPopupPermission();
        
        if (hasPermission) {
            console.log('✅ Permisos ya habilitados');
            return { success: true, message: 'Permisos de ventanas emergentes habilitados' };
        }

        console.log('❌ Permisos no habilitados, mostrando instrucciones...');
        const result = await this.showPopupInstructions();
        
        if (result.isConfirmed) {
            console.log('🔄 Usuario confirmó, probando de nuevo...');
            const retestPermission = await this.checkPopupPermission();
            
            if (retestPermission) {
                console.log('✅ Permisos habilitados tras configuración');
                return { success: true, message: 'Permisos habilitados correctamente' };
            } else {
                console.log('❌ Permisos siguen bloqueados');
                const reload = confirm('❌ Las ventanas emergentes siguen bloqueadas.\n\n¿Quieres recargar la página para intentar de nuevo?');
                if (reload) {
                    window.location.reload();
                }
                return { success: false, message: 'Permisos no concedidos' };
            }
        }

        console.log('🚫 Usuario canceló');
        return { success: false, message: 'Usuario canceló la configuración' };
    }
}

class BrowserDetector {
    static detect() {
        const userAgent = navigator.userAgent;
        
        if (userAgent.includes('Chrome') && !userAgent.includes('Edg')) {
            return 'chrome';
        } else if (userAgent.includes('Firefox')) {
            return 'firefox';
        } else if (userAgent.includes('Safari') && !userAgent.includes('Chrome')) {
            return 'safari';
        } else if (userAgent.includes('Edg')) {
            return 'edge';
        }
        return 'chrome';
    }

    static getSpecificInstructions() {
        const browser = this.detect();
        
        const instructions = {
            chrome: {
                icon: 'fab fa-chrome',
                name: 'Chrome',
                steps: [
                    'Haz clic en el ícono 🔒 junto a la URL',
                    'Selecciona "Ventanas emergentes y redirecciones"',
                    'Cambia a "Permitir"',
                    'Recarga la página'
                ]
            },
            firefox: {
                icon: 'fab fa-firefox',
                name: 'Firefox',
                steps: [
                    'Haz clic en el escudo 🛡️ junto a la URL',
                    'Desactiva "Bloquear ventanas emergentes"',
                    'Recarga la página'
                ]
            },
            safari: {
                icon: 'fab fa-safari',
                name: 'Safari',
                steps: [
                    'Menú Safari > Preferencias',
                    'Pestaña "Sitios web" > "Ventanas emergentes"',
                    'Selecciona "Permitir" para este sitio',
                    'Recarga la página'
                ]
            },
            edge: {
                icon: 'fab fa-edge',
                name: 'Edge',
                steps: [
                    'Haz clic en el ícono 🔒 junto a la URL',
                    'Selecciona "Ventanas emergentes y redirecciones"',
                    'Cambia a "Permitir"',
                    'Recarga la página'
                ]
            }
        };

        return instructions[browser] || instructions.chrome;
    }
}


// Funciones globales para compatibilidad
function toggleCallsModal() {
    CallsBubbleSystem.toggleModal();
}

function closeCallsModal() {
    CallsBubbleSystem.closeModal();
}

function hangupCallsBubbleCall() {
    // ✅ ENVIAR LA LLAMADA AL BACKEND PARA ACTUALIZAR ESTADO
    const callId = CallsBubbleSystem.currentCallId;

    if (callId) {
        const fd = new FormData();
        fd.append('call_id', CallsBubbleSystem.currentCallId);
        fd.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

        fetch(CallsBubbleSystem.config.apiEndpoints.endCall, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                CallsBubbleSystem.showNotification(data.message || 'Llamada cancelada', 'info');
            } else {
                CallsBubbleSystem.showNotification(data.message || 'Error al cancelar llamada', 'error');
            }
        })
        .catch(err => {
            console.error('Error cancelando llamada:', err);
            // Mostrar modal de error de conexión para errores al cancelar
            if (typeof showConnectionErrorModal === 'function') {
                showConnectionErrorModal();
            } else {
                CallsBubbleSystem.showNotification('Error de conexión', 'error');
            }
        });
    }

    // ✅ CERRAR LA VENTANA POPUP SI EXISTE
    if (CallsBubbleSystem.callWindow && !CallsBubbleSystem.callWindow.closed) {
        try {
            CallsBubbleSystem.callWindow.close();
        } catch(e) {
            console.warn('No se pudo cerrar la ventana popup');
        }
    }

    // ✅ LIMPIAR EL ESTADO LOCAL
    CallsBubbleSystem.endCall();
}

function acceptCallsBubbleCall() {
    CallsBubbleSystem.acceptCall();
}

function rejectCallsBubbleCall() {
    CallsBubbleSystem.rejectCall();
}

document.addEventListener('click', function(e) {
    const modal = document.getElementById('popup-permission-modal');
    if (e.target === modal) {
        closePopupModal();
    }
});

// Inicializar cuando el DOM esté listo
$(document).ready(function() {
    CallsBubbleSystem.init();
});

// Configuración del heartbeat para renovación de sesión
window.heartbeatConfig = {
    url: '<?= get_uri("heartbeat") ?>',
    csrfName: '<?= csrf_token() ?>',
    csrfHash: '<?= csrf_hash() ?>',
    interval: 30000, // 30 segundos
    timeout: 10000,  // 10 segundos
    maxRetries: 3
};


// ========== SISTEMA DE LLAMADAS PERDIDAS ==========
window.MissedCallsSystem = {
    config: {
        apiEndpoints: {
            getMissedCalls: '<?= get_uri("calls_system/get_missed_video_calls_24h") ?>',
            acknowledgeCall: '<?= get_uri("calls_system/acknowledge_missed_call") ?>'
        },
        csrfName: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>'
    },
    
    // Store current calls data for reference
    currentCalls: [],
    
    init: function() {
        this.checkMissedCalls();
    },
    
    checkMissedCalls: function() {
        const modal = document.getElementById('missed-calls-modal');
        const loading = document.getElementById('missed-calls-loading');
        const content = document.getElementById('missed-calls-content');
        const list = document.getElementById('missed-calls-list');
        const noCalls = document.getElementById('no-missed-calls');
        const acknowledgeAllBtn = document.getElementById('acknowledge-all-btn');
        
        // Show modal and loading
        //modal.style.display = 'flex';
        loading.style.display = 'block';
        content.style.display = 'none';
        
        // Make API call
        fetch(this.config.apiEndpoints.getMissedCalls, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [this.config.csrfName]: this.config.csrfHash
            }
        })
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            content.style.display = 'block';
            
            if (data.success && data.data.length > 0) {
                // Hide "no calls" message and show list
                noCalls.style.display = 'none';
                list.style.display = 'block';
                
                // Render the calls
                this.renderMissedCalls(data.data);
                acknowledgeAllBtn.style.display = 'block';
            } else {
                this.showNoCallsMessage();
            }
        })
        .catch(error => {
            console.error('Error loading missed calls:', error);
            loading.style.display = 'none';
            content.style.display = 'block';
            this.showNoCallsMessage();
        });
    },
    
    renderMissedCalls: function(calls) {
        // Store calls data for reference
        this.currentCalls = calls;
        
        const list = document.getElementById('missed-calls-list');
        list.innerHTML = '';
        
        calls.forEach(call => {
            const callElement = this.createMissedCallElement(call);
            list.appendChild(callElement);
        });
    },
    
    createMissedCallElement: function(call) {
        const div = document.createElement('div');
        div.className = 'missed-call-item';
        div.setAttribute('data-call-id', call.id);
        
        const callerName = `${call.caller.first_name} ${call.caller.last_name}`;
        const formattedDate = call.start_time_formatted || call.start_time;
        
        // Handle profile image the same way as the calls modal
        const avatarHtml = call.caller.image ? 
            `<img src="${call.caller.image}" alt="${callerName}" class="calls-contact-avatar-mini">` : 
            `<i class="fas fa-user"></i>`;
        
        div.innerHTML = `
            <div class="missed-call-avatar">
                ${avatarHtml}
            </div>
            <div class="missed-call-info">
                <h4 class="missed-call-name">${callerName}</h4>
                <p class="missed-call-time">${formattedDate}</p>
            </div>
            <div class="missed-call-actions">
                <button class="missed-calls-btn missed-calls-btn-primary" onclick="MissedCallsSystem.acknowledgeCall(${call.id})">
                    <i class="fas fa-check"></i>
                    <span><?php echo app_lang('missed_call_acknowledge') ?: 'Aceptar'; ?></span>
                </button>
                <button class="missed-calls-btn missed-calls-btn-secondary" onclick="MissedCallsSystem.returnCall(${call.caller_id})">
                    <i class="fas fa-phone"></i>
                    <span><?php echo app_lang('missed_call_return_call') ?: 'Devolver Llamada'; ?></span>
                </button>
            </div>
        `;
        
        return div;
    },
    
    acknowledgeCall: function(callId) {
        const callElement = document.querySelector(`[data-call-id="${callId}"]`);
        const button = callElement.querySelector('.missed-calls-btn-primary');
        
        // Disable button and show loading
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Procesando...</span>';
        
        // Create form data for POST request
        const formData = new FormData();
        formData.append('call_id', callId);
        formData.append(this.config.csrfName, this.config.csrfHash);
        
        fetch(this.config.apiEndpoints.acknowledgeCall, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text(); // Get as text first
        })
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('Invalid JSON response');
            }
            
            if (data.success) {
                // Remove the call from the list
                callElement.remove();
                
                // Check if there are any remaining calls
                const remainingCalls = document.querySelectorAll('.missed-call-item');
                if (remainingCalls.length === 0) {
                    this.showNoCallsMessage();
                }
            } else {
                // Show error and re-enable button
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-check"></i> <span><?php echo app_lang('missed_call_acknowledge') ?: 'Aceptar'; ?></span>';
                alert('Error: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error acknowledging call:', error);
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-check"></i> <span><?php echo app_lang('missed_call_acknowledge') ?: 'Aceptar'; ?></span>';
            alert('Error al procesar la solicitud: ' + error.message);
        });
    },
    
    acknowledgeAllMissedCalls: function() {
        const calls = document.querySelectorAll('.missed-call-item');
        const acknowledgeAllBtn = document.getElementById('acknowledge-all-btn');
        
        if (calls.length === 0) return;
        
        // Disable button and show loading
        acknowledgeAllBtn.disabled = true;
        acknowledgeAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Procesando todas...</span>';
        
        // Process all calls sequentially to avoid overwhelming the server
        let processedCount = 0;
        const totalCalls = calls.length;
        
        const processNextCall = () => {
            if (processedCount >= totalCalls) {
                acknowledgeAllBtn.disabled = false;
                acknowledgeAllBtn.innerHTML = '<i class="fas fa-check-double"></i> <span><?php echo app_lang('accept_all_missed_calls') ?: 'Aceptar Todas'; ?></span>';
                this.showNoCallsMessage();
                return;
            }
            
            const callElement = calls[processedCount];
            const callId = callElement.getAttribute('data-call-id');
            
            // Use the same acknowledgeCall method but without UI updates
            this.acknowledgeCallSilent(callId).then(() => {
                callElement.remove();
                processedCount++;
                processNextCall();
            }).catch(() => {
                processedCount++;
                processNextCall();
            });
        };
        
        processNextCall();
    },
    
    acknowledgeCallSilent: function(callId) {
        // Create form data for POST request
        const formData = new FormData();
        formData.append('call_id', callId);
        formData.append(this.config.csrfName, this.config.csrfHash);
        
        return fetch(this.config.apiEndpoints.acknowledgeCall, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('Invalid JSON response');
            }
            
            if (!data.success) {
                throw new Error(data.message || 'Error acknowledging call');
            }
            
            return data;
        });
    },
    
    returnCall: function(callerId) {
        // Find the call data from stored calls
        const callData = this.currentCalls.find(call => call.caller_id == callerId);
        
        let callerName = 'Usuario';
        let callerImage = '';
        
        if (callData) {
            callerName = `${callData.caller.first_name} ${callData.caller.last_name}`;
            callerImage = callData.caller.image || '';
        }
        
        // Close missed calls modal
        document.getElementById('missed-calls-modal').style.display = 'none';
        
        // Initiate call to the caller with proper parameters
        if (window.CallsBubbleSystem && window.CallsBubbleSystem.initiateCall) {
            console.log('Initiating call to:', callerId, callerName, callerImage);
            window.CallsBubbleSystem.initiateCall(callerId, callerName, callerImage);
        } else {
            console.error('CallsBubbleSystem.initiateCall not available');
            alert('Error: No se puede iniciar la llamada en este momento');
        }
    },
    
    showNoCallsMessage: function() {
        const list = document.getElementById('missed-calls-list');
        const noCalls = document.getElementById('no-missed-calls');
        const acknowledgeAllBtn = document.getElementById('acknowledge-all-btn');
        const modal = document.getElementById('missed-calls-modal');
        
        list.style.display = 'none';
        noCalls.style.display = 'block';
        acknowledgeAllBtn.style.display = 'none';
        
        // Auto-close modal after 1.5 seconds when showing "no calls" message
        modal.style.display = 'none';
    },
    
    getTimeAgo: function(dateString) {
        const now = new Date();
        const callTime = new Date(dateString);
        const diffInSeconds = Math.floor((now - callTime) / 1000);
        
        if (diffInSeconds < 60) {
            return '<?php echo app_lang('missed_call_ago') ?: 'hace'; ?> ' + diffInSeconds + ' <?php echo app_lang('missed_call_minutes') ?: 'minutos'; ?>';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return '<?php echo app_lang('missed_call_ago') ?: 'hace'; ?> ' + minutes + ' <?php echo app_lang('missed_call_minutes') ?: 'minutos'; ?>';
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return '<?php echo app_lang('missed_call_ago') ?: 'hace'; ?> ' + hours + ' <?php echo app_lang('missed_call_hours') ?: 'horas'; ?>';
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return '<?php echo app_lang('missed_call_ago') ?: 'hace'; ?> ' + days + ' <?php echo app_lang('missed_call_days') ?: 'días'; ?>';
        }
    },

    // Periodic check for missed calls every 1 minute
    startPeriodicCheck: function() {
        // Check every 1 minute (60,000 milliseconds)
        this.checkInterval = setInterval(() => {
            this.checkForMissedCalls();
        }, 60000); // 1 minute
        
        console.log('Missed calls periodic check started (every 1 minute)');
    },

    // Stop periodic check
    stopPeriodicCheck: function() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
            console.log('Missed calls periodic check stopped');
        }
    },

    // Check for missed calls without opening modal
    checkForMissedCalls: function() {
        console.log('Checking for missed calls...');
        
        fetch(this.config.apiEndpoints.getMissedCalls, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [this.config.csrfName]: this.config.csrfHash
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                console.log(`Found ${data.data.length} missed calls, opening modal`);
                // Only open modal if there are missed calls
                this.openMissedCallsModal(data.data);
            } else {
                console.log('No missed calls found');
                // Don't open modal if no missed calls
            }
        })
        .catch(error => {
            console.error('Error checking for missed calls:', error);
        });
    },

    // Open modal with missed calls data
    openMissedCallsModal: function(calls) {
        const modal = document.getElementById('missed-calls-modal');
        const loading = document.getElementById('missed-calls-loading');
        const content = document.getElementById('missed-calls-content');
        const list = document.getElementById('missed-calls-list');
        const noCalls = document.getElementById('no-missed-calls');
        const acknowledgeAllBtn = document.getElementById('acknowledge-all-btn');
        
        // Show modal
        modal.style.display = 'flex';
        loading.style.display = 'none';
        content.style.display = 'block';
        
        if (calls && calls.length > 0) {
            // Hide "no calls" message and show list
            if (noCalls) noCalls.style.display = 'none';
            if (list) list.style.display = 'block';
            
            // Render the calls
            this.renderMissedCalls(calls);
            if (acknowledgeAllBtn) acknowledgeAllBtn.style.display = 'block';
        } else {
            // Show "no calls" message and hide list
            if (noCalls) noCalls.style.display = 'block';
            if (list) list.style.display = 'none';
            if (acknowledgeAllBtn) acknowledgeAllBtn.style.display = 'none';
        }
    }
};

// Initialize missed calls system when page loads
document.addEventListener('DOMContentLoaded', function() {
    MissedCallsSystem.init();
    
    // Start periodic check for missed calls every 5 minutes
    MissedCallsSystem.startPeriodicCheck();
    
    // Configurar monitoreo de videollamada para Tauri
    if (window.CallsBubbleSystem && window.CallsBubbleSystem.setupVideoCallMonitoring) {
        window.CallsBubbleSystem.setupVideoCallMonitoring();
    }
});

// Global functions for onclick handlers
function acknowledgeAllMissedCalls() {
    MissedCallsSystem.acknowledgeAllMissedCalls();
}
</script>

<style>

/* ========== SISTEMA DE LLAMADAS BURBUJA - CSS PERSONALIZADO ========== */
/* Namespace: calls- para evitar conflictos con otros estilos */

/* ========== BURBUJA FLOTANTE ========== */
.calls-bubble-wrapper {
    position: fixed;
    bottom: 80px;
    right: 20px;
    z-index: 9998;
    user-select: none;
}

.calls-bubble-icon {
    position: relative;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: calls-bubble-pulse 2s infinite;
}

.calls-bubble-icon:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 35px rgba(37, 99, 235, 0.6);
}

.calls-bubble-phone-icon {
    color: white;
    font-size: 24px;
    transition: transform 0.3s ease;
}

.calls-bubble-icon:hover .calls-bubble-phone-icon {
    transform: rotate(15deg);
}

/* Indicador de actualización de estados */
/*.calls-bubble-icon.calls-updating {
    animation: calls-updating-pulse 1s ease-in-out infinite;
}

.calls-bubble-icon.calls-updating::after {
    content: '';
    position: absolute;
    top: -2px;
    right: -2px;
    width: 16px;
    height: 16px;
    background: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.6);
    animation: calls-sync-blink 1s ease-in-out infinite;
}*/

@keyframes calls-updating-pulse {
    0%, 100% { 
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
    }
    50% { 
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.7), 
                    0 0 20px rgba(16, 185, 129, 0.3);
    }
}

@keyframes calls-sync-blink {
    0%, 100% { 
        opacity: 1;
        transform: scale(1);
    }
    50% { 
        opacity: 0.7;
        transform: scale(0.9);
    }
}

.calls-bubble-notification-dot {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    border: 2px solid white;
    animation: calls-bubble-bounce 0.5s ease;
}

.calls-bubble-status-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid white;
    transition: all 0.3s ease;
}

.calls-bubble-status-indicator.available {
    background: #10b981;
}

.calls-bubble-status-indicator.busy {
    background: #f59e0b;
}

.calls-bubble-status-indicator.offline {
    background: #6b7280;
}

/* ========== MODAL PRINCIPAL ========== */
.calls-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    z-index: 9998;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.calls-modal-overlay.calls-show {
    opacity: 1;
    visibility: visible;
}

.calls-modal-container {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 1200px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    transform: translateY(30px) scale(0.95);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.calls-modal-overlay.calls-show .calls-modal-container {
    transform: translateY(0) scale(1);
}

/* ========== HEADER ========== */
.calls-modal-header {
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: white;
    padding: 20px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}

.calls-modal-header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.calls-modal-logo {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.calls-modal-title {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}

.calls-modal-subtitle {
    opacity: 0.9;
    font-size: 14px;
}

.calls-modal-header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.calls-status-section {
    display: flex;
    align-items: center;
    gap: 15px;
}

.calls-status-display {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.15);
    padding: 8px 16px;
    border-radius: 25px;
    backdrop-filter: blur(10px);
}

.calls-status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #10b981;
}

.calls-status-dot.busy {
    background: #f59e0b;
}

.calls-status-text {
    font-size: 14px;
    font-weight: 500;
}

.calls-status-selector {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 14px;
    backdrop-filter: blur(10px);
}

.calls-status-selector option {
    background: #1e40af;
    color: white;
}

.calls-modal-close {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.calls-modal-close:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(1.1);
}

.calls-modal-body {
    flex: 1;
    overflow: hidden;
    display: flex;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); /* Gradiente más sutil */
}

.calls-modal-body {
    max-height: 80vh; /* 80% de la altura de pantalla */
    overflow-y: auto;
}

/* ========== CONTROLES Y BÚSQUEDA ========== */
.calls-controls-section {
    padding: 10px 15px !important;
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

.calls-search-wrapper {
    display: flex;
    gap: 20px;
    align-items: center;
}

.calls-search-input-wrapper {
    flex: 1;
    position: relative;
}

.calls-search-input {
    width: 100%;
    padding: 6px 10px 6px 22px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.calls-search-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.calls-search-icon {
    position: absolute;
    left: 5px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    font-size: 16px;
}

.calls-filter-buttons {
    display: flex;
    gap: 10px;
}

.calls-filter-btn {
    padding: 8px 16px;
    border: 2px solid #e2e8f0;
    background: white;
    color: #6b7280;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.calls-filter-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

.calls-filter-btn.calls-active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

/* ========== LAYOUT PRINCIPAL ========== */
.calls-main-layout {
    flex: 1;
    display: flex;
    overflow: hidden;
}

.calls-sidebar {
    width: 400px;
    background: white;
    border-right: 1px solid #e2e8f0;
    overflow-y: auto;
    flex-shrink: 0;
}

.calls-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* ========== LISTA DE CLÍNICAS ========== */
.calls-clinics-list {
    padding: 20px;
}

.calls-section-title {
    font-size: 16px;
    font-weight: 700;
    color: #6b7280;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.calls-clinic-row {
    background: rgba(255, 255, 255, 0.95); /* Más opaco */
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
    backdrop-filter: blur(10px); /* Añadir blur para mejor legibilidad */
}

.calls-clinic-row:hover {
    background: #f1f5f9;
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.calls-clinic-row.calls-active {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.calls-clinic-meta {
    display: flex;
    align-items: center;
    gap: 12px;
}

.calls-clinic-icon {
    width: 45px;
    height: 45px;
    background: #3b82f6;
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.calls-clinic-info h6 {
    margin: 0;
    font-weight: 700; /* Más bold */
    color: #6b7280 !important;
    font-size: 16px;
}

.calls-clinic-info small {
    color: #acafb4;
    font-size: 13px;
}

/* ========== PANEL DE CONTACTOS ========== */
.calls-contacts-panel {
    background: white;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.calls-contacts-header {
    padding: 10px 15px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.calls-contacts-title {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #6b7280;
}

.calls-contacts-count {
    color: #6b7280;
    font-size: 14px;
}

.calls-local-search {
    padding: 10px 15px;
    border-bottom: 1px solid #f1f5f9;
}

.calls-local-search-input {
    width: 100%;
    padding: 10px 15px 10px 40px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: #f8fafc;
}

.calls-contacts-list {
    flex: 1;
    overflow-y: auto;
    padding: 20px 30px;
}

.calls-contacts-list {
    max-height: 400px; /* o la altura que quieras */
    overflow-y: auto;
    padding-right: 5px; /* para el scrollbar */
}

.calls-contact-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    transition: all 0.3s ease;
}

.calls-contact-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
}

.calls-contact-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.calls-contact-avatar-wrapper {
    position: relative;
}

.calls-contact-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e2e8f0;
}

.calls-contact-status-dot {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid white;
    background: #10b981;
}

.calls-contact-status-dot.busy {
    background: #f59e0b;
}

.calls-contact-info h6 {
    margin: 0 0 4px 0;
    font-weight: 600;
    color: #6b7280;
    font-size: 16px;
}

.calls-contact-role {
    color: #6b7280;
    font-size: 13px;
    margin: 0;
}

.calls-contact-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    margin-top: 6px;
}

.calls-contact-status-badge.available {
    background: #dcfce7;
    color: #166534;
}

.calls-contact-status-badge.busy {
    background: #fef3c7;
    color: #92400e;
}

.calls-contact-btn {
    width: 100%;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.calls-contact-btn.available {
    background: #3b82f6;
    color: white;
}

.calls-contact-btn.available:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.calls-contact-btn:disabled {
    background: #e2e8f0;
    color: #9ca3af;
    cursor: not-allowed;
}

/* ========== ESTADO VACÍO ========== */
.calls-empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #6b7280;
}

.calls-empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.6;
}

.calls-empty-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #374151;
}

.calls-empty-message {
    font-size: 14px;
    margin: 0;
}

/* ========== MODALES DE LLAMADA ========== */
.calls-outgoing-modal,
.calls-incoming-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    z-index: 10000;
    display: none; /* ← SIN !important */
    align-items: center !important;
    justify-content: center !important;
    padding: 20px;
    text-align: center !important;
}

/* Y agregar esta regla para cuando se muestren: */
.calls-outgoing-modal[style*="display: block"],
.calls-incoming-modal[style*="display: block"] {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.calls-incoming-modal .calls-call-avatar {
    animation: calls-heartbeat 1.5s infinite, calls-vibrate 0.5s infinite;
    box-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
}

.calls-outgoing-modal .calls-call-avatar {
    animation: calls-pulse-ring 2s infinite;
    filter: brightness(1.1);
}

.calls-outgoing-modal,
.calls-incoming-modal {
    animation: calls-modal-fade-in 0.5s ease-out;
}

.calls-call-modal-content {
    text-align: center;
    color: white;
    max-width: 400px;
    width: 100%;
}

.calls-call-header {
    margin-bottom: 40px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
}

.calls-call-avatar-section {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
    margin-bottom: 30px;
    width: 100% !important;
}

.calls-call-info {
    text-align: center !important;
    width: 100% !important;
    display: flex;
    flex-direction: column;
    align-items: center !important;
    justify-content: center !important;
}

.calls-call-avatar-wrapper {
    position: relative;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 0 auto 20px auto !important; /* CENTRADO HORIZONTAL */
    width: 120px; /* ANCHO FIJO */
    height: 120px; /* ALTURA FIJA */
}

.calls-call-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255, 255, 255, 0.3);
}

.calls-pulse-rings {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.calls-ring {
    position: absolute;
    border-radius: 50%;
    animation: calls-pulse-ring 2s infinite;
    border: 2px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
}

.calls-call-name {
    animation: calls-text-glow 2s infinite alternate;
}

@keyframes calls-text-glow {
    from {
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
    }
    to {
        text-shadow: 0 0 20px rgba(255, 255, 255, 0.8);
    }
}

.calls-call-btn {
    animation: calls-button-float 3s ease-in-out infinite;
}

@keyframes calls-button-float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}

.calls-call-btn:hover {
    animation: calls-button-float 3s ease-in-out infinite, calls-button-glow 0.5s ease;
}

@keyframes calls-button-glow {
    0% { box-shadow: 0 0 5px rgba(255, 255, 255, 0.5); }
    100% { box-shadow: 0 0 25px rgba(255, 255, 255, 0.8); }
}

.calls-ring-1 {
    width: 140px;
    height: 140px;
    margin: -70px 0 0 -70px;
    animation-delay: 0s;
}

.calls-ring-2 {
    width: 160px;
    height: 160px;
    margin: -80px 0 0 -80px;
    animation-delay: 0.5s;
}

.calls-ring-3 {
    width: 180px;
    height: 180px;
    margin: -90px 0 0 -90px;
    animation-delay: 1s;
}

.calls-wave-rings {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.calls-wave {
    position: absolute;
    border-radius: 50%;
    animation: calls-wave-animation 1.5s infinite;
    border: 3px solid rgba(255, 255, 255, 0.6); /* Más opaco */
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.3); /* Resplandor */
}

.calls-wave-1 {
    width: 140px;
    height: 140px;
    margin: -70px 0 0 -70px;
    animation-delay: 0s;
}

.calls-wave-2 {
    width: 160px;
    height: 160px;
    margin: -80px 0 0 -80px;
    animation-delay: 0.3s;
}

.calls-wave-3 {
    width: 180px;
    height: 180px;
    margin: -90px 0 0 -90px;
    animation-delay: 0.6s;
}

.calls-call-name {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
    text-align: center !important;
    width: 100% !important;
    animation: calls-text-glow 2s infinite alternate;
}

.calls-call-subtitle {
    font-size: 16px;
    opacity: 0.9;
    margin: 0;
}

.calls-timer {
    margin: 15px auto 0 auto !important; /* CENTRADO */
    font-size: 20px;
    font-weight: 600;
    opacity: 0.9;
    background: rgba(255, 255, 255, 0.1);
    padding: 8px 16px;
    border-radius: 20px;
    display: inline-block;
    backdrop-filter: blur(10px);
    text-align: center !important;
}

.calls-call-actions {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 30px;
    width: 100% !important;
    margin: 0 auto !important;
}

.calls-call-actions.calls-two-buttons {
    justify-content: center !important;
    gap: 60px !important; /* MÁS ESPACIO ENTRE BOTONES */
    max-width: 300px;
    margin: 0 auto !important;
}

.calls-call-btn {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    position: relative;
    backdrop-filter: blur(10px);
}

.calls-call-btn span {
    font-size: 12px;
    font-weight: 500;
    margin-top: 4px;
}

.calls-btn-end {
    background: rgba(239, 68, 68, 0.9);
    color: white;
}

.calls-btn-end:hover {
    background: rgba(239, 68, 68, 1);
    transform: scale(1.1);
}

.calls-btn-accept {
    background: rgba(16, 185, 129, 0.9);
    color: white;
}

.calls-btn-accept:hover {
    background: rgba(16, 185, 129, 1);
    transform: scale(1.1);
}

.calls-btn-decline {
    background: rgba(239, 68, 68, 0.9);
    color: white;
}

.calls-btn-decline:hover {
    background: rgba(239, 68, 68, 1);
    transform: scale(1.1);
}

/* ========== NOTIFICACIONES ========== */
.calls-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #3b82f6;
    border-radius: 8px;
    padding: 16px 20px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    z-index: 10000;
    max-width: 350px;
    animation: calls-slide-in 0.4s ease-out;
}

.calls-notification.success {
    border-left-color: #10b981;
}

.calls-notification.error {
    border-left-color: #ef4444;
}

.calls-notification.warning {
    border-left-color: #f59e0b;
}

/* ========== ANIMACIONES ========== */
@keyframes calls-bubble-pulse {
    0%, 100% {
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
    }
    50% {
        box-shadow: 0 8px 35px rgba(37, 99, 235, 0.6);
    }
}

@keyframes calls-bubble-bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-3px);
    }
    60% {
        transform: translateY(-2px);
    }
}

@keyframes calls-pulse-ring {
    0% {
        transform: scale(0.8);
        opacity: 1;
    }
    100% {
        transform: scale(1.2);
        opacity: 0;
    }
}

@keyframes calls-wave-animation {
    0% {
        transform: scale(0.9);
        opacity: 1;
    }
    100% {
        transform: scale(1.1);
        opacity: 0;
    }
}

@keyframes calls-slide-in {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes calls-pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .calls-bubble-wrapper {
        bottom: 20px;
        right: 15px;
    }
    
    .calls-modal-container {
        width: 100%;
        height: 100%;
        max-height: 100vh;
        border-radius: 0;
        margin: 0;
    }
    
    .calls-modal-header {
        padding: 15px 20px;
    }
    
    .calls-modal-title {
        font-size: 18px;
    }
    
    .calls-controls-section {
        padding: 15px 20px;
    }
    
    .calls-search-wrapper {
        flex-direction: column;
        gap: 15px;
    }
    
    .calls-filter-buttons {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .calls-main-layout {
        flex-direction: column;
    }
    
    .calls-sidebar {
        width: 100%;
        max-height: 300px;
    }
    
    .calls-contacts-panel {
        min-height: 400px;
    }
    
    .calls-contacts-header,
    .calls-local-search,
    .calls-contacts-list {
        padding-left: 20px;
        padding-right: 20px;
    }
    
    .calls-call-avatar {
        width: 100px;
        height: 100px;
    }
    
    .calls-call-name {
        font-size: 24px;
    }
    
    .calls-call-btn {
        width: 70px;
        height: 70px;
        font-size: 20px;
    }
}

@media (max-width: 480px) {
    .calls-modal-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .calls-modal-header-left {
        justify-content: center;
    }
    
    .calls-modal-header-right {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .calls-status-section {
        flex-direction: column;
        gap: 10px;
    }
}

/* ========== SOPORTE PARA NOTCH Y SAFE AREAS ========== */
@supports (padding-top: env(safe-area-inset-top)) {
    @media screen and (max-width: 768px) {
        .calls-bubble-wrapper {
            bottom: calc(20px + env(safe-area-inset-bottom, 0));
        }
        
        .calls-modal-container {
            padding-bottom: env(safe-area-inset-bottom, 0);
        }
    }
}

/* ========== ACCESIBILIDAD ========== */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    
    .calls-bubble-icon {
        animation: none;
    }
    
    .calls-pulse-rings,
    .calls-wave-rings {
        display: none;
    }
}

/* ANIMACIÓN DE LATIDO PARA LLAMADA ENTRANTE */
@keyframes calls-heartbeat {
    0% { transform: scale(1); }
    25% { transform: scale(1.05); }
    50% { transform: scale(1.1); }
    75% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* ANIMACIÓN DE VIBRACIÓN SUTIL */
@keyframes calls-vibrate {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
    20%, 40%, 60%, 80% { transform: translateX(2px); }
}

/* ANIMACIÓN DE FADE IN PARA EL MODAL */
@keyframes calls-modal-fade-in {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
/* FORZAR CENTRADO COMPLETO DEL MODAL */
.calls-outgoing-modal,
.calls-incoming-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    z-index: 10000;
    display: none; /* OCULTO POR DEFECTO */
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: calls-modal-fade-in 0.5s ease-out;
}

/* Cuando se muestre el modal */
.calls-outgoing-modal[style*="display: block"],
.calls-incoming-modal[style*="display: block"] {
    display: flex !important;
}

.calls-call-modal-content {
    text-align: center !important;
    color: white;
    max-width: 400px;
    width: 100%;
    margin: 0 auto !important; /* FORZAR CENTRADO */
    display: flex;
    flex-direction: column;
    align-items: center !important;
    justify-content: center !important;
}

.calls-history-header {
    padding: 30px 30px 25px 30px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.calls-history-header h4 {
     margin: 0 0 15px 0;
    font-size: 20px;
    font-weight: 700;
    color: #374151;
}

.calls-history-filters {
    display: flex;
    gap: 15px;
}

.calls-history-filter-select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    color: #374151;
}

.calls-history-search {
    padding: 20px 30px;
    border-bottom: 1px solid #f1f5f9;
}

.calls-history-search-input {
    width: 100%;
    padding: 10px 15px 10px 40px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: #f8fafc;
}

.calls-history-list {
    flex: 1;
    overflow-y: auto;
    padding: 20px 30px;
    max-height: 400px;
}

.calls-history-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
}

.calls-history-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.calls-history-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.calls-history-avatar-wrapper {
    position: relative;
}

.calls-history-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.calls-history-direction-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 10px;
    border: 2px solid white;
}

.calls-history-info h6 {
    margin: 0 0 4px 0;
    font-weight: 600;
    color: #374151;
    font-size: 15px;
}

.calls-history-meta {
    display: flex;
    gap: 8px;
    margin-bottom: 4px;
}

.calls-history-date {
    color: #6b7280;
    font-size: 13px;
}

.calls-history-type {
    color: #9ca3af;
    font-size: 12px;
    text-transform: uppercase;
    font-weight: 500;
}

.calls-history-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 500;
}

.calls-history-duration {
    color: #6b7280;
    margin-left: 4px;
}

.calls-history-actions {
    display: flex;
    gap: 8px;
}

.calls-history-action-btn {
    width: 36px;
    height: 36px;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #6b7280;
}

.calls-history-action-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
    background: #f8fafc;
}

.calls-history-empty {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.calls-history-empty h5 {
    margin: 0 0 8px 0;
    color: #374151;
}

.calls-history-load-more {
    text-align: center;
    padding: 20px;
}

.calls-load-more-btn {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 12px 24px;
    border-radius: 8px;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
}

.calls-load-more-btn:hover {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}
.popup-permission-modal {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.popup-instructions {
    text-align: left;
}

.browser-instructions {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 15px 0;
}

.browser-instructions h6 {
    color: #495057;
    margin: 15px 0 10px 0;
    font-weight: 600;
}

.browser-instructions h6:first-child {
    margin-top: 0;
}

.browser-instructions ol {
    margin: 0 0 15px 20px;
    padding: 0;
}

.browser-instructions li {
    margin: 5px 0;
    color: #6c757d;
}

.alert-info {
    background-color: #e7f3ff;
    border: 1px solid #b8daff;
    color: #31708f;
    padding: 12px;
    border-radius: 6px;
}
/* ========== MODAL DE PERMISOS DE POPUP ========== */
.popup-permission-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10001;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    
}

.popup-permission-content {
    background: white;
    border-radius: 16px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
    animation: popup-modal-appear 0.3s ease-out;
}

@keyframes popup-modal-appear {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(30px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.popup-permission-header {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 24px 30px;
    border-radius: 16px 16px 0 0;
    text-align: center;
}

.popup-permission-icon {
    font-size: 48px;
    margin-bottom: 12px;
    display: block;
}

.popup-permission-title {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.popup-permission-subtitle {
    font-size: 16px;
    opacity: 0.9;
    margin: 0;
}

.popup-permission-body {
    padding: 30px;
}

.popup-why-section {
    background: #f0f9ff;
    border: 1px solid #0ea5e9;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}

.popup-why-title {
    color: #0c4a6e;
    font-weight: 600;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.popup-why-text {
    color: #0c4a6e;
    margin: 0;
    line-height: 1.5;
}

.popup-browser-instructions {
    margin-bottom: 25px;
}

.popup-browser-title {
    font-size: 18px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 15px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.popup-browser-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
}

.popup-browser-name {
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.popup-steps-list {
    margin: 0;
    padding-left: 20px;
    color: #4b5563;
}

.popup-steps-list li {
    margin: 6px 0;
    line-height: 1.4;
}

.popup-visual-hint {
    background: #fef3c7;
    border: 1px solid #f59e0b;
    border-radius: 8px;
    padding: 12px;
    margin-top: 12px;
    font-size: 14px;
    color: #92400e;
}

.popup-permission-footer {
    padding: 0 30px 30px 30px;
    text-align: center;
}

.popup-test-btn {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 14px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    margin-right: 12px;
    transition: all 0.2s ease;
}

.popup-test-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.popup-close-btn {
    background: #6b7280;
    color: white;
    border: none;
    padding: 14px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.popup-close-btn:hover {
    background: #4b5563;
}

.popup-reload-hint {
    margin-top: 15px;
    font-size: 14px;
    color: #6b7280;
    font-style: italic;
}

@media (max-width: 640px) {
    .popup-permission-content {
        margin: 10px;
        max-width: calc(100% - 20px);
    }
    
    .popup-permission-header,
    .popup-permission-body,
    .popup-permission-footer {
        padding: 20px;
    }
    
    .popup-test-btn,
    .popup-close-btn {
        width: 100%;
        margin: 8px 0;
    }
}

/* Modal específico para popup bloqueado debe tener mayor prioridad */
#popup-blocked-hangup-modal {
    z-index: 10002 !important;
}

#popup-blocked-hangup-modal[style*="display: block"] {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

/* Tarjeta compacta de contacto */
.calls-contact-card-mini {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 12px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
}

.calls-contact-card-mini:hover {
    border-color: #3b82f6;
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.15);
}

.calls-contact-avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.calls-contact-avatar-mini {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}

.calls-contact-dot {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid white;
}

.calls-contact-dot.available { background: #10b981; }
.calls-contact-dot.busy { background: #f59e0b; }
.calls-contact-dot.offline { background: #6b7280; }

.calls-contact-info-mini {
    flex: 1;
    margin-left: 10px;
}

.calls-contact-info-mini h6 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.calls-contact-info-mini small {
    font-size: 12px;
    color: #6b7280;
}

.calls-contact-btn-mini {
    border: none;
    background: #e2e8f0;
    color: #374151;
    border-radius: 8px;
    padding: 8px 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.calls-contact-btn-mini.available {
    background: #3b82f6;
    color: white;
}

.calls-contact-btn-mini.available:hover {
    background: #2563eb;
}

.calls-contact-btn-mini:disabled {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
}
/* Botón más grande */
.calls-contact-btn-big {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: none;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.calls-contact-btn-big.available {
    background: #3b82f6;
    color: white;
    box-shadow: 0 3px 8px rgba(59, 130, 246, 0.4);
}

.calls-contact-btn-big.available:hover {
    background: #2563eb;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.6);
}

.calls-contact-btn-big.offline {
    background: #6b7280;
    color: white;
    cursor: not-allowed;
    opacity: 0.6;
}

.calls-contact-btn-big.offline:hover {
    background: #6b7280;
    transform: none;
    box-shadow: none;
}

.calls-contact-btn-big:disabled {
    background: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}

/* ========== RESPONSIVE PARA MODALES DE LLAMADAS ========== */
/* ========== RESPONSIVE PARA MODALES DE LLAMADAS ========== */

/* Estilos base para móviles */
@media (max-width: 768px) {
    
    /* ========== MODAL PRINCIPAL ========== */
    .calls-modal-overlay {
        padding: 10px;
        align-items: flex-start;
        padding-top: 20px;
    }
    
    .calls-modal-container {
        width: 100%;
        max-width: 100%;
        height: calc(100vh - 40px);
        max-height: calc(100vh - 40px);
        border-radius: 15px;
    }
    
    /* ========== HEADER RESPONSIVO ========== */
    .calls-modal-header {
        padding: 15px 20px;
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .calls-modal-header-left {
        justify-content: center;
        order: 1;
    }
    
    .calls-modal-header-right {
        justify-content: center;
        flex-wrap: wrap;
        order: 2;
        gap: 10px;
    }
    
    .calls-modal-logo {
        width: 40px;
        height: 40px;
    }
    
    .calls-modal-title {
        font-size: 20px;
    }
    
    .calls-modal-subtitle {
        font-size: 13px;
    }
    
    .calls-status-section {
        flex-direction: column;
        gap: 10px;
    }
    
    /* ========== CONTROLES RESPONSIVOS ========== */
    .calls-controls-section {
        padding: 12px 15px;
    }
    
    .calls-search-wrapper {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    /* Contenedor para el buscador y los filtros en la misma línea */
    .calls-search-and-filters {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .calls-search-input-wrapper {
        flex: 1;
        position: relative;
        min-width: 0; /* Para que funcione flex-shrink */
    }
    
    .calls-filter-buttons {
        display: flex;
        gap: 6px;
        flex-shrink: 0; /* No se encoge */
    }
    
    .calls-filter-btn {
        padding: 8px 12px;
        font-size: 12px;
        white-space: nowrap;
        border-radius: 20px;
        min-width: auto;
    }
    
    /* Ocultar elementos del header en móvil */
    .calls-status-display,
    .calls-status-selector {
        display: none !important;
    }
    
    /* ========== LAYOUT PRINCIPAL RESPONSIVO ========== */
    .calls-main-layout {
        flex-direction: column;
        height: 100%;
    }
    
    .calls-sidebar {
        width: 100%;
        max-height: 250px;
        flex-shrink: 0;
        border-right: none;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .calls-content {
        flex: 1;
        min-height: 300px;
    }
    
    /* ========== SIDEBAR MÓVIL ========== */
    .calls-clinics-list {
        padding: 15px;
    }
    
    .calls-clinic-row {
        padding: 12px;
        margin-bottom: 8px;
    }
    
    .calls-clinic-icon {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }
    
    .calls-clinic-info h6 {
        font-size: 14px;
    }
    
    .calls-clinic-info small {
        font-size: 12px;
    }
    
    /* ========== CONTACTOS RESPONSIVOS ========== */
    .calls-contacts-header {
        padding: 12px 15px;
    }
    
    .calls-contacts-title {
        font-size: 16px;
    }
    
    .calls-local-search {
        padding: 10px 15px;
    }
    
    .calls-contacts-list {
        padding: 15px;
        max-height: calc(100vh - 450px);
    }
    
    .calls-contact-card-mini {
        padding: 10px 12px;
        margin-bottom: 8px;
    }
    
    .calls-contact-avatar-mini {
        width: 35px;
        height: 35px;
    }
    
    .calls-contact-dot {
        width: 8px;
        height: 8px;
    }
    
    .calls-contact-info-mini h6 {
        font-size: 13px;
    }
    
    .calls-contact-info-mini small {
        font-size: 11px;
    }
    
    .calls-contact-btn-big {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    /* ========== ESTADO VACÍO RESPONSIVO ========== */
    .calls-empty-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }
    
    .calls-empty-title {
        font-size: 16px;
    }
    
    .calls-empty-message {
        font-size: 13px;
    }
}

/* ========== MODALES DE LLAMADA RESPONSIVOS ========== */
@media (max-width: 768px) {
    
    /* Modal de llamada saliente/entrante */
    .calls-outgoing-modal,
    .calls-incoming-modal {
        padding: 15px;
    }
    
    .calls-call-modal-content {
        max-width: 100%;
        width: 100%;
    }
    
    .calls-call-header {
        margin-bottom: 30px;
    }
    
    .calls-call-avatar-wrapper {
        width: 100px;
        height: 100px;
        margin-bottom: 15px;
    }
    
    .calls-call-avatar {
        width: 100px;
        height: 100px;
    }
    
    /* Anillos de pulso más pequeños */
    .calls-ring-1 {
        width: 120px;
        height: 120px;
        margin: -60px 0 0 -60px;
    }
    
    .calls-ring-2 {
        width: 140px;
        height: 140px;
        margin: -70px 0 0 -70px;
    }
    
    .calls-ring-3 {
        width: 160px;
        height: 160px;
        margin: -80px 0 0 -80px;
    }
    
    /* Ondas más pequeñas */
    .calls-wave-1 {
        width: 120px;
        height: 120px;
        margin: -60px 0 0 -60px;
    }
    
    .calls-wave-2 {
        width: 140px;
        height: 140px;
        margin: -70px 0 0 -70px;
    }
    
    .calls-wave-3 {
        width: 160px;
        height: 160px;
        margin: -80px 0 0 -80px;
    }
    
    .calls-call-name {
        font-size: 22px;
        margin-bottom: 6px;
    }
    
    .calls-call-subtitle {
        font-size: 14px;
    }
    
    .calls-timer {
        font-size: 16px;
        padding: 6px 12px;
        margin-top: 10px;
    }
    
    .calls-call-actions {
        gap: 20px;
    }
    
    .calls-call-actions.calls-two-buttons {
        gap: 40px;
    }
    
    .calls-call-btn {
        width: 70px;
        height: 70px;
        font-size: 20px;
    }
    
    .calls-call-btn span {
        font-size: 11px;
        margin-top: 3px;
    }
}

    /* Reorganizar estructura de controles en móvil */
    .calls-controls-section .calls-search-wrapper {
        display: block;
    }
    
    /* Reorganizar HTML: mover filtros junto al buscador */
    .calls-search-wrapper {
        gap: 0;
    }
    
    /* Primera línea: Buscador + Filtros principales */
    .calls-search-and-filters-row {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .calls-search-and-filters-row .calls-search-input-wrapper {
        flex: 1;
    }
    
    .calls-search-and-filters-row .calls-filter-buttons {
        flex-shrink: 0;
        gap: 4px;
    }
    
    .calls-search-and-filters-row .calls-filter-btn {
        padding: 6px 10px;
        font-size: 11px;
        border-radius: 15px;
    }
    
    /* Solo mostrar Todos e Historial en móvil */
    .calls-filter-btn[data-filter="available"],
    .calls-filter-btn[data-filter="busy"] {
        display: none !important;
    }
    
    /* Estilos para los filtros principales */
    .calls-filter-btn[data-filter="all"],
    .calls-filter-btn[data-filter="history"] {
        display: flex !important;
        align-items: center;
        gap: 4px;
    }
    
    .calls-filter-btn[data-filter="all"] i,
    .calls-filter-btn[data-filter="history"] i {
        font-size: 10px;
    }

/* ========== MÓVILES PEQUEÑOS ========== */
@media (max-width: 480px) {
    
    /* Modal principal */
    .calls-modal-overlay {
        padding: 5px;
        padding-top: 10px;
    }
    
    .calls-modal-container {
        height: calc(100vh - 20px);
        max-height: calc(100vh - 20px);
        border-radius: 10px;
    }
    
    .calls-modal-header {
        padding: 12px 15px;
    }
    
    .calls-modal-title {
        font-size: 18px;
    }
    
    .calls-controls-section {
        padding: 10px 12px;
    }
    
    .calls-filter-btn {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .calls-sidebar {
        max-height: 200px;
    }
    
    .calls-clinics-list {
        padding: 12px;
    }
    
    .calls-clinic-row {
        padding: 10px;
    }
    
    .calls-contacts-list {
        padding: 12px;
        max-height: calc(100vh - 380px);
    }
    
    /* Modales de llamada en móviles pequeños */
    .calls-outgoing-modal,
    .calls-incoming-modal {
        padding: 10px;
    }
    
    .calls-call-avatar-wrapper {
        width: 80px;
        height: 80px;
        margin-bottom: 12px;
    }
    
    .calls-call-avatar {
        width: 80px;
        height: 80px;
    }
    
    .calls-ring-1 {
        width: 100px;
        height: 100px;
        margin: -50px 0 0 -50px;
    }
    
    .calls-ring-2 {
        width: 120px;
        height: 120px;
        margin: -60px 0 0 -60px;
    }
    
    .calls-ring-3 {
        width: 140px;
        height: 140px;
        margin: -70px 0 0 -70px;
    }
    
    .calls-wave-1 {
        width: 100px;
        height: 100px;
        margin: -50px 0 0 -50px;
    }
    
    .calls-wave-2 {
        width: 120px;
        height: 120px;
        margin: -60px 0 0 -60px;
    }
    
    .calls-wave-3 {
        width: 140px;
        height: 140px;
        margin: -70px 0 0 -70px;
    }
    
    .calls-call-name {
        font-size: 20px;
    }
    
    .calls-call-subtitle {
        font-size: 13px;
    }
    
    .calls-timer {
        font-size: 14px;
        padding: 5px 10px;
    }
    
    .calls-call-actions {
        gap: 15px;
    }
    
    .calls-call-actions.calls-two-buttons {
        gap: 30px;
    }
    
    .calls-call-btn {
        width: 60px;
        height: 60px;
        font-size: 18px;
    }
    
    .calls-call-btn span {
        font-size: 10px;
        margin-top: 2px;
    }
}

/* ========== BURBUJA FLOTANTE RESPONSIVA ========== */
@media (max-width: 768px) {
    .calls-bubble-wrapper {
        bottom: 20px;
        right: 15px;
    }
    
    .calls-bubble-icon {
        width: 55px;
        height: 55px;
    }
    
    .calls-bubble-phone-icon {
        font-size: 22px;
    }
    
    .calls-bubble-notification-dot {
        width: 18px;
        height: 18px;
        font-size: 11px;
    }
    
    .calls-bubble-status-indicator {
        width: 14px;
        height: 14px;
        border: 2px solid white;
    }
}

/* ========== NOTIFICACIONES RESPONSIVAS ========== */
@media (max-width: 768px) {
    .calls-notification {
        top: 15px;
        right: 15px;
        left: 15px;
        max-width: none;
        padding: 14px 16px;
        font-size: 14px;
    }
}

/* ========== MODAL DE PERMISOS POPUP RESPONSIVO ========== */
@media (max-width: 768px) {
    .popup-permission-modal {
        padding: 10px;
    }
    
    .popup-permission-content {
        max-width: 100%;
        margin: 0;
        border-radius: 12px;
    }
    
    .popup-permission-header {
        padding: 20px;
    }
    
    .popup-permission-title {
        font-size: 20px;
    }
    
    .popup-permission-subtitle {
        font-size: 14px;
    }
    
    .popup-permission-body {
        padding: 20px;
    }
    
    .popup-permission-footer {
        padding: 0 20px 20px 20px;
    }
    
    .popup-test-btn,
    .popup-close-btn {
        width: 100%;
        margin: 8px 0;
        padding: 12px 20px;
    }
    
    .popup-browser-card {
        padding: 15px;
    }
    
    .popup-steps-list {
        padding-left: 15px;
    }
}

/* ========== HISTORIAL RESPONSIVO ========== */
@media (max-width: 768px) {
    .calls-history-header {
        padding: 20px 15px;
        flex-direction: column;
        gap: 15px;
    }
    
    .calls-history-filters {
        gap: 10px;
        display:none !important;
    }
    
    
    .calls-history-filter-select {
        font-size: 13px;
        padding: 6px 10px;
    }
    
    .calls-history-search {
        padding: 15px;
    }
    
    .calls-history-list {
        padding: 15px;
        max-height: calc(100vh - 350px);
    }
    
    .calls-history-card {
        padding: 12px;
        flex-direction: column;
        gap: 12px;
    }
    
    .calls-history-card-header {
        width: 100%;
    }
    
    .calls-history-actions {
        align-self: flex-end;
    }
    
    .calls-history-avatar {
        width: 35px;
        height: 35px;
    }
    
    .calls-history-direction-badge {
        width: 16px;
        height: 16px;
    }
    
    .calls-history-info h6 {
        font-size: 14px;
    }
    
    .calls-history-meta {
        flex-direction: column;
        gap: 4px;
    }
    
    .calls-history-date,
    .calls-history-type {
        font-size: 12px;
    }
    
    .calls-history-status {
        font-size: 12px;
    }
}

/* ========== ORIENTACIÓN LANDSCAPE EN MÓVILES ========== */
@media (max-width: 768px) and (orientation: landscape) {
    .calls-modal-container {
        height: calc(100vh - 20px);
        max-height: calc(100vh - 20px);
    }
    
    .calls-sidebar {
        max-height: 180px;
    }
    
    .calls-contacts-list {
        max-height: calc(100vh - 280px);
    }
    
    /* Modales de llamada en landscape */
    .calls-call-header {
        margin-bottom: 20px;
    }
    
    .calls-call-avatar-wrapper {
        width: 70px;
        height: 70px;
        margin-bottom: 10px;
    }
    
    .calls-call-avatar {
        width: 70px;
        height: 70px;
    }
    
    .calls-call-name {
        font-size: 18px;
    }
    
    .calls-call-btn {
        width: 55px;
        height: 55px;
        font-size: 16px;
    }
}

/* ========== SOPORTE PARA SAFE AREAS (iPhone X+) ========== */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    @media (max-width: 768px) {
        .calls-bubble-wrapper {
            bottom: calc(20px + env(safe-area-inset-bottom, 0));
            right: calc(15px + env(safe-area-inset-right, 0));
        }
        
        .calls-modal-container {
            margin-bottom: env(safe-area-inset-bottom, 0);
        }
        
        .calls-outgoing-modal,
        .calls-incoming-modal {
            padding-bottom: calc(15px + env(safe-area-inset-bottom, 0));
        }
        
        .calls-notification {
            top: calc(15px + env(safe-area-inset-top, 0));
        }
    }
}

/* ========== MEJORAS PARA TOUCH ========== */
@media (max-width: 768px) {
    /* Aumentar área de toque */
    .calls-clinic-row,
    .calls-contact-card-mini,
    .calls-filter-btn,
    .calls-call-btn,
    .calls-history-action-btn {
        min-height: 44px; /* Mínimo recomendado por Apple */
    }
    
    /* Mejorar feedback táctil */
    .calls-clinic-row:active,
    .calls-contact-card-mini:active,
    .calls-filter-btn:active {
        background-color: #f1f5f9;
        transform: scale(0.98);
    }
    
    .calls-call-btn:active {
        transform: scale(0.95);
    }
}

/* ========== ACCESIBILIDAD EN MÓVILES ========== */
@media (max-width: 768px) and (prefers-reduced-motion: reduce) {
    .calls-call-avatar {
        animation: none;
    }
    
    .calls-pulse-rings,
    .calls-wave-rings {
        display: none;
    }
    
    .calls-modal-container {
        animation: none;
        transform: none;
    }
    
    .calls-notification {
        animation: none;
        transform: none;
    }
}

/* ========== MODAL DE ERROR DE CONEXIÓN ========== */
.calls-error-icon {
    position: relative;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(239, 68, 68, 0.1);
    border-radius: 50%;
    margin: 0 auto 20px;
}

.connection-error-icon {
    font-size: 32px;
    color: #ef4444;
    z-index: 2;
}

.connection-error-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.2);
    animation: error-pulse 2s infinite;
}

@keyframes error-pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(1.4);
        opacity: 0;
    }
}

.connection-error-instructions {
    margin: 20px 0;
    padding: 20px;
    background: #f8fafc;
    border-radius: 12px;
    border-left: 4px solid #3b82f6;
}

.error-instruction-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.error-instruction-item:last-child {
    margin-bottom: 0;
}

.error-instruction-item i {
    width: 20px;
    text-align: center;
    font-size: 16px;
}

.text-danger { color: #ef4444; }
.text-primary { color: #3b82f6; }
.text-success { color: #10b981; }

/* Asegurar que todos los textos del modal de error sean legibles */
#connection-error-modal .calls-call-name {
    color: #ffffff !important;
}

#connection-error-modal .calls-call-subtitle {
    color: #ffffff !important;
}

#connection-error-modal .error-instruction-item span {
    color: #374151 !important;
}

.calls-btn-secondary {
    background: #6b7280;
    color: white;
}

.calls-btn-secondary:hover {
    background: #4b5563;
}

.calls-btn-primary {
    background: #3b82f6;
    color: white;
}

.calls-btn-primary:hover {
    background: #2563eb;
}
    
    .connection-error-instructions {
        margin: 15px 0;
        padding: 15px;
    }
    
    .error-instruction-item {
        font-size: 13px;
    }
    
    .calls-error-icon {
        width: 60px;
        height: 60px;
        margin-bottom: 15px;
    }
    
    .connection-error-icon {
        font-size: 24px;
    }
}

/* ========== MODALES DE ESTADO OCUPADO ========== */
.calls-busy-icon {
    position: relative;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(245, 158, 11, 0.1);
    border-radius: 50%;
    margin: 0 auto 20px;
}

.busy-status-icon {
    font-size: 32px;
    color: #f59e0b;
    z-index: 2;
}

.recipient-busy-icon {
    font-size: 32px;
    color: #ef4444;
    z-index: 2;
}

.busy-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(245, 158, 11, 0.2);
    animation: busy-pulse 2s infinite;
}

.recipient-busy-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.2);
    animation: busy-pulse 2s infinite;
}

@keyframes busy-pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(1.4);
        opacity: 0;
    }
}

.busy-user-instructions,
.recipient-busy-instructions {
    margin: 20px 0;
    padding: 20px;
    background: #f8fafc;
    border-radius: 12px;
    border-left: 4px solid #f59e0b;
}

.recipient-busy-instructions {
    border-left-color: #ef4444;
}

.busy-instruction-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.busy-instruction-item:last-child {
    margin-bottom: 0;
}

.busy-instruction-item i {
    width: 20px;
    text-align: center;
    font-size: 16px;
}

.text-warning { color: #f59e0b; }

.calls-one-button {
    display: flex;
    justify-content: center;
}

.calls-btn-success {
    background: #10b981;
    color: white;
}

.calls-btn-success:hover {
    background: #059669;
}

/* Asegurar que todos los textos de los modales ocupados sean legibles */
#user-busy-modal .calls-call-name,
#recipient-busy-modal .calls-call-name {
    color: #ffffff !important;
}

#user-busy-modal .calls-call-subtitle,
#recipient-busy-modal .calls-call-subtitle {
    color: #ffffff !important;
}

#user-busy-modal .busy-instruction-item span,
#recipient-busy-modal .busy-instruction-item span {
    color: #374151 !important;
}

/* Adaptaciones responsive para los modales ocupados */
@media (max-width: 768px) {
    .calls-one-button .calls-call-btn {
        width: 100%;
        min-height: 48px;
    }
    
    .busy-user-instructions,
    .recipient-busy-instructions {
        margin: 15px 0;
        padding: 15px;
    }
    
    .busy-instruction-item {
        font-size: 13px;
    }
    
    .calls-busy-icon {
        width: 60px;
        height: 60px;
        margin-bottom: 15px;
    }
    
    .busy-status-icon,
    .recipient-busy-icon {
        font-size: 24px;
    }
}

/* ========== MODAL DE LLAMADAS PERDIDAS ========== */
.missed-calls-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.missed-calls-modal-container {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    position: relative;
    animation: missedCallsModalSlideIn 0.3s ease-out;
}

@keyframes missedCallsModalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.missed-calls-modal-header {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    color: white;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.missed-calls-modal-icon {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.missed-calls-modal-title-section {
    flex: 1;
}

.missed-calls-modal-title {
    margin: 0 0 4px 0;
    font-size: 24px;
    font-weight: 600;
}

.missed-calls-modal-subtitle {
    margin: 0;
    font-size: 14px;
    opacity: 0.9;
}

.missed-calls-modal-body {
    padding: 24px;
    max-height: 400px;
    overflow-y: auto;
}

.missed-calls-loading {
    text-align: center;
    padding: 40px 20px;
}

.missed-calls-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #dc2626;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.missed-calls-loading p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.missed-calls-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.missed-call-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s ease;
}

.missed-call-item:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.missed-call-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #6b7280;
    flex-shrink: 0;
}

.missed-call-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.missed-call-info {
    flex: 1;
    min-width: 0;
}

.missed-call-name {
    font-weight: 600;
    color: #111827;
    margin: 0 0 4px 0;
    font-size: 16px;
}

.missed-call-time {
    color: #6b7280;
    font-size: 14px;
    margin: 0;
}

.missed-call-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.missed-calls-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.missed-calls-btn-primary {
    background: #dc2626;
    color: white;
}

.missed-calls-btn-primary:hover {
    background: #b91c1c;
}

.missed-calls-btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.missed-calls-btn-secondary:hover {
    background: #e5e7eb;
}

.no-missed-calls {
    text-align: center;
    padding: 40px 20px;
}

.no-missed-calls-icon {
    width: 64px;
    height: 64px;
    background: #dcfce7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 32px;
    color: #16a34a;
}

.no-missed-calls h4 {
    margin: 0 0 8px 0;
    color: #111827;
    font-size: 20px;
    font-weight: 600;
}

.no-missed-calls p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.missed-calls-modal-footer {
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: center;
}

#acknowledge-all-btn {
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
}

/* Responsive design */
@media (max-width: 768px) {
    .missed-calls-modal-container {
        width: 95%;
        margin: 20px;
    }
    
    .missed-calls-modal-header {
        padding: 20px;
    }
    
    .missed-calls-modal-title {
        font-size: 20px;
    }
    
    .missed-calls-modal-body {
        padding: 20px;
    }
    
    .missed-call-item {
        padding: 12px;
    }
    
    .missed-call-avatar {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
    
    .missed-call-name {
        font-size: 14px;
    }
    
    .missed-call-time {
        font-size: 12px;
    }
    
    .missed-calls-btn {
        padding: 6px 12px;
        font-size: 12px;
    }
}
</style>
