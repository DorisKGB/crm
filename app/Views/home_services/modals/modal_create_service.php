<?php
// home_services/modals/modal_create_service.php
function statusBadge($txt) { return '<span class="badge bg-secondary">'.$txt.'</span>'; }
?>

<style>
.nav-tabs .nav-link{border-radius:.375rem .375rem 0 0}
.nav-tabs .nav-link.active{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-color:transparent}
.card-gradient{background:linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%);border:none;box-shadow:0 2px 10px rgba(0,0,0,.1)}
.priority-indicator{width:12px;height:12px;border-radius:50%;display:inline-block;margin-right:8px}
.priority-normal{background:#28a745}.priority-priority{background:#ffc107}
</style>

<div class="modal fade" id="modalCreateService" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Crear Servicio Domiciliario</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="createServiceForm" method="POST" enctype="multipart/form-data" autocomplete="off">
        <?= csrf_field() ?>

        <div class="modal-body">

          <!-- Tabs -->
          <ul class="nav nav-tabs mb-4" id="createServiceTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab_appointment" type="button"><i class="fas fa-calendar-check"></i> Cita</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_service" type="button"><i class="fas fa-medical-bag"></i> Servicio</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_location" type="button"><i class="fas fa-map-marker-alt"></i> Ubicación</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_schedule" type="button"><i class="fas fa-clock"></i> Programación</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_notes" type="button"><i class="fas fa-sticky-note"></i> Notas</button></li>
          </ul>

          <div class="tab-content">

            <!-- TAB 1: Cita -->
            <div class="tab-pane fade show active" id="tab_appointment">
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label d-flex justify-content-between align-items-center">
                    <span>Cita asociada *</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnOpenCreateAppointment">
                      <i class="fas fa-plus"></i> Crear cita
                    </button>
                  </label>

                  <!-- Campo de búsqueda personalizado -->
                  <div class="position-relative">
                    <input type="text" id="appointment_search" class="input-ghost" 
                          placeholder="Escribir para buscar cita por paciente..." 
                          style="width: 100%; font-size: 14px; padding: 12px;"
                          autocomplete="off">
                    
                    <!-- Lista de resultados -->
                    <div id="appointment_results" class="position-absolute w-100" 
                        style="z-index: 1000; background: white; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; display: none;">
                    </div>
                  </div>
                  
                  <!-- Input hidden para el valor real -->
                  <input type="hidden" name="appointment_id" id="create_appointment_id" required>

                  <div class="form-text">
                    Escribe el nombre del paciente o número de cita para buscar.
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="card card-gradient">
                    <div class="card-body">
                      <h6><i class="fas fa-info-circle text-primary"></i> Info de la cita</h6>
                      <div id="appointmentInfo">
                        <small class="text-muted">Sin selección</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TAB 2: Servicio -->
            <div class="tab-pane fade" id="tab_service">
              <div class="row">
                <div class="col-md-8">
                  <div class="mb-3">
                    <label class="form-label"><i class="fas fa-medical-bag"></i> Tipo de servicio *</label>
                    <select name="service_type" class="input-ghost" required>
                      <option value="">Seleccionar tipo...</option>
                      <option value="Lipid Panel">🧪 Lipid Panel</option>
                      <option value="Sueros Vitaminados">💧 Sueros Vitaminados</option>
                      <option value="Chequeo Médico General">🩺 Chequeo Médico General</option>
                      <option value="Otro">➕ Otro</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user-md"></i> Proveedor asignado</label>
                    <select name="assigned_provider_id" class="input-ghost select2" style="width:100%;">
                      <option value="">Sin asignar</option>
                      <?php if(isset($providers) && is_array($providers)): ?>
                        <?php foreach($providers as $p): ?>
                          <option value="<?= $p->id ?>">👨‍⚕️ <?= $p->first_name.' '.$p->last_name ?></option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="fas fa-exclamation-triangle"></i> Prioridad</label>
                    <div class="btn-group w-100" role="group">
                      <input type="radio" class="btn-check" name="priority" id="priority_normal" value="normal" checked>
                      <label class="btn btn-outline-success" for="priority_normal">
                        <i class="fas fa-check-circle"></i> Normal
                      </label>

                      <input type="radio" class="btn-check" name="priority" id="priority_priority" value="priority">
                      <label class="btn btn-outline-warning" for="priority_priority">
                        <i class="fas fa-exclamation-circle"></i> Prioritario
                      </label>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="fas fa-dollar-sign"></i> Costo del servicio</label>
                    <div class="input-ghost-icon-container">
                      <span class="input-ghost-icon">$</span>
                      <input type="number" name="service_cost" class="input-ghost input-ghost-with-icon" value="0.00" step="0.01" min="0" placeholder="0.00">
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="card card-gradient">
                    <div class="card-body">
                      <h6><i class="fas fa-lightbulb text-primary"></i> Sugerencias</h6>
                      <small class="text-muted">
                        • Confirma insumos y preparación del paciente<br>
                        • Anota alergias / condiciones especiales en Notas
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TAB 3: Ubicación -->
            <div class="tab-pane fade" id="tab_location">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label"><i class="fas fa-flag-usa"></i> Estado *</label>
                    <select name="patient_state" class="input-ghost" required>
                      <option value="">Seleccionar estado...</option>
                      <?php if(isset($states) && is_array($states)): ?>
                        <?php foreach($states as $s): ?>
                          <option value="<?= $s->code ?>"><?= $s->name ?> (<?= $s->code ?>)</option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label"><i class="fas fa-city"></i> Ciudad *</label>
                    <input type="text" name="patient_city" class="input-ghost" required placeholder="Ej: Houston">
                  </div>
                  <div class="mb-3">
                    <label class="form-label"><i class="fas fa-mail-bulk"></i> Código postal</label>
                    <input type="text" name="patient_zipcode" class="input-ghost" placeholder="Ej: 77002">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label"><i class="fas fa-map-marker-alt"></i> Dirección completa *</label>
                    <textarea name="patient_address" class="input-ghost" rows="4" required placeholder="Calle, #, apto, referencias..."></textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- TAB 4: Programación -->
            <div class="tab-pane fade" id="tab_schedule">
              <div class="row">
                <div class="col-md-8">
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label"><i class="fas fa-calendar"></i> Fecha programada</label>
                      <input type="text" name="scheduled_date" id="scheduled_date" class="input-ghost" placeholder="MM/DD/YYYY">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label"><i class="fas fa-clock"></i> Hora programada</label>
                      <input type="text" name="scheduled_time" id="scheduled_time" class="input-ghost" placeholder="hh:mm AM/PM">
                    </div>
                  </div>

                  <div id="providerAvailability" class="alert d-none"></div>
                </div>

                <div class="col-md-4">
                  <div class="card card-gradient">
                    <div class="card-body">
                      <h6><i class="fas fa-calendar-check"></i> Resumen</h6>
                      <div id="serviceResumen"><small class="text-muted">Completa los campos para ver el resumen.</small></div>
                    </div>
                  </div>

                  <div class="mt-3">
                    <button type="button" class="btn btn-outline-warning btn-sm w-100" onclick="checkProviderAvailabilityCreate()">
                      <i class="fas fa-search"></i> Verificar disponibilidad
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- TAB 5: Notas -->
            <div class="tab-pane fade" id="tab_notes">
              <div class="mb-3">
                <label class="form-label"><i class="fas fa-sticky-note"></i> Notas del servicio</label>
                <textarea name="service_notes" class="input-ghost" rows="4" placeholder="Instrucciones especiales, observaciones, etc."></textarea>
              </div>
            </div>

          </div><!-- /tab-content -->
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn-ghost btn-ghost-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
          <button type="submit" class="btn-ghost btn-ghost-success"><i class="fas fa-save"></i> Crear servicio</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Mini-modal: Crear cita rápida -->
<div class="modal fade" id="modalQuickCreateAppointment" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="fas fa-plus-circle"></i> Crear cita rápida</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="quickAppointmentForm">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Paciente *</label>
            <input type="text" class="input-ghost" name="patient_name" required>
          </div>
          
          <!-- AGREGAR ESTOS CAMPOS AQUÍ -->
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Teléfono</label>
              <input type="tel" class="input-ghost" name="patient_phone" placeholder="Ej: +1 555-0123">
            </div>
            <div class="col-6">
              <label class="form-label">Email</label>
              <input type="email" class="input-ghost" name="patient_email" placeholder="ejemplo@correo.com">
            </div>
          </div>
          
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label">Fecha (MM/DD/YYYY) *</label>
              <input type="text" class="input-ghost" id="qa_date" name="appointment_date" placeholder="MM/DD/YYYY" required>
            </div>
            <div class="col-6">
              <label class="form-label">Hora *</label>
              <input type="text" class="input-ghost" id="qa_time" name="appointment_time" placeholder="hh:mm AM/PM" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Motivo</label>
            <textarea class="input-ghost" name="reason" rows="2" placeholder="Opcional"></textarea>
          </div>
          <small class="text-muted">La cita se creará y quedará seleccionada automáticamente.</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-ghost btn-ghost-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-ghost btn-ghost-success" id="qa_submit"><i class="fas fa-save"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// ------------------- Estado de inicialización segura -------------------
let createModalInitialized = false;

// Al mostrar el modal principal: inicializamos Select2 (v3) y Flatpickr
document.getElementById('modalCreateService').addEventListener('shown.bs.modal', function () {
  if (createModalInitialized) return;
  createModalInitialized = true;

  // Agregar delay para asegurar que el DOM esté listo
  setTimeout(() => {
    // Select proveedor (simple)
    if (typeof $.fn.select2 !== 'undefined') {
      $('.select2').select2({allowClear:true, placeholder:'Seleccionar...'});
    }

    // Select2 para cita
    initAppointmentSelectV3();

    // Resto de inicializaciones...
    initScheduledPickersUS();
    initQuickAppointmentPickers();
    
    document.getElementById('btnOpenCreateAppointment').addEventListener('click', openQuickAppointmentModal);
    
    document.querySelectorAll('#tab_service input, #tab_service select, #tab_location input, #tab_location select, #tab_schedule input, #tab_schedule select, #tab_notes textarea')
      .forEach(el => el.addEventListener('change', updateResumenCreate));

    updateResumenCreate();
  }, 100);
});

// Destruir instancias al cerrar, para evitar que “a veces no abra”
document.getElementById('modalCreateService').addEventListener('hide.bs.modal', function () {
  try { $('#appointment_search').select2('destroy'); } catch(e){}
  try { $('.select2').select2('destroy'); } catch(e){}
  createModalInitialized = false;
  resetCreateForm();
});

// ------------------- Select2 v3 (AJAX) para Citas -------------------
function initAppointmentSelectV3(){
  const searchInput = document.getElementById('appointment_search');
  const resultsDiv = document.getElementById('appointment_results');
  const hiddenInput = document.getElementById('create_appointment_id');
  
  let searchTimeout;
  let selectedAppointment = null;

  // Evento de escritura
  searchInput.addEventListener('input', function(e) {
    const query = e.target.value.trim();
    
    clearTimeout(searchTimeout);
    
    if (query.length < 2) {
      hideResults();
      clearSelection();
      return;
    }

    // Debounce para evitar muchas peticiones
    searchTimeout = setTimeout(() => {
      searchAppointments(query);
    }, 300);
  });

  // Ocultar resultados al hacer clic fuera
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.position-relative')) {
      hideResults();
    }
  });

  // Función para buscar citas
  async function searchAppointments(query) {
    try {
      showLoading();
      
      const response = await fetch(`<?= get_uri("appointments/search") ?>?q=${encodeURIComponent(query)}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) throw new Error('Error en la búsqueda');

      const data = await response.json();
      displayResults(data);
      
    } catch (error) {
      console.error('Error searching appointments:', error);
      showError('Error al buscar citas');
    }
  }

  // Mostrar loading
  function showLoading() {
    resultsDiv.innerHTML = '<div class="p-3 text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';
    resultsDiv.style.display = 'block';
  }

  // Mostrar error
  function showError(message) {
    resultsDiv.innerHTML = `<div class="p-3 text-danger"><i class="fas fa-exclamation-triangle"></i> ${message}</div>`;
    resultsDiv.style.display = 'block';
  }

  // Mostrar resultados
  function displayResults(appointments) {
    if (appointments.length === 0) {
      resultsDiv.innerHTML = '<div class="p-3 text-muted">No se encontraron citas</div>';
      resultsDiv.style.display = 'block';
      return;
    }

    let html = '';
    appointments.forEach(appointment => {
      html += `
        <div class="appointment-result-item p-3 border-bottom" 
             style="cursor: pointer; transition: background-color 0.2s;"
             data-id="${appointment.id}"
             data-patient="${appointment.patient_name}"
             data-date="${appointment.date}"
             data-time="${appointment.time}"
             onmouseover="this.style.backgroundColor='#f8f9fa'"
             onmouseout="this.style.backgroundColor='white'">
          <div class="fw-bold text-primary">Cita #${String(appointment.id).padStart(3, '0')}</div>
          <div class="text-dark">${appointment.patient_name}</div>
          <div class="small text-muted">${appointment.date} - ${appointment.time}</div>
        </div>
      `;
    });

    resultsDiv.innerHTML = html;
    resultsDiv.style.display = 'block';

    // Agregar eventos de clic a los resultados
    resultsDiv.querySelectorAll('.appointment-result-item').forEach(item => {
      item.addEventListener('click', function() {
        selectAppointment({
          id: this.dataset.id,
          patient_name: this.dataset.patient,
          date: this.dataset.date,
          time: this.dataset.time
        });
      });
    });
  }

  // Seleccionar una cita
  function selectAppointment(appointment) {
    selectedAppointment = appointment;
    
    // Actualizar campo de búsqueda
    searchInput.value = `Cita #${String(appointment.id).padStart(3, '0')} - ${appointment.patient_name}`;
    
    // Actualizar campo hidden
    hiddenInput.value = appointment.id;
    
    // Actualizar información
    updateAppointmentInfo(appointment.patient_name, appointment.date, appointment.time);
    updateResumenCreate();
    
    // Ocultar resultados
    hideResults();
  }

  // Limpiar selección
  function clearSelection() {
    selectedAppointment = null;
    hiddenInput.value = '';
    updateAppointmentInfo('', '', '');
    updateResumenCreate();
  }

  // Ocultar resultados
  function hideResults() {
    resultsDiv.style.display = 'none';
  }

  // Limpiar campo cuando se borra todo
  searchInput.addEventListener('keyup', function(e) {
    if (this.value.trim() === '') {
      clearSelection();
      hideResults();
    }
  });

  // Función pública para seleccionar desde fuera (crear cita rápida)
  window.selectAppointmentFromOutside = function(appointment) {
    selectAppointment(appointment);
  };
}

// ------------------- Flatpickr: formato EEUU -------------------
function initScheduledPickersUS(){
  if (typeof flatpickr !== 'function') return;
  const today = new Date();
  const mm = String(today.getMonth()+1).padStart(2,'0');
  const dd = String(today.getDate()).padStart(2,'0');
  const yyyy = today.getFullYear();

  flatpickr('#scheduled_date', {
    dateFormat: 'm/d/Y',
    defaultDate: `${mm}/${dd}/${yyyy}`,
    allowInput: true
  });
  flatpickr('#scheduled_time', {
    enableTime: true,
    noCalendar: true,
    dateFormat: 'h:i K',
    time_24hr: false,
    defaultDate: new Date(),
    allowInput: true
  });
}

function initQuickAppointmentPickers(){
  if (typeof flatpickr !== 'function') return;
  flatpickr('#qa_date', { dateFormat: 'm/d/Y', allowInput: true });
  flatpickr('#qa_time', { enableTime:true, noCalendar:true, dateFormat:'h:i K', time_24hr:false, allowInput:true });
}

// ------------------- Mini-modal: crear cita rápida -------------------
function openQuickAppointmentModal(){
  const el = document.getElementById('modalQuickCreateAppointment');
  const mm = bootstrap.Modal.getOrCreateInstance(el, {backdrop:'static', keyboard:false});

  // Defaults: hoy + siguiente cuarto de hora
  const now = new Date();
  const mon = String(now.getMonth()+1).padStart(2,'0');
  const day = String(now.getDate()).padStart(2,'0');
  const y = now.getFullYear();
  document.getElementById('qa_date')._flatpickr && document.getElementById('qa_date')._flatpickr.setDate(`${mon}/${day}/${y}`, true, "m/d/Y");
  const rounded = roundToNextQuarter(now);
  document.getElementById('qa_time')._flatpickr && document.getElementById('qa_time')._flatpickr.setDate(rounded, true);

  mm.show();
}

function roundToNextQuarter(d){
  const x=new Date(d); x.setSeconds(0,0);
  const m=x.getMinutes(); const add=15-(m%15||15); x.setMinutes(m+add); return x;
}

document.getElementById('quickAppointmentForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const btn = document.getElementById('qa_submit');
  const html = btn.innerHTML; btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Guardando...';
  try{
    const fd = new FormData(this);
    const res = await fetch('<?= get_uri("appointments/create_quick") ?>', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}});
    const data = await res.json();
    if(!data.success) throw new Error(data.message || 'No se pudo crear la cita');

    const appt = data.appointment; // {id, patient_name, date:"MM/DD/YYYY", time:"hh:mm AM/PM"}
    // Setear selección en Select2 v3
      const optionData = new Option(
      `Cita #${String(appt.id).padStart(3,'0')} – ${appt.patient_name} – ${appt.date} ${appt.time}`, 
      appt.id, 
      true, 
      true
    );
    $('#appointment_search').append(optionData).trigger('change');
    $('#create_appointment_id').val(appt.id);
    updateAppointmentInfo(appt.patient_name, appt.date, appt.time);

    bootstrap.Modal.getInstance(document.getElementById('modalQuickCreateAppointment')).hide();
    showSuccessCreate('Cita creada correctamente.');
    // Pasar a la pestaña Servicio
    new bootstrap.Tab(document.querySelector('button[data-bs-target="#tab_service"]')).show();
  }catch(err){
    alert('Error: '+err.message);
  }finally{
    btn.disabled=false; btn.innerHTML=html;
  }
});

