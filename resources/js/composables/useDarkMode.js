import { ref } from 'vue'

const isDark = ref(
  typeof window !== 'undefined'
    ? localStorage.getItem('freeflex-theme') === 'dark'
    : false
)

export function useDarkMode() {
  function toggle() {
    isDark.value = !isDark.value
    _apply(isDark.value)
  }

  function setDark(value) {
    isDark.value = value
    _apply(value)
  }

  function _apply(dark) {
    localStorage.setItem('freeflex-theme', dark ? 'dark' : 'light')
    document.documentElement.classList.toggle('dark', dark)
  }

  return { isDark, toggle, setDark }
}
