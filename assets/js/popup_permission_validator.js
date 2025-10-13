class PopupPermissionValidator {
    constructor() {
        this.permissionGranted = false;
        this.testWindow = null;
    }

    async checkPopupPermission() {
        return new Promise((resolve) => {
            this.testWindow = window.open('', 'test', 'width=1,height=1,left=-1000,top=-1000');
            
            setTimeout(() => {
                if (this.testWindow && !this.testWindow.closed && this.testWindow.location) {
                    this.testWindow.close();
                    this.permissionGranted = true;
                    resolve(true);
                } else {
                    this.permissionGranted = false;
                    resolve(false);
                }
            }, 100);
        });
    }

    showPopupInstructions() {
        const browserInstructions = BrowserDetector.getSpecificInstructions();
        
        const message = `
VENTANAS EMERGENTES BLOQUEADAS

Para realizar videollamadas necesitas habilitar las ventanas emergentes.

Instrucciones para ${browserInstructions.name}:
${browserInstructions.steps.map((step, index) => `${index + 1}. ${step}`).join('\n')}

Después de cambiar la configuración, haz clic en "Aceptar" para probar de nuevo.
        `;
        
        return Promise.resolve({
            isConfirmed: confirm(message)
        });
    }

    async validateAndRequest() {
        const hasPermission = await this.checkPopupPermission();
        
        if (hasPermission) {
            return { success: true, message: 'Permisos de ventanas emergentes habilitados' };
        }

        const result = await this.showPopupInstructions();
        
        if (result.isConfirmed) {
            const retestPermission = await this.checkPopupPermission();
            
            if (retestPermission) {
                alert('✅ ¡Perfecto! Las ventanas emergentes están habilitadas. Iniciando videollamada...');
                return { success: true, message: 'Permisos habilitados correctamente' };
            } else {
                const reload = confirm('❌ Las ventanas emergentes siguen bloqueadas.\n\n¿Quieres recargar la página para intentar de nuevo?');
                if (reload) {
                    window.location.reload();
                }
                return { success: false, message: 'Permisos no concedidos' };
            }
        }

        return { success: false, message: 'Usuario canceló la configuración' };
    }
}