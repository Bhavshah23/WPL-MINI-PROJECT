window.onload = function () {
  fetch('get_user_info.php')
      .then(response => response.json())
      .then(user => {
          if (user.error) return;

          // Set sidebar name
          document.getElementById('user-name').textContent = user.name;

          // Fill in user info box
          const infoBox = document.querySelector('.user-info-box');
          infoBox.innerHTML = `
              <h2>User Details</h2>
              <p><span>Name:</span> ${user.name}</p>
              <p><span>Email:</span> ${user.email}</p>
              <p><span>Gender:</span> ${user.gender}</p>
              <p><span>Birthdate:</span> ${user.birth_date}</p>
          `;
      })
      .catch(err => console.error('Failed to load user info:', err));
};


// Change Profile Picture
document.getElementById("profile-pic-upload").addEventListener("change", function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("profile-pic").src = e.target.result;
            // After setting profile preview
            const formData = new FormData();
            formData.append("profile_pic", file);




            fetch('upload_profile_pic.php', {
              method: 'POST',
              body: formData
            })
            .then(async res => {
              const text = await res.text(); // Not res.json() directly!
              console.log("Raw image upload response:", text);
            
              try {
                const json = JSON.parse(text);
                // handle json response
              } catch (err) {
                console.error('Upload error (invalid JSON):', text);
              }
            })
            .catch(err => {
              console.error('Network error:', err);
            });
            

            

        };
        reader.readAsDataURL(file);
    }
});


const infoBoxes = document.querySelectorAll(".info-box");

infoBoxes.forEach(box => {
    box.addEventListener("click", function () {
        // If already expanded, do nothing
        if (this.classList.contains("expanded")) return;

        // Shrink any other expanded box
        document.querySelectorAll(".info-box").forEach(item => {
            item.classList.remove("expanded");
            item.innerHTML = item.getAttribute("data-label"); // Reset text
        });

        // Expand the clicked box and add a textarea
        this.classList.add("expanded");
        const textarea = document.createElement("textarea");
        textarea.placeholder = `Enter ${this.innerText}...`;
        this.innerHTML = ""; // Clear text
        this.appendChild(textarea);
        textarea.focus();
    });

    // Store original label for reset
    box.setAttribute("data-label", box.innerText);
});

// Collapse boxes when clicking outside
document.addEventListener("click", function (event) {
    if (!event.target.classList.contains("info-box") && !event.target.tagName === "TEXTAREA") {
        infoBoxes.forEach(box => {
            box.classList.remove("expanded");
            box.innerHTML = box.getAttribute("data-label"); // Reset text
        });
    }
});

let images = []; // Holds only cropped image data URLs
let currentIndex = -1;
let cropQueue = [];
let cropper;

// Start cropping when images are selected
document.getElementById("image-upload").addEventListener("change", function (event) {
    cropQueue = Array.from(event.target.files);
    showNextCropper();
});

function showNextCropper() {
    if (cropQueue.length === 0) return;

    const file = cropQueue.shift();
    const reader = new FileReader();

    reader.onload = function (e) {
        const image = document.getElementById("cropper-image");
        image.src = e.target.result;
        document.getElementById("cropper-container").style.display = "flex";

        if (cropper) cropper.destroy();
        cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 2,
        });
    };

    reader.readAsDataURL(file);
}

document.getElementById("crop-button").addEventListener("click", function () {
    const canvas = cropper.getCroppedCanvas();
    if (!canvas) return;

    const croppedImage = canvas.toDataURL("image/png");
    images.push(croppedImage);
    currentIndex = images.length - 1;

    // Show cropped image in carousel
    document.getElementById("carousel").src = croppedImage;

    // Upload the cropped image to backend
    




    fetch("upload_carousel.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ image: croppedImage })
    })
    .then(res => res.text()) // change to text for debugging
    .then(text => {
      console.log("Raw upload response:", text);
      try {
        const response = JSON.parse(text);
        if (response.status === "success") {
          console.log("Carousel image uploaded");
        } else {
          console.error("Upload failed:", response.message);
        }
      } catch (err) {
        console.error("Invalid JSON:", text);
      }
    })

   
    


    // Add dot
    const dot = document.createElement("span");
    dot.classList.add("dot");
    document.getElementById("dots").appendChild(dot);
    updateDots();

    // Cleanup
    document.getElementById("cropper-container").style.display = "none";
    cropper.destroy();

    // Crop next image
    showNextCropper();
});

