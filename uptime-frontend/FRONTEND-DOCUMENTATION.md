# Uptime Monitor - Vue.js Frontend

Aplikasi Vue.js frontend untuk sistem monitoring uptime yang terintegrasi dengan backend Laravel.

## 🚀 Fitur Utama

### ✅ Autentikasi & Keamanan
- Login dengan JWT token
- Route guards untuk proteksi halaman
- Auto-refresh token
- Logout otomatis saat token expired

### 📊 Dashboard
- Overview statistik monitoring
- Grafik uptime 24 jam terakhir  
- Recent incidents
- Monitor status cards

### 🖥️ Monitor Management
- CRUD operasi untuk semua jenis monitor
- Support untuk multiple monitor types:
  - HTTP/HTTPS monitoring
  - Ping monitoring
  - Port monitoring
  - Keyword monitoring
  - SSL certificate monitoring
  - Heartbeat monitoring
- Filtering dan pencarian monitors
- Pause/resume monitoring
- Real-time status updates

### 🚨 Incident Management
- Timeline view untuk semua incidents
- Status tracking (open, acknowledged, resolved)
- Add notes ke incidents
- Filter berdasarkan status dan monitor
- Acknowledge dan resolve incidents

### 🔔 Notification Channels
- Support multiple notification types:
  - Telegram Bot
  - Discord Webhooks
  - Slack Webhooks
  - Generic Webhooks
- Test notification functionality
- Channel management (enable/disable)

## 🛠️ Tech Stack

- **Vue 3** - Framework utama dengan Composition API
- **Vite** - Build tool dan development server
- **Vue Router** - Client-side routing
- **Pinia** - State management
- **Axios** - HTTP client untuk API calls
- **Chart.js** - Charting dan visualisasi data

## 📁 Struktur Proyek

```
src/
├── components/           # Komponen reusable
│   └── Navbar.vue       # Navigation bar
├── services/            # API layer
│   └── api.js          # Axios configuration & endpoints
├── stores/             # Pinia stores
│   ├── auth.js         # Authentication state
│   └── monitors.js     # Monitor management state
├── views/              # Page components
│   ├── LoginView.vue           # Login page
│   ├── DashboardView.vue       # Main dashboard
│   ├── MonitorsView.vue        # Monitor listing
│   ├── MonitorDetailView.vue   # Monitor details & checks
│   ├── CreateMonitorView.vue   # Create new monitor
│   ├── EditMonitorView.vue     # Edit monitor form
│   ├── NotificationChannelsView.vue  # Notification management
│   └── IncidentsView.vue       # Incident management
├── router/
│   └── index.js        # Route configuration
├── App.vue             # Root component
└── main.js            # Application entry point
```

## 🔌 API Integration

### Endpoints yang Digunakan

#### Authentication
- `POST /auth/login` - User login
- `POST /auth/logout` - User logout
- `POST /auth/refresh` - Refresh JWT token
- `GET /auth/user` - Get current user info

#### Monitors
- `GET /monitors` - List all monitors
- `GET /monitors/{id}` - Get specific monitor
- `POST /monitors` - Create new monitor
- `PUT /monitors/{id}` - Update monitor
- `DELETE /monitors/{id}` - Delete monitor
- `POST /monitors/{id}/pause` - Pause monitor
- `POST /monitors/{id}/resume` - Resume monitor

#### Monitor Checks
- `GET /monitor-checks` - Get check history
- `GET /monitor-checks/{id}` - Get specific check

#### Incidents
- `GET /incidents` - List all incidents
- `POST /incidents/{id}/acknowledge` - Acknowledge incident
- `POST /incidents/{id}/resolve` - Resolve incident
- `POST /incidents/{id}/reopen` - Reopen incident
- `POST /incidents/{id}/notes` - Add note to incident

#### Notification Channels
- `GET /notification-channels` - List all channels
- `POST /notification-channels` - Create channel
- `PUT /notification-channels/{id}` - Update channel
- `DELETE /notification-channels/{id}` - Delete channel
- `POST /notification-channels/{id}/test` - Test channel

## 🎯 State Management

### Auth Store (stores/auth.js)
```javascript
- user: Current user data
- token: JWT token
- isAuthenticated: Boolean login status
- login(): Authenticate user
- logout(): Clear auth data
- refreshToken(): Refresh JWT
```

