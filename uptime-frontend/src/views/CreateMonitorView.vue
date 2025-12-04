<template>
  <div class="create-monitor">
    <div class="page-header">
      <div class="header-content">
        <div class="header-main">
          <h1>Create New Monitor</h1>
          <p>Set up monitoring for your service</p>
        </div>
        <div class="header-actions">
          <router-link to="/monitors" class="btn btn-secondary">
            <span>←</span> Back to Monitors
          </router-link>
        </div>
      </div>
      <div class="progress-indicator">
        <div class="progress-step" :class="{ active: currentStep >= 1 }">
          <span class="step-number">1</span>
          <span class="step-label">Basic Info</span>
        </div>
        <div class="progress-line" :class="{ active: currentStep >= 2 }"></div>
        <div class="progress-step" :class="{ active: currentStep >= 2 }">
          <span class="step-number">2</span>
          <span class="step-label">Configuration</span>
        </div>
        <div class="progress-line" :class="{ active: currentStep >= 3 }"></div>
        <div class="progress-step" :class="{ active: currentStep >= 3 }">
          <span class="step-number">3</span>
          <span class="step-label">Notifications</span>
        </div>
      </div>
    </div>

    <div class="form-container">
      <div v-if="error" class="error-message">
        <span class="error-icon">⚠️</span>
        <span>{{ error }}</span>
      </div>

      <form @submit.prevent="handleSubmit" class="monitor-form">
        <!-- Basic Information -->
        <div class="form-section">
          <h3>Basic Information</h3>
          
          <div class="form-row">
            <div class="form-group">
              <label for="name" class="form-label">Monitor Name *</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="form-control"
                required
                placeholder="e.g. My Website"
              >
            </div>
            
            <div class="form-group">
              <label for="type" class="form-label">Monitor Type *</label>
              <select
                id="type"
                v-model="form.type"
                class="form-control"
                required
                @change="onTypeChange"
              >
                <option value="">Select Type</option>
                <option value="http">HTTP</option>
                <option value="https">HTTPS</option>
                <option value="tcp">TCP</option>
                <option value="ping">Ping</option>
                <option value="keyword">Keyword Check</option>
                <option value="push">Push Monitor</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="group_name" class="form-label">Group (Optional)</label>
              <input
                id="group_name"
                v-model="form.group_name"
                type="text"
                class="form-control"
                placeholder="e.g. Web Services"
                list="existing-groups"
              >
              <datalist id="existing-groups">
                <option v-for="group in existingGroups" :key="group" :value="group">
                  {{ group }}
                </option>
              </datalist>
              <small class="form-text">
                Group your monitors for better organization. Type a new name to create a group.
              </small>
            </div>

            <div class="form-group">
              <label for="group_description" class="form-label">Group Description</label>
              <input
                id="group_description"
                v-model="form.group_description"
                type="text"
                class="form-control"
                placeholder="e.g. Main website and API endpoints"
                :disabled="!form.group_name"
              >
              <small class="form-text">
                Optional description for the group (only if group is specified)
              </small>
            </div>
          </div>
          
          <div class="form-group">
            <label for="target" class="form-label">Target *</label>
            <input
              id="target"
              v-model="form.target"
              type="text"
              class="form-control"
              required
              :placeholder="getTargetPlaceholder()"
            >
            <small class="form-text">
              {{ getTargetHelp() }}
            </small>
          </div>
        </div>

        <!-- Monitoring Configuration -->
        <div class="form-section">
          <h3>Monitoring Configuration</h3>
          
          <div class="form-row">
            <div class="form-group">
              <label for="interval" class="form-label">Check Interval (seconds)</label>
              <input
                id="interval"
                v-model.number="form.interval_seconds"
                type="number"
                class="form-control"
                min="10"
                max="3600"
              >
            </div>
            
            <div class="form-group">
              <label for="timeout" class="form-label">Timeout (milliseconds)</label>
              <input
                id="timeout"
                v-model.number="form.timeout_ms"
                type="number"
                class="form-control"
                min="1000"
                max="30000"
              >
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="retries" class="form-label">Max Retries</label>
              <input
                id="retries"
                v-model.number="form.retries"
                type="number"
                class="form-control"
                min="1"
                max="5"
              >
            </div>
            
            <div class="form-group">
              <label for="notify_after_retries" class="form-label">Notify After Retries</label>
              <input
                id="notify_after_retries"
                v-model.number="form.notify_after_retries"
                type="number"
                class="form-control"
                min="1"
                max="5"
              >
            </div>
          </div>
        </div>

        <!-- Advanced Configuration -->
        <div v-if="form.type && ['http', 'https', 'keyword'].includes(form.type)" class="form-section">
          <h3>HTTP Configuration</h3>
          
          <div class="form-group">
            <label for="expected_status" class="form-label">Expected Status Code</label>
            <input
              id="expected_status"
              v-model.number="config.expected_status_code"
              type="number"
              class="form-control"
              placeholder="200"
            >
          </div>
          
          <div v-if="form.type === 'keyword'" class="form-group">
            <label for="expected_content" class="form-label">Expected Content/Keyword</label>
            <input
              id="expected_content"
              v-model="config.expected_content"
              type="text"
              class="form-control"
              placeholder="Text to find in response"
            >
          </div>
          
          <div class="form-group">
            <label for="user_agent" class="form-label">Custom User Agent</label>
            <input
              id="user_agent"
              v-model="config.user_agent"
              type="text"
              class="form-control"
              placeholder="Leave empty for default"
            >
          </div>
        </div>

        <!-- Tags -->
        <div class="form-section">
          <h3>Tags & Organization</h3>
          
          <div class="form-group">
            <label for="tags" class="form-label">Tags</label>
            <input
              id="tags"
              v-model="tagsInput"
              type="text"
              class="form-control"
              placeholder="production, critical, website (comma separated)"
            >
            <small class="form-text">
              Add tags to organize your monitors (comma separated)
            </small>
          </div>
        </div>

        <!-- Enable/Disable -->
        <div class="form-section">
          <div class="form-group">
            <label class="checkbox-label">
              <input
                v-model="form.enabled"
                type="checkbox"
                class="form-checkbox"
              >
              Enable monitor immediately
            </label>
          </div>
        </div>

        <!-- Submit Buttons -->
        <div class="form-actions">
          <router-link to="/monitors" class="btn btn-secondary">
            Cancel
          </router-link>
          <button
            type="submit"
            class="btn btn-success"
            :disabled="loading"
          >
            <span v-if="loading">Creating...</span>
            <span v-else>Create Monitor</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useMonitorStore } from '../stores/monitors'
