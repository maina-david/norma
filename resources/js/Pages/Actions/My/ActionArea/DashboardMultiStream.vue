<script setup>
import PageHeader from '@/vue/components/my/PageHeader.vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import DashboardDataTable from '@/vue/components/my/actions/DashboardDataTable.vue';
</script>

<template>
  <div class="h-full flex flex-col">
    <PageHeader class="flex-shrink-0">
      <div class="flex items-center">
        <LibryoIcon name="clipboard-list" class="mr-3 ml-2  text-libryo-gray-400" />
        <span>
          {{ $t('actions.dashboard.actions_dashboard') }}
        </span>
      </div>
    </PageHeader>

    <div class="flex-grow px-4 lg:px-6">
      <DashboardDataTable filterable sortable />
    </div>
  </div>
</template>
