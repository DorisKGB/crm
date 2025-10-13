<div id="page-content" class="page-wrapper clearfix">
    <div class="container-fluid ghost-container">
        
        <!-- Header Médico Profesional -->
        <div class="medical-header d-none">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="header-content-ghost">
                        <div class="header-text-ghost">
                            <h1 class="ghost-title">
                                <i class="fas fa-stethoscope me-3"></i>
                                Sistema de Comunicación Médica
                            </h1>
                            <p class="ghost-subtitle">
                                Conecta con el personal médico de forma segura y profesional
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="status-ghost-card">
                        <div class="status-ghost-header">
                            <span class="ghost-label">Tu Estado</span>
                        </div>
                        <div class="current-status-ghost">
                            <div id="status-indicator" class="ghost-dot available"></div>
                            <span id="status-text" class="ghost-status-text">Disponible</span>
                        </div>
                        <select id="status-selector" class="input-ghost">
                            <option value="available">Disponible</option>
                            <option value="busy">Ocupado</option>
                            <option value="do_not_disturb">No molestar</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controles de Búsqueda Profesionales -->
        <div class="card-ghost mb-2">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="input-ghost-icon-container">
                        <i class="fas fa-search input-ghost-icon"></i>
                        <input type="text" id="user-search" placeholder="Buscar personal médico por nombre o clínica..." class="input-ghost input-ghost-with-icon">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="btn-ghost btn-ghost-light active" data-filter="all">
                            <i class="fas fa-users me-1"></i> Todos
                        </span>
                        <span class="btn-ghost btn-ghost-light" data-filter="available">
                            <i class="fas fa-circle me-1"></i> Disponibles
                        </span>
                        <span class="btn-ghost btn-ghost-light" data-filter="busy">
                            <i class="fas fa-clock me-1"></i> Ocupados
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Layout Principal Médico -->
        <div class="ghost-split">
            <!-- Lista de Clínicas (Izquierda) -->
            <main class="ghost-main">
                <div class="card-ghost card-ghost-elevated">
                    <div id="clinics-list" class="ghost-clinic-list">
                        
                        <!-- Sección: Clínicas Principales -->
                        <div class="mb-4">
                            <div class="ghost-section-title">
                                <i class="fas fa-hospital me-2 text-primary"></i>
                                Clínicas Principales
                            </div>
                            <div id="section-main">
                                
                                <div class="ghost-clinic-row active" data-kind="main" data-index="0">
                                    <div class="ghost-clinic-meta">
                                        <div class="ghost-clinic-icon">
                                            <i class="fas fa-hospital"></i>
                                        </div>
                                        <div class="clinic-info">
                                            <h6 class="mb-1 fw-bold">Aurora Medical Center</h6>
                                            <small class="text-muted">12 profesionales disponibles</small>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge-ghost badge-ghost-success">
                                            <i class="fas fa-circle me-1"></i> Activa
                                        </span>
                                        <i class="fas fa-chevron-right ms-2 text-muted"></i>
                                    </div>
                                </div>

                                <div class="ghost-clinic-row" data-kind="main" data-index="1">
                                    <div class="ghost-clinic-meta">
                                        <div class="ghost-clinic-icon">
                                            <i class="fas fa-hospital"></i>
                                        </div>
                                        <div class="clinic-info">
                                            <h6 class="mb-1 fw-bold">Baton Rouge Medical</h6>
                                            <small class="text-muted">8 profesionales disponibles</small>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge-ghost badge-ghost-success">
                                            <i class="fas fa-circle me-1"></i> Activa
                                        </span>
                                        <i class="fas fa-chevron-right ms-2 text-muted"></i>
                                    </div>
                                </div>

                                <div class="ghost-clinic-row" data-kind="main" data-index="2">
                                    <div class="ghost-clinic-meta">
                                        <div class="ghost-clinic-icon">
                                            <i class="fas fa-hospital"></i>
                                        </div>
                                        <div class="clinic-info">
                                            <h6 class="mb-1 fw-bold">Bossier City Medical</h6>
                                            <small class="text-muted">15 profesionales disponibles</small>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge-ghost badge-ghost-success">
                                            <i class="fas fa-circle me-1"></i> Activa
                                        </span>
                                        <i class="fas fa-chevron-right ms-2 text-muted"></i>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Sección: Clínicas Aliadas -->
                        <div class="mb-4">
                            <div class="ghost-section-title">
                                <i class="fas fa-handshake me-2 text-info"></i>
                                Clínicas Aliadas
                            </div>
                            <div id="section-ally">
                                
                                <div class="ghost-clinic-row" data-kind="ally" data-index="0">
                                    <div class="ghost-clinic-meta">
                                        <div class="ghost-clinic-icon" style="background: var(--medical-info); color: white;">
                                            <i class="fas fa-hospital"></i>
                                        </div>
                                        <div class="clinic-info">
                                            <h6 class="mb-1 fw-bold">Clínica Cameron Road</h6>
                                            <small class="text-muted">6 profesionales disponibles</small>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge-ghost badge-ghost-info">
                                            <i class="fas fa-handshake me-1"></i> Aliada
                                        </span>
                                        <i class="fas fa-chevron-right ms-2 text-muted"></i>
                                    </div>
                                </div>

                                <div class="ghost-clinic-row" data-kind="ally" data-index="1">
                                    <div class="ghost-clinic-meta">
                                        <div class="ghost-clinic-icon" style="background: var(--medical-info); color: white;">
                                            <i class="fas fa-hospital"></i>
                                        </div>
                                        <div class="clinic-info">
                                            <h6 class="mb-1 fw-bold">Clínica Cedar Park</h6>
                                            <small class="text-muted">4 profesionales disponibles</small>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge-ghost badge-ghost-info">
                                            <i class="fas fa-handshake me-1"></i> Aliada
                                        </span>
                                        <i class="fas fa-chevron-right ms-2 text-muted"></i>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Sección: Personal Administrativo -->
                        <div class="mb-3">
                            <div class="ghost-section-title">
                                <i class="fas fa-users me-2 text-secondary"></i>
                                Personal Administrativo
                            </div>
                            <div id="section-admin">
                                
                                <div class="ghost-clinic-row" data-kind="admin" data-index="0">
                                    <div class="ghost-clinic-meta">
                                        <div class="ghost-clinic-icon" style="background: var(--medical-secondary); color: white;">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div class="clinic-info">
                                            <h6 class="mb-1 fw-bold">Equipo Administrativo</h6>
                                            <small class="text-muted">4 miembros disponibles</small>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge-ghost badge-ghost-secondary">
                                            <i class="fas fa-user-tie me-1"></i> Admin
                                        </span>
                                        <i class="fas fa-chevron-right ms-2 text-muted"></i>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </main>

            <!-- Panel de Contactos (Derecha) -->
            <aside class="ghost-aside">
                
                <!-- Estado Vacío -->
                <div id="clinic-aside-empty" class="ghost-aside-empty d-none">
                    <div class="text-center p-4">
                        <i class="fas fa-hospital-user fa-3x text-muted mb-3" style="opacity:.6;"></i>
                        <h5 class="text-muted">Selecciona una clínica</h5>
                        <p class="text-muted mb-0">Elige una clínica para ver sus contactos médicos</p>
                    </div>
                </div>

                <!-- Panel con Contactos -->
                <div id="clinic-aside-body" class="ghost-aside-body ">
                    
                    <!-- Header del Panel -->
                    <div class="card-ghost-header ">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 id="aside-clinic-title" class="card-ghost-title mb-1">Aurora Medical Center</h4>
                                <small id="aside-count" class="card-ghost-subtitle">12 profesionales médicos</small>
                            </div>
                            <button id="aside-collapse" class="btn-ghost btn-ghost-secondary btn-ghost-sm" type="button">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Búsqueda Local -->
                    <div class="mb-3">
                        <div class="input-ghost-icon-container">
                            <i class="fas fa-search input-ghost-icon"></i>
                            <input id="aside-search" class="input-ghost input-ghost-with-icon input-ghost-sm" placeholder="Buscar en esta clínica...">
                        </div>
                    </div>

                    <!-- Lista de Contactos -->
                    <div id="clinic-contacts" class="ghost-contacts-list">
                        
                        <!-- Contacto 1 - Ocupado -->
                        <div class="card-ghost card-ghost-sm contact-card" data-user-id="1" data-user-status="busy">
                            <div class="d-flex align-items-center mb-3">
                                <div class="position-relative me-3">
                                    <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=100&h=100&fit=crop&crop=face" 
                                         alt="Dr. Héctor Placencia" 
                                         class="rounded-circle"
                                         style="width: 50px; height: 50px; object-fit: cover; border: 3px solid #e3ebf0;">
                                    <div class="position-absolute bottom-0 end-0" 
                                         style="width: 16px; height: 16px; background: #ffc107; border-radius: 50%; border: 3px solid white;"></div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">Dr. Héctor Placencia</h6>
                                    <small class="text-muted d-block">Médico Internista</small>
                                    <span class="badge-ghost badge-ghost-warning badge-ghost-sm mt-1">
                                        <i class="fas fa-clock me-1"></i> En consulta
                                    </span>
                                </div>
                            </div>
                            <button class="btn-ghost btn-ghost-secondary btn-ghost-sm w-100" disabled>
                                <i class="fas fa-clock me-2"></i>
                                No disponible
                            </button>
                        </div>

                        <!-- Contacto 2 - Disponible -->
                        <div class="card-ghost card-ghost-sm contact-card" data-user-id="2" data-user-status="available">
                            <div class="d-flex align-items-center mb-3">
                                <div class="position-relative me-3">
                                    <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=100&h=100&fit=crop&crop=face" 
                                         alt="Dra. María González" 
                                         class="rounded-circle"
                                         style="width: 50px; height: 50px; object-fit: cover; border: 3px solid #e3ebf0;">
                                    <div class="position-absolute bottom-0 end-0" 
                                         style="width: 16px; height: 16px; background: #28a745; border-radius: 50%; border: 3px solid white;"></div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">Dra. María González</h6>
                                    <small class="text-muted d-block">Cardióloga</small>
                                    <span class="badge-ghost badge-ghost-success badge-ghost-sm mt-1">
                                        <i class="fas fa-circle me-1"></i> Disponible
                                    </span>
                                </div>
                            </div>
                            <button class="btn-ghost btn-ghost-success btn-ghost-sm w-100" onclick="initiateCall(2, 'Dra. María González', 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=100&h=100&fit=crop&crop=face')">
                                <i class="fas fa-video me-2"></i>
                                Contactar
                            </button>
                        </div>

                        <!-- Contacto 3 - Disponible -->
                        <div class="card-ghost card-ghost-sm contact-card" data-user-id="3" data-user-status="available">
                            <div class="d-flex align-items-center mb-3">
                                <div class="position-relative me-3">
                                    <img src="https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=100&h=100&fit=crop&crop=face" 
                                         alt="Dr. Carlos Mendoza" 
                                         class="rounded-circle"
                                         style="width: 50px; height: 50px; object-fit: cover; border: 3px solid #e3ebf0;">
                                    <div class="position-absolute bottom-0 end-0" 
                                         style="width: 16px; height: 16px; background: #28a745; border-radius: 50%; border: 3px solid white;"></div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">Dr. Carlos Mendoza</h6>
                                    <small class="text-muted d-block">Pediatra</small>
                                    <span class="badge-ghost badge-ghost-success badge-ghost-sm mt-1">
                                        <i class="fas fa-circle me-1"></i> Disponible
                                    </span>
                                </div>
                            </div>
                            <button class="btn-ghost btn-ghost-success btn-ghost-sm w-100" onclick="initiateCall(3, 'Dr. Carlos Mendoza', 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=100&h=100&fit=crop&crop=face')">
                                <i class="fas fa-video me-2"></i>
                                Videollamada
                            </button>
                        </div>

                        <!-- Contacto 4 - Disponible -->
                        <div class="card-ghost card-ghost-sm contact-card" data-user-id="4" data-user-status="available">
                            <div class="d-flex align-items-center mb-3">
                                <div class="position-relative me-3">
                                    <img src="https://images.unsplash.com/photo-1594824804732-ca8db4394d2a?w=100&h=100&fit=crop&crop=face" 
                                         alt="Dra. Ana Torres" 
                                         class="rounded-circle"
                                         style="width: 50px; height: 50px; object-fit: cover; border: 3px solid #e3ebf0;">
                                    <div class="position-absolute bottom-0 end-0" 
                                         style="width: 16px; height: 16px; background: #28a745; border-radius: 50%; border: 3px solid white;"></div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">Dra. Ana Torres</h6>
                                    <small class="text-muted d-block">Ginecóloga</small>
                                    <span class="badge-ghost badge-ghost-success badge-ghost-sm mt-1">
                                        <i class="fas fa-circle me-1"></i> Disponible
                                    </span>
                                </div>
                            </div>
                            <button class="btn-ghost btn-ghost-success btn-ghost-sm w-100" onclick="initiateCall(4, 'Dra. Ana Torres', 'https://images.unsplash.com/photo-1594824804732-ca8db4394d2a?w=100&h=100&fit=crop&crop=face')">
                                <i class="fas fa-video me-2"></i>
                                Videollamada
                            </button>
                        </div>

                        <!-- Contacto 5 - Enfermera -->
                        <div class="card-ghost card-ghost-sm contact-card" data-user-id="5" data-user-status="available">
                            <div class="d-flex align-items-center mb-3">
                                <div class="position-relative me-3">
                                    <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=100&h=100&fit=crop&crop=face" 
                                         alt="Enf. Laura Martínez" 
                                         class="rounded-circle"
                                         style="width: 50px; height: 50px; object-fit: cover; border: 3px solid #e3ebf0;">
                                    <div class="position-absolute bottom-0 end-0" 
                                         style="width: 16px; height: 16px; background: #28a745; border-radius: 50%; border: 3px solid white;"></div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">Enf. Laura Martínez</h6>
                                    <small class="text-muted d-block">Enfermera Jefe</small>
                                    <span class="badge-ghost badge-ghost-success badge-ghost-sm mt-1">
                                        <i class="fas fa-circle me-1"></i> Disponible
                                    </span>
                                </div>
                            </div>
                            <button class="btn-ghost btn-ghost-success btn-ghost-sm w-100" onclick="initiateCall(5, 'Enf. Laura Martínez', 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=100&h=100&fit=crop&crop=face')">
                                <i class="fas fa-video me-2"></i>
                                Videollamada
                            </button>
                        </div>

                    </div>
                </div>

            </aside>
        </div>

    </div>
