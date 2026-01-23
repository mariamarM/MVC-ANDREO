// js/simple-red-cross.js
// Este script NO verifica nada, SIEMPRE se ejecuta

console.log('🟥 CARGANDO CURSOR ROJO SIMPLE');

// 1. Crear estilos
const style = document.createElement('style');
style.textContent = `
    /* IMPORTANTE: Esto oculta el cursor normal */
    body * {
        cursor: none !important;
    }
    
    /* Líneas rojas */
    .simple-red-cross {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 999999;
    }
    
    .simple-red-line {
        position: absolute;
        background: red;
        box-shadow: 0 0 3px red;
    }
    
    .simple-red-h {
        width: 100vw;
        height: 2px;
        left: 0;
    }
    
    .simple-red-v {
        width: 2px;
        height: 100vh;
        top: 0;
    }
    
    /* Para admin pages, podemos desactivarlo con CSS */
    body.admin-page * {
        cursor: auto !important;
    }
    body.admin-page .simple-red-cross {
        display: none !important;
    }
`;
document.head.appendChild(style);

// 2. Crear líneas
const cross = document.createElement('div');
cross.className = 'simple-red-cross';

const hLine = document.createElement('div');
hLine.className = 'simple-red-line simple-red-h';

const vLine = document.createElement('div');
vLine.className = 'simple-red-line simple-red-v';

cross.appendChild(hLine);
cross.appendChild(vLine);
document.body.appendChild(cross);

// 3. Mover líneas con el mouse
document.addEventListener('mousemove', function(e) {
    hLine.style.transform = `translateY(${e.clientY}px)`;
    vLine.style.transform = `translateX(${e.clientX}px)`;
});

console.log('🟥 CURSOR ROJO ACTIVADO');