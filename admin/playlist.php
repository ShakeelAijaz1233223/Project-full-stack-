<?php
include 'db.php'; // Include database connection

// Fetch all music records
$sql = "SELECT * FROM music";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Playlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Styles */
        body {
            background-color: #2b2d42;
            color: #fff;
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin-top: 30px;
        }

        .playlist-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .song-card {
            background-color: #1f1f2e;
            margin: 10px;
            padding: 15px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1s ease-out;
        }

        .song-card:hover {
            transform: scale(1.05);
            transition: 0.3s;
        }

        .song-card h4 {
            margin: 10px 0;
            font-size: 18px;
        }

        .song-card .genre {
            font-size: 14px;
            color: #8c8c8c;
        }

        .play-btn, .delete-btn {
            background-color: #ff6f61;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            margin-top: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .play-btn:hover, .delete-btn:hover {
            background-color: #ff4b40;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        audio {
            width: 100%;
            margin-top: 20px;
        }

        .alert {
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Music Playlist</h1>

        <!-- Success/Error message -->
        <div id="message" class="alert alert-success" style="display: none;"></div>

        <div class="playlist-container">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='song-card' id='row_{$row['id']}'>
                            <h4>{$row['title']}</h4>
                            <p class='genre'>{$row['artist']} - {$row['genre']}</p>
                            <audio id='audio_{$row['id']}' src='uploads/{$row['audio_file']}' controls></audio>
                            <button class='play-btn' onclick='playMusic({$row['id']})'>Play</button>
                            <button class='delete-btn' onclick='deleteMusic({$row['id']})'>Delete</button>
                          </div>";
                }
            } else {
                echo "<p>No music found in your playlist.</p>";
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Play music function
        function playMusic(id) {
            const audioElement = document.getElementById(`audio_${id}`);
            audioElement.play();
        }

        // Delete function using AJAX
        function deleteMusic(id) {
            if (confirm('Are you sure you want to delete this song?')) {
                $.ajax({
                    url: 'delete_music.php',
                    type: 'POST',
                    data: { id: id },
                    success: function(response) {
                        const data = JSON.parse(response);
                        if (data.success) {
                            // Remove the song card from the playlist
                            $('#row_' + id).fadeOut();
                            // Show success message
                            $('#message').text(data.message).show().delay(3000).fadeOut();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>