// ------------------- UI cita seleccionada -------------------
function updateAppointmentInfo(patientName, dateUS, timeUS){
  const info = document.getElementById('appointmentInfo');
  const safe = (patientName||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  info.innerHTML = `<div class="text-start">
    <small><strong>Paciente:</strong><br>${safe}</small><br>
    <small><strong>Fecha:</strong><br>${dateUS||'-'}</small><br>
    <small><strong>Hora:</strong><br>${timeUS||'-'}</small>
  </div>`;
}

// ------------------- Resumen -------------------
function updateResumenCreate(){
  const fd = new FormData(document.getElementById('createServiceForm'));
  const providerText = document.querySelector('select[name="assigned_provider_id"] option:checked')?.text || '';
  const city = fd.get('patient_city')||''; const state = fd.get('patient_state')||'';
  const dateUS = fd.get('scheduled_date')||''; const timeUS = fd.get('scheduled_time')||'';
  const serviceType = fd.get('service_type')||'';
  const priority = fd.get('priority')||'normal';
  const map = {normal:'🟢 Normal', priority:'🟡 Prioritario'};

  let html = '<div class="text-start small">';
  if (serviceType) html += `<strong>Servicio:</strong> ${serviceType}<br>`;
  if (providerText && providerText.trim() !== 'Sin asignar') html += `<strong>Proveedor:</strong> ${providerText}<br>`;
  if (city && state) html += `<strong>Ubicación:</strong> ${city}, ${state}<br>`;
  if (dateUS && timeUS) html += `<strong>Programado:</strong> ${dateUS} ${timeUS}<br>`;
  if (priority) html += `<strong>Prioridad:</strong> ${map[priority] || priority}`;
  html += '</div>';
  document.getElementById('serviceResumen').innerHTML = html;
}

// ------------------- Disponibilidad proveedor -------------------
async function checkProviderAvailabilityCreate(){
  const dateUS = document.getElementById('scheduled_date').value;
  const timeUS = document.getElementById('scheduled_time').value;
  const div = document.getElementById('providerAvailability');

  if (!dateUS || !timeUS){ div.className='alert d-none'; return; }

  const dateISO = toISODateFromUS(dateUS);
  const time24  = to24hFromUS(timeUS);

  try{
    div.className='alert alert-info';
    div.innerHTML='<i class="fas fa-spinner fa-spin"></i> Verificando disponibilidad...';
    const res = await fetch(`<?= get_uri("home_services/check_provider_availability") ?>?date=${dateISO}&time=${encodeURIComponent(time24)}`);
    const r = await res.json();
    if (r.available){ div.className='alert alert-success'; div.innerHTML='<i class="fas fa-check-circle"></i> Horario disponible.'; }
    else { div.className='alert alert-warning'; div.innerHTML='<i class="fas fa-exclamation-triangle"></i> '+(r.message||'No disponible.'); }
  }catch(e){
    div.className='alert alert-danger'; div.innerHTML='<i class="fas fa-exclamation-circle"></i> Error al verificar.';
  }
}

// ------------------- Envío del formulario -------------------
document.getElementById('createServiceForm').addEventListener('submit', async function(e){
  e.preventDefault();

  const appointmentId = $('#create_appointment_id').val();
    if (!appointmentId) {
        alert('Debe seleccionar una cita válida');
        return;
    }
    
    // Verificar que se haya mostrado información del paciente
    const appointmentInfo = $('#appointmentInfo').text();
    if (appointmentInfo.includes('Sin selección')) {
        alert('La cita seleccionada no es válida');
        return;
    }

  // Normaliza fecha/hora US antes de enviar
  const fd = new FormData(this);
  const dUS = fd.get('scheduled_date'); if (dUS) fd.set('scheduled_date', toISODateFromUS(dUS));
  const tUS = fd.get('scheduled_time'); if (tUS) fd.set('scheduled_time', to24hFromUS(tUS));

  const btn = this.querySelector('button[type="submit"]');
  const html = btn.innerHTML; btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Creando...';

  try{
    const resp = await fetch('<?= get_uri("appointments/create_service") ?>', {
      method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}
    });
    const data = await resp.json();
    if (!data.success) throw new Error(data.message || 'Error al crear el servicio');

    bootstrap.Modal.getInstance(document.getElementById('modalCreateService')).hide();

    // Recargas opcionales
    if (typeof loadServices === 'function') loadServices();
    if (typeof loadSchedule === 'function') loadSchedule();
    if (typeof loadMapData === 'function') loadMapData();

    showSuccessCreate(data.message || 'Servicio creado.');
    resetCreateForm();
  }catch(err){
    alert('Error: '+err.message);
  }finally{
    btn.disabled=false; btn.innerHTML=html;
  }
});

