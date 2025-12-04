import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Views
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import MonitorsView from '../views/MonitorsView.vue'
import MonitorDetailView from '../views/MonitorDetailView.vue'
import CreateMonitorView from '../views/CreateMonitorView.vue'
import EditMonitorView from '../views/EditMonitorView.vue'
import NotificationChannelsView from '../views/NotificationChannelsView.vue'
import IncidentsView from '../views/IncidentsView.vue'
import LogsView from '../views/LogsView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: '/dashboard'
    },
    {
      path: '/login',
      name: 'Login',
      component: LoginView,
      meta: { requiresGuest: true }
    },
    {
      path: '/dashboard',
      name: 'Dashboard',
      component: DashboardView,
      meta: { requiresAuth: true }
    },
    {
      path: '/monitors',
      name: 'Monitors',
      component: MonitorsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/monitors/create',
      name: 'CreateMonitor',
      component: CreateMonitorView,
      meta: { requiresAuth: true }
    },
    {
      path: '/monitors/:id',
      name: 'MonitorDetail',
      component: MonitorDetailView,
      meta: { requiresAuth: true },
      props: true
    },
    {
      path: '/monitors/:id/edit',
      name: 'EditMonitor',
      component: EditMonitorView,
      meta: { requiresAuth: true },
      props: true
    },
    {
      path: '/notifications',
      name: 'NotificationChannels',
      component: NotificationChannelsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/incidents',
      name: 'Incidents',
      component: IncidentsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/logs',
      name: 'Logs',
      component: LogsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/logs/monitor/:id',
      name: 'MonitorLogs',
      component: LogsView,
      meta: { requiresAuth: true },
      props: route => ({ monitorId: route.params.id })
    }
  ]
})

// Route guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router