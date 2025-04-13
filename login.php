<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$error = '';
$success = '';
$registered_email = '';

if (isset($_SESSION['registration_success'])) {
    $success = "Registration successful! Please sign in with your credentials.";
    $registered_email = $_SESSION['registered_email'] ?? '';
    unset($_SESSION['registration_success']);
    unset($_SESSION['registered_email']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $conn = $database->getConnection();
    
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, password, role, full_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];
        header('Location: index.php');
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VolunteerHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-white">

    <div class="w-full h-screen flex">
        <div class="w-1/2 relative flex items-center justify-center" style="background-image: url('d2.jpeg'); background-size: cover; background-position: center;">
            <div class="absolute inset-0 bg-white bg-opacity-0 backdrop-blur-md flex items-center justify-center p-10">
                <div class="w-full max-w-md">
                    <div class="rounded-2xl shadow-xl p-8 bg-white bg-opacity-30 backdrop-blur-lg animate-fade-in">
                        <?php if ($error): ?>
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6 animate-shake" role="alert">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <span><?php echo $error; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-6" role="alert">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span><?php echo $success; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login.php" class="space-y-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-800 mb-1">Email Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input id="email" name="email" type="email" required 
                                        class="pl-10 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-700 focus:border-yellow-700 bg-white bg-opacity-70"
                                        placeholder="you@example.com"
                                        value="<?php echo htmlspecialchars($registered_email); ?>">
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-800 mb-1">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input id="password" name="password" type="password" required 
                                        class="pl-10 block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-700 focus:border-yellow-700 bg-white bg-opacity-70"
                                        placeholder="••••••••">
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="remember_me" name="remember_me" type="checkbox" 
                                        class="h-4 w-4 text-yellow-700 focus:ring-yellow-700 border-gray-300 rounded">
                                    <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                                        Remember me
                                    </label>
                                </div>
                                <a href="#" class="text-sm font-medium text-yellow-700 hover:text-yellow-900 transition-colors">
                                    Forgot password?
                                </a>
                            </div>

                            <button type="submit" 
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-yellow-700 hover:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-700 transition-all duration-200 transform hover:scale-[1.02]">
                                <i class="fas fa-sign-in-alt mr-2"></i> Sign in
                            </button>
                        </form>

                        <div class="text-center mt-6 animate-fade-in">
                            <p class="text-gray-800">
                                Don't have an account? 
                                <a href="register.php" class="font-medium text-yellow-700 hover:text-yellow-900 transition-colors">
                                    Create one now <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="w-1/2 overflow-y-auto">
    <div class="grid grid-cols-2 gap-4 p-6">
        <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition duration-300">
            <img src="p1<?php echo $i + 2; ?>.jpg" class="w-full h-40 object-cover" alt="Blog image">
            <div class="p-4">
                <h2 class="text-lg font-semibold text-yellow-700 mb-1">Inspiring Volunteer Story #<?php echo $i; ?></h2>
                <p class="text-gray-600 text-sm">Discover how volunteers are changing lives and making communities better with every act of kindness.</p>
            </div>
        </div>
        <?php endfor; ?>
    </div>
    </div>

    </div>
</body>


</html>
