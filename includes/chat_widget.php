<style>
    /* Chat Widget Styles */
    #chat-widget-btn {
        display: none; /* Hidden as per user request, accessible via nav */
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 60px;
        height: 60px;
        background: var(--dark);
        color: white;
        border-radius: 50%;
        justify-content: center;
        align-items: center;
        font-size: 1.5rem;
        cursor: pointer;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        transition: transform 0.2s;
    }
    #chat-widget-btn:hover {
        transform: scale(1.1);
    }
    
    #chat-window {
        position: fixed;
        bottom: 7rem;
        right: 2rem;
        width: 350px;
        height: 500px;
        background: white;
        border: 1px solid #e2e8f0;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        display: none;
        flex-direction: column;
        overflow: hidden;
    }

    .chat-header {
        background: var(--dark);
        color: white;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .chat-messages {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        background: #f8fafc;
    }

    .message {
        max-width: 80%;
        padding: 0.8rem 1rem;
        border-radius: 1rem;
        font-size: 0.9rem;
        line-height: 1.5;
    }
    
    .bot-msg {
        background: white;
        color: #1e293b;
        border-bottom-left-radius: 0;
        align-self: flex-start;
        border: 1px solid #e2e8f0;
    }
    
    .user-msg {
        background: var(--primary);
        color: white;
        border-bottom-right-radius: 0;
        align-self: flex-end;
    }

    .typing-indicator {
        display: none;
        align-self: flex-start;
        background: white;
        padding: 0.5rem 1rem;
        border-radius: 1rem;
        border-bottom-left-radius: 0;
        border: 1px solid #e2e8f0;
        margin-bottom: 0.5rem;
    }
    .typing-indicator span {
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #cbd5e1;
        border-radius: 50%;
        margin: 0 2px;
        animation: chatBounce 1.4s infinite ease-in-out both;
    }
    .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
    .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }
    
    @keyframes chatBounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }

    .quick-replies {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0 1rem 1rem;
    }
    .chip {
        background: white;
        border: 1px solid var(--primary);
        color: var(--primary);
        padding: 0.3rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        cursor: pointer;
        transition: 0.2s;
        font-weight: 600;
    }
    .chip:hover {
        background: var(--primary);
        color: white;
    }

    .chat-input-area {
        padding: 1rem;
        background: white;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 0.5rem;
    }

    .chat-input-area input {
        flex: 1;
        padding: 0.8rem;
        border: 1px solid #cbd5e1;
        outline: none;
    }
</style>

<!-- Floating Button -->
<div id="chat-widget-btn" onclick="toggleChat()">
    <i class="fa-solid fa-comments"></i>
</div>

<!-- Chat Window -->
<div id="chat-window">
    <div class="chat-header">
        <span style="font-weight: 700;">Zoonacart Assistant</span>
        <i class="fa-solid fa-xmark" style="cursor: pointer;" onclick="toggleChat()"></i>
    </div>
    
    <div class="chat-messages" id="chatMessages">
        <div class="message bot-msg">
            Hello! 👋 I'm your virtual assistant. How can I help you today?
        </div>
        <div class="typing-indicator" id="botTyping">
            <span></span><span></span><span></span>
        </div>
    </div>
    
    <div class="quick-replies" id="quickReplies">
        <div class="chip" onclick="handleChip('Where is my order?')">Where is my order?</div>
        <div class="chip" onclick="handleChip('I want to return an item')">Return an item</div>
        <div class="chip" onclick="handleChip('Shipping rates')">Shipping Info</div>
        <div class="chip" onclick="handleChip('Contact Support')">Contact Support</div>
    </div>

    <div class="chat-input-area">
        <input type="text" id="chatInput" placeholder="Type a message..." onkeypress="handleChatInput(event)">
        <button onclick="sendChatMessage()" class="btn btn-dark" style="padding: 0 1rem;"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
</div>

<script>
    function toggleChat() {
        const win = document.getElementById('chat-window');
        if (win.style.display === 'flex') {
            win.style.display = 'none';
        } else {
            win.style.display = 'flex';
            document.getElementById('chatInput').focus();
        }
    }

    function handleChatInput(e) {
        if (e.key === 'Enter') sendChatMessage();
    }
    
    function handleChip(text) {
        document.getElementById('chatInput').value = text;
        sendChatMessage();
    }

    function sendChatMessage() {
        const input = document.getElementById('chatInput');
        const text = input.value.trim();
        if (!text) return;

        // Add user message
        addMessage(text, 'user');
        input.value = '';
        
        // Hide quick replies temporarily
        document.getElementById('quickReplies').style.display = 'none';

        // Show typing
        const typing = document.getElementById('botTyping');
        typing.style.display = 'block';
        const msgs = document.getElementById('chatMessages');
        msgs.scrollTop = msgs.scrollHeight;

        // Bot reply delay
        setTimeout(() => {
            getBotReply(text);
        }, 1200);
    }

    function addMessage(text, sender) {
        // Insert before typing indicator
        const typing = document.getElementById('botTyping');
        const div = document.createElement('div');
        div.className = `message ${sender}-msg`;
        div.innerHTML = text;
        
        const container = document.getElementById('chatMessages');
        container.insertBefore(div, typing);
        container.scrollTop = container.scrollHeight;
    }

    function getBotReply(msg) {
        const typing = document.getElementById('botTyping');
        typing.style.display = 'none';
        document.getElementById('quickReplies').style.display = 'flex';
        
        msg = msg.toLowerCase();
        let reply = "I apologize, I didn't verify that. Please email us at <a href='mailto:zoonacart@gmail.com' style='color:var(--primary); font-weight:bold;'>zoonacart@gmail.com</a> for personalized help.";

        if (msg.includes('hi') || msg.includes('hello') || msg.includes('hey')) {
            reply = "Hi there! Welcome to ZoonaCart. Select an option below or ask me a question!";
        } else if (msg.includes('return') || msg.includes('refund') || msg.includes('exchange')) {
            reply = "For returns, visit our <a href='returns.php' style='color:var(--primary); font-weight:bold;'>Returns Center</a>. It only takes a minute to request a return.";
        } else if (msg.includes('order') || msg.includes('track') || msg.includes('status')) {
            reply = "You can track your order status in your <a href='profile.php' style='color:var(--primary); font-weight:bold;'>Profile Dashboard</a>.";
        } else if (msg.includes('shipping') || msg.includes('delivery') || msg.includes('ship')) {
            reply = "📦 <strong>Shipping Policy:</strong><br>• FREE shipping on orders over ₹999.<br>• Standard delivery: 3-5 business days.<br>• Express delivery available.";
        } else if (msg.includes('contact') || msg.includes('support') || msg.includes('help')) {
            reply = "For human support, please email <strong>zoonacart@gmail.com</strong> or visit our Headquarters at Sodal Chowk, Jalandhar.";
        }

        addMessage(reply, 'bot');
    }
</script>
