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