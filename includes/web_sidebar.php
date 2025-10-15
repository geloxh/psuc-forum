<?php
function renderDropdownSidebar() {
    $categories = [
        ['id' => 1, 'name' => 'General Discussion', 'desc' => 'General topics for all PSUC members', 'icon' => 'fas fa-comments', 'color' => '#007bff'],
        ['id' => 2, 'name' => 'Academic', 'desc' => 'Academic discussions and resources', 'icon' => 'fas fa-graduation-cap', 'color' => '#28a745'],
        ['id' => 3, 'name' => 'Research', 'desc' => 'Research collaboration and sharing', 'icon' => 'fas fa-microscope', 'color' => '#dc3545'],
        ['id' => 4, 'name' => 'Events & Announcements', 'desc' => 'University events and official announcements', 'icon' => 'fas fa-calendar', 'color' => '#ffc107'],
        ['id' => 5, 'name' => 'Student Life', 'desc' => 'Campus life, activities, and student concerns', 'icon' => 'fas fa-users', 'color' => '#17a2b8']
    ];

    $forums = [
        1 => [
            ['name' => 'Welcome & Introductions', 'desc' => 'Introduce yourself to the PSUC community'],
            ['name' => 'General Chat', 'desc' => 'General discussions about anything'],
            ['name' => 'Help & Support', 'desc' => 'Get help with forum usage and technical issues']
        ],
        2 => [
            ['name' => 'Course Discussions', 'desc' => 'Discuss courses, curriculum, and academic topics'],
            ['name' => 'Study Groups', 'desc' => 'Form and join study groups'],
            ['name' => 'Academic Resources', 'desc' => 'Share textbooks, notes, and study materials']
        ],
        3 => [
            ['name' => 'Research Projects', 'desc' => 'Share and collaborate on research projects'],
            ['name' => 'Publications & Papers', 'desc' => 'Share published papers and research articles'],
            ['name' => 'Research Opportunities', 'desc' => 'Post and find research opportunities']
        ],
        4 => [
            ['name' => 'University Events', 'desc' => 'Upcoming events and activities'],
            ['name' => 'Official Announcements', 'desc' => 'Important announcements from administration'],
            ['name' => 'News & Updates', 'desc' => 'Latest news and updates from PSUC institutions']
        ],
        5 => [
            ['name' => 'Campus Life', 'desc' => 'Discuss campus life and experiences'],
            ['name' => 'Organizations & Clubs', 'desc' => 'Student organizations and club activities'],
            ['name' => 'Career & Opportunities', 'desc' => 'Job opportunities, internships, and career advice']
        ]
    ];
?>

<div class="dropdown-sidebar open" id="dropdownSidebar">
    <div class="category-dropdown">
        <?php foreach($categories as $category): ?>
            <div class="category-item-dropdown">
                <div class="category-header-dropdown" onclick="toggleCategory(<?php echo $category['id']; ?>)">
                    <i class="<?php echo $category['icon']; ?> category-icon-dropdown" style="color: <?php echo $category['color']; ?>"></i>
                    <span class="category-name"><?php echo htmlspecialchars($category['name']); ?></span>
                    <i class="fas fa-chevron-down dropdown-arrow" id="arrow-<?php echo $category['id']; ?>"></i>
                </div>
                <div class="forums-dropdown" id="forums-<?php echo $category['id']; ?>">
                    <?php foreach($forums[$category['id']] as $forum): ?>
                        <div class="forum-item-dropdown" onclick="navigateToForum('<?php echo urlencode($forum['name']); ?>')">
                            <div class="forum-name"><?php echo htmlspecialchars($forum['name']); ?></div>
                            <div class="forum-desc"><?php echo htmlspecialchars($forum['desc']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>


</script>

<?php
}
?>
