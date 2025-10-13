<?php

/**
 * Document Orientation Helper
 * 
 * Helper para manejar la detección y rotación automática de documentos
 * Funciones reutilizables para detectar orientación horizontal/vertical
 * y aplicar rotaciones automáticas o manuales
 * 
 * @author  Sistema CRM
 * @version 1.0
 */

if (!function_exists('document_orientation_js')) {
    /**
     * Genera el código JavaScript para detección y manejo de orientación de documentos
     * 
     * @param array $options Opciones de configuración:
     *   - 'auto_rotate' => bool - Si debe rotar automáticamente documentos horizontales (default: false)
     *   - 'show_rotate_button' => bool - Si debe mostrar botón de rotación manual (default: true)
     *   - 'show_orientation_info' => bool - Si debe mostrar información de orientación (default: true)
     *   - 'threshold' => float - Umbral de aspect ratio para detectar horizontal (default: 1.2)
     *   - 'canvas_id' => string - ID del canvas (default: 'templateCanvas')
     *   - 'marker_id' => string - ID del marcador (default: 'marker')
     * 
     * @return string Código JavaScript
     */
    function document_orientation_js($options = [])
    {
        // Opciones por defecto
        $defaults = [
            'auto_rotate' => false,
            'show_rotate_button' => true,
            'show_orientation_info' => true,
            'threshold' => 1.2,
            'canvas_id' => 'templateCanvas',
            'marker_id' => 'marker',
            'orientation_info_id' => 'orientationInfo',
            'rotate_button_id' => 'rotateBtn',
            'canvas_container_id' => 'canvasContainer',
            'canvas_wrapper_id' => 'canvasWrapper'
        ];
        
        $config = array_merge($defaults, $options);
        
        // Convertir configuración a JSON para JavaScript
        $jsConfig = json_encode($config);
        
        return "
        <script>
        // === DOCUMENT ORIENTATION HELPER ===
        (function() {
            // Configuración
            const config = {$jsConfig};
            
            // Variables globales para orientación
            window.documentOrientation = {
                originalImage: new Image(),
                imageRotation: 0, // 0, 90, 180, 270
                isHorizontal: false,
                aspectRatio: 1,
                
                // Función para detectar orientación
                detectOrientation: function(img) {
                    const width = img.naturalWidth || img.width;
                    const height = img.naturalHeight || img.height;
                    
                    this.aspectRatio = width / height;
                    this.isHorizontal = this.aspectRatio > config.threshold;
                    
                    // ✅ CORREGIR: Asignar orientación basada en aspect ratio
                    this.orientation = this.isHorizontal ? 'landscape' : 'portrait';
                    
                    console.log(`Dimensiones: \${width}x\${height}, Aspect Ratio: \${this.aspectRatio.toFixed(2)}, Es horizontal: \${this.isHorizontal}, Orientación: \${this.orientation}`);
                    
                    // Decidir rotación inicial
                    if (config.auto_rotate && this.isHorizontal) {
                        this.imageRotation = 90;
                        console.log('Documento horizontal detectado - Rotando automáticamente a vertical');
                        this.updateOrientationInfo('Documento horizontal - Rotado automáticamente a vertical', 'warning');
                    } else if (this.isHorizontal) {
                        this.imageRotation = 0;
                        console.log('Documento horizontal detectado - Manteniendo orientación original');
                        this.updateOrientationInfo('Documento horizontal - Vista horizontal por defecto', 'info');
                    } else {
                        this.imageRotation = 0;
                        console.log('Documento vertical detectado');
                        this.updateOrientationInfo('Documento vertical detectado', 'success');
                    }
                    
                    return {
                        isHorizontal: this.isHorizontal,
                        aspectRatio: this.aspectRatio,
                        suggestedRotation: this.imageRotation,
                        orientation: this.orientation || (this.isHorizontal ? 'landscape' : 'portrait')
                    };
                },
                
                // Función para rotar imagen
                rotateImage: function(img, rotation) {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    const width = img.naturalWidth || img.width;
                    const height = img.naturalHeight || img.height;
                    
                    // Configurar dimensiones del canvas según rotación
                    if (rotation === 90 || rotation === 270) {
                        canvas.width = height;
                        canvas.height = width;
                    } else {
                        canvas.width = width;
                        canvas.height = height;
                    }
                    
                    // Aplicar rotación
                    ctx.save();
                    
                    switch (rotation) {
                        case 90:
                            ctx.translate(height, 0);
                            ctx.rotate(Math.PI / 2);
                            break;
                        case 180:
                            ctx.translate(width, height);
                            ctx.rotate(Math.PI);
                            break;
                        case 270:
                            ctx.translate(0, width);
                            ctx.rotate(-Math.PI / 2);
                            break;
                        default:
                            // Sin rotación
                            break;
                    }
                    
                    ctx.drawImage(img, 0, 0);
                    ctx.restore();
                    
                    return canvas.toDataURL('image/png');
                },
                
                // Función para rotar manualmente
                rotateManually: function() {
                    if (!this.originalImage.src) {
                        alert('Primero debes subir un documento');
                        return;
                    }
                    
                    // Incrementar rotación en 90°
                    this.imageRotation = (this.imageRotation + 90) % 360;
                    console.log(`Rotación manual: \${this.imageRotation}°`);
                    
                    // Aplicar nueva rotación
                    const rotatedDataUrl = this.rotateImage(this.originalImage, this.imageRotation);
                    
                    // Actualizar imagen principal
                    if (window.image) {
                        window.image.src = rotatedDataUrl;
                    }
                    
                    // Ocultar marcador ya que las coordenadas cambiaron
                    const marker = document.getElementById(config.marker_id);
                    if (marker) {
                        marker.style.display = 'none';
                    }
                    
                    if (window.signatureCoordinates) {
                        window.signatureCoordinates = { x: null, y: null };
                    }
                    
                    // Actualizar info de orientación
                    const rotationText = ['0° (Original)', '90° (Rotado)', '180° (Invertido)', '270° (Rotado)'];
                    this.updateOrientationInfo(`Rotación manual: \${rotationText[this.imageRotation / 90]}`, 'info');
                    
                    // Ajustar layout usando la función interna
                    setTimeout(() => {
                        this.adjustLayout();
                    }, 100);
                    
                    return rotatedDataUrl;
                },
                
                // Función para actualizar info de orientación
                updateOrientationInfo: function(text, type = 'info') {
                    if (!config.show_orientation_info) return;
                    
                    const info = document.getElementById(config.orientation_info_id);
                    if (info) {
                        info.textContent = text;
                        info.className = `text-\${type === 'success' ? 'success' : type === 'warning' ? 'warning' : type === 'info' ? 'info' : 'muted'}`;
                    }
                },
                
                // Función para ajustar layout según orientación
                adjustLayout: function() {
                    if (!window.image) return;
                    
                    const imgWidth = window.image.naturalWidth || window.image.width;
                    const imgHeight = window.image.naturalHeight || window.image.height;
                    const imgAspectRatio = imgWidth / imgHeight;
                    const canvasWrapper = document.getElementById(config.canvas_wrapper_id);
                    
                    if (canvasWrapper) {
                        if (imgAspectRatio > config.threshold) {
                            // Documento horizontal - optimizar espacio
                            canvasWrapper.style.maxWidth = '100%';
                            console.log('Layout ajustado para documento horizontal');
                        } else {
                            // Documento vertical - espacio estándar
                            canvasWrapper.style.maxWidth = '100%';
                            console.log('Layout ajustado para documento vertical');
                        }
                    }
                },
                
                // Función para inicializar
                init: function() {
                    // Configurar botón de rotación manual si está habilitado
                    if (config.show_rotate_button) {
                        const rotateBtn = document.getElementById(config.rotate_button_id);
                        if (rotateBtn) {
                            rotateBtn.addEventListener('click', () => this.rotateManually());
                        }
                    }
                    
                    console.log('Document Orientation Helper inicializado');
                },
                
                // Función para obtener información de orientación para guardar
                getOrientationData: function() {
                    return {
                        orientation: this.orientation || (this.isHorizontal ? 'landscape' : 'portrait'),
                        isHorizontal: this.isHorizontal,
                        aspectRatio: this.aspectRatio,
                        rotation: this.imageRotation,
                    };
                },
                
                // ✅ FUNCIÓN NUEVA: Para obtener datos específicos del backend
                getBackendOrientationData: function() {
                    return {
                        orientation: this.orientation || (this.isHorizontal ? 'landscape' : 'portrait'),
                        is_horizontal: this.isHorizontal ? '1' : '0',
                        rotation: this.imageRotation,
                        aspect_ratio: this.aspectRatio ? this.aspectRatio.toFixed(2) : '1.00'
                    };
                }
            };
            
            // Inicializar cuando el DOM esté listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => window.documentOrientation.init());
            } else {
                window.documentOrientation.init();
            }
            
        })();
        </script>";
    }
}

