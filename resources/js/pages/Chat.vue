<template>
  <div class="chat-container">
    <div class="chat-wrapper">
      <!-- Header -->
      <div class="chat-header">
        <h1 class="chat-title">AI Document Analyzer</h1>
        <p class="chat-subtitle">Upload PDF or chat with AI</p>
      </div>

      <!-- File Upload -->
      <div class="chat-upload">
        <label class="upload-label">
          <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span class="upload-text">Choose PDF file</span>
          <input 
            type="file" 
            accept=".pdf"
            @change="handleFileUpload"
            class="hidden"
          />
        </label>
        <p v-if="uploadedFileName" class="upload-success">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          {{ uploadedFileName }}
        </p>
      </div>

      <!-- Messages Container -->
      <div class="messages-container">
        <div v-for="(msg, idx) in messages" :key="idx" :class="['message', msg.role === 'user' ? 'message-user' : 'message-assistant']">
          <p class="message-text">{{ msg.content }}</p>
        </div>
        
        <!-- Loading indicator -->
        <div v-if="loading" class="loading-indicator">
          <div class="flex gap-1">
            <div class="loading-dot" style="animation-delay: 0s"></div>
            <div class="loading-dot" style="animation-delay: 0.1s"></div>
            <div class="loading-dot" style="animation-delay: 0.2s"></div>
          </div>
          <span class="loading-text">Analyzing...</span>
        </div>
      </div>

      <!-- Input -->
      <div class="input-container">
        <input 
          v-model="prompt" 
          @keyup.enter="sendMessage"
          type="text" 
          placeholder="Ask about your document..." 
          class="input-field"
          :disabled="loading"
        />
        <button 
          @click="sendMessage"
          :disabled="loading || !prompt"
          class="send-button"
        >
          Send
        </button>
      </div>
    </div>
  </div>
</template>

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