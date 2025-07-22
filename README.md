# ⚔️ Star Wars Character Contest

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.4.5-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Version">
  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue.js Version">
  <img src="https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge" alt="License">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Status-Production%20Ready-brightgreen?style=for-the-badge" alt="Status">
  <img src="https://img.shields.io/badge/Version-2.0-blue?style=for-the-badge" alt="Version">
</p>

<p align="center">
  <em>Choose your destiny in a galaxy far, far away...</em>
</p>

## 🌟 About This Project

**Star Wars Character Contest** is a modern, interactive web application that brings the excitement of the Star Wars universe to your browser. Built with cutting-edge technologies, it allows users to participate in epic character battles, rate their favorite heroes and villains, and discover new characters from a galaxy far, far away.

### ✨ Key Features

- 🎮 **Interactive Tournament System**: Elimination-style character battles
- ⭐ **Advanced Rating System**: 5-star rating with detailed analytics
- ❤️ **Favorites Management**: Personal collection of beloved characters
- 🏆 **Progressive Rounds**: Tournament-style elimination gameplay
- 📱 **Responsive Design**: Perfect experience on all devices
- 🎨 **Modern UI/UX**: Dark theme with Star Wars-inspired design
- ⚡ **Real-time Updates**: Instant feedback and smooth animations
- 🔄 **Session Management**: Persistent game state across sessions

### 🛠️ Technology Stack

- **Backend**: Laravel 11.x with PHP 8.4.5
- **Frontend**: Vue.js 3.4.38 with Composition API
- **Build System**: Vite 5.4.8 with hot module replacement
- **Styling**: Custom CSS with CSS Grid and Flexbox
- **Database**: SQLite with Eloquent ORM
- **API**: RESTful API with comprehensive error handling
- **Caching**: Database-driven sessions and caching

## 📋 Requirements

- **PHP**: 8.4.5 or higher
- **Composer**: Latest version
- **Node.js**: 18.0.0 or higher
- **NPM**: 8.0.0 or higher
- **Database**: SQLite (default) or MySQL/PostgreSQL

## 🚀 Quick Start

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/star-wars-character-contest.git
cd star-wars-character-contest
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database file (SQLite)
touch database/database.sqlite
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### 5. Build Assets
```bash
# For production
npm run build

# For development with hot reloading
npm run dev
```

### 6. Start the Application
```bash
# Start Laravel development server
php artisan serve

# Visit: http://localhost:8000
```

## 🔧 Development Setup

### Hot Reloading Development
```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite dev server
npm run dev
```

### Database Management
```bash
# Reset database
php artisan migrate:fresh

# Seed with sample data
php artisan db:seed

# Check migration status
php artisan migrate:status
```

## 🎮 How to Play

### Starting a Contest
1. **Choose Character Count**: Select 10, 20, or 30 characters for your tournament
2. **Click "Rozpocznij Rozgrywkę"**: Start your epic journey
3. **Make Your Choice**: Choose between two characters in each round
4. **Progress Through Rounds**: Continue until only one character remains

### Game Features
- **Character Rating**: Rate characters with 1-5 stars
- **Favorites System**: Save your favorite characters for later
- **Tournament Progress**: Track your progress through rounds
- **Final Winner**: See your ultimate Star Wars champion

## 📊 API Endpoints

### Game Management
- `POST /api/start-game` - Start a new tournament
- `POST /api/select-character` - Choose character in current round
- `GET /api/current-game` - Get current game state
- `POST /api/abandon-game` - Abandon current game

### Character Ratings
- `POST /api/ratings` - Rate a character
- `GET /api/ratings/{characterId}` - Get character ratings
- `GET /api/ratings/{characterId}/average` - Get average rating

### Favorites
- `POST /api/favorites` - Add character to favorites
- `DELETE /api/favorites` - Remove from favorites
- `GET /api/favorites` - Get user's favorite characters

## 🏗️ Project Structure

```
├── app/
│   ├── Http/Controllers/     # API and web controllers
│   ├── Models/              # Eloquent models
│   └── Services/            # Business logic services
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   ├── js/
│   │   └── components/    # Vue.js components
│   ├── css/              # Stylesheets
│   └── views/            # Blade templates
├── routes/
│   ├── api.php           # API routes
│   └── web.php           # Web routes
└── public/               # Public assets
```

## 🎨 Design System

### Color Palette
- **Primary**: `#FFE81F` (Star Wars Yellow)
- **Secondary**: `#000000` (Deep Black)
- **Accent**: `#FF6B35` (Orange)
- **Background**: `#0D1117` (Dark)
- **Cards**: `#161B22` (Dark Gray)

### Typography
- **Font Family**: Inter, system fonts
- **Headings**: 700-800 weight
- **Body**: 400-600 weight

## 🔒 Security Features

- **CSRF Protection**: All forms protected with CSRF tokens
- **Session Security**: Secure session management
- **Input Validation**: Comprehensive request validation
- **Error Handling**: Graceful error responses
- **Rate Limiting**: API rate limiting (future enhancement)

## 🧪 Testing

```bash
# Run PHP tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter=GameServiceTest
```

## 📦 Deployment

### Production Build
```bash
# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci

# Build assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/amazing-feature`
3. **Commit your changes**: `git commit -m 'Add amazing feature'`
4. **Push to the branch**: `git push origin feature/amazing-feature`
5. **Open a Pull Request**

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Use conventional commit messages

## 🐛 Troubleshooting

### Common Issues

**"No active game found"**
```bash
# Clear sessions and cache
php artisan cache:clear
php artisan session:table
php artisan migrate
```

**"Vite manifest not found"**
```bash
# Rebuild assets
npm run build
```

**"Class not found"**
```bash
# Regenerate autoloader
composer dump-autoload
```

## 📈 Performance

- **Caching**: Database query caching
- **Asset Optimization**: Minified CSS/JS
- **Image Optimization**: WebP support (future)
- **Database**: Optimized queries with indexes

## 🔮 Future Enhancements

- [ ] **User Authentication**: Registration and login system
- [ ] **Multiplayer Mode**: Play with friends
- [ ] **Advanced Statistics**: Detailed analytics dashboard
- [ ] **PWA Support**: Install as mobile app
- [ ] **Sound Effects**: Star Wars audio experience
- [ ] **Themes**: Light/Dark mode toggle
- [ ] **Internationalization**: Multiple language support

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Star Wars API**: Character data provided by external API
- **Laravel Community**: Amazing framework and ecosystem
- **Vue.js Team**: Reactive frontend framework
- **Star Wars**: The incredible universe that inspired this project

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/your-username/star-wars-character-contest/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-username/star-wars-character-contest/discussions)
- **Email**: your-email@example.com

---

<p align="center">
  <strong>May the Force be with you! ⚔️</strong>
</p>
