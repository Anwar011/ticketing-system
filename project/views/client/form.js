// Récupérer les éléments du DOM
const popup = document.getElementById('popup');
const chatbotImage = document.querySelector('.chatbot');
const closePopup = document.getElementById('closePopup');
const chatContent = document.querySelector('.chat-content');
const inputField = document.querySelector('.input-field');
const sendButton = document.querySelector('.send-button');

// Écouteur d'événement pour afficher le pop-up lorsque l'image est cliquée
chatbotImage.onclick = function(event) {
    event.preventDefault(); // Prevent form submission
    popup.style.display = 'block'; // Afficher le pop-up
};

// Fermer le pop-up lorsque l'utilisateur clique sur "x"
closePopup.onclick = function() {
    popup.style.display = 'none';
};

// Fermer le pop-up lorsque l'utilisateur clique en dehors de la boîte
window.onclick = function(event) {
    if (event.target === popup) {
        popup.style.display = 'none';
    }
};

// Fonction pour envoyer un message au chatbot
sendButton.onclick = async function(event) {
    event.preventDefault(); // Prevent the default form submission
    const prompt = inputField.value;
    if (prompt.trim() === '') return;

    // Afficher le message de l'utilisateur
    const userMessage = document.createElement('div');
    userMessage.className = 'chat-message user';
    userMessage.innerHTML = `<div class="message-content">${prompt}</div>`;
    chatContent.appendChild(userMessage);
    inputField.value = '';

    try {
        // Vérifier la base de connaissances
        const response = await fetch('/chatbot', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: prompt })
        });

        const data = await response.json();

        if (data.response && data.response.trim() !== '') {
            // Réponse de la base de connaissances
            displayBotMessage(data.response);
        } else {
            // Pas de réponse, interroger LLaMA2 API
            fetch('/llama2', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    prompt: prompt,
                    model: 'llama2'
                })
            })
            .then(response => {
                if (!response.body) {
                    throw new Error('ReadableStream not supported');
                }

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let result = '';
                let fullResponse = '';

                function processText({ done, value }) {
                    if (done) {
                        // Display the complete response once streaming is done
                        displayBotMessage(fullResponse);
                        return;
                    }

                    result += decoder.decode(value, { stream: true });

                    // Process the accumulated result to extract 'response' key
                    try {
                        result.split('\n').forEach(line => {
                            if (line.trim()) {
                                try {
                                    const data = JSON.parse(line.trim());
                                    if (data.response) {
                                        fullResponse += data.response;
                                    }
                                } catch (error) {
                                    console.error('Parsing error:', error);
                                }
                            }
                        });
                        result = ''; // Clear result to handle new data
                    } catch (error) {
                        console.error('Processing error:', error);
                        displayBotMessage('An error occurred while processing the response.');
                    }

                    // Read the next chunk
                    reader.read().then(processText);
                }

                // Start reading the stream
                reader.read().then(processText);
            })
            .catch(error => {
                console.error('Error:', error);
                displayBotMessage('An error occurred. Please try again.');
            });
        }
    } catch (error) {
        console.error('Error:', error);
        displayBotMessage('An error occurred. Please try again.');
    }
};

// Fonction pour afficher le message du bot
function displayBotMessage(message) {
    const botMessage = document.createElement('div');
    botMessage.className = 'chat-message bot';
    botMessage.innerHTML = `<div class="message-content">${message}</div>`;
    chatContent.appendChild(botMessage);
}

// Gestion de la soumission du formulaire de ticket
document.getElementById('ticketForm').addEventListener('submit', async function(event) {
    console.log("clicked");
    event.preventDefault(); // Empêche le rechargement de la page lors de la soumission du formulaire

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('http://localhost:3000/tickets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();

        if (response.ok) {
            // Afficher un message de succès
            // Optionnel : réinitialiser le formulaire
            event.target.reset();
        } else {
            alert(`Error: ${responseData.message}`);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
});
