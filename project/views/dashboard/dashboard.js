// Sélecteurs des éléments
const sidebar = document.querySelector('.sidebar');
const sidebarToggle = document.querySelector('.sidebar-toggle');
const dashboardItem = document.querySelector('.sidebar ul li.active');
const canvas = document.getElementById('myChart');

// Fonction pour afficher/masquer la barre latérale
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

// Assurez-vous que la barre latérale commence dans l'état caché si nécessaire
function initializeSidebar() {
  sidebar.classList.remove('show'); 
  dashboardItem.classList.remove('active'); 
}

// Exécuter l'initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', initializeSidebar);

// Ajout des écouteurs d'événements
sidebarToggle.addEventListener('click', toggleSidebar);
document.addEventListener('click', closeSidebar);
document.querySelector('.dropdown-toggle').addEventListener('click', toggleDropdown);

// Créer le contexte du canevas pour le graphique
const ctx = canvas.getContext('2d');

// Initialiser le graphique
const myChart = new Chart(ctx, {
  type: 'pie',
  data: {
      labels: ['open', 'Closed', 'Solved', 'Assigned'],
      datasets: [{
          data: [0, 0, 0, 0], // Initial data, will be updated
          backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0']
      }]
  },
  options: {
      responsive: true,
      plugins: {
          legend: {
              display: true,
              position: 'right',
              labels: {
                  color: '#fff'
              }
          }
      }
  }
});

// Fetch ticket data from the API
async function fetchTickets() {
  try {
    const response = await fetch('/tickets'); // Replace with your API endpoint
    const tickets = await response.json();
    console.log(tickets);
    return tickets;
  } catch (error) {
    console.error('Error fetching tickets:', error);
    // Consider displaying an error message to the user here
    return [];
  }
}

// Process tickets based on the selected filter
function processTickets(tickets, filter) {
  const today = new Date();
  const counts = {
    open: 0,
    closed: 0,
    resolved: 0,
    in_progress: 0
  };

  // Calculate the date range based on the filter
  let startDate;
  switch (filter) {
    case 'day':
      startDate = new Date(today.getTime() - 24 * 60 * 60 * 1000); // Last 24 hours
      break;
    case 'mounth':
      startDate = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000); // Last 30 days
      break;
    case 'year':
      startDate = new Date(today.getTime() - 365 * 24 * 60 * 60 * 1000); // Last 365 days
      break;
    default:
      startDate = new Date(0); // Default to beginning of time if no valid filter
  }
  console.log('Start Date:', startDate);

  tickets.forEach(ticket => {
    const ticketDate = new Date(ticket.created_at);

    // Ensure date parsing is correct
    if (isNaN(ticketDate.getTime())) {
      console.error(`Invalid date format for ticket: ${ticket.created_at}`);
      return;
    }

    // Handle cases where status might be null or empty
    const status = ticket.status ? ticket.status.toLowerCase() : 'unknown';
    
    // Ensure status is valid
    if (!counts.hasOwnProperty(status)) {
      console.warn(`Unexpected status value: ${status}`);
      return;
    }

    // Count based on the date range
    if (ticketDate >= startDate && ticketDate <= today) {
      counts[status]++;
    }
  });

  // Debugging: Log counts
  console.log('Counts after processing:', counts);

  return counts;
}

// Handle filter change
document.getElementById('filter').addEventListener('change', async (event) => {
  const filter = event.target.value;
  const tickets = await fetchTickets();
  const counts = processTickets(tickets, filter);
  updateDashboard(counts);
});

// Initialize the dashboard
document.addEventListener('DOMContentLoaded', async () => {
  const filter = document.getElementById('filter').value;
  const tickets = await fetchTickets();
  const counts = processTickets(tickets, filter);
  updateDashboard(counts);
});

 


// Update the dashboard with ticket counts
function updateDashboard(counts) {
  document.querySelector('.card:nth-child(1) span').textContent = counts.open;
  document.querySelector('.card:nth-child(2) span').textContent = counts.closed;
  document.querySelector('.card:nth-child(3) span').textContent = counts.resolved;
  document.querySelector('.card:nth-child(4) span').textContent = counts.in_progress;

  // Update the chart
  updateChart(counts);
}

// Update the chart with new data
function updateChart(counts) {
  myChart.data.datasets[0].data = [counts.open, counts.closed, counts.resolved, counts.in_progress];
  myChart.update();
}

// Handle filter change
document.getElementById('filter').addEventListener('change', async (event) => {
  const filter = event.target.value;
  const tickets = await fetchTickets();
  const counts = processTickets(tickets, filter);
  updateDashboard(counts);
});

// Initialize the dashboard
document.addEventListener('DOMContentLoaded', async () => {
  const filter = document.getElementById('filter').value;
  const tickets = await fetchTickets();
  const counts = processTickets(tickets, filter);
  updateDashboard(counts);
});

// Fonction pour afficher/masquer le menu déroulant
function toggleDropdown(event) {
  const dropdown = event.currentTarget.parentElement;
  dropdown.classList.toggle('show');
}
async function fetchAgentData() {
  try {
      const response = await fetch('/agents'); // Replace with your API endpoint for agents
      const agents = await response.json();
      return agents;
  } catch (error) {
      console.error('Error fetching agents:', error);
      return [];
  }
}

async function initializeDashboard() {
  const filter = document.getElementById('filter').value;
  const tickets = await fetchTickets();
  const counts = processTickets(tickets, filter);

  // Fetch and display agent count
  const agents = await fetchAgentData();
  document.getElementById('agent-count').textContent = agents.length;

  updateDashboard(counts);
  updateAgentChart(agents);
}

function updateAgentChart(agents) {
  // Example data processing for agent chart
  const agentTicketCounts = agents.map(agent => agent.ticket_count);
  
  const agentChart = new Chart(document.getElementById('agentChart').getContext('2d'), {
      type: 'bar',
      data: {
          labels: agents.map(agent => agent.name),
          datasets: [{
              label: 'Tickets per Agent',
              data: agentTicketCounts,
              backgroundColor: '#36a2eb'
          }]
      },
      options: {
          responsive: true,
          plugins: {
              legend: {
                  display: false
              }
          }
      }
  });
}

// Initialize the dashboard on page load
document.addEventListener('DOMContentLoaded', initializeDashboard);