function updateDots() {
    const dots = document.querySelectorAll("#dots .dot");
    dots.forEach(dot => dot.classList.remove("active"));
    if (dots[currentIndex]) dots[currentIndex].classList.add("active");
}

// Handle carousel click
document.getElementById("carousel").addEventListener("click", () => {
    if (images.length === 0) return;
    currentIndex = (currentIndex + 1) % images.length;
    document.getElementById("carousel").src = images[currentIndex];
    updateDots();
});

// Track current slide
let currentSlide = 1;

// Open the modal
document.querySelector('.open-prompt-modal').addEventListener('click', () => {
  document.getElementById('promptModal').classList.remove('hidden');
});

// Handle next slide
document.querySelectorAll('.next-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById(`slide-${currentSlide}`).classList.add('hidden');
    currentSlide++;
    document.getElementById(`slide-${currentSlide}`).classList.remove('hidden');
  });
});

// Handle previous slide
document.querySelectorAll('.prev-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById(`slide-${currentSlide}`).classList.add('hidden');
    currentSlide--;
    document.getElementById(`slide-${currentSlide}`).classList.remove('hidden');
  });
});

// Collect and display answers, then send to backend
function collectAndDisplayAnswers() {
  const fields = [
    { id: 'education', label: 'Education', key: 'education' },
    { id: 'profession', label: 'Profession', key: 'profession' },
    { id: 'fun-fact', label: 'Fun Fact', key: 'fun_fact' },
    { id: 'religion', label: 'What being Jain means to me', key: 'meaning_of_jain' },
    { id: 'reletionship', label: 'I’m looking for someone who…', key: 'looking_for' },
    { id: 'diet', label: 'Dietary Preferences', key: 'diet' },
    { id: 'babies', label: 'Amount of Babies', key: 'babies' },
    { id: 'toxicity', label: 'Toxic Trait', key: 'toxic_trait' },
    { id: 'vactaion', label: 'Vacation Spots', key: 'vacation_spots' }
  ];

  const container = document.getElementById('prompt-answers-container');
  container.innerHTML = '';

  const dataToSend = {};

  fields.forEach(({ id, label, key }) => {
    const value = document.getElementById(id).value;
    if (value.trim()) {
      // Display on frontend
      const box = document.createElement('div');
      box.className = 'prompt-answer-box';
      box.innerHTML = `<strong>${label}:</strong> ${value}`;
      container.appendChild(box);

      // Prepare for backend
      dataToSend[key] = value;
    }
  });

  // Send to backend
  fetch('save_prompts.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(dataToSend)
  })
  .then(async res => {
    const text = await res.text();
    console.log("Raw response:", text);
  
    try {
      const json = JSON.parse(text);
      if (json.status === 'success') {
        console.log('Prompts saved successfully');
      } else {
        console.error('Backend error:', json.message || json);
      }
    } catch (e) {
      console.error('Invalid JSON from server:', text);
    }
  })
  .catch(err => {
    console.error('Network or fetch error:', err);
  });
  

  

  // Hide modal and reset
  document.getElementById('promptModal').classList.add('hidden');
  currentSlide = 1;
  document.querySelectorAll('.prompt-slide').forEach((slide, i) => {
    slide.classList.add('hidden');
    if (i === 0) slide.classList.remove('hidden');
  });
}

// Attach to Save button
// Can be either final Save button or submit-prompts button
const saveBtn = document.querySelector('.save-btn') || document.getElementById('submit-prompts');
saveBtn.addEventListener('click', collectAndDisplayAnswers);



