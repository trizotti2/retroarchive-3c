<template>
  <div class="panel empty-state">
    <p class="eyebrow">ERRO 404</p>
    <h1 class="page-title">Página não encontrada</h1>
    <AppButton :to="{ name: 'catalog' }" color="primary">Voltar ao catálogo</AppButton>
  </div>
</template>
