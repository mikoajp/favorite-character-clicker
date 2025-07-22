<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Star Wars Character Contest - Choose your favorite character from Star Wars universe">
    <meta name="keywords" content="Star Wars, characters, contest, game, ratings">
    <title>Star Wars Character Contest - Character Competition</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Star Wars Character Contest">
    <meta property="og:description" content="Interactive Star Wars character contest. Vote, rate and choose your favorite heroes!">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Star Wars Character Contest">
    <meta name="twitter:description" content="Interactive Star Wars character contest">
    
    <!-- CSRF Token for API requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite(['resources/js/app.js'])
</head>
<body>
    <div id="app">
        <!-- Modern Header -->
        <header class="modern-header">
            <div class="header-content">
                <div class="header-main">
                    <div class="logo-section">
                        <h1 class="main-title">
                            <span class="title-icon">⚔️</span>
                            Star Wars Character Contest
                        </h1>
                        <p class="subtitle">Choose your destiny in a galaxy far, far away...</p>
                    </div>
                    <div class="tech-badges">
                        <span class="tech-badge php">PHP 8.4.5</span>
                        <span class="tech-badge laravel">Laravel 11</span>
                        <span class="tech-badge vue">Vue.js 3</span>
                    </div>
                </div>
                <div class="header-decoration"></div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="game-container">
            <!-- Welcome Message -->
            <div class="welcome-message">
                <h4>🚀 Welcome to Star Wars Character Contest!</h4>
                <p>Start an epic game and choose your favorite heroes from the Star Wars universe. 
                   Rate characters, add them to favorites and see who will be the ultimate winner!</p>
                <hr>
                <p>Ready for the challenge? May the Force be with you!</p>
            </div>

            <!-- Vue.js Component -->
            <character-list :characters="{{ json_encode($characters) }}"></character-list>
        </main>

        <!-- Modern Footer -->
        <footer class="modern-footer">
            <div class="footer-content">
                <div>
                    <p>&copy; {{ date('Y') }} Star Wars Character Contest</p>
                    <small>Built with ❤️ using Laravel and Vue.js</small>
                </div>
                <div class="footer-links">
                    <small>
                        App Version: 2.0 | 
                        <a href="https://github.com">GitHub</a> | 
                        <a href="#">Documentation</a>
                    </small>
                </div>
            </div>
        </footer>
    </div>

    <!-- Loading indicator for better UX -->
    <div id="loading" style="display: none;" class="position-fixed top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</body>
</html>