interface TokenPayload {
  exp: number
  iat: number
  email: string
  roles: string[]
}

const EXPIRY_MARGIN_SECONDS = 30

function decode(token: string): TokenPayload | null {
  try {
    const base64Url = token.split('.')[1]
    if (!base64Url) return null

    const json = atob(base64Url.replace(/-/g, '+').replace(/_/g, '/'))
    return JSON.parse(json) as TokenPayload
  } catch {
    return null
  }
}

function isExpired(token: string): boolean {
  const payload = decode(token)
  if (!payload) return true

  const nowInSeconds = Math.floor(Date.now() / 1000)
  return payload.exp - EXPIRY_MARGIN_SECONDS <= nowInSeconds
}

function secondsUntilExpiry(token: string): number {
  const payload = decode(token)
  if (!payload) return 0

  return Math.max(0, payload.exp - Math.floor(Date.now() / 1000))
}

export const TokenIntrospector = { decode, isExpired, secondsUntilExpiry }
export type { TokenPayload }
