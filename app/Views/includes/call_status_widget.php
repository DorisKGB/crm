<?php
// Widget reutilizable para mostrar el estado de llamadas del usuario
// Se puede incluir en cualquier vista donde se necesite

// Intentar obtener user_id de diferentes formas
$user_id = null;

// Método 1: Desde $this->login_user (si está disponible)
if (isset($this->login_user) && isset($this->login_user->id)) {
    $user_id = $this->login_user->id;
}

// Método 2: Desde la sesión (fallback)
if (!$user_id) {
    $session_data = session()->get();
    $user_id = $session_data['user_id'] ?? null;
}

// Método 3: Desde $_SESSION directamente (último recurso)
if (!$user_id && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

// Log para debug - más detallado
file_put_contents('widget_debug.log', date('Y-m-d H:i:s') . " - Widget cargado. User ID: " . ($user_id ?? 'null') . "\n", FILE_APPEND);
file_put_contents('widget_debug.log', "login_user object: " . print_r($this->login_user ?? 'null', true) . "\n", FILE_APPEND);
file_put_contents('widget_debug.log', "Session data: " . print_r(session()->get() ?? 'null', true) . "\n", FILE_APPEND);
file_put_contents('widget_debug.log', "SESSION superglobal: " . print_r($_SESSION ?? 'null', true) . "\n", FILE_APPEND);

// Verificar si el usuario está logueado y obtener su estado
if ($user_id) {
    // Obtener el estado actual del usuario desde la base de datos
    $db = \Config\Database::connect();
    $query = $db->table('crm_user_call_status')->where('user_id', $user_id)->get();
    $user_status = $query->getRowArray();

    $current_status = 'available'; // Por defecto
    if ($user_status) {
        $current_status = $user_status['status'];
    }

    file_put_contents('widget_debug.log', "Estado actual: $current_status\n", FILE_APPEND);

    // Solo mostrar el widget si el estado NO es 'available'
    if ($current_status !== 'available') {
        $status_text = '';
        $status_icon = '📞';
        $banner_style = '';
        
        switch($current_status) {
            case 'busy':
                $status_text = 'Ocupado';
                $status_icon = '⏳';
                $banner_style = 'background:rgba(0,0,0,0.8);color:#fff;'; // Negro con opacidad
                break;
            case 'in_call':
                $status_text = 'En llamada';
                $status_icon = '📞';
                $banner_style = 'background:rgba(0,0,0,0.8);color:#fff;'; // Negro con opacidad
                break;
            case 'do_not_disturb':
                $status_text = 'No molestar';
                $status_icon = '🔕';
                $banner_style = 'background:rgba(0,0,0,0.8);color:#fff;'; // Negro con opacidad
                break;
            default:
                $status_text = $current_status;
                $status_icon = '📞';
                $banner_style = 'background:rgba(0,0,0,0.8);color:#fff;'; // Negro con opacidad
        }
        
        file_put_contents('widget_debug.log', "Mostrando widget - Estado: $current_status\n", FILE_APPEND);
        ?>
        <!-- ========== BANNER DE ESTADO DE LLAMADA ========== -->
        <div id="call-status-banner" style="position:fixed;bottom:20px;left:50%;transform:translateX(-50%);z-index:9999;<?php echo $banner_style; ?>padding:16px 20px;border-radius:12px;display:flex;gap:12px;align-items:center;justify-content:center;box-shadow:0 10px 25px rgba(0,0,0,.3);max-width:600px;min-height:60px;pointer-events:auto;isolation:isolate;font-size:15px;font-weight:500;border:2px solid rgba(255,255,255,0.1);">
            <span id="status-text"><?php echo $status_icon; ?> Actualmente no puedes recibir llamadas. Si estás DISPONIBLE, presiona el botón para activarlas.</span>
            <button id="set-available-btn" class="btn-ghost-success" style="border:0;padding:6px 12px;border-radius:6px;cursor:pointer;background:#10b981;color:#fff;">Aceptar llamadas</button>

        </div>
        <?php
    } else {
        file_put_contents('widget_debug.log', "NO mostrando widget - Estado es 'available'\n", FILE_APPEND);
    }
} else {
    file_put_contents('widget_debug.log', "NO hay usuario logueado\n", FILE_APPEND);
}
?>

<!-- ========== JAVASCRIPT DEL WIDGET - SIEMPRE ACTIVO ========== -->
<script>
// Función para cambiar a disponible sin redirigir
function setAvailable() {
    const userId = <?php echo $user_id ?? 'null'; ?>;
    
    if (!userId) {
        console.log('No hay usuario logueado');
        return;
    }
    
    // Mostrar mensaje de carga
    const statusText = document.getElementById('status-text');
    const btn = document.getElementById('set-available-btn');
    
    if (statusText) {
        statusText.innerHTML = '⏳ Actualizando estado...';
        statusText.style.fontSize = '15px';
    }
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Procesando...';
        btn.classList.add('loading');
    }
    
    fetch('<?php echo site_url(); ?>/status_api/set_available?user_id=' + userId)
    .then(response => response.text())
    .then(data => {
        // Ocultar el banner inmediatamente
        const banner = document.getElementById('call-status-banner');
        if (banner) {
            banner.style.display = 'none';
        }
        
        // Mostrar ventana de éxito
        showSuccessModal();
    })
    .catch(error => {
        console.log('Error:', error);
        // Mostrar mensaje de error en el banner
        if (statusText) {
            statusText.innerHTML = '❌ Error al actualizar. Intenta nuevamente.';
            statusText.style.color = '#dc3545';
        }
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Aceptar llamadas';
            btn.classList.remove('loading');
        }
    });
}

