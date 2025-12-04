# Uptime Monitor Frontend

Vue.js frontend untuk sistem uptime monitoring yang terintegrasi dengan Laravel backend.

## 🚀 Quick Start

### Prerequisites
- Node.js 20.19+ atau 22.12+
- Backend Laravel sudah berjalan di `http://localhost:8000`

### Installation
```bash
# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build
```

### Default Login
- Email: `admin@example.com`  
- Password: `password`

## 📋 Features

✅ **Authentication System**
- JWT token-based authentication
- Auto token refresh
- Protected routes

✅ **Dashboard**
- Monitor statistics overview
- Uptime charts
- Recent incidents
- System status cards

✅ **Monitor Management**
- Create, edit, delete monitors
- Support for HTTP, Ping, Port, Keyword, SSL, Heartbeat monitoring
- Pause/resume monitoring
- Real-time status updates

✅ **Incident Management**
- View all incidents with timeline
- Acknowledge and resolve incidents
- Add notes to incidents
- Filter by status and monitor

✅ **Notification Channels**
- Telegram, Discord, Slack, Webhook support
- Test notifications
- Channel management

✅ **Responsive Design**
- Mobile-friendly interface
- Touch-optimized controls

## 🛠️ Tech Stack

- Vue 3 + Composition API
- Vite (build tool)
- Vue Router (routing)
- Pinia (state management)
- Axios (HTTP client)
- Chart.js (charts)

## 📁 Project Structure

```
src/
├── components/         # Reusable components
├── views/             # Page components  
├── stores/            # Pinia state stores
├── services/          # API services
└── router/           # Route configuration
```

## 🔌 API Integration

Frontend ini berkomunikasi dengan Laravel backend melalui RESTful API endpoints. Semua endpoint sudah dikonfigurasi di `src/services/api.js`.

## 🎨 Customization

### Environment Variables
```bash
# .env
VITE_API_BASE_URL=http://localhost:8000/api
```

### Styling
Aplikasi menggunakan custom CSS dengan color scheme:
- Primary: #3498db (Blue)
- Success: #27ae60 (Green)
- Warning: #f39c12 (Orange)  
- Danger: #e74c3c (Red)

## 📱 Mobile Support

Aplikasi sudah responsive dan mobile-friendly dengan:
- Touch-optimized controls
- Responsive navigation
- Mobile-first CSS approach

## 🔐 Security

- JWT token authentication
- Protected API routes
- Automatic logout on token expiration
- CSRF protection via backend integration

## 📚 Documentation

Lihat [FRONTEND-DOCUMENTATION.md](./FRONTEND-DOCUMENTATION.md) untuk dokumentasi lengkap.

## 🤝 Backend Integration

Frontend ini dirancang untuk bekerja dengan Laravel backend uptime monitor. Pastikan:

1. Backend Laravel sudah running
2. Database sudah di-migrate
3. API endpoints accessible
4. CORS configured untuk frontend domain

## 🚀 Production Deployment

```bash
# Build for production
npm run build

# Output akan tersedia di folder dist/
# Deploy folder dist/ ke web server
```

## 🐛 Troubleshooting

### Common Issues

1. **Connection refused**: Pastikan backend Laravel running di port 8000
2. **CORS errors**: Configure Laravel CORS middleware
3. **Build fails**: Update Node.js ke versi yang sesuai
4. **Authentication errors**: Check JWT configuration di backend

### Development Tips

- Gunakan browser dev tools untuk debug API calls
- Check Pinia store state di Vue DevTools
- Verify API responses di Network tab

## 📞 Support

Untuk issues dan pertanyaan teknis, check:
1. Console browser untuk errors
2. Network tab untuk failed API calls
3. Vue DevTools untuk state debugging

---

**🎯 Status: Production Ready**

Frontend Vue.js sudah lengkap dan siap digunakan dengan semua fitur monitoring yang diperlukan!
