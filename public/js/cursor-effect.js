// cursor-effect.js - CON PUNTO ROJO PARA ADMIN Y DETECCIÓN POR CLASE
(function() {
    'use strict';
    
    // Esperar a que el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        
        // DETECTAR SI ES PÁGINA DE ADMINISTRADOR POR CLASE EN BODY
        const isAdminPage = document.body.classList.contains('admin-page') || 
                           document.body.classList.contains('admin-dashboard') ||
                           document.body.classList.contains('administrator');
        
        // DETECTAR SI ES PÁGINA DE USUARIO NORMAL POR CLASE
        const isUserPage = document.body.classList.contains('user-page') || 
                          document.body.classList.contains('dashboard-user');
        
        // --- VERSIÓN PARA ADMIN (PUNTO ROJO) ---
        if (isAdminPage) {
            console.log('Página de administración detectada - activando punto rojo');
            
            // Crear estilo para el punto rojo
            const adminStyle = document.createElement('style');
            adminStyle.id = 'admin-cursor-style';
            adminStyle.textContent = `
                /* Ocultar cursor normal */
                * {
                    cursor: none !important;
                }
                
                /* Punto rojo para admin */
                .admin-cursor-dot {
                    position: fixed;
                    width: 16px;
                    height: 16px;
                    background-color: #e11d2e;
                    border-radius: 50%;
                    pointer-events: none;
                    z-index: 999999;
                    transform: translate(-50%, -50%);
                    box-shadow: 
                        0 0 0 2px rgba(255, 255, 255, 0.8),
                        0 0 15px rgba(225, 29, 46, 0.9),
                        0 0 30px rgba(225, 29, 46, 0.5);
                    transition: transform 0.1s ease, width 0.2s ease, height 0.2s ease;
                }
                
                /* Efecto hover */
                .admin-cursor-dot.hover {
                    width: 24px;
                    height: 24px;
                    background-color: #ff0000;
                    box-shadow: 
                        0 0 0 3px rgba(255, 255, 255, 0.9),
                        0 0 20px rgba(255, 0, 0, 1),
                        0 0 40px rgba(255, 0, 0, 0.7);
                }
                
                /* Efecto click */
                .admin-cursor-dot.click {
                    width: 12px;
                    height: 12px;
                    background-color: #ff4444;
                    box-shadow: 
                        0 0 0 4px rgba(255, 255, 255, 1),
                        0 0 25px rgba(255, 68, 68, 1);
                }
                
                /* Restaurar cursor en inputs para usabilidad */
                input, textarea, select {
                    cursor: text !important;
                }
                
                button, a {
                    cursor: pointer !important;
                }
            `;
            
            document.head.appendChild(adminStyle);
            
            // Crear el punto rojo
            const cursorDot = document.createElement('div');
            cursorDot.className = 'admin-cursor-dot';
            cursorDot.id = 'adminCursorDot';
            document.body.appendChild(cursorDot);
            
            // Variables para posición
            let mouseX = 0;
            let mouseY = 0;
            let dotX = 0;
            let dotY = 0;
            const smoothFactor = 0.15;
            
            // Actualizar posición
            document.addEventListener('mousemove', function(e) {
                mouseX = e.clientX;
                mouseY = e.clientY;
            });
            
            // Efectos hover
            document.addEventListener('mouseover', function(e) {
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || 
                    e.target.classList.contains('clickable')) {
                    cursorDot.classList.add('hover');
                }
            });
            
            document.addEventListener('mouseout', function(e) {
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' ||
                    e.target.classList.contains('clickable')) {
                    cursorDot.classList.remove('hover');
                }
            });
            
            // Efecto click
            document.addEventListener('mousedown', function() {
                cursorDot.classList.add('click');
            });
            
            document.addEventListener('mouseup', function() {
                cursorDot.classList.remove('click');
            });
            
            // Animación suave
            function animate() {
                dotX += (mouseX - dotX) * smoothFactor;
                dotY += (mouseY - dotY) * smoothFactor;
                
                cursorDot.style.left = dotX + 'px';
                cursorDot.style.top = dotY + 'px';
                
                requestAnimationFrame(animate);
            }
            
            // Iniciar animación
            animate();
            
            // Limpiar al salir
            window.addEventListener('beforeunload', function() {
                cursorDot.remove();
                adminStyle.remove();
            });
            
            return; // Salir, no ejecutar el efecto de crosshair
        }
        
        // --- VERSIÓN PARA USUARIO NORMAL (CROSSHAIR) ---
        else if (isUserPage || !isAdminPage) {
            console.log('Página de usuario normal - activando crosshair');
            
            // Crear elemento de estilo para crosshair
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
                
                /* Punto central opcional */
                .crosshair-center {
                    position: fixed;
                    width: 8px;
                    height: 8px;
                    background-color: #ff0000;
                    border-radius: 50%;
                    pointer-events: none;
                    z-index: 1000000;
                    transform: translate(-50%, -50%);
                    box-shadow: 0 0 10px rgba(255, 0, 0, 0.8);
                }
            `;
            
            document.head.appendChild(style);

            // Crear elementos de las líneas
            const crosshairs = document.createElement('div');
            crosshairs.className = 'cursor-crosshairs';
            
            const horizontalLine = document.createElement('div');
            horizontalLine.className = 'crosshair-line horizontal-line';
            
            const verticalLine = document.createElement('div');
            verticalLine.className = 'crosshair-line vertical-line';
            
            // Punto central opcional (descomenta si lo quieres)
            // const centerDot = document.createElement('div');
            // centerDot.className = 'crosshair-center';
            
            crosshairs.appendChild(horizontalLine);
            crosshairs.appendChild(verticalLine);
            // crosshairs.appendChild(centerDot);
            document.body.appendChild(crosshairs);

            // Actualizar posición del mouse
            document.addEventListener('mousemove', function(e) {
                horizontalLine.style.setProperty('--mouse-y', `${e.clientY}px`);
                verticalLine.style.setProperty('--mouse-x', `${e.clientX}px`);
                // if (centerDot) {
                //     centerDot.style.left = e.clientX + 'px';
                //     centerDot.style.top = e.clientY + 'px';
                // }
            });

            // Limpiar al salir de la página
            window.addEventListener('beforeunload', function() {
                crosshairs.remove();
                style.remove();
            });
            
            return;
        }
        
        // --- SI NO TIENE CLASE ESPECÍFICA, NO HACER NADA (CURSOR NORMAL) ---
        else {
            console.log('Página sin clase específica - cursor normal activado');
            // No hacer nada, el cursor se mantiene normal
            return;
        }
        
    });
    
})();