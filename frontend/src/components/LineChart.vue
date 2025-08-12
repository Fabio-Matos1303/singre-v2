<template>
  <canvas ref="canvas"></canvas>
</template>
<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, watch } from 'vue'
import type { Chart, ChartConfiguration } from 'chart.js/auto'

const props = defineProps<{ config: ChartConfiguration }>()
const canvas = ref<HTMLCanvasElement | null>(null)
let chart: Chart | null = null

onMounted(async () => {
  const { default: ChartJs } = await import('chart.js/auto')
  if (canvas.value) {
    chart = new ChartJs(canvas.value, props.config)
  }
})

watch(() => props.config, (cfg) => {
  if (chart && cfg) {
    chart.config = cfg
    chart.update()
  }
}, { deep: true })

onBeforeUnmount(() => { chart?.destroy() })
</script>