</div>

<!-- Modal Ghost - Llamada Saliente -->
<div class="modal fade" id="outgoing-call-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content ghost-modal phone-call">
            <div class="ghost-modal-body call-body">
                <div class="call-hero">
                    <div class="call-avatar-wrap">
                        <div class="ghost-pulse-rings">
                            <div class="ghost-ring ring-1"></div>
                            <div class="ghost-ring ring-2"></div>
                            <div class="ghost-ring ring-3"></div>
                        </div>
                        <img id="calling-user-image" src="" class="ghost-avatar" alt="Usuario">
                    </div>

                    <div class="call-title">
                        <h3 id="calling-user-name" class="ghost-user-name">
                            Llamando… <span class="typing-dots"><b>.</b><b>.</b><b>.</b></span>
                        </h3>
                        <p class="ghost-user-subtitle">Personal médico</p>
                        <div class="ghost-timer"><span id="call-timer">00:00</span></div>
                    </div>
                </div>

                <div class="call-toolbar">
                    <button type="button" class="btn-call btn-end" onclick="hangupCall()">
                        <i class="fas fa-phone-slash"></i>
                        <span>Colgar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ghost - Llamada Entrante -->
<div class="modal fade" id="incoming-call-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content ghost-modal phone-call">
            <div class="ghost-modal-body call-body">
                <div class="call-hero">
                    <div class="call-avatar-wrap">
                        <div class="ghost-wave-rings">
                            <div class="ghost-wave wave-1"></div>
                            <div class="ghost-wave wave-2"></div>
                            <div class="ghost-wave wave-3"></div>
                        </div>
                        <img id="incoming-user-image" src="" class="ghost-avatar" alt="Usuario">
                    </div>

                    <div class="call-title">
                        <h3 id="incoming-user-name" class="ghost-user-name">Llamada entrante</h3>
                        <p class="ghost-user-subtitle">Desea conectarse contigo</p>
                    </div>
                </div>

                <div class="call-toolbar two">
                    <button type="button" class="btn-call btn-decline" onclick="rejectCall()">
                        <i class="fas fa-times"></i>
                        <span>Rechazar</span>
                    </button>
                    <button type="button" class="btn-call btn-accept" onclick="acceptCall()">
                        <i class="fas fa-phone"></i>
                        <span>Contestar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Información Ghost -->
