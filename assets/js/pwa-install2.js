/**
 * PWA Install Prompt Handler
 * Maneja la instalación de la aplicación PWA
 */

class PWAInstallHandler {
    constructor() {
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.installButton = null;
        
        this.init();
    }
    
    init() {
        // Verificar si ya está instalada
        this.checkInstallStatus();
        
        // Escuchar el evento beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('PWA: beforeinstallprompt event fired');
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
        });
        
        // Escuchar el evento appinstalled
        window.addEventListener('appinstalled', () => {
            console.log('PWA: App installed successfully');
            this.isInstalled = true;
            this.hideInstallButton();
            this.showInstallSuccessMessage();
        });
        
        // Detectar si se ejecuta como PWA
        this.detectPWAMode();
        
        // Crear el botón de instalación
        //this.createInstallButton();
    }
    
    checkInstallStatus() {
        // Verificar si la app está en modo standalone (instalada)
        if (window.matchMedia('(display-mode: standalone)').matches || 
            window.navigator.standalone === true) {
            this.isInstalled = true;
            console.log('PWA: App is already installed');
        }
    }
    
    detectPWAMode() {
        // Detectar si se ejecuta como PWA
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isInStandaloneMode = ('standalone' in window.navigator) && (window.navigator.standalone);
        
        if (isStandalone || (isIOS && isInStandaloneMode)) {
            document.body.classList.add('pwa-mode');
            console.log('PWA: Running in PWA mode');
        }
    }
    
    createInstallButton() {
        // Crear el botón de instalación
        const installButton = document.createElement('button');
        installButton.id = 'pwa-install-button';
        installButton.className = 'pwa-install-btn';
        installButton.innerHTML = `
            <i class="fas fa-download"></i>
            <span>Instalar App</span>
        `;
        installButton.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            z-index: 1000;
            display: none;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        `;
        
        // Agregar estilos hover
        installButton.addEventListener('mouseenter', () => {
            installButton.style.transform = 'translateY(-2px)';
            installButton.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.4)';
        });
        
        installButton.addEventListener('mouseleave', () => {
            installButton.style.transform = 'translateY(0)';
            installButton.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.3)';
        });
        
        // Agregar evento de clic
        installButton.addEventListener('click', () => {
            this.installApp();
        });
        
        document.body.appendChild(installButton);
        this.installButton = installButton;
    }
    
    showInstallButton() {
        if (this.installButton && !this.isInstalled) {
            this.installButton.style.display = 'flex';
            
            // Mostrar con animación
            setTimeout(() => {
                this.installButton.style.opacity = '1';
                this.installButton.style.transform = 'translateY(0)';
            }, 100);
        }
    }
    
    hideInstallButton() {
        if (this.installButton) {
            this.installButton.style.opacity = '0';
            this.installButton.style.transform = 'translateY(20px)';
            setTimeout(() => {
                this.installButton.style.display = 'none';
            }, 300);
        }
    }
    
    async installApp() {
        if (!this.deferredPrompt) {
            this.showIOSInstallInstructions();
            return;
        }
        
        try {
            // Mostrar el prompt de instalación
            this.deferredPrompt.prompt();
            
            // Esperar la respuesta del usuario
            const { outcome } = await this.deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('PWA: User accepted the install prompt');
                this.showInstallSuccessMessage();
            } else {
                console.log('PWA: User dismissed the install prompt');
            }
            
            // Limpiar el prompt
            this.deferredPrompt = null;
            this.hideInstallButton();
            
        } catch (error) {
            console.error('PWA: Error during installation:', error);
            this.showInstallErrorMessage();
        }
    }
    
    showIOSInstallInstructions() {
        // Mostrar instrucciones para iOS
        const modal = document.createElement('div');
        modal.className = 'pwa-install-modal';
        modal.innerHTML = `
            <div class="pwa-install-modal-content">
                <div class="pwa-install-modal-header">
                    <h3>Instalar en iOS</h3>
                    <button class="pwa-install-modal-close">&times;</button>
                </div>
                <div class="pwa-install-modal-body">
                    <p>Para instalar esta aplicación en tu iPhone o iPad:</p>
                    <ol>
                        <li>Toca el botón <strong>Compartir</strong> <span style="font-size: 18px;">📤</span> en la parte inferior de la pantalla</li>
                        <li>Desplázate hacia abajo y toca <strong>"Agregar a pantalla de inicio"</strong></li>
                        <li>Toca <strong>"Agregar"</strong> en la esquina superior derecha</li>
                    </ol>
                    <div class="pwa-install-modal-ios-icon">
                        <span style="font-size: 48px;">📱</span>
                    </div>
                </div>
            </div>
        `;
        
        // Agregar estilos
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        `;
        
        const content = modal.querySelector('.pwa-install-modal-content');
        content.style.cssText = `
            background: white;
            border-radius: 15px;
            padding: 0;
            max-width: 400px;
            width: 90%;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        `;
        
        const header = modal.querySelector('.pwa-install-modal-header');
        header.style.cssText = `
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        `;
        
        const body = modal.querySelector('.pwa-install-modal-body');
        body.style.cssText = `
            padding: 20px;
            text-align: center;
        `;
        
        const closeBtn = modal.querySelector('.pwa-install-modal-close');
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        const iosIcon = modal.querySelector('.pwa-install-modal-ios-icon');
        iosIcon.style.cssText = `
            margin: 20px 0;
        `;
        
        // Eventos
        closeBtn.addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
        
        document.body.appendChild(modal);
    }
    
    showInstallSuccessMessage() {
        this.showToast('¡Aplicación instalada correctamente!', 'success');
    }
    
    showInstallErrorMessage() {
        this.showToast('Error al instalar la aplicación', 'error');
    }
    
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `pwa-toast pwa-toast-${type}`;
        toast.textContent = message;
        
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
            z-index: 10001;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        `;
        
        document.body.appendChild(toast);
        
        // Animar entrada
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new PWAInstallHandler();
});

// Exportar para uso global
window.PWAInstallHandler = PWAInstallHandler;
