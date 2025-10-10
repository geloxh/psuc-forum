/**
 * scripts for index.php
 */
 // Update time every minute
        setInterval(() => {
            document.getElementById('current-time').textContent = new Date().toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }, 60000);

        // Auto-refresh stats every 30 seconds
        setInterval(() => {
            fetch('api/stats.php')
                .then(response => response.json())
                .then(data => {
                    document.querySelector('.stat-card:nth-child(1) .stat-number').textContent = data.total_users.toLocaleString();
                    document.querySelector('.stat-card:nth-child(2) .stat-number').textContent = data.total_topics.toLocaleString();
                    document.querySelector('.stat-card:nth-child(3) .stat-number').textContent = data.total_posts.toLocaleString();
                });
        }, 30000);

        // Add loading animation to action buttons
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                this.style.opacity = '0.7';
                setTimeout(() => this.style.opacity = '1', 200);
            });
        });

/**
 * scripts for users.php
 * 
 */ 
// Handle role changes
    document.querySelectorAll('.role-select').forEach(select => {
        select.addEventListener('change', function() {
            const userId = this.dataset.userId;
            const newRole = this.value;
                
            fetch('users.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ajax=1&action=update_role&user_id=${userId}&role=${newRole}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    this.style.background = '#d4edda';
                    setTimeout(() => this.style.background = '', 2000);
                }
            });
        });
    });

    // Handle user deletion
    document.querySelectorAll('.delete-user').forEach(btn => {
        btn.addEventListener('click', function() {
            if(confirm('Are you sure you want to delete this user?')) {
                const userId = this.dataset.userId;
                    
                fetch('users.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `ajax=1&action=delete_user&user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        this.closest('tr').remove();
                    }
                });
            }
        });
    });


    /**
     * For headers
     */
    // include/header.php
    function toggleTheme() {
        document.body.classList.toggle('dark-theme');
        document.getElementById('themeIcon').className = document.body.classList.contains('dark-theme') ? 'fas fa-sun' : 'fas fa-moon';
    }