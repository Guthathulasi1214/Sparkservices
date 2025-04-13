<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireLogin();

$database = new Database();
$conn = $database->getConnection();

// Get current user's profile
$stmt = $conn->prepare("
    SELECT * FROM users 
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['full_name', 'email'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $bio = $_POST['bio'] ?? '';

        // Check if email is already taken by another user
        $stmt = $conn->prepare("
            SELECT id FROM users 
            WHERE email = ? AND id != ?
        ");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            throw new Exception("Email is already taken by another user");
        }

        $stmt = $conn->prepare("
            UPDATE users 
            SET full_name = ?, email = ?, phone = ?, address = ?, bio = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$full_name, $email, $phone, $address, $bio, $_SESSION['user_id']]);

        $success_message = "Profile updated successfully!";
        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - sparkservices</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <a href="/soemone/pages/volunteers/events.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-calendar-alt mr-1"></i>Events
                        </a>
                        <a href="/soemone/pages/volunteers/donations.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-hand-holding-usd mr-1"></i>Donations
                        </a>
                        <a href="/soemone/pages/volunteers/volunteers.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-users mr-1"></i>Volunteers
                        </a>
                        <a href="/soemone/pages/volunteers/profile.php" class="text-yellow-700 font-semibold border-b-2 border-yellow-500">
                            <i class="fas fa-user mr-1"></i>Profile
                        </a>
                        <a href="/soemone/logout.php" class="text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="bg-yellow-700 text-white">
            <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                        My Profile
                    </h1>
                    <p class="mt-6 max-w-2xl mx-auto text-xl text-yellow-100">
                        Manage your personal information and preferences
                    </p>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <?php if (isset($success_message)): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-lg rounded-lg p-6">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                        </div>
                    </div>

                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                        <textarea name="bio" id="bio" rows="4" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-700 hover:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <footer class="bg-yellow-700 text-white shadow mt-12">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-yellow-100 text-sm">
                    &copy; <?php echo date('Y'); ?> Sparkservices. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</body>
</html>