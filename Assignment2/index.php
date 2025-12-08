<?php
require_once 'includes/db.php';
$page_title = 'Home';
include 'includes/header.php';
?>

<div class="landing-page">
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">
                <span class="highlight">Welcome to the ultimate</span>
                <br>education tracker website
            </h1>
            <h2 class="brand-name">EduTrack</h2>
            <p class="hero-subtitle">
                Your personal learning productivity system. Manage tasks, track progress, 
                and achieve your educational goals with ease.
            </p>
            <div class="cta-buttons">
                <a href="views/signup.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Get Started
                </a>
                <a href="views/signin.php" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </a>
            </div>
        </div>
    </section>

    <section class="features-section">
        <h2 class="section-title">Features</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3>Smart To-Do Lists</h3>
                <p>Organize your tasks with priorities, due dates, and status tracking</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Academic Calendar</h3>
                <p>Never miss a deadline with our integrated calendar system</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-sticky-note"></i>
                </div>
                <h3>Note Taking</h3>
                <p>Create and organize notes just like Notion, but focused on learning</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Progress Tracking</h3>
                <p>Visualize your learning journey with detailed statistics</p>
            </div>
        </div>
    </section>

    <section class="sdg-section">
        <div class="sdg-content">
            <div class="sdg-badge">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h2>Supporting SDG 4: Quality Education</h2>
            <p>
                EduTrack is committed to promoting inclusive and equitable quality education 
                and lifelong learning opportunities for all. Our platform helps students and 
                learners organize their studies more effectively, making education more 
                accessible and manageable.
            </p>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>

