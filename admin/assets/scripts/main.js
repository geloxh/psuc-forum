/**
 * scripts for index.php
 */
// Auto-refresh stats every 30 seconds
setInterval(() => {
    fetch('api/stats.php')
    .then(response => response.json())
    .then(data => {
        document.querySelector('.stat-card:nth-child(1) h3').textContent = data.total_users;
        document.querySelector('.stat-card:nth-child(2) h3').textContent = data.total_topics;
        document.querySelector('.stat-card:nth-child(3) h3').textContent = data.total_posts;
    });
}, 30000);


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