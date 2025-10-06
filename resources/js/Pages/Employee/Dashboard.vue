<template>
  <EmployeeAppLayout
    title="Employee Dashboard"
    :user="user"
    :attendance="attendance"
    :history="history"
    :leave-requests="leaveRequests"
    :flash="flash"
  />
</template>

<script setup>
import EmployeeAppLayout from "@/Layouts/EmployeeAppLayout.vue"

const props = defineProps({
  user: { type: Object, required: true },
  attendance: { type: Object, default: null },
  history: { type: Array, default: () => [] },
  leaveRequests: { type: Array, default: () => [] },
  flash: { type: Object, default: () => ({}) }
})

// Optional: log props in dev mode
import { onMounted } from "vue";
onMounted(() => {
  if (import.meta.env.DEV) console.log("EmployeeDashboard mounted:", props);
})
</script>