// Nueva función para mostrar la ventana de éxito
function showSuccessModal() {
    // Crear overlay
    const overlay = document.createElement('div');
    overlay.className = 'modal-ghost-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000000;
        animation: fadeIn 0.3s ease;
    `;
    
    // Crear modal
    const modal = document.createElement('div');
    modal.className = 'modal-ghost';
    modal.style.cssText = `
        background: white;
        border-radius: 12px;
        padding: 32px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        border: 1px solid #e5e7eb;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        animation: slideUp 0.3s ease;
    `;
    
    modal.innerHTML = `
        <div style="margin-bottom: 20px;">
            <div style="width: 60px; height: 60px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <span style="font-size: 24px; color: white;">✓</span>
            </div>
            <h3 style="color: #111827; font-size: 20px; font-weight: 600; margin: 0 0 8px 0;">Estado Actualizado</h3>
            <p style="color: #6b7280; font-size: 16px; margin: 0; line-height: 1.5;">Ya puedes recibir llamadas correctamente</p>
        </div>
        <button id="close-success-modal" class="btn-ghost-success" style="background: #10b981; color: white; border: 0; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500;">
            Entendido
        </button>
    `;
    
    // Agregar estilos de animación
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    // Ensamblar modal
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Eventos para cerrar
    const closeBtn = modal.querySelector('#close-success-modal');
    const closeModal = () => {
        overlay.style.animation = 'fadeOut 0.3s ease';
        modal.style.animation = 'slideDown 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(overlay);
            document.head.removeChild(style);
        }, 300);
    };
    
    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });
    
    // Auto-cerrar después de 3 segundos
    setTimeout(closeModal, 3000);
    
    // Agregar animación de salida
    style.textContent += `
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes slideDown {
            from { transform: translateY(0); opacity: 1; }
            to { transform: translateY(30px); opacity: 0; }
        }
    `;
}

// Asignar evento al botón cuando esté disponible
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('set-available-btn');
    if (btn) {
        btn.addEventListener('click', setAvailable);
    }
});

// Verificar el estado cada 5 segundos - SIEMPRE ACTIVO
setInterval(function() {
    const userId = <?php echo $user_id ?? 'null'; ?>;
    
    if (!userId) {
        return;
    }
    
    fetch('<?php echo site_url(); ?>/status_api/get_status?user_id=' + userId)
    .then(response => response.text())
    .then(data => {
        try {
            const result = JSON.parse(data);
            if (result.success) {
                const banner = document.getElementById('call-status-banner');
                const statusText = document.getElementById('status-text');
                
                if (result.status === 'available') {
                    // Ocultar banner si el estado es available
                    if (banner) {
                        banner.style.display = 'none';
                    }
                } else {
                    // Mostrar banner si el estado no es available
                    if (banner) {
                        banner.style.display = 'flex';
                        
                        // Actualizar el texto del estado y el estilo
                        if (statusText) {
                            const statusMap = {
                                'busy': '⏳  Actualmente no puedes recibir llamadas. Si estás DISPONIBLE, presiona el botón para activarlas.',
                                'in_call': '📞 Actualmente no puedes recibir llamadas. Si estás DISPONIBLE, presiona el botón para activarlas.',
                                'do_not_disturb': '🔕 Actualmente no puedes recibir llamadas. Si estás DISPONIBLE, presiona el botón para activarlas.'
                            };
                            statusText.textContent = statusMap[result.status] || '📞 Estado: ' + result.status;
                        }
                        
                        // Actualizar el color del banner según el estado
                        const statusColors = {
                            'busy': 'rgba(0,0,0,0.8)',
                            'in_call': 'rgba(0,0,0,0.8)',
                            'do_not_disturb': 'rgba(0,0,0,0.8)'
                        };
                        banner.style.background = statusColors[result.status] || 'rgba(0,0,0,0.8)';
                    } else {
                        // Si el banner no existe, crearlo dinámicamente
                        createBanner(result.status);
                    }
                }
            }
        } catch (e) {
            console.log('Error parseando respuesta:', e);
        }
    })
    .catch(error => {
        console.log('Error verificando estado:', error);
    });
}, 5000); // Verificar cada 5 segundos


// Función para crear el banner dinámicamente
function createBanner(status) {
    // Solo crear banner si el estado NO es 'available'
    if (status === 'available') {
        return;
    }
    
    const statusMap = {
        'busy': '⏳ Actualmente no puedes recibir llamadas. Si estás DISPONIBLE, presiona el botón para activarlas.',
        'in_call': '📞 Actualmente no puedes recibir llamadas. Si estás DISPONIBLE, presiona el botón para activarlas.',
        'do_not_disturb': '🔕 Actualmente no puedes recibir llamadas. Si estás DISPONIBLE, presiona el botón para activarlas.'
    };
    
    const statusColors = {
        'busy': 'rgba(0,0,0,0.8)',
        'in_call': 'rgba(0,0,0,0.8)',
        'do_not_disturb': 'rgba(0,0,0,0.8)'
    };
    
    const statusText = statusMap[status] || '📞 Estado: ' + status;
    const backgroundColor = statusColors[status] || 'rgba(0,0,0,0.8)';
    
    // Crear el banner
    const banner = document.createElement('div');
    banner.id = 'call-status-banner';
    banner.style.cssText = `position:fixed;bottom:20px;left:50%;transform:translateX(-50%);z-index:9999;background:${backgroundColor};color:#fff;padding:16px 20px;border-radius:12px;display:flex;gap:12px;align-items:center;justify-content:center;box-shadow:0 10px 25px rgba(0,0,0,.3);max-width:600px;min-height:60px;pointer-events:auto;isolation:isolate;font-size:15px;font-weight:500;border:2px solid rgba(255,255,255,0.1);`;
    
    banner.innerHTML = `
        <span id="status-text" style="line-height: 1.4;">${statusText}</span>
        <button id="set-available-btn" class="btn-ghost-success" style="border:0;padding:10px 16px;border-radius:8px;cursor:pointer;background:#10b981;color:#fff;font-size:14px;font-weight:500;min-height:40px;">Aceptar llamadas</button>
    `;
    
    // Agregar al body
    document.body.appendChild(banner);
    
    // Asignar evento al botón
    const btn = document.getElementById('set-available-btn');
    if (btn) {
        btn.addEventListener('click', setAvailable);
    }
}

const loadingStyle = document.createElement('style');
loadingStyle.textContent = `
    .loading {
        position: relative;
        color: transparent !important;
    }
    .loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(loadingStyle);
</script>
