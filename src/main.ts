import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import pinia from './store'
import { setupPermissionDirective } from './directives/permission'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import './styles/index.scss'

const app = createApp(App)

app.use(pinia)
app.use(router)
app.use(ElementPlus)

setupPermissionDirective(app)

app.mount('#app')
