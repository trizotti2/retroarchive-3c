<template>
  <v-app>
    <v-main>
      <div class="conteudo">
        <RouterView />
      </div>
    </v-main>
  </v-app>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>
