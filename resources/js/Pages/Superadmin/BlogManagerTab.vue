<script setup>
import { router } from '@inertiajs/vue3';

function navigateToBlogManager() {
  router.visit('/superadmin/blogs');
}

function createNewPost() {
  router.visit('/superadmin/blogs/create');
}
</script>

<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-800">Blog Manager</h2>
      <button
        @click="createNewPost"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
      >
        <i class="fas fa-plus mr-2"></i>
        Create New Post
      </button>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-6">
        <div class="text-center py-8">
          <i class="fas fa-blog text-6xl text-blue-200 mb-4"></i>
          <p class="text-gray-600 mb-6">Manage your SEO-optimized blog posts</p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button
              @click="navigateToBlogManager"
              class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition inline-flex items-center justify-center"
            >
              <i class="fas fa-list mr-2"></i>
              View All Blogs
            </button>
            <button
              @click="createNewPost"
              class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center justify-center"
            >
              <i class="fas fa-plus mr-2"></i>
              Create New Blog
            </button>
          </div>
        </div>

        <!-- Quick Info -->
        <div class="mt-8 border-t pt-6">
          <h3 class="text-lg font-semibold mb-4 text-gray-800">Blog Management Features</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-start">
              <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
              <div>
                <h4 class="font-medium text-gray-800">SEO Optimized</h4>
                <p class="text-sm text-gray-600">Meta descriptions, keywords, and schema markup</p>
              </div>
            </div>
            <div class="flex items-start">
              <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
              <div>
                <h4 class="font-medium text-gray-800">Structured Content</h4>
                <p class="text-sm text-gray-600">Sections, subsections, and dynamic content</p>
              </div>
            </div>
            <div class="flex items-start">
              <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
              <div>
                <h4 class="font-medium text-gray-800">Image Management</h4>
                <p class="text-sm text-gray-600">Featured images and section images with alt text</p>
              </div>
            </div>
            <div class="flex items-start">
              <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
              <div>
                <h4 class="font-medium text-gray-800">Draft & Publish</h4>
                <p class="text-sm text-gray-600">Save drafts and publish when ready</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