<div class="modal fade" id="info-call-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ghost-modal ghost-info-modal">
            <div class="ghost-modal-body ghost-info-body">
                <div class="ghost-info-header">
                    <div class="ghost-info-icon-container">
                        <div id="info-icon" class="ghost-info-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                    </div>
                    <button type="button" class="btn-close ghost-close-btn" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="ghost-info-content">
                    <h3 id="info-title" class="ghost-info-title">Información</h3>
                    <p id="info-message" class="ghost-info-message">Mensaje informativo</p>
                    
                    <div class="ghost-info-actions">
                        <button type="button" class="btn-ghost btn-ghost-primary" data-bs-dismiss="modal">
                            <i class="fas fa-check me-2"></i>
                            Entendido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    load_css([
        'assets/css/modules/sistema_llamada.css',
    ]);
    ?>

<script>
// ========== SISTEMA DE LLAMADAS BURBUJA - JAVASCRIPT COMPLETO ==========
// Sistema completamente funcional y sin errores

// Configuración global del sistema
window.CallsBubbleSystem = {
    // Variables del sistema
    currentCallId: null,
    incomingCallId: null,
    modalOpen: false,
    data: { clinics: [], administrative_staff: [] },
    selected: { type: null, index: null },
    filterActive: 'all',
    audioContext: null,
    ringTone: null,
    dialTone: null,
    audioUnlocked: false,
    callTimer: null,
    callStartTime: null,
    
    // Configuración (se llenará desde PHP)
    config: {
        userId: null,
        apiEndpoints: {},
        audioFiles: {},
        texts: {}
    },

    // ========== INICIALIZACIÓN ==========
    init: function() {
        console.log('🔄 Inicializando sistema de llamadas burbuja...');
        
        this.setupEventListeners();
        this.initializeAudio();
        this.loadUsers();
        this.checkUserStatus();
        this.startIncomingCallsCheck();
        this.setupAudioUnlock();
        
        // Verificar disponibilidad cada 30 segundos
        setInterval(() => this.updateUserAvailability(), 30000);
        
        console.log('✅ Sistema de llamadas burbuja inicializado');
    },

    // ========== CONFIGURACIÓN DE EVENTOS ==========
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
                document.querySelectorAll('.calls-filter-btn').forEach(b => b.classList.remove('calls-active'));
                this.classList.add('calls-active');
                
                const filter = this.getAttribute('data-filter');
                self.filterActive = filter;
                self.applyFilter(filter);
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

        // Gestión de visibilidad de página
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                self.checkIncomingCalls();
                self.checkUserStatus();
            }
        });
    },

    // ========== GESTIÓN DE AUDIO ==========
    initializeAudio: function() {
        try {
            this.ringTone = new Audio(this.config.audioFiles.ringTone);
            this.ringTone.loop = true;
            this.ringTone.volume = 0.4;
            this.ringTone.preload = 'auto';
            
            this.dialTone = new Audio(this.config.audioFiles.dialTone);
            this.dialTone.loop = true;
            this.dialTone.volume = 0.3;
            this.dialTone.preload = 'auto';
        } catch (error) {
            console.warn('Archivos de audio no disponibles:', error);
        }
    },

    setupAudioUnlock: function() {
        const self = this;
        
        // Mostrar banner de activación de sonido
        this.showEnableSoundBanner();
        
        // Desbloquear audio en primer gesto del usuario
        const unlockAudio = () => {
            if (self.audioUnlocked) return;
            
            // Reproducir en silencio para desbloquear
            [self.ringTone, self.dialTone].forEach(audio => {
                if (audio) {
                    const prevVol = audio.volume;
                    audio.volume = 0.0001;
                    const promise = audio.play();
                    if (promise && promise.catch) promise.catch(() => {});
                    setTimeout(() => {
                        try { 
                            audio.pause(); 
                            audio.currentTime = 0; 
                            audio.volume = prevVol; 
                        } catch(e) {}
                    }, 150);
                }
            });
            
            self.audioUnlocked = true;
            self.hideEnableSoundBanner();
            
            // Remover listeners
            document.removeEventListener('click', unlockAudio);
            document.removeEventListener('touchstart', unlockAudio);
            document.removeEventListener('keydown', unlockAudio);
        };
        
        document.addEventListener('click', unlockAudio);
        document.addEventListener('touchstart', unlockAudio);
        document.addEventListener('keydown', unlockAudio);
    },

    showEnableSoundBanner: function() {
        if (document.getElementById('enable-sound-banner')) return;
        
        const banner = document.createElement('div');
        banner.id = 'enable-sound-banner';
        banner.style.cssText = `
            position: fixed; bottom: 20px; left: 20px; z-index: 9999;
            background: #111; color: #fff; padding: 10px 14px; border-radius: 8px;
            box-shadow: 0 6px 16px rgba(0,0,0,.25); display: flex; gap: 10px; align-items: center;
        `;
        banner.innerHTML = `
            <span>🔔 Activa el sonido de llamadas</span>
            <button id="enable-sound-btn" style="
                background: #10b981; color: #fff; border: none; padding: 6px 10px; 
                border-radius: 6px; cursor: pointer;">Activar</button>
        `;
        
        document.body.appendChild(banner);
        
        document.getElementById('enable-sound-btn').addEventListener('click', () => {
            this.audioUnlocked = true;
            this.hideEnableSoundBanner();
        });
    },

    hideEnableSoundBanner: function() {
        const banner = document.getElementById('enable-sound-banner');
        if (banner) banner.remove();
    },

    startDialTone: function() {
        if (!this.dialTone || !this.audioUnlocked) return;
        try { 
            this.dialTone.currentTime = 0; 
            this.dialTone.play().catch(() => {}); 
        } catch(e) {}
    },

    startRingTone: function() {
        if (!this.ringTone || !this.audioUnlocked) return;
        try {
            this.ringTone.currentTime = 0;
            const promise = this.ringTone.play();
            if (promise && promise.catch) {
                promise.catch(() => this.showEnableSoundBanner());
            }
        } catch(e) {
            this.showEnableSoundBanner();
        }
    },

    stopAllTones: function() {
        try {
            if (this.dialTone) {
                this.dialTone.pause();
                this.dialTone.currentTime = 0;
            }
            if (this.ringTone) {
                this.ringTone.pause();
                this.ringTone.currentTime = 0;
            }
        } catch(e) {}
    },

    // ========== GESTIÓN DE USUARIOS ==========
    loadUsers: function() {
        const self = this;
        this.showLoadingSkeleton();
        
        fetch(this.config.apiEndpoints.getUsers, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
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

    displayUsers: function(data) {
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
    },

    isAlliedClinic: function(name = '') {
        const normalized = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        return normalized.includes('clinica');
    },

    renderClinics: function({ main, allied, adminCount }) {
        const container = document.getElementById('calls-clinics-list');
        if (!container) return;
        
        container.innerHTML = '';
        
        // Sección clínicas principales
        if (main.length > 0) {
            container.innerHTML += `
                <div class="calls-section-title">
                    <i class="fas fa-hospital" style="color: #3b82f6;"></i>
                    Clínicas Principales
                </div>
            `;
            
            main.forEach((clinic, index) => {
                container.innerHTML += this.createClinicRow(clinic, 'main', index);
            });
        }
        
        // Sección clínicas aliadas
        if (allied.length > 0) {
            container.innerHTML += `
                <div class="calls-section-title" style="margin-top: 25px;">
                    <i class="fas fa-handshake" style="color: #10b981;"></i>
                    Clínicas Aliadas
                </div>
            `;
            
            allied.forEach((clinic, index) => {
                container.innerHTML += this.createClinicRow(clinic, 'allied', index);
            });
        }
        
        // Sección personal administrativo
        if (adminCount > 0) {
            container.innerHTML += `
                <div class="calls-section-title" style="margin-top: 25px;">
                    <i class="fas fa-users" style="color: #f59e0b;"></i>
                    Personal Administrativo
                </div>
                <div class="calls-clinic-row" data-kind="admin" data-index="0" onclick="CallsBubbleSystem.selectClinic('admin', 0)">
                    <div class="calls-clinic-meta">
                        <div class="calls-clinic-icon" style="background: #f59e0b;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="calls-clinic-info">
                            <h6>Personal Administrativo</h6>
                            <small>${adminCount} contactos disponibles</small>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-chevron-right" style="color: #9ca3af;"></i>
                    </div>
                </div>
            `;
        }
    },

    createClinicRow: function(clinic, kind, index) {
        return `
            <div class="calls-clinic-row" data-kind="${kind}" data-index="${index}" onclick="CallsBubbleSystem.selectClinic('${kind}', ${index})">
                <div class="calls-clinic-meta">
                    <div class="calls-clinic-icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <div class="calls-clinic-info">
                        <h6>${clinic.clinic_name}</h6>
                        <small>${clinic.users.length} contactos disponibles</small>
                    </div>
                </div>
                <div>
                    <i class="fas fa-chevron-right" style="color: #9ca3af;"></i>
                </div>
            </div>
        `;
    },

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
            title = 'Personal Administrativo';
            users = this.data.administrative_staff || [];
        }
        
        this.showContacts(title, users);
    },

    showContacts: function(title, users) {
        const emptyState = document.getElementById('calls-empty-state');
        const contactsContainer = document.getElementById('calls-contacts-container');
        const contactsTitle = document.getElementById('calls-contacts-title');
        const contactsCount = document.getElementById('calls-contacts-count');
        const contactsList = document.getElementById('calls-contacts-list');
        
        if (emptyState) emptyState.style.display = 'none';
        if (contactsContainer) contactsContainer.style.display = 'block';
        if (contactsTitle) contactsTitle.textContent = title;
        if (contactsCount) contactsCount.textContent = `${users.length} profesionales`;
        
        if (contactsList) {
            contactsList.innerHTML = users.map(user => this.createContactCard(user)).join('');
        }
        
        // Aplicar filtro activo
        setTimeout(() => this.applyFilter(this.filterActive), 100);
    },

    createContactCard: function(user) {
        const statusClass = user.available ? 'available' : 'busy';
        const statusText = user.available ? 'Disponible' : 'En consulta';
        
        return `
            <div class="calls-contact-card" data-user-id="${user.id}" data-user-status="${user.status}">
                <div class="calls-contact-header">
                    <div class="calls-contact-avatar-wrapper">
                        <img src="${user.image}" alt="${user.name}" class="calls-contact-avatar">
                        <div class="calls-contact-status-dot ${statusClass}"></div>
                    </div>
                    <div class="calls-contact-info">
                        <h6>${user.name}</h6>
                        <p class="calls-contact-role">${user.user_type === 'medical' ? 'Personal Médico' : 'Staff Administrativo'}</p>
                        <span class="calls-contact-status-badge ${statusClass}">
                            <i class="fas fa-${user.available ? 'circle' : 'clock'}"></i>
                            ${statusText}
                        </span>
                    </div>
                </div>
                <button class="calls-contact-btn ${statusClass}" 
                        onclick="CallsBubbleSystem.initiateCall(${user.id}, '${user.name}', '${user.image}')"
                        ${!user.available ? 'disabled' : ''}>
                    <i class="fas fa-${user.available ? 'phone' : 'clock'}"></i>
                    ${user.available ? 'Contactar' : 'No disponible'}
                </button>
            </div>
        `;
    },

    showEmptyState: function() {
        const emptyState = document.getElementById('calls-empty-state');
        const contactsContainer = document.getElementById('calls-contacts-container');
        
        if (emptyState) emptyState.style.display = 'block';
        if (contactsContainer) contactsContainer.style.display = 'none';
    },

    // ========== FILTROS Y BÚSQUEDA ==========
    filterUsers: function(query) {
        query = (query || '').trim().toLowerCase();
        const cards = document.querySelectorAll('#calls-contacts-list .calls-contact-card');
        
        if (!cards.length) return;
        
        if (!query) {
            cards.forEach(c => c.style.display = '');
            this.applyFilter(this.filterActive);
            return;
        }
        
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(query) ? '' : 'none';
        });
    },

    filterLocalContacts: function(query) {
        this.filterUsers(query);
    },

    applyFilter: function(filter) {
        const cards = document.querySelectorAll('.calls-contact-card');
        cards.forEach(card => {
            const isAvailable = 
                !!card.querySelector('.calls-contact-status-dot.available') ||
                (card.dataset.userStatus && card.dataset.userStatus.toLowerCase() === 'available');
            
            let visible = true;
            if (filter === 'available') visible = isAvailable;
            else if (filter === 'busy') visible = !isAvailable;
            
            card.style.display = visible ? '' : 'none';
        });
    },

    // ========== GESTIÓN DE LLAMADAS ==========
    initiateCall: function(userId, userName, userImage) {
        const self = this;
        console.log('Iniciando llamada a:', userName);
        
        // Mostrar modal de llamada saliente
        this.showOutgoingCallModal(userName, userImage);
        
        // Hacer llamada al servidor
        fetch(this.config.apiEndpoints.initiateCall, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 'receiver_id': userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                self.currentCallId = data.call_id;
                self.startCallStatusCheck();
                self.showNotification(self.config.texts.calling + ' ' + userName + '...', 'info');
            } else {
                self.endCall();
                if (data.user_busy) {
                    self.showNotification(userName + ' ' + self.config.texts.userBusy, 'warning');
                } else {
                    self.showNotification(data.message || 'Error al iniciar llamada', 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error iniciando llamada:', error);
            self.endCall();
            self.showNotification(self.config.texts.connectionError, 'error');
        });
    },

    startCallStatusCheck: function() {
        if (!this.currentCallId) return;
        
        const self = this;
        const checkInterval = setInterval(() => {
            if (!self.currentCallId) {
                clearInterval(checkInterval);
                return;
            }
            
            fetch(self.config.apiEndpoints.checkCallStatus, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 'call_id': self.currentCallId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    self.handleCallStatusUpdate(data);
                } else {
                    clearInterval(checkInterval);
                    if (self.currentCallId) {
                        self.endCall();
                    }
                }
            })
            .catch(() => {
                console.warn('Error verificando estado de llamada');
            });
        }, 2000);
        
        // Timeout después de 2 minutos
        setTimeout(() => {
            clearInterval(checkInterval);
            if (self.currentCallId) {
                self.endCall();
                self.showNotification('Llamada sin respuesta', 'warning');
            }
        }, 120000);
    },

    handleCallStatusUpdate: function(statusData) {
        switch(statusData.call_status) {
            case 'in_progress':
                if (statusData.vsee_url) {
                    this.stopAllTones();
                    this.showNotification('Llamada conectada - Abriendo videollamada...', 'success');
                    
                    setTimeout(() => {
                        window.open(statusData.vsee_url, '_blank');
                        this.endCall();
                    }, 1000);
                }
                break;
                
            case 'rejected':
                this.endCall();
                this.showNotification('Llamada rechazada', 'warning');
                break;
                
            case 'missed':
                this.endCall();
                this.showNotification('Llamada perdida por timeout', 'warning');
                break;
                
            case 'failed':
                this.endCall();
                this.showNotification('La llamada ha fallado', 'error');
                break;
        }
    },

    showOutgoingCallModal: function(name, image) {
        const modal = document.getElementById('calls-outgoing-modal');
        const nameEl = document.getElementById('calls-calling-user-name');
        const imageEl = document.getElementById('calls-calling-user-image');
        const timerEl = document.getElementById('calls-call-timer');
        
        if (modal) modal.style.display = 'block';
        if (nameEl) nameEl.textContent = name || this.config.texts.calling;
        if (imageEl && image) imageEl.src = image;
        if (timerEl) timerEl.textContent = '00:00';
        
        this.startDialTone();
        this.startCallTimer();
    },

    startCallTimer: function() {
        this.callStartTime = new Date();
        this.callTimer = setInterval(() => {
            if (this.callStartTime) {
                const elapsed = new Date() - this.callStartTime;
                const minutes = Math.floor(elapsed / 60000);
                const seconds = Math.floor((elapsed % 60000) / 1000);
                const timerEl = document.getElementById('calls-call-timer');
                if (timerEl) {
                    timerEl.textContent = 
                        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }
        }, 1000);
    },

    endCall: function() {
        try {
            this.stopAllTones();
        } catch(e) {}
        
        const outgoingModal = document.getElementById('calls-outgoing-modal');
        const incomingModal = document.getElementById('calls-incoming-modal');
        
        if (outgoingModal) outgoingModal.style.display = 'none';
        if (incomingModal) incomingModal.style.display = 'none';
        
        this.stopCallTimer();
        this.currentCallId = null;
        this.incomingCallId = null;
    },

    stopCallTimer: function() {
        if (this.callTimer) {
            clearInterval(this.callTimer);
            this.callTimer = null;
        }
        this.callStartTime = null;
    },

    // ========== LLAMADAS ENTRANTES ==========
    startIncomingCallsCheck: function() {
        if (this.__incomingTimer) clearInterval(this.__incomingTimer);
        this.__incomingTimer = setInterval(() => {
            this.checkIncomingCalls();
        }, 4000);
    },

    checkIncomingCalls: function() {
        if (this.currentCallId || this.incomingCallId) return;
        
        const url = this.config.apiEndpoints.checkIncoming + '?t=' + Date.now();
        fetch(url, { method: 'GET', cache: 'no-store' })
        .then(response => response.json())
        .then(data => {
            if (data && data.success && data.incoming) {
                this.incomingCallId = data.call_id;
                this.showIncomingCallModal(data.caller_name, data.caller_image);
            }
        })
        .catch(() => {});
    },

    showIncomingCallModal: function(name, image) {
        const modal = document.getElementById('calls-incoming-modal');
        const nameEl = document.getElementById('calls-incoming-user-name');
        const imageEl = document.getElementById('calls-incoming-user-image');
        
        if (modal) modal.style.display = 'block';
        if (nameEl && name) nameEl.textContent = name;
        if (imageEl && image) imageEl.src = image;
        
        this.startRingTone();
    },

    acceptCall: function() {
        if (!this.incomingCallId) return;
        
        fetch(this.config.apiEndpoints.answerCall, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'call_id': this.incomingCallId,
                'action': 'accept'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.stopAllTones();
                this.showNotification('Llamada aceptada - Preparando videollamada...', 'success');
                
                setTimeout(() => {
                    if (data.receiver_vsee_url) {
                        window.open(data.receiver_vsee_url, '_blank');
                    }
                    this.endCall();
                }, 1500);
            } else {
                this.showNotification(data.message || 'No se pudo aceptar la llamada', 'error');
                this.endCall();
            }
        })
        .catch(error => {
            this.showNotification('Error de conexión', 'error');
            this.endCall();
        });
    },

    rejectCall: function() {
        if (!this.incomingCallId) return;
        
        fetch(this.config.apiEndpoints.answerCall, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'call_id': this.incomingCallId,
                'action': 'reject'
            })
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

    // ========== GESTIÓN DE ESTADO ==========
    checkUserStatus: function() {
        const url = this.config.apiEndpoints.getUserStatus + '?user_id=' + encodeURIComponent(this.config.userId || '');
        return fetch(url, { method: 'GET', cache: 'no-store', credentials: 'include' })
        .then(response => response.ok ? response.json() : null)
        .then(data => {
            const dot = document.getElementById('calls-user-status-dot');
            const text = document.getElementById('calls-user-status-text');
            const status = (data && data.success && data.data && data.data.status) ? String(data.data.status) : 'available';
            
            if (dot) { 
                dot.classList.remove('available','busy','do_not_disturb'); 
                dot.classList.add(status); 
            }
            if (text) {
                const map = { available: 'Disponible', busy: 'Ocupado', do_not_disturb: 'No molestar' };
                text.textContent = map[status] || status;
            }
        }).catch(() => {});
    },

    updateStatus: function(newStatus) {
        fetch(this.config.apiEndpoints.updateStatus, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 'status': newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateStatusDisplay(newStatus);
                this.showNotification('Estado actualizado', 'success');
            } else {
                this.showNotification('Error al actualizar el estado', 'error');
            }
        })
        .catch(() => {
            this.showNotification('Error de conexión', 'error');
        });
    },

    updateStatusDisplay: function(status) {
        const dot = document.getElementById('calls-user-status-dot');
        const text = document.getElementById('calls-user-status-text');
        const selector = document.getElementById('calls-status-selector');
        
        if (dot) {
            dot.classList.remove('available', 'busy', 'do_not_disturb');
            dot.classList.add(status);
        }
        
        if (text) {
            const statusMap = {
                'available': 'Disponible',
                'busy': 'Ocupado',
                'do_not_disturb': 'No molestar'
            };
            text.textContent = statusMap[status] || status;
        }
        
        if (selector) {
            selector.value = status;
        }
    },

    updateUserAvailability: function() {
        // Reaplicar filtro visual
        this.applyFilter(this.filterActive || 'all');
    },

    // ========== GESTIÓN DE MODAL ==========
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

    clearBubbleNotifications: function() {
        const dot = document.getElementById('calls-bubble-notification');
        const count = document.getElementById('calls-bubble-count');
        if (dot) dot.style.display = 'none';
        if (count) count.textContent = '0';
    },

    // ========== UTILIDADES ==========
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

    showError: function(message) {
        const clinicsList = document.getElementById('calls-clinics-list');
        if (clinicsList) {
            clinicsList.innerHTML = `
                <div style="padding: 20px; text-align: center;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ef4444; margin-bottom: 16px;"></i>
                    <h5 style="color: #374151; margin-bottom: 8px;">Error de conexión</h5>
                    <p style="color: #6b7280; margin-bottom: 16px;">${message}</p>
                    <button onclick="CallsBubbleSystem.loadUsers()" style="
                        background: #3b82f6; color: white; border: none; padding: 8px 16px; 
                        border-radius: 6px; cursor: pointer;">
                        <i class="fas fa-redo me-2"></i>Reintentar
                    </button>
                </div>
            `;
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
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; font-size: 18px; cursor: pointer; color: #6b7280;">×</button>
            </div>
        `;
        
        // Estilos para la notificación
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            background: white; border: 1px solid #e5e7eb; border-left: 4px solid ${colors[type]};
            padding: 16px; border-radius: 8px; max-width: 350px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            animation: slideInRight 0.4s ease-out;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOutRight 0.4s ease-in';
                setTimeout(() => notification.remove(), 400);
            }
        }, 4000);
    }
};

// ========== FUNCIONES GLOBALES PARA COMPATIBILIDAD ==========
function toggleCallsModal() {
    CallsBubbleSystem.toggleModal();
}

function closeCallsModal() {
    CallsBubbleSystem.closeModal();
}

function hangupCallsBubbleCall() {
    CallsBubbleSystem.endCall();
}

function acceptCallsBubbleCall() {
    CallsBubbleSystem.acceptCall();
}

function rejectCallsBubbleCall() {
    CallsBubbleSystem.rejectCall();
}

// ========== ESTILOS CSS ADICIONALES ==========
const additionalCSS = `
@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(20px);
    }
}

