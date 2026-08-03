import { ref } from 'vue'

export function useChatThread({ fetchUrl, sendUrl, channelName }) {
  const messages = ref([])
  const loading = ref(false)
  const sending = ref(false)
  let channel = null

  async function load() {
    loading.value = true
    try {
      const res = await fetch(fetchUrl, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      })
      if (res.ok) messages.value = await res.json()
    } finally {
      loading.value = false
    }
  }

  function subscribe(onMessage) {
    if (!window.Echo || !channelName) return
    channel = window.Echo.private(channelName).listen('.message.sent', (e) => {
      if (!messages.value.find((m) => m.id === e.id)) {
        messages.value.push(e)
        onMessage?.(e)
      }
    })
  }

  function unsubscribe() {
    if (channel && channelName) {
      window.Echo?.leave(channelName)
      channel = null
    }
  }

  async function send(body) {
    const text = body.trim()
    if (!text || sending.value) return null

    sending.value = true
    try {
      const res = await fetch(sendUrl, {
        method: 'POST',
        headers: {
          'Content-Type':     'application/json',
          'Accept':           'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN':     document.querySelector('meta[name=csrf-token]')?.content ?? '',
        },
        body: JSON.stringify({ body: text }),
      })
      if (!res.ok) return null

      const msg = await res.json()
      if (!messages.value.find((m) => m.id === msg.id)) messages.value.push(msg)
      return msg
    } finally {
      sending.value = false
    }
  }

  return { messages, loading, sending, load, subscribe, unsubscribe, send }
}
