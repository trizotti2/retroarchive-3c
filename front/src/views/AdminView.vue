<script setup>
import { onMounted, reactive, ref } from 'vue'
import StatusChip from '../components/StatusChip.vue'
import api, { failure, mutate } from '../api/api'
import { date, money, orderStatuses } from '../util/format'

const tab = ref('products')
const products = ref([])
const categories = ref([])
const orders = ref([])
const users = ref([])
const meta = ref(null)
const loading = ref(true)
const busy = ref(false)
const error = ref('')
const statusFilter = ref('')
const editor = ref('')
const editingId = ref(null)
const fields = reactive({})
const errors = ref({})
const image = ref(null)
const deleting = ref(null)
const statusOptions = [
  { title: 'Todos', value: '' },
  ...Object.entries(orderStatuses).map(([value, title]) => ({ value, title })),
]

let loadSequence = 0

async function load(page = 1) {
  const sequence = ++loadSequence
  const section = tab.value
  loading.value = true
  error.value = ''
  meta.value = null
  try {
    if (section === 'categories' || section === 'products') {
      const { data: catalog } = await api.get('/categories')
      if (sequence !== loadSequence) return
      categories.value = catalog.data
    }
    if (section === 'categories') return

    const { data } = await api.get(`/admin/${section}`, {
      params: { page, status: section === 'orders' && statusFilter.value ? statusFilter.value : undefined },
    })
    if (sequence !== loadSequence) return
    if (section === 'products') products.value = data.data
    else if (section === 'orders') orders.value = data.data
    else users.value = data.data
    meta.value = data.meta
  } catch (requestError) {
    if (sequence === loadSequence) error.value = failure(requestError, 'Não foi possível carregar o painel.')
  } finally {
    if (sequence === loadSequence) loading.value = false
  }
}

function startEdit(type, record = null) {
  editor.value = type
  editingId.value = record?.id ?? null
  errors.value = {}
  error.value = ''
  image.value = null
  for (const key of Object.keys(fields)) delete fields[key]
  Object.assign(fields, type === 'products' ? {
    name: record?.name ?? '',
    category_id: record?.category?.id ?? null,
    description: record?.description ?? '',
    price_cents: record?.price_cents ?? '',
    stock: record?.stock ?? 0,
    is_active: record?.is_active ?? true,
    photo_url: /^https?:\/\//i.test(record?.photo_url ?? '') ? record.photo_url : '',
  } : { name: record?.name ?? '', slug: record?.slug ?? '' })
}

async function save() {
  if (busy.value) return
  busy.value = true
  error.value = ''
  errors.value = {}

  try {
    const url = '/admin/' + editor.value + (editingId.value ? '/' + editingId.value : '')
    if (editor.value === 'products') {
      const payload = new FormData()
      for (const [key, value] of Object.entries(fields)) {
        payload.append(key, typeof value === 'boolean' ? Number(value) : (value ?? ''))
      }
      const file = Array.isArray(image.value) ? image.value[0] : image.value
      if (file instanceof File) payload.append('image', file)
      if (editingId.value) payload.append('_method', 'PATCH')
      await mutate('post', url, payload)
    } else {
      await mutate(editingId.value ? 'patch' : 'post', url, fields)
    }

    editor.value = ''
    await load(meta.value?.current_page ?? 1)
  } catch (requestError) {
    errors.value = requestError.response?.data?.errors ?? {}
    error.value = failure(requestError)
  } finally {
    busy.value = false
  }
}

async function remove() {
  if (!deleting.value || busy.value) return
  const { type, item } = deleting.value
  busy.value = true
  error.value = ''
  try {
    await mutate('delete', `/admin/${type}/${item.id}`)
    await load(1)
  } catch (requestError) {
    error.value = failure(requestError)
  } finally {
    deleting.value = null
    busy.value = false
  }
}

async function advance(order, next) {
  if (busy.value) return
  busy.value = true
  error.value = ''
  try {
    await mutate('patch', `/admin/orders/${order.id}/status`, { status: next })
    await load(meta.value?.current_page ?? 1)
  } catch (requestError) {
    error.value = failure(requestError)
  } finally {
    busy.value = false
  }
}