.calls-notification {
    backdrop-filter: blur(10px);
    color: #374151;
    font-size: 14px;
    line-height: 1.4;
}

.calls-clinic-row {
    transition: all 0.2s ease;
    cursor: pointer;
}

.calls-clinic-row:hover {
    background-color: #f8fafc;
    transform: translateY(-1px);
}

.calls-clinic-row.calls-active {
    background-color: #eff6ff;
    border-color: #3b82f6;
}

.calls-contact-card {
    transition: all 0.2s ease;
}

.calls-contact-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.calls-filter-btn.calls-active {
    background-color: #3b82f6 !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.calls-contact-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.calls-contact-status-dot.available {
    background-color: #10b981;
}

.calls-contact-status-dot.busy {
    background-color: #f59e0b;
}

.calls-user-status-dot.available {
    background-color: #10b981;
}

.calls-user-status-dot.busy,
.calls-user-status-dot.do_not_disturb {
    background-color: #f59e0b;
}

/* Animaciones para modales de llamada */
.calls-pulse-rings .calls-ring {
    position: absolute;
    border: 2px solid #3b82f6;
    border-radius: 50%;
    animation: calls-pulse 2s infinite;
}

.calls-pulse-rings .calls-ring-1 {
    width: 80px;
    height: 80px;
    top: -15px;
    left: -15px;
    animation-delay: 0s;
}

