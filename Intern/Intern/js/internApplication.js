function updateDateTime() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true };
    const dateTimeStr = now.toLocaleString('en-US', options).replace(',', ' |').replace(' at ', ' | ');
    document.getElementById('datetime').textContent = dateTimeStr;
}
function updateYear() {
    const year = new Date().getFullYear();
    document.getElementById('program-year').textContent = year;
}
setInterval(updateDateTime, 1000);
window.onload = updateYear;


//Para sa dropdown pag *pag click ng other sa select may lalabas na textbox
function handleSchoolChange() {
    const schoolDropdown = document.getElementById('school_university');
    const otherSchoolInput = document.getElementById('other_school');

    if (schoolDropdown.value === 'Others') {
        otherSchoolInput.style.display = 'block';
        otherSchoolInput.required = true; 
    } else {
        otherSchoolInput.style.display = 'none';
        otherSchoolInput.required = false; 
        otherSchoolInput.value = ''; 
}
}

//pang validate ng email
function validateEmail() {
const emailField = document.getElementById('email');
const email = emailField.value;
const validDomains = ["gmail.com", "yahoo.com", "outlook.com"]; // Add other valid domains as necessary.
const domain = email.split('@')[1];

if (!domain || !validDomains.includes(domain)) {
    alert("Please enter a valid email address with a recognized domain (e.g., Gmail, Yahoo, Outlook).");
    return false; // Prevent form submission.
}
return true;
}

//pang restrict ng file upload ng resume para maiwasan redundant
//pang limit ng 10mb file upload

const uploadedFiles = new Set(); // To store hashes of uploaded files.

async function hashFile(file) {
const arrayBuffer = await file.arrayBuffer(); 
const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer);
const hashArray = Array.from(new Uint8Array(hashBuffer));
return hashArray.map(byte => byte.toString(16).padStart(2, '0')).join('');
}

async function validateFiles() {
const maxSize = 10 * 1024 * 1024; // 10MB in bytes

// Get file inputs
const resumeInput = document.getElementById('resume');
const moaInput = document.getElementById('moa');
const recomInput = document.getElementById('recom');

// pang validate ng resume size and duplication
if (resumeInput.files[0]) {
const resumeFile = resumeInput.files[0];

// pang check file size
if (resumeFile.size > maxSize) {
    alert("The uploaded Resume exceeds the 10MB limit. Please upload a smaller file.");
    return false;
}

// pang check duplicate resume
const fileHash = await hashFile(resumeFile);
if (uploadedFiles.has(fileHash)) {
    alert("This Resume file has already been uploaded. Please choose a different file.");
    return false;
}
uploadedFiles.add(fileHash);
}

// pang validate ng MOA size
if (moaInput.files[0] && moaInput.files[0].size > maxSize) {
alert("The uploaded MOA file exceeds the 10MB limit. Please upload a smaller file.");
return false;
}

// pang validate ng Recommendation Letter size
if (recomInput.files[0] && recomInput.files[0].size > maxSize) {
alert("The uploaded Recommendation Letter exceeds the 10MB limit. Please upload a smaller file.");
return false;
}

return true;
}