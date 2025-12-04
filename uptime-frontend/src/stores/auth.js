import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const loading = ref(false)
  const error = ref(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  // Actions
  async function login(credentials) {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.auth.login(credentials)
      
      if (response.data.success) {
        const { user: userData, token: authToken } = response.data.data
        
        user.value = userData
        token.value = authToken
        
        // Store in localStorage
        localStorage.setItem('token', authToken)
        localStorage.setItem('user', JSON.stringify(userData))
        
        return { success: true }
      }
    } catch (err) {
      const message = err.response?.data?.message || 'Login failed'
      error.value = message
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  async function register(userData) {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.auth.register(userData)
      
      if (response.data.success) {
        const { user: newUser, token: authToken } = response.data.data
        
        user.value = newUser
        token.value = authToken
        
        // Store in localStorage
        localStorage.setItem('token', authToken)
        localStorage.setItem('user', JSON.stringify(newUser))
        
        return { success: true }
      }
    } catch (err) {
      const message = err.response?.data?.message || 'Registration failed'
      error.value = message
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.auth.logout()
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      // Clear state
      user.value = null
      token.value = null
      error.value = null
      
      // Clear localStorage
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }

  async function checkAuth() {
    if (!token.value) return
    
    try {
      const response = await api.auth.me()
      
      if (response.data.success) {
        user.value = response.data.data
        localStorage.setItem('user', JSON.stringify(response.data.data))
      }
    } catch (err) {
      console.error('Auth check failed:', err)
      logout()
    }
  }

  async function refreshToken() {
    try {
      const response = await api.auth.refresh()
      
      if (response.data.success) {
        const { token: newToken } = response.data.data
        token.value = newToken
        localStorage.setItem('token', newToken)
        return true
      }
    } catch (err) {
      console.error('Token refresh failed:', err)
      logout()
      return false
    }
  }

  // Initialize user from localStorage
  function initializeAuth() {
    const storedUser = localStorage.getItem('user')
    if (storedUser) {
      try {
        user.value = JSON.parse(storedUser)
      } catch (err) {
        console.error('Error parsing stored user:', err)
        localStorage.removeItem('user')
      }
    }
  }

  // Initialize on store creation
  initializeAuth()

  return {
    // State
    user,
    token,
    loading,
    error,
    
    // Getters
    isAuthenticated,
    isAdmin,
    
    // Actions
    login,
    register,
    logout,
    checkAuth,
    refreshToken,
    initializeAuth
  }
})