// cursor-effect.js - VERSIÓN CORREGIDA
(function() {
    // VERIFICAR SI EL ARCHIVO SE CARGÓ CORRECTAMENTE
    try {
        // Verificar que estamos en un contexto de JavaScript
        if (typeof window === 'undefined') {
            throw new Error('No se ejecuta en navegador');
        }

        // Verificar contenido del archivo
        const firstLine = (function() { 
            /*
            Este comentario asegura que el archivo empiece como JS
            */ 
            return true; 
        })();
        
        if (!firstLine) {
            throw new Error('Formato incorrecto');
        }

        // Solo ejecutar en páginas NO admin
        const isAdminPage = () => {
            // Verificar múltiples indicadores
            const indicators = [
                window.location.pathname.includes('/admin'),
                window.location.pathname.includes('/wp-admin'),
                window.location.href.includes('wp-admin'),
                document.body.classList.contains('admin'),
                document.body.classList.contains('wp-admin'),
                document.body.classList.contains('admin-page'),
                document.getElementById('wpadminbar') !== null,
                document.querySelector('body.admin') !== null
            ];
            
            return indicators.some(indicator => indicator === true);
        };

        // SALIR si es admin
        if (isAdminPage()) {
            return; // ¡Ahora este return está dentro de una función!
        }

        // Crear elemento de estilo
        const style = document.createElement('style');
        style.id = 'cursor-effect-styles';
        style.textContent = `
            /* Ocultar cursor normal */
            * {
                cursor: none !important;
            }
            
            /* Contenedor de líneas */
            .cursor-crosshairs {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 999999;
                display: block !important;
            }
            
            /* Líneas */
            .crosshair-line {
                position: absolute;
                background-color: #ff0000;
                box-shadow: 0 0 3px rgba(255, 0, 0, 0.8);
                transition: transform 0.1s linear;
            }
            
            .horizontal-line {
                width: 100vw;
                height: 2px;
                left: 0;
                transform: translateY(var(--mouse-y, 0px));
            }
            
            .vertical-line {
                width: 2px;
                height: 100vh;
                top: 0;
                transform: translateX(var(--mouse-x, 0px));
            }
        `;
        
        // Añadir estilos al documento
        document.head.appendChild(style);

        // Crear elementos de las líneas
        const crosshairs = document.createElement('div');
        crosshairs.className = 'cursor-crosshairs';
        
        const horizontalLine = document.createElement('div');
        horizontalLine.className = 'crosshair-line horizontal-line';
        
        const verticalLine = document.createElement('div');
        verticalLine.className = 'crosshair-line vertical-line';
        
        crosshairs.appendChild(horizontalLine);
        crosshairs.appendChild(verticalLine);
        document.body.appendChild(crosshairs);

        // Actualizar posición del mouse
        document.addEventListener('mousemove', (e) => {
            horizontalLine.style.setProperty('--mouse-y', `${e.clientY}px`);
            verticalLine.style.setProperty('--mouse-x', `${e.clientX}px`);
        });

        // Limpiar al salir de la página
        window.addEventListener('beforeunload', () => {
            if (crosshairs.parentNode) {
                crosshairs.parentNode.removeChild(crosshairs);
            }
            if (style.parentNode) {
                style.parentNode.removeChild(style);
            }
        });


    } catch (error) {
        console.warn('Cursor Effect: Error inicializando -', error.message);
        
        // Intentar limpiar elementos si existieran
        const existingStyle = document.getElementById('cursor-effect-styles');
        const existingCrosshairs = document.querySelector('.cursor-crosshairs');
        
        if (existingStyle && existingStyle.parentNode) {
            existingStyle.parentNode.removeChild(existingStyle);
        }
        if (existingCrosshairs && existingCrosshairs.parentNode) {
            existingCrosshairs.parentNode.removeChild(existingCrosshairs);
        }
    }
})(); 