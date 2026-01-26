<?php
include 'db.php';  // Make sure this file contains the correct DB connection setup


// Check if 'id' is set in the URL and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $media_id = $_GET['id'];
} else {
    die('Invalid media ID.');
}

// Secure query to fetch media details using prepared statement
$query = "SELECT * FROM media WHERE media_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $media_id);  // "i" means integer
$stmt->execute();
$result = $stmt->get_result();
$media = $result->fetch_assoc();

// Check if media exists
if (!$media) {
    die('Media not found.');
}

// Reviews for this media
$reviews_query = "SELECT * FROM reviews WHERE media_id = ?";
$reviews_stmt = $conn->prepare($reviews_query);
$reviews_stmt->bind_param("i", $media_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// Handle adding a review
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        die('You need to log in to submit a review.');
    }

    $review = $_POST['review'];
    $rating = $_POST['rating'];
    $user_id = $_SESSION['user_id'];

    // Prepare and bind the insert statement
    $insert_review_query = "INSERT INTO reviews (user_id, media_id, review, rating) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_review_query);
    $insert_stmt->bind_param("iiis", $user_id, $media_id, $review, $rating);

    // Execute and provide feedback
    if ($insert_stmt->execute()) {
        echo "Review added!";
    } else {
        echo "Error: " . $insert_stmt->error;
    }
}
?>

<h1><?php echo htmlspecialchars($media['title']); ?></h1>
<p>Artist: <?php echo htmlspecialchars($media['artist']); ?></p>
<p>Album: <?php echo htmlspecialchars($media['album']); ?></p>
<p>Year: <?php echo htmlspecialchars($media['year']); ?></p>
<p><?php echo nl2br(htmlspecialchars($media['description'])); ?></p>

<h2>Reviews</h2>
<?php while ($review = $reviews_result->fetch_assoc()) { ?>
    <div>
        <p><strong>User:</strong> <?php echo htmlspecialchars($review['user_id']); ?></p>
        <p><strong>Rating:</strong> <?php echo htmlspecialchars($review['rating']); ?> / 5</p>
        <p><strong>Review:</strong> <?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
    </div>
<?php } ?>

<h3>Leave a Review</h3>
<form method="POST" action="media_detail.php?id=<?php echo $media_id; ?>">
    <textarea name="review" placeholder="Write your review..." required></textarea>
    <select name="rating" required>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
    </select>
    <button type="submit">Submit Review</button>
</form>
