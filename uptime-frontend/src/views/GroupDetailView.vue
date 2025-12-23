<template>
  <div class="group-detail">
    <div class="page-header">
      <div class="header-content">
        <div class="header-main">
          <h1>Group: {{ displayName }}</h1>
          <p v-if="groupDescription">{{ groupDescription }}</p>
        </div>
        <div class="header-actions">
          <button @click="$router.back()" class="btn btn-secondary">← Back</button>
          <button @click="refresh" class="btn btn-primary">Refresh</button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading">Loading group monitors...</div>
    <div v-else-if="error" class="error">{{ error }}</div>

    <div v-else>
      <div class="group-stats">
        <div class="stat">Total: {{ monitors.length }}</div>
        <div class="stat">Online: {{ upCount }}</div>
        <div class="stat">Offline: {{ downCount }}</div>
      </div>

      <div class="monitors-list">
        <div v-for="m in monitors" :key="m.id" class="monitor-item clickable" @click="openMonitor(m.id)">
          <div class="left">
            <h3>{{ m.name }}</h3>
            <p class="target">{{ m.target }}</p>
          </div>
          <div class="right">
            <span :class="['status-badge', m.last_status]">{{ (m.last_status||'unknown').toUpperCase() }}</span>
          </div>
        </div>
      </div>

      <div v-if="!monitors.length" class="empty-state">No monitors in this group.</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMonitorStore } from '../stores/monitors'

const route = useRoute()
const router = useRouter()
const monitorStore = useMonitorStore()

const rawName = route.params.name || route.params.group || ''
const groupName = decodeURIComponent(rawName)
const displayName = groupName || 'Ungrouped'

const monitors = ref([])
const loading = ref(false)
const error = ref(null)
const groupDescription = ref('')

const upCount = computed(() => monitors.value.filter(m => m.last_status === 'up').length)
const downCount = computed(() => monitors.value.filter(m => m.last_status === 'down').length)

async function loadGroup() {
  loading.value = true
  error.value = null
  try {
    // Try to fetch monitors filtered by group via store
    const params = {}
    if (displayName !== 'Ungrouped') {
      params.group = displayName
    } else {
      params.group = 'ungrouped'
    }
    const resp = await monitorStore.fetchMonitors(params)
    monitors.value = monitorStore.monitors.filter(m => {
      if (displayName === 'Ungrouped') return !m.group_name
      return m.group_name === displayName
    })

    // If store exposes grouped data, try to get description
    try {
      const gresp = await monitorStore.getGroupedMonitors({})
      const groups = gresp.data || {}
      if (groups[displayName]) {
        groupDescription.value = groups[displayName].description || ''
      }
    } catch (e) {
      // ignore
    }
  } catch (e) {
    error.value = 'Failed to load group monitors.'
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openMonitor(id) {
  router.push(`/monitors/${id}`)
}

function refresh() {
  loadGroup()
}

onMounted(() => {
  loadGroup()
})
</script>

<style scoped>
.group-detail { padding: 20px; }
.page-header .header-content { max-width: 1280px; margin: 0 auto; padding: 12px 20px; display:flex; justify-content:space-between; align-items:center }
.group-stats { display:flex; gap:12px; margin:12px 0 }
.monitors-list { margin-top: 12px; display:flex; flex-direction:column; gap:8px }
.monitor-item { background: #fff; padding:12px; border-radius:8px; display:flex; justify-content:space-between; align-items:center; border:1px solid rgba(0,0,0,0.04) }
.monitor-item .target { color:#666; font-size:0.9rem; margin:4px 0 0 }
.status-badge { padding:6px 10px; border-radius:999px; font-weight:600 }
.status-badge.up { background:#e6ffef; color:#0a8a5b }
.status-badge.down { background:#ffecef; color:#c92a2a }
.clickable { cursor:pointer }
</style>
