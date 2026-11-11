import axios from 'axios'

export const api = axios.create({
  headers: { Accept: 'application/json' },
  withCredentials: true,
  withXSRFToken: true,
})