.calls-pulse-rings .calls-ring-2 {
    width: 100px;
    height: 100px;
    top: -25px;
    left: -25px;
    animation-delay: 0.7s;
}

.calls-pulse-rings .calls-ring-3 {
    width: 120px;
    height: 120px;
    top: -35px;
    left: -35px;
    animation-delay: 1.4s;
}

@keyframes calls-pulse {
    0% {
        transform: scale(0.8);
        opacity: 1;
    }
    100% {
        transform: scale(1.2);
        opacity: 0;
    }
}

.calls-wave-rings .calls-wave {
    position: absolute;
    border: 2px solid #10b981;
    border-radius: 50%;
    animation: calls-wave 1.5s infinite;
}

.calls-wave-rings .calls-wave-1 {
    width: 80px;
    height: 80px;
    top: -15px;
    left: -15px;
    animation-delay: 0s;
}

.calls-wave-rings .calls-wave-2 {
    width: 100px;
    height: 100px;
    top: -25px;
    left: -25px;
    animation-delay: 0.5s;
}

.calls-wave-rings .calls-wave-3 {
    width: 120px;
    height: 120px;
    top: -35px;
    left: -35px;
    animation-delay: 1s;
}

@keyframes calls-wave {
    0% {
        transform: scale(0.9);
        opacity: 0.8;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.4;
    }
    100% {
        transform: scale(1.3);
        opacity: 0;
    }
}
`;

// Añadir CSS al head
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalCSS;
document.head.appendChild(styleSheet);

// ========== CONFIGURACIÓN E INICIALIZACIÓN ==========
// Esta parte se ejecutará cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Configurar el sistema con los datos de PHP
    if (window.CallsBubbleSystem && window.CallsBubbleSystem.config) {
        // La configuración ya debe estar establecida desde PHP
        CallsBubbleSystem.init();
    } else {
        console.error('Configuración del sistema de llamadas no encontrada');
    }
});

// También inicializar con jQuery para compatibilidad
if (typeof $ !== 'undefined') {
    $(document).ready(function() {
        if (window.CallsBubbleSystem && window.CallsBubbleSystem.config && window.CallsBubbleSystem.config.userId) {
            CallsBubbleSystem.init();
        }
    });
}

// Debug helpers para desarrollo
if (typeof console !== 'undefined' && console.log) {
    window.debugCallsSystem = {
        getCurrentCall: () => CallsBubbleSystem.currentCallId,
        getIncomingCall: () => CallsBubbleSystem.incomingCallId,
        forceEndCall: () => CallsBubbleSystem.endCall(),
        testNotification: (msg, type) => CallsBubbleSystem.showNotification(msg, type),
        reloadUsers: () => CallsBubbleSystem.loadUsers(),
        checkStatus: () => CallsBubbleSystem.checkUserStatus(),
        getSystemData: () => CallsBubbleSystem.data,
        toggleModal: () => CallsBubbleSystem.toggleModal()
    };
}

console.log('🎯 Sistema de llamadas burbuja cargado. Usa window.debugCallsSystem para debugging.');
</script>