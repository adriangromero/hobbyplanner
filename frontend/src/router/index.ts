import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/armies'
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('@/views/LoginView.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/armies',
      name: 'ArmyList',
      component: () => import('@/views/armies/ArmyListView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/armies/:id',
      name: 'ArmyDetail',
      component: () => import('@/views/armies/ArmyDetailView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/projects',
      redirect: '/armies'
    },
    { path: '/projects/:id', redirect: to => ({ name: 'ArmyDetail', params: { id: to.params.id } }) },
    { path: '/inventory', redirect: '/armies' },
  ]
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    auth.clearSession()
    return { name: 'Login' }
  }

  if (to.name === 'Login' && auth.isAuthenticated) {
    return { name: 'ArmyList' }
  }
})

export default router
