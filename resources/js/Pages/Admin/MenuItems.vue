<script setup>
import { ref, onMounted, watch } from "vue";
import { useForm } from '@inertiajs/inertia-vue3';
import axios from "axios";

const form = useForm({
  name: '',
  slug: '',
  icon: '',
  route: '',
  url: '',
  parent_id: null,
  sort_order: 0,
  is_active: true,
  roles: []
});

const parentMenus = ref([]);
const roles = ref([]);
const selectedRoles = ref([]);

// Fetch parent menus and roles from API
onMounted(async () => {
  try {
    const { data } = await axios.get("/api/admin/menu-items/create-data");
    parentMenus.value = data.parentMenus;
    roles.value = data.roles;
  } catch (error) {
    console.error("Failed to load data:", error);
  }
});

// Keep form.roles in sync with selectedRoles
watch(selectedRoles, (newVal) => {
  form.roles = newVal;
});

const submit = () => {
  form.post('/admin/menu-items', {
    onSuccess: () => {
      form.reset();
      selectedRoles.value = [];
      alert('Menu item created successfully!');
    },
    onError: (errors) => {
      console.log(errors);
    }
  });
};
</script>

<template>
  <div class="p-6 bg-white shadow rounded-lg">
    <h2 class="text-2xl font-bold mb-4">Create Menu Item</h2>

    <form @submit.prevent="submit" class="space-y-4">

      <!-- Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Name</label>
        <input type="text" v-model="form.name" class="mt-1 block w-full border rounded p-2" />
      </div>

      <!-- Slug -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Slug</label>
        <input type="text" v-model="form.slug" class="mt-1 block w-full border rounded p-2" />
      </div>

      <!-- Icon -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Icon</label>
        <input type="text" v-model="form.icon" class="mt-1 block w-full border rounded p-2" />
      </div>

      <!-- Route -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Route</label>
        <input type="text" v-model="form.route" class="mt-1 block w-full border rounded p-2" />
      </div>

      <!-- URL -->
      <div>
        <label class="block text-sm font-medium text-gray-700">URL</label>
        <input type="text" v-model="form.url" class="mt-1 block w-full border rounded p-2" />
      </div>

      <!-- Parent Menu -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Parent Menu</label>
        <select v-model="form.parent_id" class="mt-1 block w-full border rounded p-2">
          <option :value="null">None (Top Level)</option>
          <option v-for="menu in parentMenus" :key="menu.id" :value="menu.id">
            {{ menu.name }}
          </option>
        </select>
      </div>

      <!-- Sort Order -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Sort Order</label>
        <input type="number" v-model="form.sort_order" class="mt-1 block w-full border rounded p-2" />
      </div>

      <!-- Active -->
      <div class="flex items-center space-x-2">
        <input type="checkbox" v-model="form.is_active" id="is_active" />
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
      </div>

      <!-- Assign Roles -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Assign Roles</label>
        <div class="flex flex-wrap gap-2 mt-1">
          <label v-for="role in roles" :key="role.id" class="flex items-center space-x-1">
            <input type="checkbox" :value="role.id" v-model="selectedRoles" />
            <span>{{ role.name }}</span>
          </label>
        </div>
      </div>

      <!-- Submit -->
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Create Menu Item
      </button>
    </form>
  </div>
</template>

<style scoped>
/* Optional custom styles */
</style>

