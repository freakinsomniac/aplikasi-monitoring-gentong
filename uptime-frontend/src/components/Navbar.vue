<template>
  <nav class="navbar" :class="{ 'navbar-collapsed': isCollapsed }">
    <!-- Toggle Button -->
    <button class="navbar-toggle" @click="toggleSidebar">
      <i class="fas" :class="isCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
    </button>
    
    <div class="navbar-brand">
      <div class="brand-content">
        <div class="brand-icon">🔍</div>
        <div class="brand-text" v-show="!isCollapsed">
          <h2>Uptime Monitor</h2>
          <span class="brand-subtitle">Real-time monitoring</span>
        </div>
      </div>
    </div>
    
    <ul class="navbar-nav">
      <li class="nav-item">
        <router-link to="/dashboard" class="nav-link" active-class="active" :title="isCollapsed ? 'Dashboard' : ''">
          <span class="nav-icon">📊</span>
          <span class="nav-text" v-show="!isCollapsed">Dashboard</span>
          <div class="nav-indicator"></div>
        </router-link>
      </li>
      <li class="nav-item">
        <router-link to="/monitors" class="nav-link" active-class="active" :title="isCollapsed ? 'Monitors' : ''">
          <span class="nav-icon">🖥️</span>
          <span class="nav-text" v-show="!isCollapsed">Monitors</span>
          <div class="nav-indicator"></div>
        </router-link>
      </li>
      <li class="nav-item">
        <router-link to="/notifications" class="nav-link" active-class="active" :title="isCollapsed ? 'Notifications' : ''">
          <span class="nav-icon">🔔</span>
          <span class="nav-text" v-show="!isCollapsed">Notifications</span>
          <div class="nav-indicator"></div>
        </router-link>
      </li>
      <li class="nav-item">
        <router-link to="/incidents" class="nav-link" active-class="active" :title="isCollapsed ? 'Incidents' : ''">
          <span class="nav-icon">🚨</span>
          <span class="nav-text" v-show="!isCollapsed">Incidents</span>
          <div class="nav-indicator"></div>
        </router-link>
      </li>
    </ul>
    
    <div class="navbar-user">
      <div class="user-info" v-show="!isCollapsed">
        <div class="user-avatar">
          <span>{{ getInitials(authStore.user?.name) }}</span>
        </div>
        <div class="user-details">
          <span class="user-name">{{ authStore.user?.name || 'User' }}</span>
          <span class="user-role">{{ authStore.user?.role || 'Admin' }}</span>
        </div>
      </div>
      <div class="user-info-collapsed" v-show="isCollapsed">
        <div class="user-avatar-small" :title="authStore.user?.name || 'User'">
          <span>{{ getInitials(authStore.user?.name) }}</span>
        </div>
      </div>
      <button @click="handleLogout" class="btn btn-logout" :title="isCollapsed ? 'Logout' : ''">
        <span class="logout-icon">🚪</span>
        <span v-show="!isCollapsed">Logout</span>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const isCollapsed = ref(false)

function toggleSidebar() {
  isCollapsed.value = !isCollapsed.value
  // Emit event untuk memberitahu parent component
  document.dispatchEvent(new CustomEvent('sidebar-toggled', { 
    detail: { isCollapsed: isCollapsed.value } 
  }))
}

