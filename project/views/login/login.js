document.getElementById('loginForm').addEventListener('submit', async function(event) {
    event.preventDefault(); // Prevent the default form submission

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        const response = await fetch('/login', { // Adjust URL to your login endpoint
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (data.message === 'Login successful.') {
            // Save user role and redirect
            localStorage.setItem('userRole', data.role); // Save role for later use
            window.location.href = getRedirectUrl(data.role);
        } else {
            // Display error message
            document.getElementById('error-message').textContent = data.message;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('error-message').textContent = 'An error occurred. Please try again.';
    }
});

function getRedirectUrl(role) {
    switch (role) {
        case 'support':
            return '../interface/support_dashboard.html';
        case 'agent':
            return '../interface/agent_tickets.html';
        case 'client':
            return '/client.html';
        case 'admin':
            return '../interface/admin_dashboard.html';
        default:
            return 'index.html'; // Default to login if no role matches
    }
}