function selectTab(section) {
  tab.value = section
  load(1)
}

onMounted(() => load())
</script>

<template>
  <div class="page-heading">
    <p class="eyebrow"><span class="signal-dot"></span> OPERAÇÕES DA LOJA</p>
    <h1 class="page-title">Painel administrativo</h1>
  </div>

  <nav class="admin-tabs mb-4" aria-label="Seções do painel">
    <button
      v-for="section in [
        { key: 'products', label: 'Produtos' },
        { key: 'categories', label: 'Categorias' },
        { key: 'orders', label: 'Pedidos' },
        { key: 'users', label: 'Usuários' },
      ]"
      :key="section.key"
      type="button"
      :aria-pressed="tab === section.key"
      :disabled="busy"
      @click="selectTab(section.key)"
    >{{ section.label }}</button>
  </nav>

  <AppAlert v-if="error && !editor" type="error" class="mb-4">{{ error }}</AppAlert>

  <section class="panel content-panel">
    <div class="section-heading mb-4">
      <h2>{{ { products: 'Produtos', categories: 'Categorias', orders: 'Pedidos da loja', users: 'Usuários cadastrados' }[tab] }}</h2>
      <AppButton
        v-if="tab === 'products' || tab === 'categories'"
        color="primary"
        prepend-icon="mdi-plus"
        :disabled="busy || loading"
        @click="startEdit(tab)"
      >{{ tab === 'products' ? 'Novo produto' : 'Nova categoria' }}</AppButton>
      <AppSelect
        v-if="tab === 'orders'"
        v-model="statusFilter"
        label="Filtrar por status"
        :items="statusOptions"
        :disabled="busy"
        class="status-filter"
        @update:model-value="load(1)"
      />
    </div>

    <AppProgress v-if="loading" />

    <template v-else-if="tab === 'products'">
      <p v-if="!products.length" class="muted">Nenhum produto nesta página.</p>
      <div v-for="item in products" :key="item.id" class="admin-row">
        <div>
          <strong>{{ item.name }}</strong>
          <p class="muted">{{ item.category?.name }} · {{ money(item.price_cents) }} · Estoque: {{ item.stock }}</p>
        </div>
        <AppChip :color="item.is_active ? 'success' : 'warning'">{{ item.is_active ? 'Ativo' : 'Inativo' }}</AppChip>
        <div class="actions">
          <AppButton size="small" variant="tonal" :disabled="busy" @click="startEdit('products', item)">Editar</AppButton>
          <AppButton size="small" variant="text" color="error" :disabled="busy" @click="deleting = { type: 'products', item }">Excluir</AppButton>
        </div>
      </div>
    </template>

    <template v-else-if="tab === 'categories'">
      <p v-if="!categories.length" class="muted">Nenhuma categoria cadastrada.</p>
      <div v-for="item in categories" :key="item.id" class="admin-row">
        <div><strong>{{ item.name }}</strong><p class="muted">{{ item.slug }}</p></div>
        <div class="actions">
          <AppButton size="small" variant="tonal" :disabled="busy" @click="startEdit('categories', item)">Editar</AppButton>
          <AppButton size="small" variant="text" color="error" :disabled="busy" @click="deleting = { type: 'categories', item }">Excluir</AppButton>
        </div>
      </div>
    </template>

    <template v-else-if="tab === 'orders'">
      <p v-if="!orders.length" class="muted">Nenhum pedido neste filtro.</p>
      <div v-for="item in orders" :key="item.id" class="admin-row">
        <div>
          <strong>Pedido #{{ item.id }} · {{ money(item.total_cents) }}</strong>
          <p class="muted">Conta #{{ item.user_id }} · {{ date(item.created_at) }}</p>
        </div>
        <StatusChip :status="item.status" />
        <div class="actions">
          <AppButton size="small" variant="text" :to="{ name: 'order', params: { id: item.id } }">Ver pedido</AppButton>
          <AppButton v-if="item.status === 'paid'" size="small" variant="tonal" :disabled="busy" @click="advance(item, 'shipped')">Marcar enviado</AppButton>
          <AppButton v-if="item.status === 'shipped'" size="small" variant="tonal" :disabled="busy" @click="advance(item, 'delivered')">Marcar entregue</AppButton>
        </div>
      </div>
    </template>

    <template v-else-if="tab === 'users'">
      <p v-if="!users.length" class="muted">Nenhum usuário encontrado.</p>
      <div v-else class="table-scroll">
        <table class="admin-table">
          <thead>
            <tr><th scope="col">ID</th><th scope="col">Nome</th><th scope="col">E-mail</th><th scope="col">Perfil</th></tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.id }}</td>
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td><AppChip :color="user.role === 'admin' ? 'primary' : 'secondary'">{{ user.role === 'admin' ? 'Administrador' : 'Cliente' }}</AppChip></td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <AppPagination
      v-if="!loading && meta?.last_page > 1"
      :model-value="meta.current_page"
      :length="meta.last_page"
      :disabled="busy"
      class="mt-4"
      @update:model-value="load"
    />
  </section>

  <AppDialog :model-value="Boolean(editor)" max-width="620" :persistent="busy" @update:model-value="!$event && (editor = '')" :title="editingId ? 'Editar registro' : 'Novo registro'">
      <div class="dialog-body">
        <AppAlert v-if="error" type="error" class="mb-4">{{ error }}</AppAlert>
        <form @submit.prevent="save"><fieldset :disabled="busy">
          <AppField v-model="fields.name" label="Nome" :error-messages="errors.name" />
          <template v-if="editor === 'products'">
            <AppSelect v-model="fields.category_id" :items="categories" item-title="name" item-value="id" label="Categoria" :error-messages="errors.category_id" />
            <AppField type="textarea" v-model="fields.description" label="Descrição" rows="2" :error-messages="errors.description" />
            <div class="form-grid">
              <AppField v-model.number="fields.price_cents" type="number" min="0" label="Preço em centavos" hint="Ex.: 79990 para R$ 799,90" :error-messages="errors.price_cents" />
              <AppField v-model.number="fields.stock" type="number" min="0" label="Estoque" :error-messages="errors.stock" />
            </div>
            <AppField
              v-model.trim="fields.photo_url"
              label="URL da foto"
              type="url"
              placeholder="https://exemplo.com/foto.jpg"
              hint="Cole o link direto da imagem. Se enviar um arquivo, ele terá prioridade."
              :error-messages="errors.photo_url"
            />
            <AppField type="file" v-model="image" label="Imagem JPG, PNG ou WebP" accept="image/jpeg,image/png,image/webp" :error-messages="errors.image" />
            <label class="checkbox-field">
              <input v-model="fields.is_active" type="checkbox" />
              <span>Visível no catálogo</span>
            </label>
            <p v-if="errors.is_active?.length" class="field-error" role="alert">{{ errors.is_active.join(' ') }}</p>
          </template>
          <AppField v-else v-model="fields.slug" label="Slug (ex.: jogos-portateis)" :error-messages="errors.slug" />

          <div class="actions">
            <AppButton type="submit" color="primary" :loading="busy">Salvar</AppButton>
            <AppButton type="button" variant="text" :disabled="busy" @click="editor = ''; error = ''">Cancelar</AppButton>
          </div>
        </fieldset></form>
      </div>

  </AppDialog>

  <AppDialog :model-value="Boolean(deleting)" max-width="440" :persistent="busy" @update:model-value="!$event && (deleting = null)" title="Excluir registro?">
      <div class="dialog-body">{{ deleting?.item.name }}</div>
      <div class="actions dialog-actions">
        <AppButton :disabled="busy" @click="deleting = null">Voltar</AppButton>
        <AppButton color="error" :loading="busy" @click="remove">Confirmar exclusão</AppButton>
      </div>

  </AppDialog>
</template>
