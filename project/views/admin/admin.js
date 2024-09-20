// Obtenez les éléments
const sidebar = document.querySelector('.sidebar');
const sidebarToggle = document.querySelector('.sidebar-toggle');
const container = document.querySelector('.container');

async function fetchTickets() {
try {
const response = await fetch('/tickets');
if (!response.ok) {
    throw new Error(`HTTP error! Status: ${response.status}`);
}

const textResponse = await response.text(); // Get response as text first
console.log('Response Text:', textResponse); // Log the text response

const tickets = JSON.parse(textResponse); // Parse the text response to JSON

const tbody = document.getElementById('ticket-body');
tbody.innerHTML = ''; // Clear existing rows
console.log(tickets);
console.log(Array.isArray(tickets));

tickets.forEach(ticket => {
    const row = document.createElement('tr');

    row.innerHTML = `
        <td class="checkbox-cell"><input type="checkbox" value="${ticket.id} "></td>
        <td class="left-align"><img src="image/agent.jpg" alt="User" class="user-photo"> ${ticket.client_id}</td>
        <td class="center-align">${ticket.title}</td>
        <td class="left-align">${ticket.agent_id}</td>
        <td class="active">${ticket.status}</td>
        <td class="${ticket.priority}">${ticket.priority}</td>
        <td class="left-align">${new Date(ticket.updated_at).toLocaleDateString()}</td>
        <td><button  id="assign-button" class="assign-button">Assign</button></td>
    `;
    row.addEventListener('click', (event) => {
      const target = event.target;
if (target.type == 'checkbox' || target.classList.contains('assign-button')) {
return;
}
viewTicketDetails(ticket.id);
});

  tbody.appendChild(row);
});
} catch (error) {
console.error('Error fetching tickets:', error);
}
}


// Call fetchTickets when the page loads
document.addEventListener('DOMContentLoaded', fetchTickets);
// Fonction pour ouvrir ou fermer la sidebar
function toggleSidebar() {
  sidebar.classList.toggle('show');

  // Gérer le surlignage
  if (sidebar.classList.contains('show')) {
    dashboardItem.classList.add('active'); 
  } else {
    dashboardItem.classList.remove('active'); 
  }
}

// Fonction pour fermer la barre latérale lorsqu'on clique en dehors
function closeSidebar(event) {
  if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
    sidebar.classList.remove('show'); 
    dashboardItem.classList.remove('active'); 
  }
}

// Ajouter des écouteurs d'événements
sidebarToggle.addEventListener('click', toggleSidebar);
document.addEventListener('click', closeSidebar);

// Fonction pour ouvrir ou fermer le menu déroulant
function toggleDropdown(event) {
  const dropdown = event.currentTarget.parentElement;
  dropdown.classList.toggle('show');
}

// Ajouter des écouteurs d'événements pour les dropdowns
document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
  toggle.addEventListener('click', toggleDropdown);
});
async function fetchAgents() {
    try {
        // Fetch agents
        const agentResponse = await fetch('/agents');
        if (!agentResponse.ok) {
            throw new Error('Failed to fetch agents');
        }
        const agents = await agentResponse.json();

        // Fetch tickets
        const ticketResponse = await fetch('/tickets');
        if (!ticketResponse.ok) {
            throw new Error('Failed to fetch tickets');
        }
        const tickets = await ticketResponse.json();

        // Calculate ticket counts for each agent
        const agentTicketCounts = agents.map(agent => {
            const agentTickets = tickets.filter(ticket => ticket.agent_id === agent.id);
            return {
                ...agent,
                ticketsAssigned: agentTickets.length,
                ticketsOpen: agentTickets.filter(ticket => ticket.status === 'open').length,
                ticketsClosed: agentTickets.filter(ticket => ticket.status === 'closed').length,
                ticketsResolved: agentTickets.filter(ticket => ticket.status === 'resolved').length,
                ticketsInProgress: agentTickets.filter(ticket => ticket.status === 'in_progress').length
            };
        });

        // Populate agent table
        const agentTableBody = document.querySelector('#agentModal tbody');
        agentTableBody.innerHTML = ''; // Clear existing rows

        agentTicketCounts.forEach(agent => {
            const row = document.createElement('tr');

            row.innerHTML = `
                <td>${agent.name}</td>
                <td>${agent.category || 'Support Technique'}</td>
                <td><span>${agent.ticketsAssigned || 0}</span></td>
                <td><span>${agent.ticketsOpen || 0}</span></td>
                <td><span>${agent.ticketsInProgress || 0}</span></td>
                <td><span>${agent.ticketsResolved || 0}</span></td>
                <td><span>${agent.ticketsClosed || 0}</span></td>
                <td><button class="assign-confirm" data-agent-id="${agent.id}">Assign</button></td>
            `;
            agentTableBody.appendChild(row);
        });

        // Add event listeners for the assign buttons
        document.querySelectorAll('.assign-confirm').forEach(button => {
            button.addEventListener('click', async (event) => {
                const agentId = event.currentTarget.getAttribute('data-agent-id');
                const selectedTickets = Array.from(document.querySelectorAll('.checkbox-cell input[type="checkbox"]:checked')).map(cb => cb.value);
                
            
                try {
                    const response = await fetch('/assign-tickets', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            agent_id: agentId,
                            ticket_ids: selectedTickets,
                        }),
                    });
                    if (!response.ok) {
                        throw new Error('Failed to assign tickets');
                    }
                    const result = await response.json();
                    alert(result.message);
                    fetchTickets(); // Refresh tickets after assignment
                } catch (error) {
                    console.error('Error assigning tickets:', error);
                }
            });
        });
    } catch (error) {
        console.error('Error fetching agents:', error);
    }
}