import { useRouter } from 'vue-router'

const monitorStore = useMonitorStore()
const router = useRouter()

const loading = ref(false)
const error = ref(null)
const tagsInput = ref('')
const existingGroups = ref([])

const form = reactive({
  name: '',
  type: '',
  target: '',
  group_name: '',
  group_description: '',
  interval_seconds: 10,
  timeout_ms: 5000,
  retries: 3,
  notify_after_retries: 2,
  enabled: true
})

const config = reactive({
  expected_status_code: 200,
  expected_content: '',
  user_agent: ''
})

onMounted(async () => {
  // Load existing groups for autocomplete
  try {
    const groupsResponse = await monitorStore.getGroups()
    if (groupsResponse.success) {
      existingGroups.value = groupsResponse.data.map(group => group.group_name).filter(Boolean)
    }
  } catch (err) {
    console.error('Failed to load existing groups:', err)
  }
})

function onTypeChange() {
  // Reset target when type changes
  form.target = ''
  
  // Reset config
  Object.keys(config).forEach(key => {
    if (typeof config[key] === 'string') {
      config[key] = ''
    } else if (typeof config[key] === 'number') {
      config[key] = key === 'expected_status_code' ? 200 : 0
    }
  })
}

function getTargetPlaceholder() {
  switch (form.type) {
    case 'http':
    case 'https':
    case 'keyword':
      return 'https://example.com'
    case 'tcp':
      return 'example.com:80'
    case 'ping':
      return 'example.com or 192.168.1.1'
    case 'push':
      return 'Heartbeat key will be generated automatically'
    default:
      return 'Enter target to monitor'
  }
}

function getTargetHelp() {
  switch (form.type) {
    case 'http':
    case 'https':
    case 'keyword':
      return 'Enter the full URL to monitor'
    case 'tcp':
      return 'Enter hostname:port (e.g., example.com:80)'
    case 'ping':
      return 'Enter hostname or IP address'
    case 'push':
      return 'Push monitors receive heartbeats from your application'
    default:
      return ''
  }
}

async function handleSubmit() {
  loading.value = true
  error.value = null

  try {
    // Prepare monitor data
    const monitorData = { ...form }
    
    // Add config if applicable
    const configData = {}
    if (['http', 'https', 'keyword'].includes(form.type)) {
      if (config.expected_status_code) {
        configData.expected_status_code = config.expected_status_code
      }
      if (config.expected_content) {
        configData.expected_content = config.expected_content
      }
      if (config.user_agent) {
        configData.user_agent = config.user_agent
      }
    }
    
    if (Object.keys(configData).length > 0) {
      monitorData.config = configData
    }
    
    // Add tags
    if (tagsInput.value.trim()) {
      monitorData.tags = tagsInput.value
        .split(',')
        .map(tag => tag.trim())
        .filter(tag => tag.length > 0)
    }

    const result = await monitorStore.createMonitor(monitorData)

    if (result.success) {
      router.push('/monitors')
    } else {
      error.value = result.message || 'Failed to create monitor'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create monitor'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.create-monitor {
  padding: 20px;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  margin: 0 0 5px 0;
  color: #2c3e50;
}

.page-header p {
  margin: 0;
  color: #7f8c8d;
}

.monitor-form {
  padding: 30px;
}

.form-section {
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid #ecf0f1;
}

.form-section:last-of-type {
  border-bottom: none;
}

.form-section h3 {
  margin: 0 0 20px 0;
  color: #2c3e50;
  font-size: 1.2em;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-text {
  color: #6c757d;
  font-size: 0.85em;
  margin-top: 5px;
  display: block;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-weight: normal;
}

.form-checkbox {
  width: auto !important;
  margin: 0;
}

.form-actions {
  display: flex;
  gap: 15px;
  justify-content: flex-end;
  padding-top: 20px;
  border-top: 1px solid #ecf0f1;
  margin-top: 30px;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column-reverse;
  }
}
</style>