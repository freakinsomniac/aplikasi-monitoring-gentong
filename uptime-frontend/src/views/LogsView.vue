<template>
  <div class="logs-view">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Header with Live indicator and Clear button -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <h1 class="text-2xl font-semibold text-white">Status History</h1>
          <div class="flex items-center gap-2 bg-green-500 px-3 py-1 rounded-full">
            <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
            <span class="text-white text-sm font-medium">Live</span>
          </div>
        </div>
        <button @click="clearData" class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
          Clear Data
        </button>
      </div>
      
      <!-- Monitor Selection -->
      <div class="bg-gray-700 rounded-lg p-4 mb-6">
        <div class="flex gap-4">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-300 mb-2">Monitor</label>
            <select v-model="selectedMonitorId" @change="fetchLogs" class="w-full px-3 py-2 bg-gray-600 text-white border border-gray-500 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="">All Monitors</option>
              <option v-for="monitor in monitors" :key="monitor.id" :value="monitor.id">
                {{ monitor.name }}
              </option>
            </select>
          </div>
          
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-300 mb-2">Time Period</label>
            <select v-model="selectedHours" @change="fetchLogs" class="w-full px-3 py-2 bg-gray-600 text-white border border-gray-500 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="1">Last 1 hour</option>
              <option value="6">Last 6 hours</option>
              <option value="24">Last 24 hours</option>
              <option value="72">Last 3 days</option>
              <option value="168">Last 7 days</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-8">
        <div class="text-gray-400">Loading status history...</div>
      </div>

      <!-- Status History Table -->
      <div v-else-if="logs.length > 0" class="bg-gray-800 rounded-lg overflow-hidden">
        <!-- Table Header -->
        <div class="bg-gray-700 px-6 py-4">
          <div class="grid grid-cols-12 gap-4 text-sm font-medium text-gray-300 uppercase tracking-wider">
            <div class="col-span-2">Status</div>
            <div class="col-span-4">DateTime</div>
            <div class="col-span-6">Message</div>
          </div>
        </div>

        <!-- Table Body -->
        <div class="divide-y divide-gray-700">
          <div v-for="log in logs" :key="log.id" class="px-6 py-4 hover:bg-gray-700 transition-colors">
            <div class="grid grid-cols-12 gap-4 items-center">
              <!-- Status -->
              <div class="col-span-2">
                <span :class="getStatusBadgeClass(log.status)" class="px-3 py-1 rounded text-xs font-bold uppercase">
                  {{ getStatusText(log.status) }}
                </span>
              </div>
              
              <!-- DateTime -->
              <div class="col-span-4 text-gray-300 text-sm">
                {{ formatDateTime(log.logged_at) }}
              </div>
              
              <!-- Message -->
              <div class="col-span-6 text-gray-300 text-sm">
                {{ getStatusMessage(log) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="bg-gray-700 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-400">
              Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </div>
            <div class="flex items-center gap-2">
              <button 
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="px-3 py-1 text-sm bg-gray-600 text-white rounded hover:bg-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                ← Previous
              </button>
              <button 
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Next →
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <div class="text-gray-400">No status history found</div>
        <p class="text-gray-500 text-sm mt-2">Status checks will appear here when monitors are running</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'

export default {
  name: 'LogsView',
  props: {
    monitorId: {
      type: [String, Number],
      default: null
    }
  },
  setup(props) {
    const loading = ref(false)
    const logs = ref([])
    const monitors = ref([])
    const selectedMonitorId = ref(props.monitorId || '')
    const selectedHours = ref(24)
    const pagination = ref(null)
    const currentPage = ref(1)

    const fetchLogs = async () => {
      loading.value = true
      try {
        const params = new URLSearchParams({
          hours: selectedHours.value,
          page: currentPage.value,
          per_page: 20
        })
        
        if (selectedMonitorId.value) {
          params.append('monitor_id', selectedMonitorId.value)
        }

        // Use monitor checks endpoint for status history
        const response = await fetch(`/api/monitor-checks?${params}`)
        const data = await response.json()
        
        if (data.success) {
          logs.value = data.data?.data || data.data || []
          pagination.value = data.data?.meta || {
            current_page: data.data?.current_page || 1,
            last_page: data.data?.last_page || 1
          }
        }
      } catch (error) {
        console.error('Error fetching logs:', error)
        logs.value = []
      } finally {
        loading.value = false
      }
    }

    const fetchMonitors = async () => {
      try {
        const response = await fetch('/api/monitors')
        const data = await response.json()
        if (data.success) {
          monitors.value = data.data || []
        }
      } catch (error) {
        console.error('Error fetching monitors:', error)
      }
    }

    const changePage = (page) => {
      if (page >= 1 && (!pagination.value || page <= pagination.value.last_page)) {
        currentPage.value = page
        fetchLogs()
      }
    }

    const clearData = () => {
      if (confirm('Are you sure you want to clear all status history data?')) {
        // Implementation would depend on backend endpoint
        console.log('Clear data functionality - to be implemented')
      }
    }

    const formatDateTime = (timestamp) => {
      const date = new Date(timestamp)
      return date.toLocaleString('en-US', {
        month: '2-digit',
        day: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      })
    }

    const getStatusBadgeClass = (status) => {
      const classes = {
        'up': 'bg-green-500 text-white',
        'down': 'bg-red-500 text-white',
        'validating': 'bg-blue-500 text-white',
        'pending': 'bg-yellow-500 text-black'
      }
      return classes[status] || 'bg-gray-500 text-white'
    }

    const getStatusText = (status) => {
      const texts = {
        'up': 'UP',
        'down': 'DOWN', 
        'validating': 'VALIDATING',
        'pending': 'PENDING'
      }
      return texts[status] || 'UNKNOWN'
    }

    const getStatusMessage = (log) => {
      if (log.status === 'up' && log.latency_ms) {
        return `${log.latency_ms}ms - OK`
      } else if (log.status === 'validating') {
        return 'Status check completed'
      } else if (log.error_message) {
        return log.error_message
      } else if (log.status === 'down') {
        return 'Service unavailable'
      }
      return 'Status check completed'
    }

    // Watch for prop changes
    watch(() => props.monitorId, (newId) => {
      selectedMonitorId.value = newId || ''
      currentPage.value = 1
      fetchLogs()
    }, { immediate: true })

    // Watch filter changes
    watch([selectedMonitorId, selectedHours], () => {
      currentPage.value = 1
      fetchLogs()
    })

    onMounted(() => {
      fetchMonitors()
      fetchLogs()
    })

    return {
      loading,
      logs,
      monitors,
      selectedMonitorId,
      selectedHours,
      pagination,
      fetchLogs,
      changePage,
      clearData,
      formatDateTime,
      getStatusBadgeClass,
      getStatusText,
      getStatusMessage
    }
  }
}
</script>

<style scoped>
.logs-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Animate the live indicator dot */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>