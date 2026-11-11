import axios from 'axios'

const apiBase = (import.meta.env.VITE_API_BASE_URL || '/api').replace(/\/+$/, '')

const api = axios.create({
  baseURL: apiBase,
  timeout: 15000,
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
})

let preparingCsrf

function prepareCsrf() {
  if (!preparingCsrf) {
    const base = apiBase.replace(/\/api$/, '')
    preparingCsrf = axios.get(base + '/sanctum/csrf-cookie', {
      timeout: 15000,
      withCredentials: true,
      headers: { Accept: 'application/json' },
    }).finally(() => { preparingCsrf = undefined })
  }

  return preparingCsrf
}

export async function mutate(method, url, data) {
  await prepareCsrf()
  const { data: payload } = await api.request({ method, url, data })

  return payload
}

export function failure(error, fallback = 'Não foi possível concluir. Tente novamente.') {
  const response = error.response
  if (response?.status === 419) return 'A sessão expirou. Tente a operação novamente.'
  if (response?.status === 429) return 'Muitas tentativas. Aguarde um minuto e tente novamente.'
  if (!response || response.status >= 500) return fallback

  return Object.values(response.data?.errors ?? {}).flat()[0]
    ?? response.data?.message
    ?? fallback
}

export default api