if (!function_exists('document_orientation_css')) {
    /**
     * Genera el CSS necesario para el manejo de orientación de documentos
     * 
     * @return string Código CSS
     */
    function document_orientation_css()
    {
        return "
        <style>
        /* === DOCUMENT ORIENTATION STYLES === */
        
        /* Canvas wrapper adaptativo */
        #canvasWrapper {
            max-height: 60vh;
            max-width: 100%;
            overflow: auto;
            position: relative;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
            padding: 10px;
            box-sizing: border-box;
        }

        #templateCanvas {
            display: block;
            margin: 0 auto;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Botón de rotación */
        #rotateBtn {
            transition: all 0.2s ease;
        }

        #rotateBtn:hover {
            transform: rotate(90deg);
        }

        /* Información de orientación */
        #orientationInfo {
            font-size: 0.875rem;
            margin-top: 0.25rem;
            font-weight: 500;
        }

        /* Signature guide layout */
        .signature-guide {
            position: sticky;
            top: 20px;
            align-self: flex-start;
        }

        #canvasContainer {
            min-height: 400px;
            align-items: flex-start !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #canvasContainer {
                flex-direction: column !important;
            }
            
            .signature-guide {
                position: static;
                order: -1;
                margin-bottom: 1rem;
            }
            
            #canvasWrapper {
                max-width: 100% !important;
            }
        }
        </style>";
    }
}

