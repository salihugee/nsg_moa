import { defineStore } from 'pinia'
import { PublicClientApplication } from '@azure/msal-browser'
import type { AccountInfo, AuthenticationResult } from '@azure/msal-browser'
import { msalConfig, loginRequest } from '../auth/authConfig'

interface AuthState {
  account: AccountInfo | null
  isAuthenticated: boolean
  error: string | null
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    account: null,
    isAuthenticated: false,
    error: null
  }),
  
  actions: {
    async initialize() {
      const msalInstance = new PublicClientApplication(msalConfig)
      await msalInstance.initialize()

      const accounts = msalInstance.getAllAccounts()
      if (accounts.length > 0) {
        this.account = accounts[0]
        this.isAuthenticated = true
      }
    },

    async login() {
      try {
        const msalInstance = new PublicClientApplication(msalConfig)
        const response: AuthenticationResult = await msalInstance.loginPopup(loginRequest)
        this.account = response.account
        this.isAuthenticated = true
        this.error = null
      } catch (error) {
        this.error = error instanceof Error ? error.message : 'Failed to login'
        console.error('Login error:', error)
      }
    },
    
    async logout() {
      try {
        const msalInstance = new PublicClientApplication(msalConfig)
        await msalInstance.logoutPopup()
        this.account = null
        this.isAuthenticated = false
        this.error = null
      } catch (error) {
        this.error = error instanceof Error ? error.message : 'Failed to logout'
        console.error('Logout error:', error)
      }
    },

    async getToken(scopes: string[]): Promise<string | null> {
      try {
        const msalInstance = new PublicClientApplication(msalConfig)
        if (!this.account) {
          throw new Error('No account found')
        }
        
        const response = await msalInstance.acquireTokenSilent({
          scopes,
          account: this.account
        })
        return response.accessToken
      } catch (error) {
        try {
          const msalInstance = new PublicClientApplication(msalConfig)
          const response = await msalInstance.acquireTokenPopup({ scopes })
          return response.accessToken
        } catch (error) {
          this.error = error instanceof Error ? error.message : 'Failed to get token'
          console.error('Token acquisition error:', error)
          return null
        }
      }
    }
  },
  
  getters: {
    currentUser: (state) => state.account,
    isLoggedIn: (state) => state.isAuthenticated,
    hasError: (state) => state.error !== null
  }
})
