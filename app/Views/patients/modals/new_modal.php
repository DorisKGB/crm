<div class="modal fade" id="modalPacienteNuevo" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <form id="formNuevoPaciente">
        <div class="modal-header">
          <?= csrf_field() ?>
          <h5 class="modal-title">
            <i class="fas fa-user-plus me-2"></i>
            Nuevo Paciente
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nombre <span class="text-danger">*</span></label>
              <input type="text" 
                     id="first_name" 
                     name="first_name" 
                     class="form-control" 
                     placeholder="Ingresa el nombre"
                     required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Apellido <span class="text-danger">*</span></label>
              <input type="text" 
                     id="last_name" 
                     name="last_name" 
                     class="form-control" 
                     placeholder="Ingresa el apellido"
                     required>
            </div>
          </div>
          
          <!-- Campo oculto para nombre completo -->
          <input type="hidden" id="full_name" name="full_name">
          
          <div class="mb-3">
            <label class="form-label">
              <i class="fas fa-envelope me-1"></i>
              Correo electrónico
            </label>
            <input type="email" 
                   name="email" 
                   class="form-control" 
                   placeholder="ejemplo@correo.com">
            <small class="form-text text-muted">Opcional</small>
          </div>
          
          <div class="mb-3">
            <label class="form-label">
              <i class="fas fa-phone me-1"></i>
              Celular
            </label>
            <input type="text" 
                   name="phone" 
                   class="form-control" 
                   placeholder="300 123 4567">
            <small class="form-text text-muted">Opcional</small>
          </div>
          
          <div class="mb-3">
            <label class="form-label">
              <i class="fas fa-notes-medical me-1"></i>
              Motivo de consulta
            </label>
            <textarea name="reason" 
                      class="form-control" 
                      rows="4"
                      placeholder="Describe el motivo de la consulta..."></textarea>
            <small class="form-text text-muted">Información adicional sobre la consulta</small>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="submit" class="btn-button btn-button-purple">
            <i class="fas fa-save me-2"></i>
            Guardar
          </button>
          <button type="button" class="btn-button btn-button-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>
            Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  // Función para unir nombre y apellido
  function updateFullName() {
    const firstName = $('#first_name').val().trim();
    const lastName = $('#last_name').val().trim();
    const fullName = `${firstName} ${lastName}`.trim();
    $('#full_name').val(fullName);
  }

  // Actualizar el nombre completo cuando se escriba en cualquier campo
  $('#first_name, #last_name').on('input', function() {
    updateFullName();
  });

  // Formato automático para el teléfono (solo números y espacios)
  $('input[name="phone"]').on('input', function() {
    let value = this.value.replace(/\D/g, '');
    if (value.length >= 10) {
      value = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
    }
    this.value = value;
  });

  // Envío del formulario
  $('#formNuevoPaciente').on('submit', function(e) {
    e.preventDefault();
    
    // Validar campos requeridos
    const firstName = $('#first_name').val().trim();
    const lastName = $('#last_name').val().trim();
    
    if (!firstName || !lastName) {
      showError('Por favor completa el nombre y apellido.');
      return;
    }
    
    // Actualizar nombre completo antes de enviar
    updateFullName();
    
    // Deshabilitar botón mientras se procesa
    const $submitBtn = $(this).find('button[type="submit"]');
    const originalText = $submitBtn.html();
    $submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);
    
    showLoading();
    
    $.post("<?= get_uri('patients/create') ?>", $(this).serialize(), function(res) {
      if (res.success) {
        showSuccess('¡Paciente agregado correctamente!');
        $('#modalPacienteNuevo').modal('hide');
        $('#formNuevoPaciente')[0].reset();
        if (typeof loadPatients === 'function') {
          loadPatients();
        }
      } else {
        showError(res.message || 'Error al agregar paciente.');
      }
    }, 'json').fail(function() {
      showError('Error de conexión. Intenta nuevamente.');
    }).always(function() {
      // Restaurar botón
      $submitBtn.html(originalText).prop('disabled', false);
      hideLoading();
    });
  });

  // Limpiar formulario al cerrar modal
  $('#modalPacienteNuevo').on('hidden.bs.modal', function() {
    $('#formNuevoPaciente')[0].reset();
  });

  // Focus en el primer campo al abrir modal
  $('#modalPacienteNuevo').on('shown.bs.modal', function() {
    $('#first_name').focus();
  });
});
</script>