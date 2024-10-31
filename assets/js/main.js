// Main JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const imageInputs = document.querySelectorAll('.image-upload');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const preview = document.querySelector(`#${this.dataset.preview}`);
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // AI Integration for blog posts
    const aiGenerateBtn = document.querySelector('#ai-generate');
    if (aiGenerateBtn) {
        aiGenerateBtn.addEventListener('click', async function() {
            const apiKey = document.querySelector('#ai-api-key').value;
            const prompt = document.querySelector('#ai-prompt').value;
            
            try {
                const response = await fetch('/api/ai-generate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ apiKey, prompt })
                });
                
                const data = await response.json();
                if (data.success) {
                    document.querySelector('#blog-title-en').value = data.titleEn;
                    document.querySelector('#blog-title-ru').value = data.titleRu;
                    document.querySelector('#blog-content-en').value = data.contentEn;
                    document.querySelector('#blog-content-ru').value = data.contentRu;
                }
            } catch (error) {
                console.error('AI Generation failed:', error);
            }
        });
    }

    // Dynamic form handling
    const dynamicForms = document.querySelectorAll('.dynamic-form');
    dynamicForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    showNotification('Success!', 'success');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    showNotification(data.message || 'An error occurred', 'error');
                }
            } catch (error) {
                showNotification('An error occurred', 'error');
            }
        });
    });
});

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// SEO Preview functionality
function updateSeoPreview() {
    const title = document.querySelector('#seo-title').value;
    const description = document.querySelector('#seo-description').value;
    
    document.querySelector('#seo-preview-title').textContent = title;
    document.querySelector('#seo-preview-description').textContent = description;
}

// Telegram Bot Integration
function testTelegramBot() {
    const botToken = document.querySelector('#telegram-bot-token').value;
    
    fetch('/api/test-telegram-bot.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ botToken })
    })
    .then(response => response.json())
    .then(data => {
        showNotification(data.message, data.success ? 'success' : 'error');
    })
    .catch(error => {
        showNotification('Failed to test Telegram bot', 'error');
    });
}