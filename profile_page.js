let images = [];
let currentIndex = -1;

// Change Profile Picture
document.getElementById("profile-pic-upload").addEventListener("change", function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("profile-pic").src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

const imageUpload = document.getElementById("image-upload");
const dotsContainer = document.getElementById("dots");

const files = [];

const reader = new FileReader(); // Declare reader globally

imageUpload.addEventListener("change", function (event) {
    const dot = document.createElement("span");
    dotsContainer.appendChild(dot);
    currentIndex ++ ;
    Array.from(event.target.files).forEach(file => files.push(file)); // Add files correctly

    if (files.length === 0) return; 

    reader.onload = function (e) {
        document.getElementById("carousel").src = e.target.result;

        
        dot[currentIndex].classList.add("active");
        
    };

    reader.readAsDataURL(files[currentIndex]); 
});

document.getElementById("carousel").addEventListener("click", () => {
    dotsContainer.children[currentIndex].classList.remove("active");
    currentIndex = (currentIndex + 1) % files.length; 
    reader.readAsDataURL(files[currentIndex]);
});


