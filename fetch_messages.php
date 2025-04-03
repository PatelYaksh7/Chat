<?php
session_start();
include('database.php');

if (!isset($_POST['sender']) || !isset($_POST['receiver'])) {
    exit();
}

$sender = mysqli_real_escape_string($conn, $_POST['sender']);
$receiver = mysqli_real_escape_string($conn, $_POST['receiver']);

$query = "SELECT * FROM messages WHERE 
          (sender = '$sender' AND receiver = '$receiver') 
          OR (sender = '$receiver' AND receiver = '$sender') 
          ORDER BY timestamp ASC";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $isSentByUser = ($row['sender'] == $sender);
    $messageStyle = $isSentByUser
        ? 'background: #DCF8C6; align-self: flex-end; text-align: right; border-top-right-radius: 0;'
        : 'background: #FFF; align-self: flex-start; text-align: left; border-top-left-radius: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1);';

    echo '<div style="max-width: 70%; padding: 8px 12px; border-radius: 10px; margin: 5px 0; word-wrap: break-word; font-size: 15px;' . $messageStyle . '">';
    
    // Message Text
    if (!empty($row['message'])) {
        echo '<p style="margin: 0; padding: 5px 0;">' . htmlspecialchars($row['message']) . '</p>';
    }

    // File Attachments
    if (!empty($row['image'])) {
        $fileName = htmlspecialchars($row['image']);
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $filePath = 'uploads/' . $fileName;
    
        // Supported file types
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $documentExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
    
        echo '<div style="margin-top: 5px; max-width: 100%; display: flex; flex-direction: column;">';
    
        if (in_array(strtolower($fileExt), $imageExtensions)) {
            // ✅ WhatsApp-style compact image display
            echo '<div style="max-width: 250px; display: inline-block; border-radius: 10px; overflow: hidden;">
                      <img src="' . $filePath . '" alt="Image"
                           style="max-width: 100%; height: auto; border-radius: 10px; display: block;">
                  </div>';
        } elseif (in_array(strtolower($fileExt), $documentExtensions)) {
            // ✅ Document Preview with Light Green Background
            echo '<div style="background: #DCF8C6; padding: 10px; border-radius: 10px; text-align: center;">
                    <span style="font-size: 14px; font-weight: 500; color: #333;">
                        📄 ' . $fileName . '
                    </span>
                    <a href="' . $filePath . '" download
                       style="display: inline-block; padding: 6px 12px; margin-top: 5px; 
                              background: #128C7E; color: white; border-radius: 20px; 
                              text-decoration: none; font-weight: 500;">
                        ⬇️ Download ' . strtoupper($fileExt) . '
                    </a>
                  </div>';
        }
    
        echo '</div>'; // Close file container
    }
    

    echo '</div>'; // Close message container
}
?>
