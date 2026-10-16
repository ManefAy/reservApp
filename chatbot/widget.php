
<?php
// Only show the chatbot for membres
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'membre') return;
?>

<!-- ══ CHATBOT FLOATING BUTTON ══ -->
<div id="chat-bubble" onclick="toggleChat()">
    💬
</div>

<!-- ══ CHATBOT WINDOW ══ -->
<div id="chat-window">
    <div id="chat-header">
        <span>🤖 ReservApp Assistant</span>
        <button onclick="toggleChat()">✕</button>
    </div>
    <div id="chat-messages">
        <div class="msg bot">
            👋 Hello! I'm the ReservApp assistant. Ask me anything about the app!
        </div>
    </div>
    <div id="chat-input-area">
        <input type="text" id="chat-input" placeholder="Ask a question..." onkeydown="if(event.key==='Enter') sendMessage()">
        <button onclick="sendMessage()">➤</button>
    </div>
</div>

<style>
/* ── Floating button ── */
#chat-bubble {
    position     : fixed;
    bottom       : 30px;
    right        : 30px;
    width        : 55px;
    height       : 55px;
    background   : var(--color-primary);
    border-radius: 50%;
    display      : flex;
    align-items  : center;
    justify-content: center;
    font-size    : 1.5rem;
    cursor       : pointer;
    box-shadow   : 0 4px 15px rgba(0,0,0,0.2);
    z-index      : 9999;
    transition   : transform 0.2s;
}
#chat-bubble:hover { transform: scale(1.1); }

/* ── Chat window ── */
#chat-window {
    position     : fixed;
    bottom       : 100px;
    right        : 30px;
    width        : 350px;
    height       : 450px;
    background   : var(--bg-card);
    border-radius: 12px;
    box-shadow   : 0 8px 30px rgba(0,0,0,0.2);
    display      : none;
    flex-direction: column;
    z-index      : 9998;
    overflow     : hidden;
    border       : 1px solid var(--border-color);
}
#chat-window.open { display: flex; }

/* ── Header ── */
#chat-header {
    background     : var(--color-primary);
    color          : white;
    padding        : 15px;
    display        : flex;
    justify-content: space-between;
    align-items    : center;
    font-weight    : bold;
}
#chat-header button {
    background : transparent;
    border     : none;
    color      : white;
    font-size  : 1rem;
    cursor     : pointer;
}

/* ── Messages area ── */
#chat-messages {
    flex      : 1;
    overflow-y: auto;
    padding   : 15px;
    display   : flex;
    flex-direction: column;
    gap       : 10px;
}

/* ── Individual messages ── */
.msg {
    max-width    : 80%;
    padding      : 10px 14px;
    border-radius: 12px;
    font-size    : 0.88rem;
    line-height  : 1.5;
    word-wrap    : break-word;
}
.msg.bot {
    background  : var(--bg-input);
    color       : var(--text-main);
    align-self  : flex-start;
    border-radius: 12px 12px 12px 0;
}
.msg.user {
    background  : var(--color-primary);
    color       : white;
    align-self  : flex-end;
    border-radius: 12px 12px 0 12px;
}
.msg.typing {
    background: var(--bg-input);
    align-self: flex-start;
    color     : var(--text-muted);
    font-style: italic;
}

/* ── Input area ── */
#chat-input-area {
    display    : flex;
    padding    : 10px;
    gap        : 8px;
    border-top : 1px solid var(--border-color);
}
#chat-input {
    flex         : 1;
    padding      : 8px 12px;
    border       : 1px solid var(--border-color);
    border-radius: 20px;
    font-size    : 0.88rem;
    background   : var(--bg-input);
    color        : var(--text-main);
    outline      : none;
}
#chat-input-area button {
    background   : var(--color-primary);
    color        : white;
    border       : none;
    border-radius: 50%;
    width        : 36px;
    height       : 36px;
    cursor       : pointer;
    font-size    : 1rem;
    transition   : background 0.2s;
}
#chat-input-area button:hover { background: var(--color-primary-h); }
</style>

<script>
// Toggle chat window open/close
function toggleChat() {
    document.getElementById('chat-window').classList.toggle('open');
    // Focus input when opened
    if (document.getElementById('chat-window').classList.contains('open')) {
        document.getElementById('chat-input').focus();
    }
}

// Send message to chatbot
function sendMessage() {
    const input   = document.getElementById('chat-input');
    const message = input.value.trim();
    if (!message) return;

    // 1. Show user message in chat
    appendMessage(message, 'user');
    input.value = '';

    // 2. Show typing indicator
    const typing = appendMessage('...typing', 'typing');

    // 3. Send AJAX request to chat.php
    fetch('chatbot/chat.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ message: message })
    })
    .then(res => res.json())
    .then(data => {
        // 4. Remove typing indicator
        typing.remove();
        // 5. Show bot response
        appendMessage(data.answer || data.error, 'bot');
    })
    .catch(() => {
        typing.remove();
        appendMessage('Sorry, something went wrong.', 'bot');
    });
}

// Helper: add a message bubble to the chat
function appendMessage(text, type) {
    const messages = document.getElementById('chat-messages');
    const div      = document.createElement('div');
    div.className  = 'msg ' + type;
    div.innerText  = text;
    messages.appendChild(div);
    // Auto scroll to bottom
    messages.scrollTop = messages.scrollHeight;
    return div;
}
</script>