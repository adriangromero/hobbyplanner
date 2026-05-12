import api from '@/api/axios'

interface LoginResponse {
  token: string
  user: {
    id:    string
    email: string
    name:  string
  }
}

interface RegisterResponse {
  id:        string
  email:     string
  name:      string
  createdAt: string
}

export const authApi = {
  async login(email: string, password: string): Promise<LoginResponse> {
    const { data } = await api.post('/auth/login', { email, password })
    return data
  },

  async register(email: string, password: string, name: string): Promise<RegisterResponse> {
    const { data } = await api.post('/auth/register', { email, password, name })
    return data
  },
}
