<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireLogin();

$database = new Database();
$conn = $database->getConnection();

if (isset($_POST['delete_event']) && isset($_POST['event_id'])) {
    try {
        // First check if the current user is the creator of the event
        $stmt = $conn->prepare("SELECT created_by FROM events WHERE id = :id");
        $stmt->execute([':id' => $_POST['event_id']]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($event && $event['created_by'] == $_SESSION['user_id']) {
            $stmt = $conn->prepare("DELETE FROM events WHERE id = :id");
            $stmt->execute([':id' => $_POST['event_id']]);
            $success_message = 'Event deleted successfully!';
        } else {
            $error_message = 'You do not have permission to delete this event.';
        }
    } catch (PDOException $e) {
        $error_message = 'Error deleting event: ' . $e->getMessage();
    }
}

// Handle event registration
if (isset($_POST['register_event']) && isset($_POST['event_id'])) {
    try {
        $stmt = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$_POST['event_id'], $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $error_message = 'You are already registered for this event.';
        } else {
            $stmt = $conn->prepare("
                SELECT e.max_volunteers, COUNT(er.id) as current_registrations 
                FROM events e 
                LEFT JOIN event_registrations er ON e.id = er.event_id 
                WHERE e.id = ? AND e.status = 'Open'
                GROUP BY e.id
            ");
            $stmt->execute([$_POST['event_id']]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$event) {
                $error_message = 'Event is not available for registration.';
            } elseif ($event['current_registrations'] >= $event['max_volunteers']) {
                $error_message = 'Event is full. No more volunteers can be registered.';
            } else {
                // Register for event
                $stmt = $conn->prepare("INSERT INTO event_registrations (event_id, user_id, status) VALUES (?, ?, 'Registered')");
                if ($stmt->execute([$_POST['event_id'], $_SESSION['user_id']])) {
                    $success_message = 'Successfully registered for the event!';
                } else {
                    $error_message = 'Failed to register for the event. Please try again.';
                }
            }
        }
    } catch (PDOException $e) {
        $error_message = 'Error registering for event: ' . $e->getMessage();
    }
}

// Get all events with creator information and registration status
$stmt = $conn->prepare("
    SELECT e.*, u.full_name as creator_name,
        (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id) as registered_count,
        (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id AND er.user_id = ?) as is_registered
    FROM events e
    JOIN users u ON e.created_by = u.id
    ORDER BY e.date_time ASC
");
$stmt->execute([$_SESSION['user_id']]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - VolunteerHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': {
                            50: '#fefce8',
                            100: '#fef9c3',
                            200: '#fef08a',
                            300: '#fde047',
                            400: '#facc15',
                            500: '#eab308',
                            600: '#ca8a04',
                            700: '#a16207',
                            800: '#854d0e',
                            900: '#713f12'
                        },
                        'secondary': {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f'
                        },
                        'accent': {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .modal-content {
            position: relative;
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 80%;
            max-width: 600px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #a16207;
        }
        
        .event-card {
            transition: all 0.3s ease;
            border-bottom: 3px solid #a16207;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(161, 98, 7, 0.1);
        }
    </style>
</head>
<body class="bg-yellow-50">
    <div class="min-h-screen">
        <nav class="bg-white shadow-lg border-b-2 border-yellow-700">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/soemone" class="font-bold text-xl text-yellow-700">
                            <i class="fas fa-hands-helping mr-2 text-yellow-600"></i>Sparkservices
                        </a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/soemone/pages/volunteers/dashboard.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        <a href="/soemone/pages/volunteers/events.php" class="text-yellow-700 font-semibold border-b-2 border-yellow-500">
                            <i class="fas fa-calendar-alt mr-1"></i>Events
                        </a>
                        <a href="/soemone/pages/volunteers/donations.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-hand-holding-usd mr-1"></i>Donations
                        </a>
                        <a href="/soemone/pages/volunteers/volunteers.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-users mr-1"></i>Volunteers
                        </a>
                        <a href="/soemone/pages/volunteers/profile.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-user mr-1"></i>Profile
                        </a>
                        <a href="/soemone/logout.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="bg-yellow-600 text-white user-hero-bg">
            <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                        Volunteer Events
                    </h1>
                    <p class="mt-6 max-w-2xl mx-auto text-xl text-amber-100">
                        Join our community events and make a difference in people's lives.
                    </p>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">
            <?php if (isset($success_message)): ?>
                <div class="mb-4 p-4 bg-yellow-100 text-yellow-700 rounded-md border-l-4 border-green-500">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md border-l-4 border-red-500">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-lg rounded-lg p-6 mb-8 border-t-4 border-yellow-700">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search Events</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-yellow-700"></i>
                            </div>
                            <input type="text" name="search" id="search" class="focus:ring-yellow-700 focus:border-yellow-700 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="Search by title, location...">
                        </div>
                    </div>
                    <div class="flex-1">
                        <label for="status" class="block text-sm font-medium text-gray-700">Filter by Status</label>
                        <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-yellow-700 focus:border-yellow-700 sm:text-sm rounded-md">
                            <option value="all">All Events</option>
                            <option value="open">Open Events</option>
                            <option value="closed">Closed Events</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <a href="/soemone/pages/volunteers/add_event.php" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-yellow-700 to-amber-700 hover:from-yellow-800 hover:to-amber-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-700 transition duration-300 ease-in-out transform hover:-translate-y-1 hover:scale-105 shadow-md">
                            <i class="fas fa-plus mr-2"></i>Add Event
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($events as $event): ?>
                <div class="bg-white overflow-hidden shadow-lg rounded-lg event-card transform transition duration-300">
                    <div class="h-48 bg-gradient-to-r from-yellow-700 to-amber-600 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-6xl"></i>
                    </div>
                    
                    <div class="px-5 py-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $event['status'] === 'Open' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($event['status']); ?>
                            </span>
                        </div>
                        
                        <div class="mt-4 space-y-3">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="far fa-calendar-alt mr-2 text-yellow-700"></i>
                                <?php echo date('M d, Y', strtotime($event['date_time'])); ?>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="far fa-clock mr-2 text-yellow-700"></i>
                                <?php echo date('h:i A', strtotime($event['date_time'])); ?>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-map-marker-alt mr-2 text-yellow-700"></i>
                                <?php echo htmlspecialchars($event['location']); ?>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-users mr-2 text-yellow-700"></i>
                                <?php echo $event['registered_count']; ?>/<?php echo htmlspecialchars($event['max_volunteers']); ?> Volunteers
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-user mr-2 text-yellow-700"></i>
                                Created by: <?php echo htmlspecialchars($event['creator_name']); ?>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between items-center">
                            <button onclick="showEventDetails(<?php echo htmlspecialchars(json_encode($event)); ?>)" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-yellow-700 to-amber-700 hover:from-yellow-800 hover:to-amber-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-700 transition duration-300 ease-in-out shadow-md">
                                View Details
                                <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                            <?php if ($event['created_by'] == $_SESSION['user_id']): ?>
                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <button type="submit" name="delete_event" class="inline-flex items-center px-4 py-2 border border-red-600 text-sm font-medium rounded-md text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-300 ease-in-out shadow-sm">
                                    Delete
                                    <i class="fas fa-trash-alt ml-2"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($events)): ?>
            <div class="text-center py-12 bg-white rounded-lg shadow-lg border-t-4 border-yellow-700">
                <i class="fas fa-calendar-times text-6xl text-yellow-400 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-900">No Events Available</h3>
                <p class="mt-2 text-sm text-gray-600">
                    There are currently no events scheduled. Please check back later.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <div id="eventModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div id="eventDetails"></div>
            </div>
        </div>

        <footer class="bg-gradient-to-r from-yellow-700 via-amber-600 to-orange-500 text-white shadow mt-12">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-amber-100 text-sm">
                    &copy; <?php echo date('Y'); ?> VolunteerHub. All rights reserved.
                </p>
            </div>
        </footer>
    </div>

    <script>
        const modal = document.getElementById("eventModal");
        const closeBtn = document.getElementsByClassName("close")[0];

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function showEventDetails(event) {
            const modal = document.getElementById("eventModal");
            const detailsDiv = document.getElementById("eventDetails");
            
            const details = `
                <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b-2 border-yellow-300 pb-2">${event.title}</h2>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <i class="far fa-calendar-alt text-yellow-700 mr-2"></i>
                        <span>Date: ${new Date(event.date_time).toLocaleDateString()}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="far fa-clock text-yellow-700 mr-2"></i>
                        <span>Time: ${new Date(event.date_time).toLocaleTimeString()}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt text-yellow-700 mr-2"></i>
                        <span>Location: ${event.location}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-users text-yellow-700 mr-2"></i>
                        <span>Volunteers: <span class="math-inline">\{event\.registered\_count\}/</span>{event.max_volunteers}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-user text-yellow-700 mr-2"></i>
                        <span>Created by: <span class="math-inline">\{event\.creator\_name\}</span\>
</div\>
<div class\="mt\-4"\>
<h3 class\="text\-lg font\-semibold text\-gray\-900 mb\-2 border\-b border\-yellow\-200 pb\-1"\>Description</h3\>
<p class\="text\-gray\-600"\></span>{event.description || 'No description provided.'}</p>
                    </div>
                    <div class="mt-6 flex justify-between">
                        ${event.status === 'Open' && !event.is_registered ? `
                            <form method="POST" class="inline">
                                <input type="hidden" name="event_id" value="${event.id}">
                                <button type="submit" name="register_event" 
                                    class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-yellow-700 to-amber-700 hover:from-yellow-800 hover:to-amber-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-700 transition duration-300 ease-in-out shadow-md">
                                    Participate
                                    <i class="fas fa-user-plus ml-2"></i>
                                </button>
                            </form>
                        ` : ''}
                        ${event.is_registered ? `
                            <span class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-yellow-700 bg-yellow-100 shadow-sm">
                                Already Registered
                                <i class="fas fa-check ml-2"></i>
                            </span>
                        ` : ''}
                    </div>
                </div>
            `;
            
            detailsDiv.innerHTML = details;
            modal.style.display = "block";
        }

        document.getElementById('search').addEventListener('input', filterEvents);
        document.getElementById('status').addEventListener('change', filterEvents);

        function filterEvents() {
            const searchTerm = document.getElementById('search').value.toLowerCase();
            const statusFilter = document.getElementById('status').value;
            const eventCards = document.querySelectorAll('.grid > div');

            eventCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const location = card.querySelector('.fa-map-marker-alt').nextSibling.textContent.toLowerCase();
                const status = card.querySelector('span').textContent.toLowerCase();
                
                const matchesSearch = title.includes(searchTerm) || location.includes(searchTerm);
                const matchesStatus = statusFilter === 'all' || 
                                    (statusFilter === 'open' && status === 'open') ||
                                    (statusFilter === 'closed' && status === 'closed');

                card.style.display = matchesSearch && matchesStatus ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>