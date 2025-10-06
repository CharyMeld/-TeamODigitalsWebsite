<script setup>
import { ref, watch } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'

const form = useForm({
  title: '',
  slug: '',
  meta_description: '',
  primary_keyword: '',
  secondary_keywords: '',
  tags: '',
  introduction: '',
  sections: [
    {
      title: '',
      content: '',
      images: [],
      alt_text: '',
      subsections: []
    }
  ],
  conclusion: '',
  cta_text: '',
  cta_link: '',
  featured_image: null,
  featured_image_alt: '',
  category: '',
  status: 'draft',
  featured: false,
  canonical_url: ''
})

// Auto-generate slug from title
watch(() => form.title, (newTitle) => {
  if (!form.slug || form.slug === slugify(form.title)) {
    form.slug = slugify(newTitle)
  }
})

function slugify(text) {
  return text
    .toLowerCase()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

function addSection() {
  form.sections.push({
    title: '',
    content: '',
    images: [],
    alt_text: '',
    subsections: []
  })
}

function removeSection(index) {
  if (form.sections.length > 1) {
    form.sections.splice(index, 1)
  }
}

function addSubsection(sectionIndex) {
  if (!form.sections[sectionIndex].subsections) {
    form.sections[sectionIndex].subsections = []
  }
  form.sections[sectionIndex].subsections.push({
    title: '',
    content: ''
  })
}

function removeSubsection(sectionIndex, subsectionIndex) {
  form.sections[sectionIndex].subsections.splice(subsectionIndex, 1)
}

function handleFeaturedImageChange(event) {
  form.featured_image = event.target.files[0]
}

function handleSectionImagesChange(event, sectionIndex) {
  form.sections[sectionIndex].images = Array.from(event.target.files)
}

function submit() {
  form.post(route('admin.blogs.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by redirect
    }
  })
}

const categories = [
  'web-development',
  'it-consulting',
  'digital-transformation',
  'technology'
]
</script>