function getInitials(name) {
  if (!name) return 'U'
  return name.split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.navbar {
  position: fixed;
  left: 0;
  top: 0;
  width: 280px;
  height: 100vh;
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: white;
  padding: 0;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.navbar-collapsed {
  width: 70px;
}

.navbar-toggle {
  position: absolute;
  top: 20px;
  right: -35px;
  width: 30px;
  height: 30px;
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
  border: none;
  border-radius: 0 8px 8px 0;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
  z-index: 1001;
}

.navbar-toggle:hover {
  background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
  transform: scale(1.1);
}

.navbar-toggle i {
  font-size: 14px;
  transition: transform 0.3s ease;
}

.navbar-brand {
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 20px;
  min-height: 80px;
  display: flex;
  align-items: center;
}

.brand-content {
  display: flex;
  align-items: center;
  gap: 15px;
  width: 100%;
}

.brand-icon {
  font-size: 2rem;
  background: linear-gradient(45deg, #3498db, #2980b9);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  min-width: 35px;
}

.brand-text {
  flex: 1;
  opacity: 1;
  transition: opacity 0.3s ease;
}

.navbar-collapsed .brand-text {
  opacity: 0;
}

.brand-text h2 {
  color: white;
  margin: 0;
  font-size: 1.4rem;
  font-weight: 700;
  letter-spacing: -0.5px;
}

.brand-subtitle {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.85rem;
  margin-top: 2px;
  display: block;
}

.navbar-nav {
  flex: 1;
  list-style: none;
  padding: 0;
  margin: 0;
  overflow-y: auto;
}

.nav-item {
  margin: 0;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  border-left: 3px solid transparent;
  transition: all 0.3s ease;
  position: relative;
  gap: 15px;
}

.navbar-collapsed .nav-link {
  justify-content: center;
  padding: 15px 10px;
}

.nav-icon {
  font-size: 1.4rem;
  min-width: 25px;
  text-align: center;
}

.nav-text {
  font-weight: 500;
  font-size: 0.95rem;
  opacity: 1;
  transition: opacity 0.3s ease;
}

.navbar-collapsed .nav-text {
  opacity: 0;
}

.nav-indicator {
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 0;
  background: linear-gradient(180deg, #3498db, #2980b9);
  transition: height 0.3s ease;
  border-radius: 3px 0 0 3px;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border-left-color: #3498db;
}

.nav-link:hover .nav-indicator {
  height: 60%;
}

.nav-link.active {
  background: rgba(52, 152, 219, 0.15);
  color: white;
  border-left-color: #3498db;
}

.nav-link.active .nav-indicator {
  height: 80%;
}

.navbar-user {
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin-top: auto;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 15px;
  opacity: 1;
  transition: opacity 0.3s ease;
}

.user-info-collapsed {
  display: flex;
  justify-content: center;
  margin-bottom: 15px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #3498db, #2980b9);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
  color: white;
}

.user-avatar-small {
  width: 35px;
  height: 35px;
  background: linear-gradient(135deg, #3498db, #2980b9);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.8rem;
  color: white;
}

.user-details {
  flex: 1;
}

.user-name {
  display: block;
  font-weight: 600;
  margin-bottom: 2px;
  font-size: 0.95rem;
}

.user-role {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
  text-transform: capitalize;
}

.btn-logout {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  border: none;
  color: white;
  padding: 10px 15px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  justify-content: center;
}

.navbar-collapsed .btn-logout {
  padding: 10px;
  gap: 0;
}

.btn-logout:hover {
  background: linear-gradient(135deg, #c0392b, #e74c3c);
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
}

.logout-icon {
  font-size: 1.1rem;
}

/* Smooth transitions for text elements */
.nav-text, .brand-text, .user-info {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.navbar-collapsed .nav-text,
.navbar-collapsed .brand-text,
.navbar-collapsed .user-info {
  opacity: 0;
  transform: translateX(-10px);
}

/* Tooltip styles for collapsed state */
.navbar-collapsed [title] {
  position: relative;
}

.navbar-collapsed [title]:hover::after {
  content: attr(title);
  position: absolute;
  left: 100%;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  white-space: nowrap;
  font-size: 0.85rem;
  z-index: 1002;
  margin-left: 10px;
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-50%) translateX(-5px); }
  to { opacity: 1; transform: translateY(-50%) translateX(0); }
}

/* Mobile responsive */
@media (max-width: 768px) {
  .navbar {
    width: 100%;
    height: auto;
    position: relative;
  }
  
  .navbar-collapsed {
    width: 100%;
  }
  
  .navbar-toggle {
    display: none;
  }
}
</style>