import { ref, watch } from 'vue'
import type { Ref } from 'vue'

export function useThrottle<T>(value: Ref<T>, delay = 300): Ref<T> {
  const throttledValue = ref(value.value) as Ref<T>
  let lastTime = 0

  watch(value, (newVal) => {
    const now = Date.now()
    if (now - lastTime >= delay) {
      throttledValue.value = newVal
      lastTime = now
    }
  })

  return throttledValue
}
