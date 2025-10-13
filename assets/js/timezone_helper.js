/**
 * Helper para manejar zonas horarias en JavaScript
 * Compatible con la configuración del servidor (America/New_York)
 */

window.TimezoneHelper = {
    /**
     * Obtiene la zona horaria del servidor desde la configuración
     * @returns {string} Zona horaria del servidor
     */
    getServerTimezone: function() {
        // Obtener la zona horaria desde la configuración de excusas
        if (window.GlobalExcuseNotifications && window.GlobalExcuseNotifications.config && window.GlobalExcuseNotifications.config.serverTimezone) {
            return window.GlobalExcuseNotifications.config.serverTimezone;
        }
        
        // Obtener la zona horaria desde la configuración global
        if (window.AppHelper && window.AppHelper.settings && window.AppHelper.settings.timezone) {
            return window.AppHelper.settings.timezone;
        }
        
        // Fallback: usar la zona horaria del servidor (America/New_York)
        return 'America/New_York';
    },

    /**
     * Convierte una fecha local a la zona horaria del servidor
     * @param {Date} date - Fecha a convertir
     * @returns {string} Fecha en formato ISO para la zona horaria del servidor
     */
    toServerTimezone: function(date) {
        if (!date) {
            date = new Date();
        }
        
        const serverTimezone = this.getServerTimezone();
        
        try {
            // Crear una fecha en la zona horaria del servidor
            const serverDate = new Date(date.toLocaleString("en-US", {timeZone: serverTimezone}));
            
            // Convertir a formato ISO pero manteniendo la hora local del servidor
            const year = serverDate.getFullYear();
            const month = String(serverDate.getMonth() + 1).padStart(2, '0');
            const day = String(serverDate.getDate()).padStart(2, '0');
            const hours = String(serverDate.getHours()).padStart(2, '0');
            const minutes = String(serverDate.getMinutes()).padStart(2, '0');
            const seconds = String(serverDate.getSeconds()).padStart(2, '0');
            const milliseconds = String(serverDate.getMilliseconds()).padStart(3, '0');
            
            return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}.${milliseconds}Z`;
        } catch (error) {
            console.warn('Error converting to server timezone:', error);
            // Fallback: usar UTC
            return date.toISOString();
        }
    },

    /**
     * Obtiene el timestamp actual en la zona horaria del servidor
     * @returns {string} Timestamp actual en formato ISO para la zona horaria del servidor
     */
    now: function() {
        return this.toServerTimezone(new Date());
    },

    /**
     * Obtiene un timestamp de hace X minutos en la zona horaria del servidor
     * @param {number} minutes - Minutos hacia atrás
     * @returns {string} Timestamp en formato ISO para la zona horaria del servidor
     */
    minutesAgo: function(minutes) {
        const date = new Date(Date.now() - (minutes * 60 * 1000));
        return this.toServerTimezone(date);
    },

    /**
     * Obtiene un timestamp de hace X horas en la zona horaria del servidor
     * @param {number} hours - Horas hacia atrás
     * @returns {string} Timestamp en formato ISO para la zona horaria del servidor
     */
    hoursAgo: function(hours) {
        const date = new Date(Date.now() - (hours * 60 * 60 * 1000));
        return this.toServerTimezone(date);
    },

    /**
     * Convierte un timestamp ISO a la zona horaria del servidor
     * @param {string} isoString - Timestamp ISO (UTC)
     * @returns {string} Timestamp en formato ISO para la zona horaria del servidor
     */
    fromUTC: function(isoString) {
        if (!isoString) {
            return this.now();
        }
        
        const date = new Date(isoString);
        return this.toServerTimezone(date);
    },

    /**
     * Compara dos timestamps considerando la zona horaria del servidor
     * @param {string} timestamp1 - Primer timestamp
     * @param {string} timestamp2 - Segundo timestamp
     * @returns {number} -1 si timestamp1 < timestamp2, 0 si son iguales, 1 si timestamp1 > timestamp2
     */
    compare: function(timestamp1, timestamp2) {
        const date1 = new Date(timestamp1);
        const date2 = new Date(timestamp2);
        
        if (date1 < date2) return -1;
        if (date1 > date2) return 1;
        return 0;
    },

    /**
     * Verifica si un timestamp es más reciente que otro
     * @param {string} timestamp1 - Timestamp a verificar
     * @param {string} timestamp2 - Timestamp de referencia
     * @returns {boolean} true si timestamp1 es más reciente
     */
    isNewer: function(timestamp1, timestamp2) {
        return this.compare(timestamp1, timestamp2) > 0;
    },

    /**
     * Obtiene la diferencia en minutos entre dos timestamps
     * @param {string} timestamp1 - Primer timestamp
     * @param {string} timestamp2 - Segundo timestamp
     * @returns {number} Diferencia en minutos
     */
    getDifferenceInMinutes: function(timestamp1, timestamp2) {
        const date1 = new Date(timestamp1);
        const date2 = new Date(timestamp2);
        return Math.abs(date1 - date2) / (1000 * 60);
    },

    /**
     * Debug: Muestra información de zona horaria
     * @param {string} label - Etiqueta para el debug
     * @param {string} timestamp - Timestamp a analizar
     */
    debug: function(label, timestamp) {
        const serverTimezone = this.getServerTimezone();
        const localDate = new Date(timestamp);
        const serverDate = new Date(localDate.toLocaleString("en-US", {timeZone: serverTimezone}));
        
        console.log(`🕐 ${label}:`, {
            original: timestamp,
            serverTimezone: serverTimezone,
            localTime: localDate.toLocaleString(),
            serverTime: serverDate.toLocaleString(),
            difference: this.getDifferenceInMinutes(timestamp, this.now())
        });
    }
};

// Función de conveniencia para uso global
window.getServerTime = function() {
    return TimezoneHelper.now();
};

window.getServerTimeMinutesAgo = function(minutes) {
    return TimezoneHelper.minutesAgo(minutes);
};

window.getServerTimeHoursAgo = function(hours) {
    return TimezoneHelper.hoursAgo(hours);
};
