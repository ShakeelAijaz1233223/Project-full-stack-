<?php
include 'db.php';

/* FETCH ONLY PUBLIC MUSIC */
$sql = "SELECT * FROM music WHERE status='public' ORDER BY id DESC";
$res = mysqli_query($conn_user, "
    SELECT * FROM music 
    WHERE status='public' 
    ORDER BY created_at DESC
");

if (!$res) {
    die("Database Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Music Gallery</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">

    <input type="text" id="search" class="search-box" placeholder="🔍 Search music">

    <div class="grid" id="musicGrid">

        <?php
        if (mysqli_num_rows($res) == 0) {
            echo "<p>No music available.</p>";
        }

        while ($row = mysqli_fetch_assoc($res)) {
            $title  = htmlspecialchars($row['title']);
            $artist = htmlspecialchars($row['artist']);
            $file   = htmlspecialchars($row['file']);
        ?>

      <div class="card" data-search="<?= strtolower($title.' '.$artist) ?>">
    <audio controls>
        <source src="uploads/music/<?= $file ?>" type="audio/mpeg">
        Your browser does not support audio.
    </audio>
    <div class="title"><?= $title ?></div>
    <div class="artist"><?= $artist ?></div>
</div>

        <?php } ?>

    </div>
</div>

<script>
document.getElementById("search").addEventListener("keyup", function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll(".card").forEach(card => {
        card.style.display = card.dataset.search.includes(value) ? "block" : "none";
    });
});






</script>

</body>
</html>
