<script setup lang="ts">
// Core
import { computed } from 'vue';

// Intrefaces
import type { MassTable } from '@/interfaces/MassTable';

// Props
const props = defineProps<{
  massSpectra: MassTable[][];
  compare: boolean;
}>();

// Data
const colors = ['#FF9800', '#4CAF50', '#3F51B5', '#9C27B0', '#E91E63']; // orange, green, indigo, purple, pink
const allPeaks = computed(() => props.massSpectra.flatMap((e) => e));

// Computed
const maxIntensity = computed(() => {
  return allPeaks.value.length ? Math.max(...allPeaks.value.map((e) => e.intensity)) : 1;
});

const mzTicks = computed(() => {
  let visiblePeaks = [];
  if (!allPeaks.value.length) return [];
  else visiblePeaks = allPeaks.value;
  const minMz = 50;
  const maxMz = Math.ceil(Math.max(...visiblePeaks.map((e) => e.mz)) / 50) * 50;
  const ticks: number[] = [];
  for (let val = minMz; val <= maxMz; val += 50) {
    ticks.push(val);
  }

  return ticks;
});
</script>

<template>
  <div class="ms-chart">
    <div class="y-axis">
      <span class="axis-title">
        <strong>Intensity</strong>
      </span>
      <div class="ticks">
        <div v-for="i in [100, 80, 60, 40, 20, 0]" :key="i" class="tick">
          <span>{{ i }}</span>
        </div>
      </div>
    </div>

    <div class="chart-area">
      <div class="peaks">
        <template v-for="(spectrum, sIndex) in props.massSpectra" :key="sIndex">
          <div
            v-for="(entry, index) in spectrum"
            :key="sIndex + '-' + index"
            class="peak"
            :style="{
              height: (entry.intensity / maxIntensity) * 100 + '%',
              left:
                ((entry.mz - mzTicks[0]) / (mzTicks[mzTicks.length - 1] - mzTicks[0])) * 100 + '%',
              backgroundColor: colors[sIndex % colors.length],
            }"
            :title="'m/z: ' + entry.mz + ', Intensity: ' + entry.intensity"
          >
            <div v-if="props.compare ? true : entry.visible" class="mz-label">
              {{ entry.mz }}
            </div>
          </div>
        </template>
      </div>

      <div class="x-ticks">
        <div
          v-for="val in mzTicks"
          :key="val"
          class="x-tick"
          :style="{
            left: ((val - mzTicks[0]) / (mzTicks[mzTicks.length - 1] - mzTicks[0])) * 100 + '%',
          }"
        >
          <span>{{ val }}</span>
        </div>
      </div>

      <span class="x-axis-title">
        <strong>m/z</strong>
      </span>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.ms-chart {
  display: flex;
  align-items: flex-end;
  margin: 30px;

  .y-axis {
    display: flex;
    flex-direction: column;
    align-items: flex-end;

    .axis-title {
      font-weight: bold;
      font-size: 14px;
      margin-bottom: 5px;
    }

    .ticks {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 200px;

      .tick {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        border-top: 1px solid #000;

        span {
          font-size: 14px;
          margin-right: 4px;
        }
      }

      .tick:last-child {
        span {
          position: absolute;
          margin-top: 18px;
        }
      }
    }
  }

  .chart-area {
    position: relative;
    flex: 1;
    display: flex;
    align-items: flex-end;
    height: 200px;
    border-left: 1px solid #000;
    border-bottom: 1px solid #000;

    .peaks {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 100%;

      .peak {
        position: absolute;
        bottom: 0;
        width: 4px;
        transition: background-color 0.2s ease;

        &:hover {
          background-color: #000;
        }

        .mz-label {
          position: absolute;
          top: -20px;
          font-size: 14px;
          text-align: center;
          z-index: 2;
        }
      }
    }

    .x-ticks {
      position: absolute;
      left: -1px;
      width: 100%;

      .x-tick {
        position: absolute;
        border-left: 1px solid #000;

        span {
          font-size: 14px;
          margin-top: 4px;
          margin-left: 4px;
          display: block;
          text-align: center;
        }
      }
    }

    .x-axis-title {
      position: absolute;
      bottom: -50px;
      left: 50%;
      transform: translateX(-50%);
      font-weight: bold;
      font-style: italic;
      font-size: 14px;
    }
  }
}
</style>
