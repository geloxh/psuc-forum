<?php
require_once __DIR__ . '/forum.php';

function renderDropdownSidebar() {
    $forum = new Forum();
    $categories = $forum->getCategories();
    
    $forums = [];
    foreach($categories as $category) {
        $forums[$category['id']] = $forum->getForumsByCategory($category['id']);
    }
?>


<!-- Mobile Sidebar Toggle Button  -->
<button class="mobile-sidebar-toggle" id="mobileSidebarToggle" onclick="toggleMobileSidebar()"></div>
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="dropdown-sidebar" id="dropdownSidebar">
    <div class="category-dropdown">
        <?php foreach($categories as $category): ?>
            <div class="category-item-dropdown">
                <div class="category-header-dropdown" onclick="toggleCategory(<?php echo $category['id']; ?>)">
                    <i class="<?php echo $category['icon'] ?? 'fas fa-folder'; ?> category-icon-dropdown" style="color: <?php echo $category['color'] ?? '#007bff'; ?>"></i>
                    <span class="category-name"><?php echo htmlspecialchars($category['name']); ?></span>
                    <i class="fas fa-chevron-down dropdown-arrow" id="arrow-<?php echo $category['id']; ?>"></i>
                </div>
                <div class="forums-dropdown" id="forums-<?php echo $category['id']; ?>">
                    <?php foreach($forums[$category['id']] as $forum): ?>
                        <div class="forum-item-dropdown" onclick="navigateToForum(<?php echo $forum['id']; ?>)">
                            <div class="forum-name"><?php echo htmlspecialchars($forum['name']); ?></div>
                            <div class="forum-desc"><?php echo htmlspecialchars($forum['description'] ?? ''); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    /* Mobile Sidebar Toggle */
    .mobile-sidebar-toggle {
        display: none;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1003;
        background: #1877f2;
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size:18px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(24, 119, 242, 0.3);
        transition: all 0.3s ease;
    }

    .mobile-sidebar-toggle:hover {
        background: #166fe5;
        transform: scale(1.0s);
    }

    /* Sidebar Overlay */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 998;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sidebar-overlay.active {
        display: block;
    opacity: 1;
    }

    /* Mobile Sidebar Styles */
    @media (max-width: 768px) {
        .mobile-sidebar-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    
        .dropdown-sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 999;
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.15);
        }
    
        .dropdown-sidebar.mobile-open {
            transform: translateX(0);
        }
    
        body.sidebar-open {
            overflow: hidden;
        }
    }
</style>

<script>
function toggleCategory(categoryId) {
    const forums = document.getElementById('forums-' + categoryId);
    const arrow = document.getElementById('arrow-' + categoryId);
    
    if (forums.classList.contains('open')) {
        forums.classList.remove('open');
        arrow.classList.remove('rotated');
    } else {
        forums.classList.add('open');
        arrow.classList.add('rotated');
    }
}

function navigateToForum(forumId) {
    window.location.href = 'forum.php?id=' + forumId;
}

function toggleMobileSidebar() {
    const sidebar = document.getElementById('dropdownSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const body = document.body;

    if (sidebar.classList.contains('mobile-open')) {
        closeMobileSidebar();
    } else {
        sidebar.classList.add('mobile-open');
        overlay.classList.add('active');
        body.classList.add('sidebar-open');
    }
}

function closeMobileSidebar() {
    const sidebar = document.getElementById('dropdownSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const body = document.body;

    sidebar.classList.remove('mbile-open');
    overlay.classList.remove('active');
    body.classList.remove('sidebar-open');
}

// Close sidebar on window resize if screen becomes larger
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeMobileSidebar();
    }
});
</script>

<?php
}
?>
