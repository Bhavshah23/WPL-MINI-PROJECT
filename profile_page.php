<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dating Site Profile</title>
    <link rel="stylesheet" href="profile_page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
</head>
<body>
    <div id="cropper-container" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.7); justify-content:center; align-items:center; z-index:1000;">
        <div style="background:white; padding:20px; border-radius:8px; max-width:90%; max-height:90%;">
          <img id="cropper-image" style="max-width:100%; max-height:500px;" />
          <br>
          <button id="crop-button">Crop & Add to Carousel</button>
        </div>
    </div>
      

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="site-name">JainZ</div>

        <div class="profile-pic-container">
            <img id="profile-pic" class="profile-pic"  alt="Profile Picture">
            <label for="profile-pic-upload" class="upload-icon">📷</label>
            <input type="file" id="profile-pic-upload" accept="image/*">
        </div>
        <p class="user-name" id="user-name">Your Name</p>

        <!-- Sidebar Links -->
        <div class="sidebar-links">
            <a href="#">👤 Profile Page</a>
            <a href="matching.php">💖 Matching</a>
            <a href="messages.php">📩 Messaging</a>
            
        </div>
        <button class="logout-btn">Logout</button>


    </div>
    <div class="center-stage">
        <div class = "not-movable">
            <div class="carousel-container">
                <div id="preview-container"></div> <!-- Add this container for preview image -->
                <img id="carousel" class="carousel"  alt="carousel images">
                <div class="dots" id="dots"></div>
                <label for="image-upload" class="upload-button">+ Add Photos</label>
                <input type="file" class="img" id="image-upload" multiple accept="image/*">
            </div>
            <div class="user-info-box">
                <h2>User Details</h2>
                <p><span>Name:</span> Bhav shah</p>
                <p><span>Email:</span> bitch.com</p>
                <p><span>Gender:</span> Male</p>
                <p><span>Birthdate</span> </p>
                
                <!-- Add more fields as needed -->
            </div>
        </div>
        <!-- Button to open modal -->
        <div class="prompt-section">
            <button class="open-prompt-modal">📝 Answer Prompts</button>
            <div id="prompt-answers-container"></div>
        </div>
          
          <!-- Modal 1 -->
        <div class="modal" id="promptModal">
            <div class="modal-content">
              <div class="prompt-slide" id="slide-1">
                    <h2>Formal Stuff</h2>
                    <label>Education</label>
                    <textarea id="education" placeholder="e.g. B.Tech in Computer Science"></textarea>

                    <label>Profession</label>
                    <textarea id="profession" placeholder="e.g. Software Engineer at Google"></textarea>

                    <label>Fun Fact</label>
                    <textarea id="fun-fact" placeholder="e.g. I can solve a Rubik’s cube under 30 seconds"></textarea>
                    <button class="next-btn">Next →</button>
              </div>
        
              <div class="prompt-slide hidden" id="slide-2">
                    <h2>Reletionship Stuff</h2>
                    <label>What does being Jain mean to me?</label>
                    <textarea id="religion" placeholder="e.g. B.Tech in Computer Science"></textarea>

                    <label>I’m looking for someone who…</label>
                    <textarea id="reletionship" placeholder="e.g. Software Engineer at Google"></textarea>

                    <label>My dietary preferences are…</label>
                    <textarea id="diet" placeholder="vegetarianism, veganism or pure jain"></textarea>
                  <button class="prev-btn">← Previous</button>
                  <button class="next-btn">Next →</button>
              </div>
        
              <div class="prompt-slide hidden" id="slide-3">
                    <h2>Personal Stuff</h2>
                    <label>Amount of Babies?</label>
                    <textarea id="babies" placeholder="1 or 2 or 3"></textarea>

                    <label>Toxic Trait</label>
                    <textarea id="toxicity" placeholder="e.g. Software Engineer at Google"></textarea>

                    <labe>Vacation Spots</label>
                    <textarea id="vactaion" placeholder="e.g. I can solve a Rubik’s cube under 30 seconds"></textarea>
                
                 <button class="prev-btn">← Previous</button>
                 <button class="save-btn">Save</button>
              </div>
        

            </div>
        </div>
        

      
        
    
        
        
    </div>
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    

    
    

    <script type="module" src="profile_page.js"></script>

</body>
</html>