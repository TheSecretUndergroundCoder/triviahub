<!-- Taskbar -->
<link rel="stylesheet" href="/styles/taskbar.css"> <!-- Link to the external CSS file -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=block" />
    <!-- Taskbar -->
    <div class="taskbar glass">
        <a href="index.php" class="button"><span class="material-symbols-outlined">home</span></a>
        <a href="create_quiz.php" class="button"><span class="material-symbols-outlined">add_circle</span></a>
        <a href="find_quiz.php" class="button"><span class="material-symbols-outlined">search</span></a>
        <a href="account.php" class="button"><span class="material-symbols-outlined">account_circle</span></a>
        <a href="contact.php" class="button"><span class="material-symbols-outlined">mail</span></a>
        <a href="news.php" class="button"><span class="material-symbols-outlined">newspaper</span></a>
        <a href="education.php" class="button"><span class="material-symbols-outlined">school</span></a>
        <a href="logout.php" class="button"><span class="material-symbols-outlined">logout</span></a>
        <?php
    		// Check if the user is an admin or owner
    		if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'owner')): ?>
        		<!-- Add a link to the admin panel for admins and owners -->
        		<a href="admin_panel.php" class="button"><span class="material-symbols-outlined">admin_panel_settings</span></a>
    	<?php endif; ?>
    </div>
