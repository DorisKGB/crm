// Configuración y manejo de Firebase Cloud Messaging
console.log('🔥 Firebase config script cargado');

class FirebaseHandler {
    constructor() {
      this.app = null;
      this.messaging = null;
      this.vapidKey = 'BNp7WSOpc0sB5yZ1Z-MbRP_s7o3oA0_HAG1DCWnAJAWCZpvofqR4i_HBosvsUobl90voUxGUrPsEna3gfmsZ8cA';
       this.isTauriApp = window._TAURI_ !== undefined;
    }
  
    // Configuración de Firebase
    getFirebaseConfig() {
      return {
        apiKey: "AIzaSyAdI7o9CeC8BuHNkarhdglyrUvToTrup_M",
        authDomain: "intranet-message.firebaseapp.com",
        projectId: "intranet-message",
        storageBucket: "intranet-message.firebasestorage.app",
        messagingSenderId: "200044606467",
        appId: "1:200044606467:web:cd2dc2bb542a1327c38a36",
        measurementId: "G-V5N0S9ZEZ0"
      };
    }
  
    // Inicializar Firebase
    /*async initializeFirebase() {
      try {
        const { initializeApp } = await import("https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js");
        const { getMessaging, getToken, onMessage } = await import("https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js");
        
        this.app = initializeApp(this.getFirebaseConfig());
        this.messaging = getMessaging(this.app);
        
        // Hacer disponible globalmente
        window.firebaseApp = this.app;
        window.firebaseMessaging = this.messaging;
        
        // Registrar Service Worker
        await this.registerServiceWorker();
        
        // Configurar listener de mensajes
        this.setupMessageListener();
        
        return true;
      } catch (error) {
        //console.error('Error al inicializar Firebase:', error);
        return false;
      }
    }*/

     async initializeFirebase() {
      try {
        const { initializeApp } = await import("https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js");
        const { getMessaging, getToken, onMessage } = await import("https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js");
        
        this.app = initializeApp(this.getFirebaseConfig());
        this.messaging = getMessaging(this.app);
        
        // Hacer disponible globalmente
        window.firebaseApp = this.app;
        window.firebaseMessaging = this.messaging;
        
        // Registrar Service Worker solo si NO es Tauri
        if (!this.isTauriApp) {
          await this.registerServiceWorker();
        }
        
        // Configurar listener de mensajes
        this.setupMessageListener();
        
        return true;
      } catch (error) {
        //console.error('Error al inicializar Firebase:', error);
        return false;
      }
    }
  
    // Registrar Service Worker
    async registerServiceWorker() {
      if ('serviceWorker' in navigator) {
        try {
          const baseUrl = window.AppHelper?.baseUrl || '';
          const swUrl = baseUrl + 'firebase-messaging-sw.js';
          const registration = await navigator.serviceWorker.register(swUrl);
          //console.log('Service Worker registrado correctamente:', registration);
          return registration;
        } catch (error) {
          //console.log('Error al registrar Service Worker:', error);
        }
      }
    }
  
