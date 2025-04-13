<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireLogin();

$database = new Database();
$conn = $database->getConnection();

$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header('Location: events.php');
    exit();
}

// Get event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header('Location: events.php');
    exit();
}

// Get volunteer profile
$stmt = $conn->prepare("SELECT id FROM volunteers WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$volunteer = $stmt->fetch(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if already registered
    $stmt = $conn->prepare("SELECT id FROM event_volunteers WHERE event_id = ? AND volunteer_id = ?");
    $stmt->execute([$event_id, $volunteer['id']]);
    if ($stmt->fetch()) {
        $error = "You are already registered for this event.";
    } else {
        // Register for event
        $stmt = $conn->prepare("INSERT INTO event_volunteers (event_id, volunteer_id, status) VALUES (?, ?, 'registered')");
        if ($stmt->execute([$event_id, $volunteer['id']])) {
            $success = "Successfully registered for the event!";
        } else {
            $error = "Failed to register for the event. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event - Volunteer Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/" class="font-bold text-xl">Sparkservices</a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/soemone/pages/volunteers/dashboard.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        <a href="/soemone/pages/volunteers/events.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-calendar-alt mr-1"></i>Events
                        </a>
                        <a href="/soemone/pages/volunteers/donations.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-hand-holding-usd mr-1"></i>Donations
                        </a>
                        <a href="/soemone/pages/volunteers/volunteers.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-users mr-1"></i>Volunteers
                        </a>
                        <a href="/soemone/pages/volunteers/profile.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-user mr-1"></i>Profile
                        </a>
                        <a href="/soemone/logout.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="mb-6">
                    <a href="events.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left"></i> Back to Events
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <h1 class="text-2xl font-bold text-gray-900 mb-6">Register for Event</h1>

                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4"><?php echo htmlspecialchars($event['title']); ?></h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Date & Time</h3>
                            <p class="mt-1 text-sm text-gray-900">
                                <?php echo date('F j, Y', strtotime($event['start_date'])); ?> at 
                                <?php echo date('g:i A', strtotime($event['start_date'])); ?>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Location</h3>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($event['location']); ?></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Required Volunteers</h3>
                            <p class="mt-1 text-sm text-gray-900"><?php echo $event['required_volunteers'] ?? 'No limit'; ?></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1 text-sm text-gray-900"><?php echo ucfirst($event['status']); ?></p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500">Description</h3>
                        <p class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    </div>

                    <?php if (!$success): ?>
                        <form action="register_event.php?id=<?php echo $event_id; ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Register for Event
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center">
                            <a href="events.php" class="inline-block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                View All Events
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 