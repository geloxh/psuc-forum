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

<!-- Mobile Sidebar Toggle Button -->
<button class="mobile-sidebar-toggle" id="mobileSidebarToggle" onclick="toggleMobileSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

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
.mobile-sidebar-toggle {
    display: none;
    position: fixed;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1003;
    background: #1877f2;
    color: white;
    border: none;
    border-radius: 0 12px 12px 0;
    width: 24px;
    height: 48px;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 2px 0 8px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
}

.mobile-sidebar-toggle:hover {
    background: #166fe5;
    width: 28px;
}

.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 998;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sidebar-overlay.active {
    display: block;
    opacity: 1;
}

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
        box-shadow: 2px 0 20px rgba(0,0,0,0.15);
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
    
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
    body.classList.remove('sidebar-open');
}

window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeMobileSidebar();
    }
});
</script>

<?php
}
?>
