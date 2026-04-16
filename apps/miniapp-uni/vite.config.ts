import { defineConfig } from 'vite'
import uni from '@dcloudio/vite-plugin-uni'

const uniPlugin = (uni as unknown as { default?: () => unknown }).default || uni

export default defineConfig({
  plugins: [uniPlugin()],
})
