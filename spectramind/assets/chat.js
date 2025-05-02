document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('messages-container');
    const messageForm = document.getElementById('message-form');
    const messageInput = messageForm.querySelector('textarea');
    
    // Auto-resize textarea
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    }
    
    messageInput.addEventListener('input', function() {
        autoResize(this);
    });
    
    // Scroll to bottom of messages
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    scrollToBottom();
    
    // Handle message submission
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if(messageInput.value.trim() !== '') {
            fetch('send_message.php', {
                method: 'POST',
                body: new FormData(messageForm),
                credentials: 'same-origin'
            })
            .then(response => response.text())
            .then(() => {
                messageInput.value = '';
                autoResize(messageInput);
                loadMessages();
            })
            .catch(error => console.error('Error:', error));
        }
    });
    
    // Alternative Enter key handling
    messageInput.addEventListener('keydown', function(e) {
        if(e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.dispatchEvent(new Event('submit'));
        }
    });
    
    // Load messages via AJAX
    function loadMessages() {
        fetch('get_messages.php')
            .then(response => response.json())
            .then(messages => {
                messagesContainer.innerHTML = '';
                
                messages.forEach(message => {
                    const messageElement = document.createElement('div');
                    messageElement.className = 'message';
                    
                    let avatarContent;
                    if(message.avatar && message.avatar !== 'default.png') {
                        avatarContent = `<img src="assets/images/avatars/${message.avatar}" alt="${message.username}">`;
                    } else {
                        avatarContent = `<span>${message.username.charAt(0).toUpperCase()}</span>`;
                    }
                    
                    messageElement.innerHTML = `
                        <div class="message-avatar">
                            ${avatarContent}
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-username">${message.username}</span>
                                <span class="message-time">${new Date(message.created_at).toLocaleString()}</span>
                            </div>
                            <div class="message-text">
                                ${message.content.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    `;
                    
                    messagesContainer.appendChild(messageElement);
                });
                
                scrollToBottom();
            })
            .catch(error => console.error('Error loading messages:', error));
    }
    
    // Poll for new messages every 3 seconds
    setInterval(loadMessages, 3000);
    
    // Initial load
    loadMessages();
});
