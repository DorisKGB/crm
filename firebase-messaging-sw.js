// firebase-messaging-sw.js
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// Tu configuración de Firebase (debe ser la misma que en head.php)
const firebaseConfig = {
    apiKey: "AIzaSyAdI7o9CeC8BuHNkarhdglyrUvToTrup_M",
    authDomain: "intranet-message.firebaseapp.com",
    projectId: "intranet-message",
    storageBucket: "intranet-message.firebasestorage.app",
    messagingSenderId: "200044606467",
    appId: "1:200044606467:web:cd2dc2bb542a1327c38a36",
    measurementId: "G-V5N0S9ZEZ0"
      };

// Inicializar Firebase
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Manejar mensajes en background
messaging.onBackgroundMessage((payload) => {
  console.log('Mensaje recibido en background:', payload);
  //duplica notificacion
/*   const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: payload.notification.icon || '/assets/images/logo.png',
    badge: '/assets/images/badge.png',
    data: payload.data
  };
  
  self.registration.showNotification(notificationTitle, notificationOptions); */
});

// Manejar clics en notificaciones
self.addEventListener('notificationclick', (event) => {
  console.log('Notificación clickeada:', event);
  event.notification.close();
  
  // Aquí puedes manejar la navegación cuando se hace clic en la notificación
  event.waitUntil(
    clients.openWindow('/') // Redirigir a la página principal
  );
});
