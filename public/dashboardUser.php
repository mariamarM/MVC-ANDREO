<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id'])) {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Access denied</title>
    <link rel="stylesheet" href="/css/app.css">
<link rel="stylesheet" 
      href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link rel="stylesheet" href="/views/css/views.css">
    <script src="/js/main.js" defer></script>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #cfcfcf;
            text-align: center;
            width: 360px;
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../views/layout/nav.php'; ?>

<div class="box">
    <h2>Get logged in</h2>
    <p>You need an account to access your dashboard.</p>

    <a href="<?php echo BASE_URL; ?>login.php">Log in</a>
    <a href="<?php echo BASE_URL; ?>register.php">Create account</a>
</div>

</body>
</html>
<?php
exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
   <link rel="stylesheet" 
      href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/views/css/views.css">
    <script src="/js/main.js" defer></script>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        main {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        section {
            margin-top: 30px;
        }
        h2 {
            font-size: 18px;
            color: #555;
            margin-bottom: 10px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../views/layout/nav.php'; ?>

<main>
    <h1>Bienvenido, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>!</h1>

    <section>
        <h2>Tus reviews recientes</h2>
        <ul>
<li><button id="openReviewModal" class="btn">Crear una review</button></li>

            
        </ul>
    </section>

    <section>
        <h2>Tus canciones recientes escuchadas</h2>
        <ul>
            <li>:(</li>
        </ul>
    </section>

<div id="reviewModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Crear una review</h2>
        
        <input type="text" id="searchSong" placeholder="Buscar canción..." style="width: 100%; padding:5px; margin-bottom:10px;">

        <form id="reviewForm">
            <label for="rating">Calificación:</label><br>
            <select id="rating" name="rating">
                <option value="1">★☆☆☆☆</option>
                <option value="2">★★☆☆☆</option>
                <option value="3">★★★☆☆</option>
                <option value="4">★★★★☆</option>
                <option value="5">★★★★★</option>
            </select><br><br>

            <label for="comment">Comentario:</label><br>
            <textarea id="comment" name="comment" rows="3" style="width:100%"></textarea><br><br>

            <button type="submit" class="btn">Publicar review</button>
        </form>
    </div>
</div>

</main>
<script>
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

</script>
</body>
</html>
