const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })
const dateFormat = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'medium', timeStyle: 'short' })

export const money = (cents) => currency.format(cents / 100)
export const date = (value) => value ? dateFormat.format(new Date(value)) : '—'

export const orderStatuses = {
  pending_payment: 'Aguardando pagamento',
  paid: 'Pago',
  shipped: 'Enviado',
  delivered: 'Entregue',
  cancelled: 'Cancelado',
}

export const addressLabel = (a) => a
  ? [a.recipient, `${a.street}, ${a.number}`, a.complement, a.neighborhood, `${a.city} / ${a.state}`, a.postal_code].filter(Boolean).join(' · ')
  : ''

export const categoryInfo = {
  consoles: { icon: 'mdi-controller-classic-outline', number: '01', tagline: 'Da sala ao portátil' },
  jogos: { icon: 'mdi-gamepad-square-outline', number: '02', tagline: 'Cartuchos e discos' },
  acessorios: { icon: 'mdi-cable-data', number: '03', tagline: 'Complete seu setup' },
}

export function illustration(slug) {
  const category = Object.hasOwn(categoryInfo, slug) ? slug : 'consoles'
  return `/media/${category}.svg`
}
