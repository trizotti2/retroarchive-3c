<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import SwaggerUI from 'swagger-ui-dist/swagger-ui-bundle.js'
import 'swagger-ui-dist/swagger-ui.css'

const container = ref(null)
let ui

onMounted(() => {
  ui = SwaggerUI({
    domNode: container.value,
    url: '/openapi.yaml',
    docExpansion: 'list',
    deepLinking: false,
    withCredentials: true,
    requestInterceptor: async (request) => {
      if (['POST', 'PATCH', 'PUT', 'DELETE'].includes(String(request.method).toUpperCase())) {
        const response = await fetch('/sanctum/csrf-cookie')
        if (!response.ok) throw new Error('Falha ao preparar CSRF.')
        const token = document.cookie.split('; ').find((value) => value.startsWith('XSRF-TOKEN='))
        if (token) request.headers['X-XSRF-TOKEN'] = decodeURIComponent(token.slice('XSRF-TOKEN='.length))
      }

      return request
    },
  })
})

onUnmounted(() => ui?.unmount?.())
</script>

<template>
  <div class="page-heading">
    <p class="eyebrow"><span class="signal-dot"></span> DESENVOLVIMENTO</p>
    <h1 class="page-title">Documentação da API</h1>
    <p class="muted">Entre na loja em outra aba da mesma origem para testar rotas autenticadas.</p>
  </div>
  <div ref="container" class="swagger-panel" />
</template>
