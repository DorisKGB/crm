// public/js/loading-modal.js
(function(){
  let loadingModal, modalEl, msgEl, spinnerEl, buttonEl, errorEl, successEl;
  let reloadTimer = null;

  document.addEventListener('DOMContentLoaded', () => {
    modalEl   = document.getElementById('processingModal');
    msgEl     = document.getElementById('processingModalMessage');
    spinnerEl = document.getElementById('processingSpinner');
    buttonEl  = document.getElementById('processingModalButton');
    errorEl   = document.querySelector('.errorProcess');
    successEl = document.querySelector('.checkProcess');
    if (!modalEl) return;

    modalEl.classList.add('modal-front');
    // Instancia única
    loadingModal = new bootstrap.Modal(modalEl, {
      backdrop: 'static',
      keyboard: false
    });

    const style = document.createElement('style');
    style.textContent = `
      /* Primer plano del loader */
      .modal-front {
        z-index: 20000 !important;
      }
      /* Backdrop justo por debajo */
      .backdrop-front {
        z-index: 19999 !important;
      }
    `;
    document.head.appendChild(style);

    // Cuando se muestra cualquier modal, marcamos su backdrop
   modalEl.addEventListener('shown.bs.modal', () => {
  // Selecciona todos los backdrops “show” y toma el último (el de processingModal)
        const all = document.querySelectorAll('.modal-backdrop.show');
        const bd  = all[all.length - 1];
        if (bd) {
            bd.classList.add('backdrop-front');
            bd.style.zIndex = '19999';    // Fallback directo en el elemento
        }
    });


    // Botón “Aceptar” por defecto oculta modal y vuelve a estado loading
    buttonEl.addEventListener('click', () => {
      resetModal();
      loadingModal.hide();
    });

    // Muestra spinner + mensaje + sin botón
    window.showLoading = function(message = 'Procesando...') {
      clearTimeout(reloadTimer);
      msgEl.textContent     = message;
      spinnerEl.style.display = '';
      errorEl.style.display   = 'none';
      successEl.style.display = 'none';
      buttonEl.style.display  = 'none';
      buttonEl.onclick        = null;
      loadingModal.show();

      // Tras 2 minutos, convertimos el botón en “Recargar página”
      reloadTimer = setTimeout(() => {
        spinnerEl.style.display = 'none';
        msgEl.textContent       = 'Sigue tardando… ¿Quieres recargar la página?';
        buttonEl.textContent    = 'Recargar página';
        buttonEl.style.display  = '';
        buttonEl.onclick        = () => location.reload();
      }, 30000);
    };


    window.hideLoading = function() {
        clearTimeout(reloadTimer);

        const inst = bootstrap.Modal.getOrCreateInstance(modalEl);
        inst.hide();

        setTimeout(() => {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            console.log('✔ Modal forzado ocultarse tras 200 ms');
        }, 200);

        resetModal();
    };

     // 2) Mostrar icono de check (éxito)
    window.showSuccess = function(message = '¡Hecho!') {
      clearTimeout(reloadTimer);
      msgEl.textContent       = message;
      spinnerEl.style.display = 'none';
      errorEl.style.display   = 'none';
      successEl.style.display = '';
      buttonEl.textContent    = 'Aceptar';
      buttonEl.style.display  = '';
      buttonEl.onclick        = () => {
        resetModal();
        loadingModal.hide();
      };
      loadingModal.show();
    };

    // Muestra modal en modo error (spinner off + botón Aceptar)
    window.showError = function(message = 'Ocurrió un error') {
      clearTimeout(reloadTimer);
      msgEl.textContent       = message;
      spinnerEl.style.display = 'none';
      buttonEl.textContent    = 'Aceptar';
      errorEl.style.display   = '';
      successEl.style.display = 'none';
      buttonEl.className = 'btn-button btn-button-danger';
      buttonEl.style.display  = '';
      buttonEl.onclick        = () => {
        resetModal();
        loadingModal.hide();
      };
      loadingModal.show();
    };

    // Restaura el modal a su estado “loading” original
    function resetModal() {
      clearTimeout(reloadTimer);
      msgEl.textContent       = 'Procesando...';
      spinnerEl.style.display = '';
        errorEl.style.display   = 'none';
      successEl.style.display = 'none';
      buttonEl.textContent    = 'Aceptar';
      buttonEl.style.display  = 'none';
      buttonEl.onclick        = null;
    }
  });
})();
