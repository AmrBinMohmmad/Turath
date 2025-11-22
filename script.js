function showForm(formId){
    document.querySelectorAll(".form_box")
        .forEach(form => form.classList.remove("active"));
    document.getElementById(formId).classList.add("active");
}

const openBtn = document.getElementById("openCardBtn");
    const testCard = document.getElementById("testCard");
    const form = testCard.querySelector("form");
    const typesGrid = document.querySelector(".types-grid");
    const noCardsMessage = document.getElementById("noCardsMessage");

    // Show/hide the form
    openBtn.onclick = () => testCard.classList.toggle("hidden");

    // Update "no cards" message
    function updateMessage() {
      noCardsMessage.style.display = typesGrid.children.length ? "none" : "block";
    }
    updateMessage(); // initial check

    // Map region to PHP page
    const pageMap = {
      "west": "western_region.php",
      "central": "central_region.php",
      "south": "southern_region.php",
      "east": "eastern_region.php",
    
    };

    // Handle form submission
    form.onsubmit = e => {
      e.preventDefault();

      const name = form["test-name"].value;
      const region = form["region"].value;
      const students = form["students"].value;
      const attempts = form["attempts"].value;

      // Create a card
      const card = document.createElement("a");
      card.className = "type-card";

      // Link to correct PHP page with info in URL
      const page = pageMap[region];
      card.href = `${page}?name=${encodeURIComponent(name)}&students=${students}&attempts=${attempts}`;

      card.innerHTML = `
        <h3>${name}</h3>
        <p>Region: ${region}</p>
        <p>Allowed Students: ${students}</p>
        <p>Attempts per Student: ${attempts}</p>
      `;

      typesGrid.appendChild(card);

      // Clear form and hide it
      form.reset();
      testCard.classList.add("hidden");

      updateMessage(); // hide "no cards" message if needed
    };
