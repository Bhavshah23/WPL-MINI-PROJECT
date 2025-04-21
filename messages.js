
class MessagingApp {
  constructor() {
    this.currentUserId = currentUserId
    this.conversations = []
    this.currentConversation = null
    this.messages = []

    // DOM elements
    this.conversationsList = document.getElementById("conversations-list")
    this.messagesList = document.getElementById("messages-list")
    this.conversationContainer = document.getElementById("conversation-container")
    this.noConversationMessage = document.getElementById("no-conversation-selected")
    this.conversationProfilePic = document.getElementById("conversation-profile-pic")
    this.conversationName = document.getElementById("conversation-name")
    this.messageInput = document.getElementById("message-input")
    this.sendMessageBtn = document.getElementById("send-message-btn")
    this.conversationSearch = document.getElementById("conversation-search")

    // Initialize event listeners
    this.initEventListeners()

    // Load initial data
    this.init()
  }

  async init() {
    try {
      // Load conversations
      await this.loadConversations()

      // If a specific conversation or user is selected via URL parameters
      if (selectedConversation > 0) {
        this.openConversation(selectedConversation)
      } else if (selectedUser > 0) {
        this.createConversation(selectedUser)
      }

      // Set up polling for new messages
      this.startPolling()
    } catch (error) {
      console.error("Error initializing app:", error)
      this.showError("Failed to load conversations. Please try again later.")
    }
  }