### Monitors Store (stores/monitors.js)
```javascript
- monitors: Array of all monitors
- currentMonitor: Currently selected monitor
- fetchMonitors(): Load all monitors
- fetchMonitor(id): Load specific monitor
- createMonitor(data): Create new monitor
- updateMonitor(id, data): Update monitor
- deleteMonitor(id): Delete monitor
- pauseMonitor(id, duration): Pause monitoring
- resumeMonitor(id): Resume monitoring
```

## 🎨 UI/UX Features

### Responsive Design
- Mobile-first approach
- Responsive grid layouts
- Touch-friendly interface
- Adaptive navigation

### Real-time Updates
- Status indicators
- Live monitor states
- Incident notifications
- Auto-refresh capabilities

### User Experience
- Loading states untuk semua async operations
- Error handling dengan user-friendly messages
- Form validation
- Confirmation dialogs untuk destructive actions

## 🔧 Development Setup

### Prerequisites
- Node.js 20.19+ atau 22.12+
- npm atau yarn

### Installation
```bash
cd uptime-frontend
npm install
```

### Development Server
```bash
npm run dev
```

### Build for Production
```bash
npm run build
```

### Preview Production Build
```bash
npm run preview
```

## 🔐 Security Features

### Route Protection
- Authentication guards
- Role-based access (if needed)
- Automatic redirect to login

### API Security
- JWT token authentication
- Request/response interceptors
- Automatic token refresh
- Secure token storage

## 📱 Responsive Breakpoints

```css
/* Mobile First */
@media (max-width: 768px) {
  /* Mobile styles */
}

@media (min-width: 769px) {
  /* Tablet and desktop styles */
}
```

## 🎨 Theme & Styling

### Color Palette
- Primary: #3498db (Blue)
- Success: #27ae60 (Green)  
- Warning: #f39c12 (Orange)
- Danger: #e74c3c (Red)
- Secondary: #95a5a6 (Gray)

### Status Colors
- Up: Green (#27ae60)
- Down: Red (#e74c3c)
- Unknown: Gray (#95a5a6)
- Paused: Orange (#f39c12)

## 🚀 Deployment

### Environment Variables
```bash
VITE_API_BASE_URL=http://localhost:8000/api
```

### Build Configuration
```javascript
// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    port: 3000,
    proxy: {
      '/api': 'http://localhost:8000'
    }
  }
})
```

## 📄 Changelog

### v1.0.0
- ✅ Sistem autentikasi lengkap
- ✅ CRUD monitoring dengan semua tipe
- ✅ Dashboard dengan statistik dan grafik
- ✅ Incident management dengan timeline
- ✅ Notification channel management
- ✅ Responsive design untuk mobile dan desktop
- ✅ Integration lengkap dengan Laravel backend

## 🤝 Integrasi dengan Backend Laravel

Frontend ini dirancang khusus untuk bekerja dengan backend Laravel uptime monitor yang sudah dibuat sebelumnya. Semua endpoint API, struktur data, dan business logic sudah sesuai dengan implementasi backend.

### Fitur yang Sudah Terintegrasi:
1. ✅ Authentication dengan Sanctum/JWT
2. ✅ Monitor CRUD dengan validation
3. ✅ Real-time check data
4. ✅ Incident tracking dan management
5. ✅ Multi-channel notifications
6. ✅ Dashboard analytics

### Default Test Account:
- Email: admin@example.com
- Password: password

## 📞 Support & Troubleshooting

### Common Issues

1. **Build Errors**: Pastikan Node.js version sesuai requirements
2. **API Connection**: Check VITE_API_BASE_URL configuration
3. **Authentication**: Verify JWT token dan expiration settings
4. **CORS Issues**: Configure Laravel CORS untuk frontend domain

### Performance Optimization
- Lazy loading untuk routes
- Component-based architecture
- Efficient state management dengan Pinia
- Optimized bundle dengan Vite

---

**Status: ✅ Production Ready**
Frontend Vue.js sudah lengkap dan siap untuk production use dengan semua fitur yang sesuai dengan SRS dan terintegrasi penuh dengan backend Laravel.