import type { Plugin } from 'vite'
import { handleAuthMock } from './auth'
import { handleUserMock } from './user'
import { handleRoleMock } from './role'
import { handleMenuMock } from './menu'

export function viteMockPlugin(): Plugin {
  return {
    name: 'vite-mock-plugin',
    configureServer(server) {
      server.middlewares.use((req, res, next) => {
        const url = req.url || ''
        const method = (req.method || 'GET').toUpperCase()

        if (!url.startsWith('/api/')) {
          next()
          return
        }

        // Add a small delay to simulate network latency
        setTimeout(() => {
          if (handleAuthMock(url, method, req, res)) return
          if (handleUserMock(url, method, req, res)) return
          if (handleRoleMock(url, method, req, res)) return
          if (handleMenuMock(url, method, req, res)) return

          next()
        }, 200)
      })
    },
  }
}
