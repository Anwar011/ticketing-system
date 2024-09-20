<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start session
// Load required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/src/controllers/TicketController.php';
require_once __DIR__ . '/src/controllers/UserController.php';
require_once __DIR__ . '/src/controllers/AuthController.php';
require_once __DIR__ . '/src/controllers/AdminController.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Load the FAQs from the JSON file
$faqs = json_decode(file_get_contents('faqs.json'), true);

// Function to find the FAQ answer
function get_faq_response($message, $faqs) {
    foreach ($faqs as $faq) {
        if (stripos($message, $faq['question']) !== false) {
            return $faq['answer'];
        }
    }
    return "";
}
// Function to get response from GPT-Neo
function get_gpt_neo_response($message) {
    $url = 'http://localhost:5001/generate'; // URL of the GPT-Neo server
    $data = json_encode(['prompt' => $message]);

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => $data,
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return "Sorry, there was an error processing your request.";
    }

    $response_data = json_decode($result, true);
    return $response_data['response'];
}

ob_start();
header('Access-Control-Allow-Origin: *'); // Allow all origins. You can restrict this to specific domains.
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Simulate current user for role-based access (In a real application, you would retrieve the current user from session or JWT)
$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : ['role' => 'guest']; // Default role if not logged in
// Initialize controllers with current user
$ticketController = new TicketController($db, $currentUser);
$userController = new UserController($db);
$authController = new AuthController($db);
$adminController = new AdminController($db);
$requestUri = $_SERVER['REQUEST_URI'];

// Routing logic
$requestMethod = $_SERVER["REQUEST_METHOD"];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
error_log('Request URI: ' . $_SERVER['REQUEST_URI']);
error_log('Request Method: ' . $requestMethod);

function send_to_llama2($model, $prompt) {
    $url = 'http://localhost:11434/api/generate'; // URL of the LLaMA2 API in the container
    $data = json_encode([
        'model' => $model,
        'prompt' => $prompt,
        'stream' => true,
        'max_tokens' => 20,
    ]);

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => $data,
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return "Sorry, there was an error processing your request.";
    }

    return $result; // Return raw result or process it as needed
}

// Helper function to extract ID from URL
function getIdFromUrl($url) {
    $urlParts = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));
    return isset($urlParts[1]) ? $urlParts[1] : null; // Adjust indexing based on your URL structure
}



switch ($path) {
    case '/tickets':
        if ($requestMethod == 'GET') {
            header('Content-Type: application/json');
            echo $ticketController->readTickets();
        } elseif ($requestMethod == 'POST') {
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            echo $ticketController->createTicket($data);
        }
        break;
        case preg_match('/^\/ticket\/(\d+)$/', $path, $matches) ? $path : '':
            if ($requestMethod == 'GET') {
                $id = $matches[1]; // Extracted ticket ID
                header('Content-Type: application/json');
                echo $ticketController->readTicket($id);
            }
            break;
        
    case '/ticket/update':
        if ($requestMethod == 'PUT') {
            $data = json_decode(file_get_contents("php://input"), true);
            echo $ticketController->updateTicket($data);
        }
        break;
    case '/ticket/delete':
        if ($requestMethod == 'DELETE') {
            $id = getIdFromUrl($path);
            echo $ticketController->deleteTicket($id);
        }
        break;
    case '/ticket/comment':
            if ($requestMethod == 'POST') {
                $data = json_decode(file_get_contents("php://input"), true);
                echo $ticketController->addComment($data);
            } 
            break;
    case preg_match('/^\/ticket\/(\d+)\/comments$/', $path, $matches) && $requestMethod == 'GET':
            $id = $matches[1];
            header('Content-Type: application/json');
            echo $ticketController->getComments($id);
        break;
    case '/ticket/status':
        if ($requestMethod == 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            echo $ticketController->changeStatus($data);
        }
        break;
        case preg_match('/^\/ticket\/(\d+)\/type$/', $path, $matches) && $method === 'PUT':
            $id = $matches[1];
            $data = json_decode(file_get_contents('php://input'), true);
    
            if (isset($data['type'])) {
                header('Content-Type: application/json');
                echo $ticketController->updateTicketType($id, $data['type']);
            } else {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['message' => 'Type is required']);
            }
            break;
    case '/register':
        if ($requestMethod == 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            echo $authController->registerUser($data);
        }
        break;
    case '/login':
        if ($requestMethod == 'POST') {
           
            $data = json_decode(file_get_contents("php://input"), true);
            echo $authController->loginUser($data);
        }
        break;
    case '/users':
        if ($requestMethod == 'GET') {
            echo $authController->getUsers();
        }
        break;
    case '/user/role':
        if ($requestMethod == 'PUT') {
            $data = json_decode(file_get_contents("php://input"), true);
            echo $adminController->updateUserRole($data);
        }
        break;
        case '/agents':
            if ($requestMethod == 'GET') {
                echo $userController->getAgents();
            }
            break;
            case '/clients':
                if ($requestMethod == 'GET') {
                    echo $userController->getClients();
                }
                break;
         case '/admins':
                    if ($requestMethod == 'GET') {
                        echo $userController->getAdmins();
                    }
                break;
         case '/supports':
                    if ($requestMethod == 'GET') {
                        echo $userController->getSupports();
                    }
                break;
        case '/users':
                if ($requestMethod == 'GET') {
                    echo $userController->getAllUsers();
                }
            break;
        case '/assign-tickets':
            if ($requestMethod == 'POST') {
                $data = json_decode(file_get_contents("php://input"), true);
                echo $ticketController->assignTickets($data);
            }
            break;
        case '/chatbot' :
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Get the user's message from the POST request
                $data = json_decode(file_get_contents('php://input'), true);
                $user_message = $data['message'];
            
                // Get the response from the FAQ
                $response = get_faq_response($user_message, $faqs);
            
                // Return the response as JSON
                header('Content-Type: application/json');
                echo json_encode(['response' => $response]);
                exit(); // Ensure no additional output is added
            }
            break;
            case '/llama3':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Get the user's message from the POST request
                    $data = json_decode(file_get_contents('php://input'), true);
                    $prompt = $data['prompt'];
                    $model = $data['model'] ?? 'llama2'; // Default model if not provided
            
                    // Send the prompt to the LLaMA2 container and get the response
                    $response = send_to_llama2($model, $prompt);
            
                    // Return the response as JSON
                    header('Content-Type: application/json');
                    echo $response;
                    exit(); // Ensure no additional output is added
                }
                break;
                case '/logout':
                    if ($requestMethod == 'POST') {
                        session_destroy(); // Destroy the session
                        echo json_encode(['message' => 'Successfully logged out.']);
                    }
                    break;
            
        
    default:
        echo json_encode(['message' => 'Route not found.']);
        break;
}

?>
