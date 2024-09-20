## Project Overview

This project is a comprehensive **Ticketing System** designed for managing support requests and tickets in an organized and efficient way. It features a **Client Interface**, **Support Interface**, **Admin Interface**, and **Agent Interface**.

One key feature of this system is an **AI-powered chatbot** that assists clients with their queries. The chatbot first checks a **knowledge base** for an answer and, if no match is found, leverages **LLaMA3** to generate intelligent responses for the user. The system also sends email notifications to clients, agents, and support staff when ticket statuses change.

## Features

### Client Interface
- Submit tickets
- Track ticket status
- Chatbot support that uses a knowledge base and LLaMA3 for responses
- Email notifications on ticket updates

### Support Interface
- Dashboard with ticket summary
- Assign tickets to appropriate agents
- Manage ticket priority and statuses

### Admin Interface
- All support features
- Manage user roles and permissions (clients, agents, support)

### Agent Interface
- Manage and resolve assigned tickets
- Add comments and updates to tickets
- Email notifications when a ticket is assigned or updated

## Technologies Used

- **Backend**: PHP, RESTful API
- **Frontend**: HTML, CSS, JavaScript
- **Database**: MySQL
- **AI Integration**: LLaMA3 for chatbot responses
- **Email Service**: PHPMailer
- **Version Control**: Git
- **Libraries/Frameworks**: 
  - jQuery (for dynamic UI updates)
  - Toastr (for notifications)
  
## Architecture

The project is structured around a **RESTful API** that handles requests from multiple interfaces. It integrates a **role-based access control (RBAC)** system to ensure proper permissions for clients, agents, support staff, and admins. The AI-powered chatbot is integrated with a knowledge base and calls LLaMA3 for intelligent responses when required.

### Overview:
- **Client Interface**: Submit and track tickets, interact with the chatbot.
- **Support Interface**: Dashboard to assign and manage tickets.
- **Admin Interface**: Manage users and permissions.
- **Agent Interface**: Handle and resolve tickets.

## Installation

1. Clone the repository:
    ```bash
    git clone https://github.com/yourusername/ticketing-system.git
    ```

2. Navigate to the project directory:
    ```bash
    cd ticketing-system
    ```

3. Install dependencies:
    ```bash
    composer install
    ```

4. Set up your `.env` file for database and email configuration.

5. Run the project:
    - Set up the MySQL database and import the provided SQL schema.
    - Start the local PHP server or deploy to your preferred server.

## Usage

- Clients can open tickets, track their progress, and receive assistance via the chatbot.
- Support staff can assign tickets to agents and monitor their progress through a dashboard.
- Admins can manage users and their roles.
- Agents can resolve tickets and add comments to keep clients updated.

## Challenges and Solutions

- **Challenge**: Integrating the AI chatbot with LLaMA3 and the knowledge base.
  - **Solution**: Developed a fallback system where the chatbot checks the knowledge base first, and if no match is found, it queries the LLaMA3 API for intelligent responses.
  
- **Challenge**: Ensuring real-time updates for ticket statuses and notifications.
  - **Solution**: Implemented AJAX with `fetch` to handle dynamic updates and used PHPMailer for instant notifications.

## Future Improvements

- Expand the **knowledge base** for the chatbot to enhance its response accuracy.
- Improve the **UI/UX** for better user experience across all interfaces.
- Add more **analytics and reports** for the admin and support staff, such as ticket resolution time and agent performance metrics.