// ------------------- Utilidades -------------------
function toISODateFromUS(mdy){ const p=mdy.split('/'); return `${p[2]}-${p[0].padStart(2,'0')}-${p[1].padStart(2,'0')}`; }
function to24hFromUS(timeStr){ const d=new Date(`1970-01-01 ${timeStr}`); return String(d.getHours()).padStart(2,'0')+':'+String(d.getMinutes()).padStart(2,'0'); }

function resetCreateForm(){
  const f = document.getElementById('createServiceForm');
  if (f) f.reset();
  
  // Limpiar búsqueda personalizada
  document.getElementById('appointment_search').value = '';
  document.getElementById('create_appointment_id').value = '';
  document.getElementById('appointment_results').style.display = 'none';
  
  document.getElementById('appointmentInfo').innerHTML='<small class="text-muted">Sin selección</small>';
  document.getElementById('serviceResumen').innerHTML='<small class="text-muted">Completa los campos para ver el resumen.</small>';
}

function showSuccessCreate(msg){
  const el=document.createElement('div');
  el.className='alert alert-success alert-dismissible fade show position-fixed';
  el.style.cssText='top:20px;right:20px;z-index:9999;min-width:300px;';
  el.innerHTML=`<i class="fas fa-check-circle"></i> ${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
  document.body.appendChild(el); setTimeout(()=>{el.remove();},5000);
}
</script>
