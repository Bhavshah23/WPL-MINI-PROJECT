class MatchingApp {
  constructor() {
    console.log("JS currentUserId:", window.currentUserId)

    this.profiles = []
    this.currentProfileIndex = 0
    this.cardsContainer = document.getElementById("profile-cards-container")
    this.noProfilesMessage = document.getElementById("no-profiles")
    this.currentUserId = 1 // In a real app, this would come from a session/login

    // Check if currentUserId is defined in the global scope (from PHP)
    if (typeof window.currentUserId !== "undefined") {
      this.currentUserId = window.currentUserId
    }

    this.init()
  }

  async init() {
    try {
      await this.fetchProfiles()

      if (this.profiles.length > 0) {
        this.renderProfiles()
      } else {
        this.showNoProfilesMessage()
      }
    } catch (error) {
      console.error("Error initializing app:", error)
      this.showErrorMessage("Failed to load profiles. Please try again later.")
    }
  }

  async fetchProfiles() {
    try {
      const response = await fetch(`get_profiles.php?user_id=${this.currentUserId}`)
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      this.profiles = await response.json()
    } catch (error) {
      console.error("Error fetching profiles:", error)
      throw error
    }
  }

  renderProfiles() {
    // Clear the container
    this.cardsContainer.innerHTML = ""

    // Render the current profile and a few more for better UX
    const numToRender = Math.min(3, this.profiles.length - this.currentProfileIndex)

    for (let i = 0; i < numToRender; i++) {
      const profileIndex = this.currentProfileIndex + i
      const profile = this.profiles[profileIndex]
      const card = this.createProfileCard(profile, i === 0)

      // Position cards in a stack
      card.style.zIndex = 1000 - i
      if (i > 0) {
        // Center the cards but scale them down and move them slightly down
        card.style.transform = `translateX(-50%) scale(${1 - i * 0.05}) translateY(${i * 10}px)`
        card.style.opacity = `${1 - i * 0.2}`
      }

      this.cardsContainer.appendChild(card)
    }
  }

  createProfileCard(profile, isActive) {
    const card = document.createElement("div")
    card.className = `profile-card ${isActive ? "active" : ""}`
    card.dataset.profileId = profile.id

    // Create carousel
    const carousel = this.createCarousel(profile.images)

    // Create profile info
    const profileInfo = document.createElement("div")
    profileInfo.className = "profile-info"

    const nameElement = document.createElement("h2")
    nameElement.className = "profile-name"
    nameElement.textContent = profile.name

    const ageGender = document.createElement("p")
    ageGender.className = "profile-age-location"
    ageGender.textContent = `${profile.age} • ${profile.gender === "male" ? "Male" : "Female"}`

    const promptsSection = document.createElement("div")
    promptsSection.className = "prompts-section"

    profile.prompts.forEach((prompt) => {
      const promptBox = document.createElement("div")
      promptBox.className = "prompt-box"

      const question = document.createElement("div")
      question.className = "prompt-question"
      question.textContent = prompt.question

      const answer = document.createElement("div")
      answer.className = "prompt-answer"
      answer.textContent = prompt.answer

      promptBox.appendChild(question)
      promptBox.appendChild(answer)
      promptsSection.appendChild(promptBox)
    })

    // Create buttons
    const buttonRow = document.createElement("div")
    buttonRow.className = "button-row"

    const dislikeBtn = document.createElement("button")
    dislikeBtn.className = "btn btn-dislike"
    dislikeBtn.innerHTML = "❌"
    dislikeBtn.addEventListener("click", () => this.handleDislike(card))

    const likeBtn = document.createElement("button")
    likeBtn.className = "btn btn-like"
    likeBtn.innerHTML = "❤️"
    likeBtn.addEventListener("click", () => this.handleLike(card))

    buttonRow.appendChild(dislikeBtn)
    buttonRow.appendChild(likeBtn)

    // Assemble the card
    profileInfo.appendChild(nameElement)
    profileInfo.appendChild(ageGender)
    profileInfo.appendChild(promptsSection)
    profileInfo.appendChild(buttonRow)

    card.appendChild(carousel)
    card.appendChild(profileInfo)

    return card
  }

  createCarousel(images) {
    const carouselContainer = document.createElement("div")
    carouselContainer.className = "carousel-container"

    // Create slides
    images.forEach((image, index) => {
      const slide = document.createElement("div")
      slide.className = "carousel-slide"
      slide.style.transform = `translateX(${index * 100}%)`

      const img = document.createElement("img")
      img.src = image
      img.alt = "Profile photo"
      img.onerror = function () {
        // If image fails to load, use a placeholder
        this.src = `assets/placeholder.svg?height=400&width=400&text=No+Image`
      }

      slide.appendChild(img)
      carouselContainer.appendChild(slide)
    })

    // Create controls
    const controls = document.createElement("div")
    controls.className = "carousel-controls"

    const prevArrow = document.createElement("div")
    prevArrow.className = "carousel-arrow prev"
    prevArrow.innerHTML = "❮"

    const nextArrow = document.createElement("div")
    nextArrow.className = "carousel-arrow next"
    nextArrow.innerHTML = "❯"

    controls.appendChild(prevArrow)
    controls.appendChild(nextArrow)

    // Create dot indicators
    const dotIndicators = document.createElement("div")
    dotIndicators.className = "carousel-dot-indicators"

    images.forEach((_, index) => {
      const dot = document.createElement("div")
      dot.className = `carousel-dot ${index === 0 ? "active" : ""}`
      dot.dataset.index = index
      dotIndicators.appendChild(dot)
    })

    carouselContainer.appendChild(controls)
    carouselContainer.appendChild(dotIndicators)

    // Set up carousel functionality
    let currentSlide = 0

    const updateCarousel = (newIndex) => {
      const slides = carouselContainer.querySelectorAll(".carousel-slide")
      const dots = carouselContainer.querySelectorAll(".carousel-dot")

      slides.forEach((slide, index) => {
        slide.style.transform = `translateX(${(index - newIndex) * 100}%)`
      })

      dots.forEach((dot, index) => {
        dot.className = `carousel-dot ${index === newIndex ? "active" : ""}`
      })

      currentSlide = newIndex
    }

    prevArrow.addEventListener("click", (e) => {
      e.stopPropagation()
      if (currentSlide > 0) {
        updateCarousel(currentSlide - 1)
      }
    })

    nextArrow.addEventListener("click", (e) => {
      e.stopPropagation()
      if (currentSlide < images.length - 1) {
        updateCarousel(currentSlide + 1)
      }
    })

    dotIndicators.addEventListener("click", (e) => {
      if (e.target.classList.contains("carousel-dot")) {
        const index = Number.parseInt(e.target.dataset.index)
        updateCarousel(index)
      }
    })

    // Enable swipe functionality
    let touchStartX = 0
    let touchEndX = 0

    carouselContainer.addEventListener("touchstart", (e) => {
      touchStartX = e.changedTouches[0].screenX
    })

    carouselContainer.addEventListener("touchend", (e) => {
      touchEndX = e.changedTouches[0].screenX
      handleSwipe()
    })

    function handleSwipe() {
      const swipeThreshold = 50
      if (touchEndX < touchStartX - swipeThreshold && currentSlide < images.length - 1) {
        // Swipe left, go to next slide
        updateCarousel(currentSlide + 1)
      } else if (touchEndX > touchStartX + swipeThreshold && currentSlide > 0) {
        // Swipe right, go to previous slide
        updateCarousel(currentSlide - 1)
      }
    }

    return carouselContainer
  }

  async handleLike(card) {
    const profileId = Number.parseInt(card.dataset.profileId)
    await this.saveAction(profileId, "like")
    this.swipeCard(card, "right")
  }

  async handleDislike(card) {
    const profileId = Number.parseInt(card.dataset.profileId)
    await this.saveAction(profileId, "dislike")
    this.swipeCard(card, "left")
  }

  async saveAction(profileId, action) {
    console.log(profileId);
    console.log(currentUserId);
    try {
      
      const response = await fetch("save_action.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          user_id: this.currentUserId,
          profile_id: profileId,
          action: action,
        }),
      });
  
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
  
      const text = await response.text(); // Get raw response
      let result;
  
      try {
        result = JSON.parse(text); // Try parsing it as JSON
      } catch (jsonError) {
        console.error("Response is not valid JSON:", text);
        throw new Error("Invalid JSON returned from server");
      }
  
      if (result.success && result.is_match) {
        this.showMatchNotification(profileId);
      }
  
      return result;
    } catch (error) {
      console.error("Error saving action:", error);
      return { success: false };
    }
  }
  

  showMatchNotification(profileId) {
    // Find the profile that matched
    const profile = this.profiles.find((p) => p.id === profileId)
    if (!profile) return

    // Create notification element
    const notification = document.createElement("div")
    notification.className = "match-notification"
    notification.innerHTML = `
      <div class="match-notification-content">
        <h2>It's a Match!</h2>
        <p>You and ${profile.name} have liked each other</p>
        <div class="match-profile-pic">
          <img src="${profile.images[0]}" alt="${profile.name}">
        </div>
        <button class="match-btn">Send a Message</button>
        <button class="match-btn-secondary">Keep Swiping</button>
      </div>
    `

    // Add styles for the notification
    const style = document.createElement("style")
    style.textContent = `
      .match-notification {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 107, 107, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        animation: fadeIn 0.5s ease;
      }
      
      .match-notification-content {
        background-color: white;
        padding: 30px;
        border-radius: 16px;
        text-align: center;
        max-width: 90%;
        width: 350px;
      }
      
      .match-notification h2 {
        color: #ff6b6b;
        font-size: 28px;
        margin-bottom: 10px;
      }
      
      .match-profile-pic {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        margin: 20px auto;
        border: 3px solid #ff6b6b;
      }
      
      .match-profile-pic img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      
      .match-btn {
        background-color: #ff6b6b;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 50px;
        font-size: 16px;
        font-weight: 600;
        margin-top: 20px;
        cursor: pointer;
        width: 100%;
      }
      
      .match-btn-secondary {
        background-color: transparent;
        color: #666;
        border: none;
        padding: 12px 24px;
        border-radius: 50px;
        font-size: 16px;
        margin-top: 10px;
        cursor: pointer;
        width: 100%;
      }
      
      @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
      }
    `

    document.head.appendChild(style)
    document.body.appendChild(notification)

    // Add event listeners to buttons
    const messageBtn = notification.querySelector(".match-btn")
    const keepSwipingBtn = notification.querySelector(".match-btn-secondary")

    messageBtn.addEventListener("click", () => {
      // In a real app, this would redirect to the messaging page
      window.location.href = `messages.php?match_id=${profileId}`
      notification.remove()
    })

    keepSwipingBtn.addEventListener("click", () => {
      notification.remove()
    })
  }

  swipeCard(card, direction) {
    // Remove any existing transform to avoid conflicts
    card.style.transform = ""

    // Add swipe animation class
    card.classList.add(`swipe-${direction}`)

    // Wait for animation to complete
    setTimeout(() => {
      this.currentProfileIndex++

      if (this.currentProfileIndex >= this.profiles.length) {
        this.showNoProfilesMessage()
      } else {
        this.renderProfiles()
      }
    }, 500)
  }

  showNoProfilesMessage() {
    this.cardsContainer.innerHTML = ""
    this.noProfilesMessage.classList.remove("hidden")
  }

  showErrorMessage(message) {
    this.cardsContainer.innerHTML = ""
    this.noProfilesMessage.classList.remove("hidden")
    this.noProfilesMessage.innerHTML = `
      <h2>Oops! Something went wrong</h2>
      <p>${message}</p>
      <button class="retry-button">Try Again</button>
    `

    const retryButton = this.noProfilesMessage.querySelector(".retry-button")
    if (retryButton) {
      retryButton.addEventListener("click", () => {
        window.location.reload()
      })
    }
  }
}

// Initialize the app when the DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new MatchingApp()
})