<template>
  <div class="max-w-5xl mx-auto py-10 px-6">
    <!-- Header -->
    <div class="mb-6">
      <Link href="/admin/blogs" class="text-blue-600 hover:text-blue-800 flex items-center mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to Blogs
      </Link>
      <h1 class="text-3xl font-bold text-gray-900">Create SEO-Friendly Blog Post</h1>
      <p class="text-gray-600 mt-2">Fill in all fields to create an optimized blog post</p>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Core Information -->
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Core Information</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Title -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Post Title (H1) *</label>
            <input
              v-model="form.title"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter your blog title"
            />
            <p v-if="form.errors.title" class="text-red-600 text-sm mt-1">{{ form.errors.title }}</p>
          </div>

          <!-- Slug -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
            <input
              v-model="form.slug"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="auto-generated from title"
            />
            <p class="text-xs text-gray-500 mt-1">URL: {{ form.slug || 'auto-generated' }}</p>
            <p v-if="form.errors.slug" class="text-red-600 text-sm mt-1">{{ form.errors.slug }}</p>
          </div>

          <!-- Meta Description -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description *</label>
            <textarea
              v-model="form.meta_description"
              required
              maxlength="160"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="150-160 characters for optimal SEO"
            ></textarea>
            <p class="text-xs text-gray-500 mt-1">{{ form.meta_description.length }}/160 characters</p>
            <p v-if="form.errors.meta_description" class="text-red-600 text-sm mt-1">{{ form.errors.meta_description }}</p>
          </div>

          <!-- Primary Keyword -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Primary Keyword *</label>
            <input
              v-model="form.primary_keyword"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="main SEO keyword"
            />
            <p v-if="form.errors.primary_keyword" class="text-red-600 text-sm mt-1">{{ form.errors.primary_keyword }}</p>
          </div>

          <!-- Secondary Keywords -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Secondary Keywords</label>
            <input
              v-model="form.secondary_keywords"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="keyword1, keyword2, keyword3"
            />
            <p class="text-xs text-gray-500 mt-1">Comma-separated</p>
          </div>

          <!-- Tags -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
            <input
              v-model="form.tags"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="tag1, tag2, tag3"
            />
          </div>

          <!-- Category -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select
              v-model="form.category"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select category</option>
              <option v-for="cat in categories" :key="cat" :value="cat">
                {{ cat.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Introduction -->
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Introduction *</h2>
        <textarea
          v-model="form.introduction"
          required
          rows="5"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
          placeholder="Write an engaging introduction..."
        ></textarea>
        <p v-if="form.errors.introduction" class="text-red-600 text-sm mt-1">{{ form.errors.introduction }}</p>
      </div>

      <!-- Dynamic Sections -->
      <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Sections (H2)</h2>
          <button
            type="button"
            @click="addSection"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
          >
            <i class="fas fa-plus mr-2"></i>Add Section
          </button>
        </div>

        <div v-for="(section, sIndex) in form.sections" :key="sIndex" class="border-2 border-gray-200 rounded-lg p-4 mb-4">
          <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold text-gray-700">Section {{ sIndex + 1 }}</h3>
            <button
              v-if="form.sections.length > 1"
              type="button"
              @click="removeSection(sIndex)"
              class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200"
            >
              <i class="fas fa-trash"></i>
            </button>
          </div>

          <!-- Section Title -->
          <div class="mb-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Section Title (H2) *</label>
            <input
              v-model="section.title"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="Section heading"
            />
          </div>

          <!-- Section Content -->
          <div class="mb-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Section Content *</label>
            <textarea
              v-model="section.content"
              required
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="Section content..."
            ></textarea>
          </div>

          <!-- Section Images -->
          <div class="mb-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Images</label>
            <input
              type="file"
              @change="(e) => handleSectionImagesChange(e, sIndex)"
              multiple
              accept="image/*"
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>

          <!-- Image Alt Text -->
          <div class="mb-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Image Alt Text</label>
            <input
              v-model="section.alt_text"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="alt text 1, alt text 2 (comma-separated)"
            />
          </div>

          <!-- Subsections -->
          <div class="bg-gray-50 rounded p-3 mt-3">
            <div class="flex justify-between items-center mb-2">
              <h4 class="text-sm font-semibold text-gray-700">Subsections (H3)</h4>
              <button
                type="button"
                @click="addSubsection(sIndex)"
                class="px-2 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600"
              >
                <i class="fas fa-plus mr-1"></i>Add Subsection
              </button>
            </div>

            <div v-for="(sub, subIndex) in section.subsections" :key="subIndex" class="border border-gray-300 rounded p-3 mb-2 bg-white">
              <div class="flex justify-between items-center mb-2">
                <span class="text-xs text-gray-600">Subsection {{ subIndex + 1 }}</span>
                <button
                  type="button"
                  @click="removeSubsection(sIndex, subIndex)"
                  class="text-red-600 hover:text-red-800 text-xs"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <input
                v-model="sub.title"
                type="text"
                class="w-full px-2 py-1 text-sm border border-gray-300 rounded mb-2"
                placeholder="Subsection title"
              />
              <textarea
                v-model="sub.content"
                rows="3"
                class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                placeholder="Subsection content"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Conclusion & CTA -->
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Conclusion & Call-to-Action</h2>

        <!-- Conclusion -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Conclusion *</label>
          <textarea
            v-model="form.conclusion"
            required
            rows="5"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            placeholder="Summarize your blog post..."
          ></textarea>
          <p v-if="form.errors.conclusion" class="text-red-600 text-sm mt-1">{{ form.errors.conclusion }}</p>
        </div>

        <!-- CTA Text -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">CTA Text</label>
            <input
              v-model="form.cta_text"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="e.g., Contact Us Today"
            />
          </div>

          <!-- CTA Link -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">CTA Link</label>
            <input
              v-model="form.cta_link"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="/contact"
            />
          </div>
        </div>
      </div>

      <!-- Featured Image -->
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Featured Image</h2>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Upload Image</label>
          <input
            type="file"
            @change="handleFeaturedImageChange"
            accept="image/*"
            class="w-full px-3 py-2 border border-gray-300 rounded-md"
          />
          <p class="text-xs text-gray-500 mt-1">Max size: 2MB. Formats: JPEG, PNG, WEBP</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Image Alt Text</label>
          <input
            v-model="form.featured_image_alt"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            placeholder="Describe the image for SEO"
          />
        </div>
      </div>

      <!-- Publishing Options -->
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Publishing Options</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="form.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="draft">Draft</option>
              <option value="published">Published</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          <!-- Featured -->
          <div class="flex items-center">
            <input
              v-model="form.featured"
              type="checkbox"
              id="featured"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="featured" class="ml-2 block text-sm text-gray-700">
              Mark as Featured
            </label>
          </div>

          <!-- Canonical URL -->
          <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Canonical URL (Optional)</label>
            <input
              v-model="form.canonical_url"
              type="url"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="https://example.com/original-post"
            />
            <p class="text-xs text-gray-500 mt-1">Only if this content was published elsewhere first</p>
          </div>
        </div>
      </div>

      <!-- Submit Buttons -->
      <div class="flex items-center justify-end space-x-4">
        <Link
          href="/admin/blogs"
          class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
        >
          Cancel
        </Link>
        <button
          type="submit"
          :disabled="form.processing"
          class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
          <span v-if="form.processing">Creating...</span>
          <span v-else>Create Blog Post</span>
        </button>
      </div>
    </form>
  </div>
</template>
