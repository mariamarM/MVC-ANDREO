<h1><?= htmlspecialchars($song['title']) ?></h1>
<div class="song-details">
    <p><strong>Artista:</strong> <?= htmlspecialchars($song['artist']) ?></p>
    <p><strong>Álbum:</strong> <?= htmlspecialchars($song['album']) ?></p>
    <p><strong>Duración:</strong> <?= htmlspecialchars($song['duration']) ?></p>
    <p><strong>Género:</strong> <?= htmlspecialchars($song['genre']) ?></p>
</div>

<h2>Reviews</h2>
<?php if (empty($comments)): ?>
    <p>:( No se encontraron reviews </p>
<?php else: ?>
    <div class="comments">
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <p><strong><?= htmlspecialchars($comment['username']) ?>:</strong></p>
                <p><?= htmlspecialchars($comment['text']) ?></p>
                <small><?= $comment['created_at'] ?></small>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<a href="/" class="btn">Volver al inicio</a>