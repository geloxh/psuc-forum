<?php
require_once 'includes/auth.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

if(!$user) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$success = '';
$error = '';

if($_POST) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $university = $_POST['university'];
    
    if(empty($full_name) || empty($email) || empty($university)) {
        $error = 'Please fill out all required fields.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $query = "UPDATE users SET full_name = ?, email = ?, university = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        if($stmt->execute([$full_name, $email, $university, $user['id']])) {
            $success = 'Profile updated successfully!';
            // Refresh user data
            $user = $auth->getCurrentUser();
        } else {
            $error = 'Failed to update profile. Email may already be in use.';
        }
    }
}

$universities = [
    'University of the Philippines System' => [
        'University of the Philippines Diliman',
        'University of the Philippines Manila',
        'University of the Philippines Los Baños',
        'University of the Philippines Visayas',
        'University of the Philippines Mindanao',
        'University of the Philippines Open University',
        'University of the Philippines Baguio',
        'University of the Philippines Cebu'
    ],
    'Major State Universities' => [
        'Polytechnic University of the Philippines',
        'Technological University of the Philippines',
        'Philippine Normal University',
        'Mindanao State University',
        'Central Luzon State University',
        'Visayas State University',
        'Bicol University'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main style="background: #f8fafc; min-height: 100vh; padding: 3rem 1rem;">
        <div style="max-width: 700px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <div style="width: 80px; height: 80px; background: var(--primary-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white; font-size: 2rem;">
                    <i class="fas fa-cog"></i>
                </div>
                <h1 style="font-size: 2.5rem; font-weight: 300; color: var(--text-primary); margin: 0 0 0.5rem 0; letter-spacing: -0.02em;">Account Settings</h1>
                <p style="color: var(--text-secondary); font-size: 1.1rem; margin: 0;">Manage your profile information</p>
            </div>

            <?php if($success): ?>
                <div style="background: rgba(34, 197, 94, 0.1); color: #059669; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid rgba(34, 197, 94, 0.2); text-align: center;">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: #dc2626; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid rgba(239, 68, 68, 0.2); text-align: center;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div style="background: white; border-radius: 20px; padding: 3rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 2rem;">
                <form method="POST">
                    <div style="margin-bottom: 2rem;">
                        <label style="display: block; font-weight: 500; color: var(--text-primary); margin-bottom: 0.75rem; font-size: 0.95rem;">Full Name</label>
                        <input type="text" name="full_name" required 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>"
                               style="width: 100%; padding: 1rem 1.25rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: #f8fafc;"
                               onfocus="this.style.borderColor='var(--secondary-blue)'; this.style.background='white'"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc'">
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <label style="display: block; font-weight: 500; color: var(--text-primary); margin-bottom: 0.75rem; font-size: 0.95rem;">Email Address</label>
                        <input type="email" name="email" required 
                               value="<?php echo htmlspecialchars($user['email']); ?>"
                               style="width: 100%; padding: 1rem 1.25rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: #f8fafc;"
                               onfocus="this.style.borderColor='var(--secondary-blue)'; this.style.background='white'"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc'">
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <label style="display: block; font-weight: 500; color: var(--text-primary); margin-bottom: 0.75rem; font-size: 0.95rem;">University/College</label>
                        <select name="university" required 
                                style="width: 100%; padding: 1rem 1.25rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: #f8fafc;"
                                onfocus="this.style.borderColor='var(--secondary-blue)'; this.style.background='white'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc'">
                            <?php foreach($universities as $group => $unis): ?>
                                <optgroup label="<?php echo htmlspecialchars($group); ?>">
                                    <?php foreach($unis as $uni): ?>
                                        <option value="<?php echo htmlspecialchars($uni); ?>" 
                                                <?php echo $user['university'] == $uni ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($uni); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
                        <div>
                            <label style="display: block; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.75rem; font-size: 0.95rem;">Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled
                                   style="width: 100%; padding: 1rem 1.25rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; background: #f1f5f9; color: var(--text-secondary);">
                            <small style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.5rem; display: block;">Cannot be changed</small>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.75rem; font-size: 0.95rem;">Role</label>
                            <input type="text" value="<?php echo ucfirst($user['role']); ?>" disabled
                                   style="width: 100%; padding: 1rem 1.25rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; background: #f1f5f9; color: var(--text-secondary);">
                            <small style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.5rem; display: block;">Assigned by admin</small>
                        </div>
                    </div>

                    <div style="text-align: center;">
                        <button type="submit" 
                                style="background: var(--primary-gradient); color: white; border: none; padding: 1rem 3rem; border-radius: 50px; font-weight: 500; cursor: pointer; transition: all 0.3s ease; font-size: 1rem;"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(59, 130, 246, 0.3)'"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04); text-align: center;">
                    <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--secondary-blue);">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">
                        <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                    </div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem;">Member Since</div>
                </div>
                <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04); text-align: center;">
                    <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--accent-gold);">
                        <i class="fas fa-star"></i>
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">
                        <?php echo $user['reputation']; ?>
                    </div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem;">Reputation</div>
                </div>
            </div>

            <div style="background: rgba(59, 130, 246, 0.05); border-radius: 16px; padding: 2rem; margin-top: 2rem; text-align: center; border: 1px solid rgba(59, 130, 246, 0.1);">
                <i class="fas fa-shield-alt" style="font-size: 2rem; color: var(--secondary-blue); margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.2rem; font-weight: 500; color: var(--text-primary); margin-bottom: 1rem;">Privacy & Security</h3>
                <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6; margin: 0;">
                    Your account information is kept secure and private. Only your username and university are visible to other users.
                </p>
            </div>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
    <script>
        // Add responsive behavior for mobile
        if (window.innerWidth <= 768) {
            const gridContainer = document.querySelector('[style*="grid-template-columns: 1fr 1fr"]');
            if (gridContainer) {
                gridContainer.style.gridTemplateColumns = '1fr';
                gridContainer.style.gap = '1rem';
            }
        }
    </script>
</body>
</html>