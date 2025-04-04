function updateDateTime() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true };
    const dateTimeStr = now.toLocaleString('en-US', options).replace(',', ' |').replace(' at ', ' | ');
    document.getElementById('datetime').textContent = dateTimeStr;
}
setInterval(updateDateTime, 1000);


function openModal(index) {
        var title = document.querySelectorAll('.card h3')[index].innerText;
        var description = document.getElementById('fullDesc' + index).innerText.split("|");
        var imageSrc = document.getElementById('imageSrc' + index).innerText;
        
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('modalImage').alt = title;
        
        var descList = document.getElementById('modalDescription');
        descList.innerHTML = ""; // Clear previous content

        description.forEach(function(item) {
            var li = document.createElement("li");
            li.innerText = item;
            descList.appendChild(li);
        });

        document.getElementById('modal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('modal').style.display = 'none';
    }


    window.onclick = function(event) {
        var modal = document.getElementById('modal');
        if (event.target === modal) {
            closeModal();
        }
    }
    
    function closeModal() {
        document.getElementById('modal').style.display = 'none';
    }