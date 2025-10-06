<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
  blogs: Object,
  filters: Object
})

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const category = ref(props.filters.category || '')

// Determine route prefix based on user role
const routePrefix = window.location.pathname.includes('/superadmin/') ? 'superadmin.blogs' : 'admin.blogs'

function applyFilters() {
  router.get(route(`${routePrefix}.index`), {
    search: search.value,
    status: status.value,
    category: category.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

function deleteBlog(blog) {
  if (confirm(`Are you sure you want to delete "${blog.title}"?`)) {
    router.delete(route(`${routePrefix}.destroy`, blog.id), {
      preserveScroll: true
    })
  }
}

const categories = [
  'web-development',
  'it-consulting',
  'digital-transformation',
  'technology'
]
</script>

<template>
  <div class="max-w-7xl mx-auto py-10 px-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Blog Management</h1>
        <p class="text-gray-600 mt-1">Create and manage SEO-optimized blog posts</p>
      </div>
      <Link
        :href="route(`${routePrefix}.create`)"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center"
      >
        <i class="fas fa-plus mr-2"></i>
        Create Blog Post
      </Link>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
          <input
            v-model="search"
            @keyup.enter="applyFilters"
            type="text"
            placeholder="Search by title, keyword, or description..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Status Filter -->
        <div>
          <select
            v-model="status"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
        </div>

        <!-- Category Filter -->
        <div>
          <select
            v-model="category"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">
              {{ cat.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Blogs List -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div v-if="blogs.data.length === 0" class="text-center py-12">
        <i class="fas fa-blog text-5xl text-gray-300 mb-4"></i>
        <p class="text-gray-600">No blog posts found. Create your first blog post!</p>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stats</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="blog in blogs.data" :key="blog.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <img
                      v-if="blog.featured_image"
                      :src="`/storage/${blog.featured_image}`"
                      :alt="blog.title"
                      class="h-10 w-10 rounded object-cover"
                    />
                    <div v-else class="h-10 w-10 rounded bg-gray-200 flex items-center justify-center">
                      <i class="fas fa-image text-gray-400"></i>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ blog.title }}</div>
                    <div class="text-xs text-gray-500">{{ blog.primary_keyword }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'px-2 py-1 text-xs rounded-full',
                    blog.status === 'published' ? 'bg-green-100 text-green-800' :
                    blog.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ blog.status }}
                </span>
                <span v-if="blog.featured" class="ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                  <i class="fas fa-star"></i>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                {{ blog.category ? blog.category.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Uncategorized' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                {{ blog.author?.name || 'Unknown' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                <div class="flex items-center space-x-3">
                  <span :title="`${blog.views} views`">
                    <i class="fas fa-eye text-gray-400"></i> {{ blog.views }}
                  </span>
                  <span :title="`${blog.likes} likes`">
                    <i class="fas fa-heart text-gray-400"></i> {{ blog.likes }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                {{ new Date(blog.created_at).toLocaleDateString() }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <Link
                    :href="route('blog.show', blog.slug)"
                    target="_blank"
                    class="text-gray-600 hover:text-gray-900"
                    title="View"
                  >
                    <i class="fas fa-eye"></i>
                  </Link>
                  <Link
                    :href="route(`${routePrefix}.edit`, blog.id)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Edit"
                  >
                    <i class="fas fa-edit"></i>
                  </Link>
                  <button
                    @click="deleteBlog(blog)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="blogs.links.length > 3" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ blogs.from }} to {{ blogs.to }} of {{ blogs.total }} results
            </div>
            <div class="flex space-x-1">
              <Link
                v-for="link in blogs.links"
                :key="link.label"
                :href="link.url"
                v-html="link.label"
                :class="[
                  'px-3 py-2 text-sm rounded',
                  link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                  !link.url ? 'opacity-50 cursor-not-allowed' : ''
                ]"
                :disabled="!link.url"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
