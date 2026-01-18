document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("reviewModal");
    const openBtn = document.getElementById("openReviewModal");
    const closeBtn = document.querySelector(".modal .close");

    if(openBtn && modal && closeBtn){
        // Abrir modal
        openBtn.addEventListener("click", () => {
            modal.style.display = "block";
        });

        // Cerrar modal con X
        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });

        window.addEventListener("click", (e) => {
            if(e.target === modal){
                modal.style.display = "none";
            }
        });

        const form = document.getElementById("reviewForm");
        const reviewsList = document.getElementById("reviewsList");

        form.addEventListener("submit", (e) => {
            e.preventDefault();
            const song = document.getElementById("searchSong").value || "Canción sin nombre";
            const rating = document.getElementById("rating").value;
            const comment = document.getElementById("comment").value;
            const date = new Date().toISOString().split('T')[0];

            const li = document.createElement("li");
            li.innerHTML = `<strong>🎵 ${song}</strong><br>
                            <span>${"★".repeat(rating) + "☆".repeat(5-rating)}</span><br>
                            <p>${comment}</p>
                            <small>Publicado el ${date}</small>
                            <button class="likeBtn">👍 0</button>`;
            reviewsList.appendChild(li);

            form.reset();
            modal.style.display = "none";

            li.querySelector(".likeBtn").addEventListener("click", function(){
                let count = parseInt(this.textContent.split(" ")[1]);
                count++;
                this.textContent = `👍 ${count}`;
            });
        });
    }
});
