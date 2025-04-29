function updateDateTime() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true };
    const dateTimeStr = now.toLocaleString('en-US', options).replace(',', ' |').replace(' at ', ' | ');
    document.getElementById('datetime').textContent = dateTimeStr;
}
setInterval(updateDateTime, 1000);

const images = ["assets/heroimg/hero1.jpg", "assets/heroimg/hero2.jpg", "assets/heroimg/hero3.jpg", "assets/heroimg/hero4.jpg"];
let index = 0;
const slider = document.getElementById("slider");

function changeImage() {
    slider.style.opacity = 0;  // Fade out
    setTimeout(() => {
        index = (index + 1) % images.length;
        slider.src = images[index];
        slider.style.transform = "translateX(-10px)"; // Slide effect
        setTimeout(() => {
            slider.style.opacity = 1; // Fade in
            slider.style.transform = "translateX(0)"; // Reset position
        }, 100);
    }, 1000);
}

setInterval(changeImage, 5000); // Change image every 3 seconds


function toggleAnswer(id, arrowId) {
const answer = document.getElementById(id);
const arrow = document.getElementById(arrowId);
if (answer.style.maxHeight === "0px" || !answer.style.maxHeight) {
    answer.style.maxHeight = "150px"; 
    arrow.style.transform = "rotate(90deg)";
} else {
    answer.style.maxHeight = "0px";
    arrow.style.transform = "rotate(0deg)";
}
}