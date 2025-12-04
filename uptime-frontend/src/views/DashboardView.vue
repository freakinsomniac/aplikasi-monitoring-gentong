<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <h1>Dashboard</h1>
      <p>Overview of your monitoring system</p>
      <div class="header-status">
        <span class="status-indicator live">
          <div class="pulse-dot"></div>
          <span>Live Data</span>
        </span>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="dashboard-grid skeleton">
      <div class="stats-row">
        <div class="stat-card skeleton-card" v-for="n in 4" :key="n">
          <div class="skeleton-icon"></div>
          <div class="skeleton-content">
            <div class="skeleton-line large"></div>
            <div class="skeleton-line small"></div>
          </div>
        </div>
      </div>
      
      <div class="card skeleton-card-large">
        <div class="skeleton-header">
          <div class="skeleton-line medium"></div>
          <div class="skeleton-line small"></div>
        </div>
        <div class="skeleton-list">
          <div class="skeleton-item" v-for="n in 5" :key="n">
            <div class="skeleton-status"></div>
            <div class="skeleton-content-item">
              <div class="skeleton-line title"></div>
              <div class="skeleton-line subtitle"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="error" class="error">
      {{ error }}
    </div>

    <div v-else class="dashboard-grid">
      <!-- Stats Cards -->
      <div class="stats-row">
        <div class="stat-card stat-total" style="animation-delay: 0.1s">
          <div class="stat-icon">
            <span>📊</span>
            <div class="icon-glow"></div>
          </div>
          <div class="stat-content">
            <h3 class="counter" data-target="{{ dashboardData?.total_monitors || 0 }}">{{ dashboardData?.total_monitors || 0 }}</h3>
            <p>Total Monitors</p>
          </div>
          <div class="stat-trend positive">+{{ Math.floor(Math.random() * 5) }}% this week</div>
        </div>

        <div class="stat-card stat-up" style="animation-delay: 0.2s">
          <div class="stat-icon">
            <span>✅</span>
            <div class="icon-glow success"></div>
          </div>
          <div class="stat-content">
            <h3 class="counter" data-target="{{ dashboardData?.monitors_up || 0 }}">{{ dashboardData?.monitors_up || 0 }}</h3>
            <p>Monitors Up</p>
          </div>
          <div class="stat-trend positive">{{ uptimePercentage }}% uptime</div>
        </div>

        <div class="stat-card stat-down" style="animation-delay: 0.3s">
          <div class="stat-icon">
            <span>❌</span>
            <div class="icon-glow danger"></div>
          </div>
          <div class="stat-content">
            <h3 class="counter" data-target="{{ dashboardData?.monitors_down || 0 }}">{{ dashboardData?.monitors_down || 0 }}</h3>
            <p>Monitors Down</p>
          </div>
          <div class="stat-trend negative" v-if="dashboardData?.monitors_down > 0">Needs attention</div>
          <div class="stat-trend positive" v-else>All good!</div>
        </div>

        <div class="stat-card stat-incidents" style="animation-delay: 0.4s">
          <div class="stat-icon">
            <span>🚨</span>
            <div class="icon-glow warning"></div>
          </div>
          <div class="stat-content">
            <h3 class="counter" data-target="{{ dashboardData?.open_incidents || 0 }}">{{ dashboardData?.open_incidents || 0 }}</h3>
            <p>Open Incidents</p>
          </div>
          <div class="stat-trend neutral">{{ averageResponseTime }}ms avg response</div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card activity-card" style="animation-delay: 0.5s">
        <div class="card-header">
          <h3>
            <span class="icon">📊</span>
            Recent Monitor Activity
          </h3>
          <router-link to="/monitors" class="btn btn-primary btn-sm">
            <span>👁️</span> View All Monitors
          </router-link>
        </div>
        
        <div class="activity-list" v-if="recentChecks?.length">
          <div
            v-for="(check, index) in recentChecks"
            :key="check.id"
            class="activity-item"
            :style="{ animationDelay: `${0.1 * index}s` }"
          >
            <div class="activity-status">
              <span 
                class="status-indicator"
                :class="{
                  'status-up': check.status === 'up',
                  'status-down': check.status === 'down',
                  'status-unknown': check.status === 'unknown'
                }"
              >
                <div class="status-pulse"></div>
              </span>
            </div>
            <div class="activity-content">
              <strong>{{ check.monitor?.name }}</strong>
              <p class="activity-status-text">
                <span class="status-badge" :class="check.status">{{ check.status.toUpperCase() }}</span>
                <span class="activity-time">{{ formatDate(check.checked_at) }}</span>
              </p>
              <small v-if="check.error_message" class="error-msg">
                ⚠️ {{ check.error_message }}
              </small>
            </div>
            <div class="activity-metrics">
              <span v-if="check.latency_ms" class="latency-badge" :class="getLatencyClass(check.latency_ms)">
                {{ check.latency_ms }}ms
              </span>
              <div class="activity-actions">
                <button @click="viewMonitor(check.monitor?.id)" class="action-btn">
                  <span>👁️</span>
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="no-data">
          <div class="no-data-icon">📊</div>
          <p>No recent activity</p>
        </div>
      </div>

      <!-- Current Incidents -->
      <div class="card">
        <div class="card-header">
          <h3>Current Incidents</h3>
          <router-link to="/incidents" class="btn btn-primary btn-sm">
            View All Incidents
          </router-link>
        </div>
        
        <div v-if="!dashboardData?.current_incidents?.length" class="no-data">
          No active incidents 🎉
        </div>
        
        <div v-else class="incidents-list">
          <div
            v-for="incident in dashboardData.current_incidents"
            :key="incident.id"
            class="incident-item"
          >
            <div class="incident-content">
              <strong>{{ incident.monitor?.name }}</strong>
              <p>Started: {{ formatDate(incident.started_at) }}</p>
              <small>{{ incident.description || 'No description' }}</small>
            </div>
            <div class="incident-duration">
              {{ getIncidentDuration(incident.started_at) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <div class="card-header">
          <h3>Quick Actions</h3>
        </div>
        
        <div class="quick-actions">
          <router-link to="/monitors/create" class="btn btn-success">
            ➕ Add New Monitor
          </router-link>
          <router-link to="/notifications" class="btn btn-primary">
            🔔 Manage Notifications
          </router-link>
          <button @click="refreshDashboard" class="btn btn-warning">
            🔄 Refresh Data
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import { formatDistanceToNow } from 'date-fns'

const loading = ref(true)
const error = ref(null)
const dashboardData = ref(null)
const recentChecks = ref([])

onMounted(() => {
  fetchDashboardData()
})

async function fetchDashboardData() {
  loading.value = true
  error.value = null

  try {
    // Fetch overview data
    const overviewResponse = await api.dashboard.overview()
    if (overviewResponse.data.success) {
      dashboardData.value = overviewResponse.data.data
    }

    // Fetch recent monitor checks
    const checksResponse = await api.monitorChecks.getAll({ per_page: 10 })
    if (checksResponse.data.success) {
      recentChecks.value = checksResponse.data.data.data || checksResponse.data.data
    }

  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load dashboard data'
  } finally {
    loading.value = false
  }
}

async function refreshDashboard() {
  await fetchDashboardData()
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleString()
}

function getIncidentDuration(startDate) {
  return formatDistanceToNow(new Date(startDate), { addSuffix: true })
}
</script>

<style scoped>
.dashboard {
  padding: 20px;
}

.dashboard-header {
  margin-bottom: 30px;
}

.dashboard-header h1 {
  margin: 0 0 5px 0;
  color: #2c3e50;
}

.dashboard-header p {
  margin: 0;
  color: #7f8c8d;
}

.dashboard-grid {
  display: grid;
  gap: 20px;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
}

.stat-icon {
  font-size: 2em;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-total .stat-icon { background: #e3f2fd; }
.stat-up .stat-icon { background: #e8f5e8; }
.stat-down .stat-icon { background: #ffebee; }
.stat-incidents .stat-icon { background: #fff3e0; }

.stat-content h3 {
  margin: 0;
  font-size: 1.8em;
  color: #2c3e50;
}

.stat-content p {
  margin: 5px 0 0 0;
  color: #7f8c8d;
  font-size: 0.9em;
}

.card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
}

.card-header {
  padding: 20px;
  border-bottom: 1px solid #ecf0f1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  color: #2c3e50;
}

.activity-list, .incidents-list {
  padding: 0;
}

.activity-item, .incident-item {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #ecf0f1;
  gap: 15px;
}

.activity-item:last-child, .incident-item:last-child {
  border-bottom: none;
}

.activity-status {
  flex-shrink: 0;
}

.status-indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  display: block;
}

.status-indicator.status-up { background-color: #27ae60; }
.status-indicator.status-down { background-color: #e74c3c; }
.status-indicator.status-unknown { background-color: #95a5a6; }

.activity-content, .incident-content {
  flex: 1;
}

.activity-content strong, .incident-content strong {
  color: #2c3e50;
}

.activity-content p, .incident-content p {
  margin: 2px 0;
  color: #7f8c8d;
  font-size: 0.9em;
}

.error-msg {
  color: #e74c3c;
  font-size: 0.8em;
}

.activity-time, .incident-duration {
  flex-shrink: 0;
  font-size: 0.8em;
  color: #95a5a6;
}

.quick-actions {
  padding: 20px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.no-data {
  padding: 40px 20px;
  text-align: center;
  color: #7f8c8d;
}

@media (max-width: 768px) {
  .stats-row {
    grid-template-columns: 1fr;
  }
  
  .card-header {
    flex-direction: column;
    gap: 10px;
    align-items: stretch;
  }
  
  .quick-actions {
    flex-direction: column;
  }
}
</style>