if (!function_exists('add_orientation_detection_to_upload')) {
    /**
     * Genera el código JavaScript para agregar detección de orientación al proceso de upload
     * 
     * @param array $options Opciones de configuración
     * @return string Código JavaScript
     */
    function add_orientation_detection_to_upload($options = [])
    {
        $config = array_merge(['auto_rotate' => false], $options);
        
        return "
        <script>
        // Función para procesar imagen con detección de orientación
        function processImageWithOrientation(imageElement) {
            // Evitar procesamiento múltiple
            if (imageElement.dataset.orientationProcessed === 'true') {
                return;
            }
            
            // Guardar imagen original
            if (window.documentOrientation) {
                window.documentOrientation.originalImage.src = imageElement.src;
                
                // Detectar orientación cuando la imagen original esté cargada
                window.documentOrientation.originalImage.onload = function() {
                    // Marcar como procesada para evitar bucles
                    imageElement.dataset.orientationProcessed = 'true';
                    
                    const orientationData = window.documentOrientation.detectOrientation(this);
                    
                    // Aplicar rotación si es necesaria
                    const rotatedDataUrl = window.documentOrientation.rotateImage(this, window.documentOrientation.imageRotation);
                    
                    // Actualizar imagen principal
                    imageElement.src = rotatedDataUrl;
                    
                    // Ajustar layout
                    setTimeout(() => {
                        if (window.documentOrientation.adjustLayout) {
                            window.documentOrientation.adjustLayout();
                        }
                    }, 100);
                    
                    console.log('Orientación procesada:', orientationData);
                };
            }
        }
        </script>";
    }
}
