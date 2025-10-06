<script setup>
import { ref, computed } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  menuItems: Array
})

const page = usePage()

// Get user role for route prefix
const userRole = computed(() => page.props.auth?.user?.role || 'admin')
const routePrefix = computed(() => {
  if (userRole.value === 'developer') return 'developer'
  if (userRole.value === 'superadmin') return 'superadmin'
  return 'admin'
})

// Organize menu items hierarchically
const parentMenus = computed(() => {
  return props.menuItems.filter(item => !item.parent_id)
})

const getChildren = (parentId) => {
  return props.menuItems.filter(item => item.parent_id === parentId)
}

const deleteMenuItem = (id) => {
  if (!confirm('Are you sure you want to delete this menu item?')) return

  router.delete(`/${routePrefix.value}/menu-items/${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Menu item deleted successfully')
    }
  })
}

const toggleActive = (menuItem) => {
  router.put(`/${routePrefix.value}/menu-items/${menuItem.id}`, {
    ...menuItem,
    is_active: !menuItem.is_active
  }, {
    preserveScroll: true
  })
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-10 px-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Menu Management</h1>
      <Link
        :href="`/${routePrefix}/menu-items/create`"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
      >
        <i class="fas fa-plus mr-2"></i>
        Create Menu Item
      </Link>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div class="p-6">
        <div v-if="menuItems.length === 0" class="text-center py-8 text-gray-500">
          <i class="fas fa-bars text-4xl mb-3"></i>
          <p>No menu items found. Create your first menu item!</p>
        </div>

        <div v-else class="space-y-4">
          <!-- Parent Menus -->
          <div v-for="parent in parentMenus" :key="parent.id" class="border rounded-lg">
            <!-- Parent Menu Item -->
            <div class="bg-gray-50 p-4 flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <i :class="['fas', 'fa-' + (parent.icon || 'circle'), 'text-gray-600 text-xl']"></i>
                <div>
                  <h3 class="font-semibold text-gray-900">{{ parent.name }}</h3>
                  <div class="flex items-center space-x-4 mt-1">
                    <span class="text-sm text-gray-600">
                      <i class="fas fa-link mr-1"></i>
                      {{ parent.route || 'No route' }}
                    </span>
                    <span class="text-sm text-gray-600">
                      <i class="fas fa-sort mr-1"></i>
                      Order: {{ parent.sort_order }}
                    </span>
                    <span
                      :class="[
                        'text-xs px-2 py-1 rounded',
                        parent.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ parent.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="flex items-center space-x-2">
                <button
                  @click="toggleActive(parent)"
                  :class="[
                    'px-3 py-1 rounded text-sm',
                    parent.is_active ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200'
                  ]"
                >
                  {{ parent.is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <Link
                  :href="`/${routePrefix}/menu-items/${parent.id}/edit`"
                  class="px-3 py-1 bg-blue-100 text-blue-800 rounded text-sm hover:bg-blue-200"
                >
                  <i class="fas fa-edit"></i>
                </Link>
                <button
                  @click="deleteMenuItem(parent.id)"
                  class="px-3 py-1 bg-red-100 text-red-800 rounded text-sm hover:bg-red-200"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>

            <!-- Child Menu Items -->
            <div v-if="getChildren(parent.id).length > 0" class="p-4 space-y-2">
              <div
                v-for="child in getChildren(parent.id)"
                :key="child.id"
                class="ml-8 p-3 border-l-2 border-gray-300 bg-white flex items-center justify-between"
              >
                <div class="flex items-center space-x-3">
                  <i :class="['fas', 'fa-' + (child.icon || 'circle'), 'text-gray-500']"></i>
                  <div>
                    <h4 class="font-medium text-gray-800">{{ child.name }}</h4>
                    <div class="flex items-center space-x-3 mt-1">
                      <span class="text-xs text-gray-600">{{ child.route || 'No route' }}</span>
                      <span class="text-xs text-gray-600">Order: {{ child.sort_order }}</span>
                      <span
                        :class="[
                          'text-xs px-2 py-0.5 rounded',
                          child.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        ]"
                      >
                        {{ child.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </div>
                  </div>
                </div>

                <div class="flex items-center space-x-2">
                  <button
                    @click="toggleActive(child)"
                    :class="[
                      'px-2 py-1 rounded text-xs',
                      child.is_active ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200'
                    ]"
                  >
                    {{ child.is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                  <Link
                    :href="`/${routePrefix}/menu-items/${child.id}/edit`"
                    class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs hover:bg-blue-200"
                  >
                    <i class="fas fa-edit"></i>
                  </Link>
                  <button
                    @click="deleteMenuItem(child.id)"
                    class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs hover:bg-red-200"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Info Section -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <i class="fas fa-info-circle text-blue-400"></i>
        </div>
        <div class="ml-3">
          <p class="text-sm text-blue-700">
            Menu items control the navigation sidebar. Parent items can have children.
            Use the sort order to control the display sequence.
            Inactive items won't appear in the navigation.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
