<?php
// views/rag/chat_popup.php
?>
<div id="rag-popup-overlay" style="
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9998;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
"></div>

<div id="rag-popup-container" style="
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.9);
    width: 90%;
    max-width: 500px;
    height: 70vh;
    max-height: 600px;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: none;
    opacity: 0;
    overflow: hidden;
    border: 2px solid rgba(76, 201, 240, 0.3);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
">
    <!-- Cabecera -->
    <div style="
        background: rgba(0, 0, 0, 0.4);
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    ">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="
                width: 40px;
                height: 40px;
                background: linear-gradient(135deg, #4cc9f0, #4361ee);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                color: white;
                animation: pulse 2s infinite;
            ">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <h3 style="margin: 0; color: white; font-size: 1.3rem; font-weight: 600;">Asistente Musical IA</h3>
                <p style="margin: 5px 0 0 0; color: #4cc9f0; font-size: 0.9rem; opacity: 0.9;">
                    <i class="fas fa-headphones"></i> Pregúntame sobre música
                </p>
            </div>
        </div>
        <button id="rag-popup-close" style="
            background: rgba(255, 255, 255, 0.1);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        " onmouseover="this.style.background='rgba(255, 255, 255, 0.2)';" 
        onmouseout="this.style.background='rgba(255, 255, 255, 0.1)';">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <!-- Línea decorativa -->
    <div style="
        height: 3px;
        background: linear-gradient(90deg, #4cc9f0, #4361ee, #f72585);
        width: 100%;
        animation: shimmer 3s infinite;
    "></div>
    
    <!-- Área de mensajes -->
    <div id="rag-popup-messages" style="
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        gap: 15px;
    ">
        <!-- Mensaje de bienvenida -->
        <div style="
            background: rgba(76, 201, 240, 0.15);
            border-left: 4px solid #4cc9f0;
            padding: 15px;
            border-radius: 0 10px 10px 0;
            max-width: 80%;
            align-self: flex-start;
            animation: slideInLeft 0.5s ease;
        ">
            <div style="color: #e0e0ff; line-height: 1.5;">
                ¡Hola <strong><?php echo $_SESSION['nombre'] ?? 'amigo'; ?></strong>! 👋<br>
                Soy tu asistente musical inteligente. Puedo ayudarte con:
            </div>
            <ul style="color: #a0a0e0; margin: 10px 0 0 20px; font-size: 0.9rem;">
                <li>🎵 Buscar canciones y artistas</li>
                <li>⭐ Ver reviews de usuarios</li>
                <li>🎯 Recomendaciones personalizadas</li>
                <li>📊 Estadísticas musicales</li>
            </ul>
            <div style="
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px dashed rgba(255, 255, 255, 0.1);
                color: #88aaff;
                font-size: 0.85rem;
                display: flex;
                align-items: center;
                gap: 5px;
            ">
                <i class="fas fa-lightbulb"></i>
                <span>Prueba preguntando: "canciones de rock" o "mejores reviews"</span>
            </div>
        </div>
    </div>
    
    <!-- Área de entrada -->
    <div style="
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.3);
        position: relative;
    ">
        <!-- Indicador de escritura (oculto por defecto) -->
        <div id="rag-typing-indicator" style="
            position: absolute;
            top: -40px;
            left: 20px;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 15px;
            border-radius: 20px;
            color: #4cc9f0;
            font-size: 0.9rem;
            display: none;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.3s ease;
        ">
            <div style="display: flex; gap: 4px;">
                <div class="typing-dot" style="animation-delay: 0s;"></div>
                <div class="typing-dot" style="animation-delay: 0.2s;"></div>
                <div class="typing-dot" style="animation-delay: 0.4s;"></div>
            </div>
            El asistente está escribiendo...
        </div>
        
        <form id="rag-popup-form" style="display: flex; gap: 10px;">
            <input type="text" 
                   id="rag-popup-input" 
                   placeholder="Escribe tu pregunta musical..."
                   style="
                        flex: 1;
                        padding: 15px 20px;
                        background: rgba(255, 255, 255, 0.08);
                        border: 2px solid rgba(255, 255, 255, 0.2);
                        border-radius: 25px;
                        color: white;
                        font-size: 1rem;
                        transition: all 0.3s;
                   "
                   onfocus="this.style.borderColor='#4cc9f0'; this.style.background='rgba(255, 255, 255, 0.12)';"
                   onblur="this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.background='rgba(255, 255, 255, 0.08)';">
            <button type="submit" 
                    style="
                        background: linear-gradient(135deg, #4cc9f0, #4361ee);
                        color: white;
                        border: none;
                        width: 50px;
                        height: 50px;
                        border-radius: 50%;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 1.2rem;
                        transition: all 0.3s;
                    "
                    onmouseover="this.style.transform='scale(1.1)';"
                    onmouseout="this.style.transform='scale(1)';">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
        
        <!-- Sugerencias rápidas -->
        <div id="rag-quick-suggestions" style="
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            opacity: 0.8;
            transition: opacity 0.3s;
        ">
            <button class="rag-quick-btn" data-question="Canciones de rock populares">🎸 Rock</button>
            <button class="rag-quick-btn" data-question="Mejores reviews 5 estrellas">⭐ Top reviews</button>
            <button class="rag-quick-btn" data-question="Música para estudiar">📚 Estudiar</button>
            <button class="rag-quick-btn" data-question="Artistas más populares">👑 Artistas</button>
        </div>
    </div>
</div>

<style>
    /* Animaciones */
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(76, 201, 240, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(76, 201, 240, 0); }
        100% { box-shadow: 0 0 0 0 rgba(76, 201, 240, 0); }
    }
    
    @keyframes shimmer {
        0% { background-position: -200px 0; }
        100% { background-position: 200px 0; }
    }
    
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes typing {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-8px); }
    }
    
    .typing-dot {
        width: 8px;
        height: 8px;
        background: #4cc9f0;
        border-radius: 50%;
        animation: typing 1.4s infinite;
    }
    
    /* Estilos para sugerencias rápidas */
    .rag-quick-btn {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #a0a0e0;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .rag-quick-btn:hover {
        background: rgba(76, 201, 240, 0.2);
        color: white;
        transform: translateY(-2px);
    }
    
    /* Scrollbar personalizado */
    #rag-popup-messages::-webkit-scrollbar {
        width: 6px;
    }
    
    #rag-popup-messages::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }
    
    #rag-popup-messages::-webkit-scrollbar-thumb {
        background: rgba(76, 201, 240, 0.5);
        border-radius: 10px;
    }
    
    #rag-popup-messages::-webkit-scrollbar-thumb:hover {
        background: rgba(76, 201, 240, 0.8);
    }
    
    /* Efectos de entrada/salida */
    .rag-message-user {
        background: linear-gradient(135deg, #4361ee, #3a0ca3) !important;
        border-left: 4px solid #f72585 !important;
        padding: 15px !important;
        border-radius: 10px 0 10px 10px !important;
        max-width: 80% !important;
        align-self: flex-end !important;
        animation: slideInRight 0.5s ease !important;
        margin-left: auto !important;
        color: white !important;
    }
    
    .rag-message-bot {
        background: rgba(76, 201, 240, 0.15) !important;
        border-left: 4px solid #4cc9f0 !important;
        padding: 15px !important;
        border-radius: 0 10px 10px 10px !important;
        max-width: 80% !important;
        align-self: flex-start !important;
        animation: slideInLeft 0.5s ease !important;
        color: #e0e0ff !important;
        line-height: 1.5 !important;
    }
</style>

<script>
// Estado del chat
let ragPopupOpen = false;

// Abrir popup
function openRagPopup() {
    const overlay = document.getElementById('rag-popup-overlay');
    const container = document.getElementById('rag-popup-container');
    
    if (!ragPopupOpen) {
        // Mostrar con animación
        overlay.style.display = 'block';
        container.style.display = 'block';
        
        // Animar
        setTimeout(() => {
            overlay.style.opacity = '1';
            container.style.opacity = '1';
            container.style.transform = 'translate(-50%, -50%) scale(1)';
        }, 10);
        
        ragPopupOpen = true;
        
        // Enfocar input
        setTimeout(() => {
            document.getElementById('rag-popup-input').focus();
        }, 400);
        
        // Bloquear scroll del body
        document.body.style.overflow = 'hidden';
    }
}

// Cerrar popup
function closeRagPopup() {
    const overlay = document.getElementById('rag-popup-overlay');
    const container = document.getElementById('rag-popup-container');
    
    if (ragPopupOpen) {
        // Animar salida
        overlay.style.opacity = '0';
        container.style.opacity = '0';
        container.style.transform = 'translate(-50%, -50%) scale(0.9)';
        
        // Ocultar después de la animación
        setTimeout(() => {
            overlay.style.display = 'none';
            container.style.display = 'none';
        }, 300);
        
        ragPopupOpen = false;
        
        // Restaurar scroll del body
        document.body.style.overflow = '';
    }
}

// Añadir mensaje al chat
function addRagMessage(text, isUser = false) {
    const messagesDiv = document.getElementById('rag-popup-messages');
    const messageDiv = document.createElement('div');
    
    messageDiv.className = isUser ? 'rag-message-user' : 'rag-message-bot';
    messageDiv.innerHTML = text;
    
    messagesDiv.appendChild(messageDiv);
    
    // Scroll al final
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
    
    // Ocultar sugerencias después del primer mensaje del usuario
    if (isUser) {
        document.getElementById('rag-quick-suggestions').style.opacity = '0.3';
    }
}

// Mostrar indicador de escritura
function showRagTyping() {
    document.getElementById('rag-typing-indicator').style.display = 'flex';
}

// Ocultar indicador de escritura
function hideRagTyping() {
    document.getElementById('rag-typing-indicator').style.display = 'none';
}

// Enviar pregunta
async function sendRagQuestion(question) {
    if (!question.trim()) return;
    
    // Añadir mensaje del usuario
    addRagMessage(question, true);
    
    // Mostrar indicador de escritura
    showRagTyping();
    
    try {
        const formData = new FormData();
        formData.append('question', question);
        
        const response = await fetch('/rag/answer-api', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        hideRagTyping();
        
        if (data.success) {
            // Formatear respuesta
            let answer = data.answer;
            
            // Reemplazar emojis y formato
            answer = answer.replace(/\n/g, '<br>')
                          .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                          .replace(/🎵/g, '<i class="fas fa-music"></i>')
                          .replace(/⭐/g, '<i class="fas fa-star"></i>')
                          .replace(/🎯/g, '<i class="fas fa-bullseye"></i>')
                          .replace(/📊/g, '<i class="fas fa-chart-bar"></i>')
                          .replace(/💡/g, '<i class="fas fa-lightbulb"></i>')
                          .replace(/🎧/g, '<i class="fas fa-headphones"></i>')
                          .replace(/👥/g, '<i class="fas fa-users"></i>')
                          .replace(/🎸/g, '<i class="fas fa-guitar"></i>');
            
            addRagMessage(answer, false);
        } else {
            addRagMessage(`<span style="color: #ff6b6b;">❌ Error: ${data.error || 'Desconocido'}</span>`, false);
        }
    } catch (error) {
        hideRagTyping();
        addRagMessage('<span style="color: #ff6b6b;">❌ Error de conexión. Intenta de nuevo.</span>', false);
    }
}

// Configurar eventos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Botón cerrar
    document.getElementById('rag-popup-close').addEventListener('click', closeRagPopup);
    
    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && ragPopupOpen) {
            closeRagPopup();
        }
    });
    
    // Cerrar al hacer clic en overlay
    document.getElementById('rag-popup-overlay').addEventListener('click', closeRagPopup);
    
    // Formulario
    document.getElementById('rag-popup-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('rag-popup-input');
        const question = input.value.trim();
        
        if (question) {
            sendRagQuestion(question);
            input.value = '';
        }
    });
    
    // Sugerencias rápidas
    document.querySelectorAll('.rag-quick-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const question = this.getAttribute('data-question');
            document.getElementById('rag-popup-input').value = question;
            document.getElementById('rag-popup-input').focus();
        });
    });
    
    // Enviar con Enter (sin Shift)
    document.getElementById('rag-popup-input').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('rag-popup-form').requestSubmit();
        }
    });
    
    // Hacerla función global para que el navbar pueda abrirla
    window.openRagPopup = openRagPopup;
    window.closeRagPopup = closeRagPopup;
});

// Función para abrir desde el navbar (global)
window.openRagAssistant = function(event) {
    if (event) event.preventDefault();
    openRagPopup();
};
</script>