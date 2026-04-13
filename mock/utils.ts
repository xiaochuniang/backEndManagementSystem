import type { IncomingMessage } from 'http'

export function parseRequestBody(req: IncomingMessage): Promise<Record<string, unknown>> {
  return new Promise((resolve) => {
    let body = ''
    req.on('data', (chunk: Buffer) => {
      body += chunk.toString()
    })
    req.on('end', () => {
      try {
        resolve(body ? JSON.parse(body) : {})
      } catch {
        resolve({})
      }
    })
  })
}

export function success<T>(data: T, message = 'success') {
  return { code: 200, message, data }
}

export function fail(message = '操作失败', code = 500) {
  return { code, message, data: null }
}

export function parseQuery(url: string): Record<string, string> {
  const queryString = url.split('?')[1]
  if (!queryString) return {}
  const params: Record<string, string> = {}
  queryString.split('&').forEach((pair) => {
    const [key, value] = pair.split('=')
    params[decodeURIComponent(key)] = decodeURIComponent(value || '')
  })
  return params
}
