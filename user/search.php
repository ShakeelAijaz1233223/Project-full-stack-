<form method="GET" action="search.php">
    <input type="text" name="search" placeholder="Search media..." required>
    <button type="submit">Search</button>
</form>

<?php
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "SELECT * FROM media WHERE title LIKE '%$search%' OR artist LIKE '%$search%' OR genre LIKE '%$search%'";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div>" . $row['title'] . " by " . $row['artist'] . "</div>";
    }
}
?>
