
(function() {
    // No ejecutar en móviles o admin
    if (window.innerWidth <= 768 || window.location.pathname.includes('admin')) return;
    
    // Crear líneas
    const style = document.createElement('style');
    style.textContent = `
        body.red-lines-mode * { cursor: none !important; }
        .red-cross-lines { 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            pointer-events: none; 
            z-index: 9999; 
            opacity: 0.7;
        }
        .red-line { 
            position: absolute; 
            background: #ff0000; 
            box-shadow: 0 0 5px #ff0000;
            transition: 0.03s linear;
        }
        .red-line-h { 
            width: 100vw; 
            height: 1px; 
            left: 0; 
        }
        .red-line-v { 
            width: 1px; 
            height: 100vh; 
            top: 0; 
        }
        @media (max-width: 768px) {
            .red-cross-lines { display: none; }
            body.red-lines-mode * { cursor: auto !important; }
        }
    `;
    document.head.appendChild(style);
    
    const container = document.createElement('div');
    container.className = 'red-cross-lines';
    
    const hLine = document.createElement('div');
    hLine.className = 'red-line red-line-h';
    
    const vLine = document.createElement('div');
    vLine.className = 'red-line red-line-v';
    
    container.appendChild(hLine);
    container.appendChild(vLine);
    document.body.appendChild(container);
    document.body.classList.add('red-lines-mode');
    
    // Seguir mouse
    document.addEventListener('mousemove', e => {
        hLine.style.top = e.clientY + 'px';
        vLine.style.left = e.clientX + 'px';
        container.style.opacity = '0.7';
    });
    
    // Ocultar cuando el mouse sale
    document.addEventListener('mouseleave', () => {
        container.style.opacity = '0';
    });
    
    document.addEventListener('mouseenter', () => {
        container.style.opacity = '0.7';
    });
    
    // Efectos simples
    document.addEventListener('mouseover', e => {
        if (e.target.matches('a, button, .btn')) {
            hLine.style.background = vLine.style.background = '#ff3333';
            hLine.style.boxShadow = vLine.style.boxShadow = '0 0 10px #ff3333';
        }
    });
    
    document.addEventListener('mouseout', e => {
        if (e.target.matches('a, button, .btn')) {
            hLine.style.background = vLine.style.background = '#ff0000';
            hLine.style.boxShadow = vLine.style.boxShadow = '0 0 5px #ff0000';
        }
    });
})();
