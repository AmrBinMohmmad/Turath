function showForm(formId){
    document.querySelectorAll(".form_box")
        .forEach(form => form.classList.remove("active"));
    document.getElementById(formId).classList.add("active");
}
const toggleBtn = document.getElementById("theme-toggle");
const body = document.body;

// حمّل الاختيار السابق إذا موجود
if(localStorage.getItem("theme") === "dark") {
    body.classList.add("dark-mode");
    toggleBtn.textContent = "☀️";
}

toggleBtn.addEventListener("click", () => {
    body.classList.toggle("dark-mode");
    
    if(body.classList.contains("dark-mode")) {
        toggleBtn.textContent = "☀️";
        localStorage.setItem("theme", "dark");
    } else {
        toggleBtn.textContent = "🌙";
        localStorage.setItem("theme", "light");
    }
});