  initEventListeners() {
    // Send message button
    this.sendMessageBtn.addEventListener("click", () => {
      this.sendMessage()
    })

    // Send message on Enter key (but allow Shift+Enter for new line)
    this.messageInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault()
        this.sendMessage()
      }
    })

    // Search conversations
    this.conversationSearch.addEventListener("input", () => {
      this.filterConversations(this.conversationSearch.value)
    })
  }

  async loadConversations() {
    try {
      const response = await fetch(`get_conversations.php?user_id=${this.currentUserId}`)
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (data.success) {
        this.conversations = data.conversations
        this.renderConversations()
      } else {
        throw new Error(data.message || "Failed to load conversations")
      }
    } catch (error) {
      console.error("Error loading conversations:", error)
      this.conversationsList.innerHTML = `
        <div class="error-message">
          <p>Failed to load conversations. Please try again.</p>
          <button class="retry-button">Retry</button>
        </div>
      `

      const retryButton = this.conversationsList.querySelector(".retry-button")
      if (retryButton) {
        retryButton.addEventListener("click", () => {
          this.loadConversations()
        })
      }
    }
  }

  renderConversations() {
    if (this.conversations.length === 0) {
      this.conversationsList.innerHTML = `
        <div class="no-conversations-message">
          <p>No conversations yet</p>
          <p class="no-conversations-subtext">Match with someone to start chatting</p>
        </div>
      `
      return
    }

    this.conversationsList.innerHTML = ""

    this.conversations.forEach((conversation) => {
      const conversationElement = document.createElement("div")
      conversationElement.className = "conversation-item"
      conversationElement.dataset.conversationId = conversation.id

      // Add 'active' class if this is the current conversation
      if (this.currentConversation && this.currentConversation.id === conversation.id) {
        conversationElement.classList.add("active")
      }

      // Add 'unread' class if there are unread messages
      if (conversation.unread_count > 0) {
        conversationElement.classList.add("unread")
      }

      conversationElement.innerHTML = `
        <div class="conversation-profile-pic">
          <img src="${conversation.profile_picture}" alt="${conversation.name}">
          ${conversation.unread_count > 0 ? `<span class="unread-badge">${conversation.unread_count}</span>` : ""}
        </div>
        <div class="conversation-info">
          <div class="conversation-header-row">
            <h3 class="conversation-name">${conversation.name}</h3>
            <span class="conversation-time">${conversation.last_message_time}</span>
          </div>
          <p class="conversation-preview">${this.truncateText(conversation.last_message, 40)}</p>
        </div>
      `

      conversationElement.addEventListener("click", () => {
        this.openConversation(conversation.id)
      })

      this.conversationsList.appendChild(conversationElement)
    })
  }

  async openConversation(conversationId) {
    try {
      // Show loading state
      this.messagesList.innerHTML = '<div class="loading-indicator">Loading messages...</div>'
      this.conversationContainer.classList.remove("hidden")
      this.noConversationMessage.classList.add("hidden")

      // Mark conversation as active in the UI
      const conversationItems = this.conversationsList.querySelectorAll(".conversation-item")
      conversationItems.forEach((item) => {
        if (Number.parseInt(item.dataset.conversationId) === conversationId) {
          item.classList.add("active")
          // Remove unread indicator
          item.classList.remove("unread")
          const unreadBadge = item.querySelector(".unread-badge")
          if (unreadBadge) {
            unreadBadge.remove()
          }
        } else {
          item.classList.remove("active")
        }
      })

      // Fetch messages
      const response = await fetch(`get_messages.php?user_id=${this.currentUserId}&conversation_id=${conversationId}`)
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (data.success) {
        this.currentConversation = data.conversation
        this.messages = data.messages

        // Update conversation header
        this.conversationProfilePic.src = this.currentConversation.profile_picture
        this.conversationName.textContent = this.currentConversation.name

        // Render messages
        this.renderMessages()

        // Update conversation in the list (mark as read)
        this.updateConversationInList(conversationId, { unread_count: 0 })
      } else {
        throw new Error(data.message || "Failed to load messages")
      }
    } catch (error) {
      console.error("Error opening conversation:", error)
      this.messagesList.innerHTML = `
        <div class="error-message">
          <p>Failed to load messages. Please try again.</p>
          <button class="retry-button">Retry</button>
        </div>
      `

      const retryButton = this.messagesList.querySelector(".retry-button")
      if (retryButton) {
        retryButton.addEventListener("click", () => {
          this.openConversation(conversationId)
        })
      }
    }
  }

  renderMessages() {
    if (this.messages.length === 0) {
      this.messagesList.innerHTML = `
        <div class="no-messages-message">
          <p>No messages yet</p>
          <p class="no-messages-subtext">Send a message to start the conversation</p>
        </div>
      `
      return
    }

    this.messagesList.innerHTML = ""

    let currentDate = ""

    this.messages.forEach((message) => {
      // Add date separator if this is a new date
      if (message.date !== currentDate) {
        currentDate = message.date
        const dateSeparator = document.createElement("div")
        dateSeparator.className = "date-separator"
        dateSeparator.textContent = currentDate
        this.messagesList.appendChild(dateSeparator)
      }

      const messageElement = document.createElement("div")
      messageElement.className = `message ${message.is_self ? "message-self" : "message-other"}`

      messageElement.innerHTML = `
        <div class="message-bubble">
          <div class="message-text">${this.formatMessageText(message.message)}</div>
          <div class="message-time">${message.time} ${message.is_self ? (message.is_read ? "✓✓" : "✓") : ""}</div>
        </div>
      `

      this.messagesList.appendChild(messageElement)
    })

    // Scroll to bottom
    this.scrollToBottom()
  }

  scrollToBottom() {
    this.messagesList.scrollTop = this.messagesList.scrollHeight
  }

  async sendMessage() {
    const messageText = this.messageInput.value.trim()

    if (!messageText || !this.currentConversation) {
      return
    }

    // Clear input
    this.messageInput.value = ""

    // Add message to UI immediately (optimistic UI update)
    const tempMessage = {
      id: "temp-" + Date.now(),
      sender_id: this.currentUserId,
      is_self: true,
      message: messageText,
      is_read: false,
      time: this.formatTime(new Date()),
      date: this.formatDate(new Date()),
    }

    this.messages.push(tempMessage)
    this.renderMessages()

    try {
      // Send message to server
      const response = await fetch("send_message.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          user_id: this.currentUserId,
          recipient_id: this.currentConversation.user_id,
          conversation_id: this.currentConversation.id,
          message: messageText,
        }),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (data.success) {
        // Replace temp message with real message
        const index = this.messages.findIndex((m) => m.id === tempMessage.id)
        if (index !== -1) {
          this.messages[index] = {
            id: data.data.message_id,
            sender_id: this.currentUserId,
            is_self: true,
            message: messageText,
            is_read: false,
            time: data.data.time,
            date: data.data.date,
          }
        }

        // Update conversation in list
        this.updateConversationInList(this.currentConversation.id, {
          last_message: messageText,
          last_message_time: this.formatTime(new Date()),
        })

        // Move this conversation to the top of the list
        this.moveConversationToTop(this.currentConversation.id)
      } else {
        throw new Error(data.message || "Failed to send message")
      }
    } catch (error) {
      console.error("Error sending message:", error)
      // Show error in UI
      const errorElement = document.createElement("div")
      errorElement.className = "message-error"
      errorElement.textContent = "Failed to send message. Tap to retry."

      // Find the temp message and add error indicator
      const messageElements = this.messagesList.querySelectorAll(".message-self")
      const lastMessage = messageElements[messageElements.length - 1]
      if (lastMessage) {
        lastMessage.appendChild(errorElement)
        lastMessage.classList.add("message-failed")

        // Add retry functionality
        lastMessage.addEventListener("click", () => {
          // Remove the failed message
          const index = this.messages.findIndex((m) => m.id === tempMessage.id)
          if (index !== -1) {
            this.messages.splice(index, 1)
          }

          // Re-render messages
          this.renderMessages()

          // Put the message text back in the input
          this.messageInput.value = messageText
          this.messageInput.focus()
        })
      }
    }
  }

  async createConversation(userId) {
    try {
      const response = await fetch(`get_conversation.php?user_id=${this.currentUserId}&other_user_id=${userId}`)
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (data.success) {
        // Check if this conversation is already in our list
        const existingConversation = this.conversations.find((c) => c.id === data.conversation.id)

        if (!existingConversation) {
          // Add to conversations list
          this.conversations.unshift({
            id: data.conversation.id,
            user_id: data.conversation.user_id,
            name: data.conversation.name,
            profile_picture: data.conversation.profile_picture,
            last_message: "Start a conversation",
            last_message_time: this.formatTime(new Date()),
            unread_count: 0,
          })

          // Re-render conversations
          this.renderConversations()
        }

        // Open the conversation
        this.openConversation(data.conversation.id)
      } else {
        // Show a more specific error message if users aren't matched
        if (data.message && data.message.includes("matched")) {
          this.showError("You can only message users you have matched with.")
        } else {
          throw new Error(data.message || "Failed to create conversation")
        }
      }
    } catch (error) {
      console.error("Error creating conversation:", error)
      this.showError("Failed to create conversation. Please try again later.")
    }
  }

  updateConversationInList(conversationId, updates) {
    const index = this.conversations.findIndex((c) => c.id === conversationId)
    if (index !== -1) {
      this.conversations[index] = { ...this.conversations[index], ...updates }
      this.renderConversations()
    }
  }

  moveConversationToTop(conversationId) {
    const index = this.conversations.findIndex((c) => c.id === conversationId)
    if (index !== -1) {
      const conversation = this.conversations[index]
      this.conversations.splice(index, 1)
      this.conversations.unshift(conversation)
      this.renderConversations()
    }
  }

  filterConversations(query) {
    if (!query) {
      this.renderConversations()
      return
    }

    query = query.toLowerCase()

    const filteredConversations = this.conversations.filter(
      (conversation) =>
        conversation.name.toLowerCase().includes(query) || conversation.last_message.toLowerCase().includes(query),
    )

    if (filteredConversations.length === 0) {
      this.conversationsList.innerHTML = `
        <div class="no-conversations-message">
          <p>No conversations match your search</p>
        </div>
      `
      return
    }

    this.conversationsList.innerHTML = ""

    filteredConversations.forEach((conversation) => {
      const conversationElement = document.createElement("div")
      conversationElement.className = "conversation-item"
      conversationElement.dataset.conversationId = conversation.id

      // Add 'active' class if this is the current conversation
      if (this.currentConversation && this.currentConversation.id === conversation.id) {
        conversationElement.classList.add("active")
      }

      // Add 'unread' class if there are unread messages
      if (conversation.unread_count > 0) {
        conversationElement.classList.add("unread")
      }

      conversationElement.innerHTML = `
        <div class="conversation-profile-pic">
          <img src="${conversation.profile_picture}" alt="${conversation.name}">
          ${conversation.unread_count > 0 ? `<span class="unread-badge">${conversation.unread_count}</span>` : ""}
        </div>
        <div class="conversation-info">
          <div class="conversation-header-row">
            <h3 class="conversation-name">${conversation.name}</h3>
            <span class="conversation-time">${conversation.last_message_time}</span>
          </div>
          <p class="conversation-preview">${this.truncateText(conversation.last_message, 40)}</p>
        </div>
      `

      conversationElement.addEventListener("click", () => {
        this.openConversation(conversation.id)
      })

      this.conversationsList.appendChild(conversationElement)
    })
  }

  startPolling() {
    // Poll for new messages every 10 seconds
    setInterval(() => {
      this.checkForNewMessages()
    }, 10000)
  }

  async checkForNewMessages() {
    // If we have an open conversation, check for new messages
    if (this.currentConversation) {
      try {
        const response = await fetch(
          `get_messages.php?user_id=${this.currentUserId}&conversation_id=${this.currentConversation.id}`,
        )
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }

        const data = await response.json()

        if (data.success && data.messages.length > this.messages.length) {
          // We have new messages
          this.messages = data.messages
          this.renderMessages()
        }
      } catch (error) {
        console.error("Error checking for new messages:", error)
      }
    }

    // Also refresh the conversations list to check for new conversations or messages
    this.loadConversations()
  }

  // Helper methods
  truncateText(text, maxLength) {
    if (text.length <= maxLength) {
      return text
    }
    return text.substring(0, maxLength) + "..."
  }

  formatMessageText(text) {
    // Convert URLs to links
    const urlRegex = /(https?:\/\/[^\s]+)/g
    return text.replace(urlRegex, (url) => `<a href="${url}" target="_blank">${url}</a>`).replace(/\n/g, "<br>")
  }

  formatTime(date) {
    return date.toLocaleTimeString([], { hour: "numeric", minute: "2-digit" })
  }

  formatDate(date) {
    return date.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" })
  }

  showError(message) {
    const errorElement = document.createElement("div")
    errorElement.className = "error-notification"
    errorElement.innerHTML = `
    <div class="error-content">
      <p>${message}</p>
      <button class="error-close-btn">OK</button>
    </div>
  `

    document.body.appendChild(errorElement)

    const closeBtn = errorElement.querySelector(".error-close-btn")
    closeBtn.addEventListener("click", () => {
      errorElement.remove()
    })

    // Auto-remove after 5 seconds
    setTimeout(() => {
      if (document.body.contains(errorElement)) {
        errorElement.remove()
      }
    }, 5000)
  }
}

// Initialize the app when the DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new MessagingApp()
})
