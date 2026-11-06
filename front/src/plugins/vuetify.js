import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'

export default createVuetify({
  components,
  theme: {
    defaultTheme: 'retro',
    themes: {
      retro: {
        dark: true,
        colors: {
          background: '#141224',
          surface: '#25203c',
          primary: '#ffe575',
          'on-primary': '#141224',
        },
      },
    },
  },
})