    // Solicitar permisos de notificación
    async requestNotificationPermission() {
      try {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
          console.log('Permisos de notificación concedidos');
          return true;
        } else {
          console.log('Permisos de notificación denegados');
          return false;
        }
      } catch (error) {
        console.error('Error al solicitar permisos:', error);
        return false;
      }
    }
  
    // Obtener token FCM
    /*async getFCMToken() {
      try {
        const { getToken } = await import("https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js");
        const baseUrl = window.AppHelper?.baseUrl || '';
        const swUrl = baseUrl + 'firebase-messaging-sw.js';
        console.log("Ruta Obtener FCM:" + swUrl);
        const registration = await navigator.serviceWorker.getRegistration(swUrl);
        console.log("Registration:" + registration);

        const token = await getToken(this.messaging, {
          vapidKey: this.vapidKey,
          serviceWorkerRegistration: registration
        });
        console.log("VapidKey :" + this.vapidKey);
        console.log("Token :" + token);
        
        console.log('FCM Token:', token);
        return token;
      } catch (error) {
        //console.error('Error al obtener el token FCM:', error);
        return null;
      }
    }*/

    async getFCMToken() {
      try {
        const { getToken } = await import("https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js");
        
        let tokenOptions = {
          vapidKey: this.vapidKey
        };
        
        // Solo agregar service worker si NO es Tauri y está disponible
        if (!this.isTauriApp) {
          const baseUrl = window.AppHelper?.baseUrl || '';
          const swUrl = baseUrl + 'firebase-messaging-sw.js';
          console.log("Ruta Obtener FCM:" + swUrl);
          const registration = await navigator.serviceWorker.getRegistration(swUrl);
          if (registration) {
            tokenOptions.serviceWorkerRegistration = registration;
          }
        }
        console.log("Es una aplicacion tauri");
        const token = await getToken(this.messaging, tokenOptions);
        console.log("Token Generado : " + token);
        //console.log('FCM Token:', token);
        return token;
      } catch (error) {
        //console.error('Error al obtener el token FCM:', error);
        return null;
      }
    }
  
    // Configurar listener de mensajes en primer plano
    setupMessageListener() {
      import("https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js")
        .then(({ onMessage }) => {
          onMessage(this.messaging, (payload) => {
            //console.log('Mensaje recibido en primer plano:', payload);
            
/*             if (payload.notification) {
              const notificationTitle = payload.notification.title;
              const notificationOptions = {
                body: payload.notification.body,
                icon: '/crm_rubymed/assets/images/logoNotification.png',//payload.notification.icon || '/assets/images/logo.png',
                badge: '/crm_rubymed/assets/images/badge.png', //'/assets/images/badge.png'
              };
              
              new Notification(notificationTitle, notificationOptions);
            } */ 
          });
        });
    }

  // Enviar token al servidor
  async sendTokenToServer(token) {
    try {
      const baseUrl = window.AppHelper?.baseUrl || '';
      const response = await fetch(baseUrl + 'index.php/api/save-fcm-token', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          token: token,
          user_id: window.AppHelper?.userId && window.AppHelper.userId !== '' ? window.AppHelper.userId : null
        })
      });

      console.log('Enviando token para user_id:', window.AppHelper?.userId && window.AppHelper.userId !== '' ? window.AppHelper.userId : null);

       if (response.ok) {
        const data = await response.text();
        console.log('Token FCM guardado en el servidor:', data);
      } else {
        const errorData = await response.text();
        console.error('Error del servidor:', errorData);
        console.error('Status:', response.status);
        console.error('Status Text:', response.statusText);
      }
    } catch (error) {
      console.error('Error de red al enviar token:', error);
    }
  }

  // Inicialización completa
  async initialize() {
    console.log('🔥 Inicializando Firebase...');
    const initialized = await this.initializeFirebase();
    
    if (initialized) {
      console.log('✅ Firebase inicializado correctamente');
      const hasPermission = await this.requestNotificationPermission();
      
      if (hasPermission) {
        console.log('🔑 Obteniendo token FCM...');
        const token = await this.getFCMToken();
        
         if (token) {
        console.log('📱 Token FCM obtenido:', token ? 'SÍ' : 'NO');
        console.log('👤 Usuario ID disponible:', window.AppHelper?.userId || 'NO');
        // Verificar si el usuario está logueado y enviar token
        if (window.AppHelper?.userId && window.AppHelper.userId !== '') {
            console.log('📤 Enviando token al servidor...');
            await this.sendTokenToServer(token);
          } else {
            console.log('⚠️ Usuario no logueado, token no enviado');
          }
        } else {
          console.log('❌ No se pudo obtener token FCM');
        }
      } else {
        console.log('❌ Permisos de notificación denegados');
      }
    } else {
      console.log('❌ Error al inicializar Firebase');
    }
  }
}

// Crear instancia global
window.firebaseHandler = new FirebaseHandler();

// Inicializar Firebase
console.log('🔥 Firebase config script completamente cargado');

// Función para inicializar Firebase
function initializeFirebase() {
  console.log('🔥 Inicializando Firebase...');
  console.log('👤 AppHelper disponible:', !!window.AppHelper);
  console.log('👤 Usuario ID:', window.AppHelper?.userId || 'NO DISPONIBLE');
  
  // Solo inicializar si hay un usuario logueado
  if (window.AppHelper?.userId && window.AppHelper.userId !== '') {
    window.firebaseHandler.initialize();
  } else {
    console.log('⚠️ Usuario no logueado, Firebase no se inicializará');
  }
}

// Inicializar inmediatamente si el DOM ya está listo, o esperar
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeFirebase);
} else {
  // DOM ya está listo, inicializar inmediatamente
  initializeFirebase();
}

// Exponer funciones globalmente para compatibilidad
window.requestNotificationPermission = () => window.firebaseHandler.requestNotificationPermission();
window.getFCMToken = () => window.firebaseHandler.getFCMToken();
window.sendTokenToServer = (token) => window.firebaseHandler.sendTokenToServer(token);

// Función para forzar la generación y envío de token (para debug)
window.forceGenerateToken = async () => {
  console.log('🔄 Forzando generación de token...');
  const handler = window.firebaseHandler;
  if (handler) {
    const token = await handler.getFCMToken();
    if (token && window.AppHelper?.userId && window.AppHelper.userId !== '') {
      await handler.sendTokenToServer(token);
      console.log('✅ Token generado y enviado exitosamente');
      return token;
    } else {
      console.log('❌ No se pudo generar o enviar token');
      return null;
    }
  }
  return null;
};

// Función para inicializar Firebase manualmente (útil después del login)
window.initFirebase = () => {
  console.log('🔄 Inicializando Firebase manualmente...');
  initializeFirebase();
};    