// Modal handling
const modal = document.getElementById('agentModal');
const closeButton = document.getElementsByClassName('close')[0];
const assignConfirmButton = document.getElementById('assign-confirm');

const assignButtons = document.querySelectorAll('.assign-button');

document.addEventListener('click', (event) => {
    if (event.target.matches('.assign-button')) { 
        modal.style.display = 'block';
        fetchAgents();
        const row = event.target.closest('tr');
        if (row) {
            const checkbox = row.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.checked = true;
                console.log('Checkbox in the row checked:', checkbox.value);
                
            }
       
    }
}
});
// Close agent modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById("agentModal");
    if (event.target == modal) {
        closeAgentModal();
    }
}

// Event listeners for agent modal
const agentClose = document.querySelector(".closee");
agentClose.onclick = function() {
    agentModal.style.display = "none";
}


closeButton.onclick = function() {
    modal.style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

assignConfirmButton.onclick = async function() {
    const selectedAgentId = document.getElementById('agent-list').value;
    const selectedTickets = Array.from(document.querySelectorAll('.checkbox-cell input[type="checkbox"]:checked')).map(cb => cb.value);


    try {
        const response = await fetch('/assign-tickets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                agent_id: selectedAgentId,
                ticket_ids: selectedTickets,
            }),
        });
        if (!response.ok) {
            throw new Error('Failed to assign tickets');
        }
        const result = await response.json();
        alert(result.message);
        modal.style.display = 'none';
        fetchTickets(); // Refresh tickets after assignment
    } catch (error) {
        console.error('Error assigning tickets:', error);
    }
}


// Initialize
document.addEventListener('DOMContentLoaded', fetchTickets);

// Sidebar and dropdown handling


function toggleSidebar() {
    sidebar.classList.toggle('show');
}

function closeSidebar(event) {
    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
        sidebar.classList.remove('show');
    }
}

function toggleDropdown(event) {
    const dropdown = event.currentTarget.parentElement;
    dropdown.classList.toggle('show');
}

sidebarToggle.addEventListener('click', toggleSidebar);
document.addEventListener('click', closeSidebar);
document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', toggleDropdown);
});

function viewTicketDetails(ticketId) {
window.location.href = `/views/ticket-details.html?id=${ticketId}`;
}

document.addEventListener('DOMContentLoaded', function() {
    
    const registerModal = document.getElementById('registerModal');
    const closeRegisterModal = document.querySelector('#registerModal .close');
    const registerForm = document.getElementById('registerForm');
    const registerButton = document.getElementById('register-button');
    // Show modal
    if (registerButton) {
        registerButton.addEventListener('click', function() {
            console.log("register clicked");
            registerModal.style.display = 'block';
        });
    }

    // Close modal
    if (closeRegisterModal) {
        closeRegisterModal.addEventListener('click', function() {
            registerModal.style.display = 'none';
        });
    }

    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target === registerModal) {
            registerModal.style.display = 'none';
        }
    });

    // Handle form submission
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            const formData = new FormData(registerForm);
            const data = Object.fromEntries(formData.entries());

            fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                alert('Registration successful');
                registerModal.style.display = 'none';
                registerForm.reset(); // Reset form fields
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const agentsMenu = document.getElementById('agents-menu');
    const agentListSection = document.getElementById('agent-list-section');
    
    agentsMenu.addEventListener('click', async function(event) {
        event.preventDefault(); // Prevent default link behavior
        agentListSection.style.display = 'block'; // Show agent list section

        try {
            const response = await fetch('/agents');
            if (!response.ok) {
                throw new Error('Failed to fetch agents');
            }
            const agents = await response.json();

            const agentListBody = document.getElementById('agent-list-body');
            agentListBody.innerHTML = ''; // Clear existing rows

            agents.forEach(agent => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${agent.name}</td>
                    <td>${agent.category || 'Support Technique'}</td>
                    <td>${agent.ticketsAssigned || 0}</td>
                    <td>${agent.ticketsOpen || 0}</td>
                    <td>${agent.ticketsResolved || 0}</td>
                    <td>${agent.ticketsClosed || 0}</td>
                `;
                agentListBody.appendChild(row);
            });
        } catch (error) {
            console.error('Error fetching agents:', error);
        }
    });
});