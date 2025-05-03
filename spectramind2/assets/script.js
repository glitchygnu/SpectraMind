let lastMessageId = 0;

function sendMessage() {
    const messageInput = document.getElementById('message');
    const message = messageInput.value.trim();
    
    if (message) {
        fetch('send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                username: '<?= $_SESSION['user']['username'] ?>'
            })
        });
        messageInput.value = '';
    }
}

function loadMessages() {
    fetch(`get_messages.php?last_id=${lastMessageId}`)
        .then(response => response.json())
        .then(messages => {
            if (messages.length > 0) {
                messages.forEach(msg => {
                    const div = document.createElement('div');
                    div.className = 'message';
                    div.innerHTML = `
                        <strong>${msg.username}</strong>
                        <span class="time">${new Date(msg.timestamp).toLocaleTimeString()}</span>
                        <p>${msg.message}</p>
                    `;
                    document.getElementById('chat-box').appendChild(div);
                    lastMessageId = msg.id;
                });
                document.getElementById('chat-box').scrollTop = document.getElementById('chat-box').scrollHeight;
            }
        });
}

setInterval(loadMessages, 1000);
document.getElementById('message').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendMessage();
});
