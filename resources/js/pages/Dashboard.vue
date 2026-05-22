<script setup>
import { ref } from 'vue'

const prompt = ref('')
const messages = ref([])
const loading = ref(false)
const uploadedFile = ref(null)
const uploadedFileName = ref('')

const handleFileUpload = (e) => {
  const file = e.target.files[0]
  if (file) {
    uploadedFile.value = file
    uploadedFileName.value = file.name
    messages.value.push({
      role: 'assistant',
      content: `📄 Loaded: ${file.name}\n\nNow you can ask questions about this document.`
    })
  }
}

const sendMessage = async () => {
  if (!prompt.value.trim()) return

  // Add user message
  messages.value.push({
    role: 'user',
    content: prompt.value
  })

  const userMessage = prompt.value
  prompt.value = ''
  loading.value = true

  try {
    const formData = new FormData()
    formData.append('prompt', userMessage)
    if (uploadedFile.value) {
      formData.append('file', uploadedFile.value)
    }

    const response = await fetch('/api/chat', {
      method: 'POST',
      body: formData
    })

    const data = await response.json()
    
    // Extract AI response
    const aiMessage = data.candidates?.[0]?.content?.parts?.[0]?.text || 'No response'
    
    messages.value.push({
      role: 'assistant',
      content: aiMessage
    })
  } catch (error) {
    messages.value.push({
      role: 'assistant',
      content: `Error: ${error.message}`
    })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 p-4">
    <div class="max-w-2xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-white mb-2">AI Document Analyzer</h1>
        <p class="text-gray-400">Upload PDF or chat with AI</p>
      </div>

      <!-- File Upload -->
      <div class="bg-gray-800 rounded-lg p-4 mb-4">
        <label class="block">
          <input 
            type="file" 
            accept=".pdf"
            @change="handleFileUpload"
            class="block w-full text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:bg-blue-600 file:text-white hover:file:bg-blue-700"
          />
        </label>
        <p v-if="uploadedFileName" class="text-sm text-green-400 mt-2">
          ✓ Loaded: {{ uploadedFileName }}
        </p>
      </div>

      <!-- Messages Container -->
      <div class="bg-gray-800 rounded-lg p-6 mb-4 h-96 overflow-y-auto space-y-4">
        <div v-for="(msg, idx) in messages" :key="idx" :class="['p-4 rounded-lg', msg.role === 'user' ? 'bg-blue-600 text-white ml-auto max-w-xs' : 'bg-gray-700 text-gray-100 mr-auto max-w-xs']">
          <p class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
        </div>
        
        <!-- Loading indicator -->
        <div v-if="loading" class="bg-gray-700 text-gray-100 p-4 rounded-lg max-w-xs">
          <div class="flex gap-2">
            <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
            <div class="w-2 h-2 bg-white rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
            <div class="w-2 h-2 bg-white rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
          </div>
        </div>
      </div>

      <!-- Input -->
      <div class="flex gap-2">
        <input 
          v-model="prompt" 
          @keyup.enter="sendMessage"
          type="text" 
          placeholder="Type your message..." 
          class="flex-1 px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          :disabled="loading"
        />
        <button 
          @click="sendMessage"
          :disabled="loading || !prompt"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Send
        </button>
      </div>
    </div>
  </div>
</template>