<?php

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Virtual Closet PLAYLISTS</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/app.css">
    <script src="<?php echo BASE_URL; ?>js/cursor-effect.js" defer></script>
</head>
<style>
    h1 {
        font-size: 140px;
        font-weight: 900;
        margin-top: 20px;
        color: #ff0000;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: -2px;
        line-height: 1;
    }

    p {
        color: #DA1E28;
        font-family: "Konkhmer Sleokchher";
        font-size: 21px;
        font-style: normal;
        font-weight: 400;
        line-height: 0;
        /* 0% */
        letter-spacing: 0.15px;
    }

    .containerLog {
        display: flex;
        width: 554px;
        height: 287px;
        padding: 47px 10px;
        flex-direction: column;
        align-items: center;
        gap: 53px;
    }

    h2 {
        color: #000;
        font-family: Milker;
        font-size: 30px;
        font-style: normal;
        font-weight: 400;
        line-height: 0;
        /* 0% */
        letter-spacing: 0.15px;
    }

    h5 {
        color: #000;
        text-align: center;
        font-family: "Konkhmer Sleokchher";
        font-size: 18px;
        font-style: normal;
        font-weight: 400;
        line-height: 28px;
        /* 155.556% */
        letter-spacing: 0.15px;
    }

    .containerLog {
        display: flex;
        width: 554px;
        height: 287px;
        padding: 47px 10px;
        flex-direction: column;
        align-items: center;
        gap: 53px;
        border-radius: 20px;
        border: 1px solid #F00;
    }

    .btnsLog a {
        display: flex;
        padding: 10px 21px;
        align-items: flex-start;
        gap: 19px;
    }
</style>

<body>
    <?php include_once __DIR__ . '/../views/layout/nav.php'; ?>


    <main>
        <div class="titlesong">
            <h1>PLAYLISTS</h1>
        </div>
        
    </main>
</body>

</html>