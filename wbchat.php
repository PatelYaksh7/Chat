<?php
session_start();
include('database.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$selectedUser = '';

if (isset($_GET['user']) && !empty($_GET['user'])) {
    $selectedUser = mysqli_real_escape_string($conn, $_GET['user']);
} else {
    die("Error: No user selected!");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - WhatsApp Style</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { background-color: #f0f2f5; }
        .chat-container {
            width: 100%; max-width: 90%; margin: 20px auto;
            background: white; border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden; display: flex; flex-direction: column; height: 600px;
        }
        .chat-header {
            display: flex; align-items: center; justify-content: space-between;
            background-color: #128C7E; color: white; padding: 15px;
        }
        .chat-body { flex-grow: 1; overflow-y: auto; padding: 15px; display: flex; flex-direction: column; gap: 10px; }
        .message { max-width: 75%; padding: 10px; border-radius: 8px; word-wrap: break-word; }
        .message.sent { align-self: flex-end; background-color: #dcf8c6; text-align: right; }
        .message.received { align-self: flex-start; background-color: #ffffff; text-align: left; }
        .chat-footer {
            display: flex; align-items: center; padding: 10px;
            background: #ffffff; border-top: 1px solid #ddd; position: relative;
        }
        .chat-footer input[type="text"] {
            flex-grow: 1; padding: 10px; border: 1px solid #ddd;
            border-radius: 20px; outline: none;
        }
        .chat-footer button {
            background-color: #128C7E; color: white; border: none;
            padding: 12px; border-radius: 50%; margin-left: 10px;
            cursor: pointer; font-size: 16px;
        }
        #file-preview-container {
            display: none; flex-direction: column; align-items: center;
            background: #fff; padding: 10px; border-radius: 10px; margin-bottom: 5px;
            position: relative; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        #file-preview {
            max-width: 100%; max-height: 200px; border-radius: 10px; margin-bottom: 5px;
            object-fit: contain; border: 1px solid #ddd;
        }
        #file-name { font-size: 14px; color: #555; }
        .file-upload { position: relative; width: 40px; height: 40px; }
        .file-upload input { position: absolute; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .file-upload .upload-icon {
            width: 40px; height: 40px; background-color: #128C7E; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 18px; cursor: pointer;
        }
        .close-preview {
            position: absolute; top: 5px; right: 5px; background: red;
            color: white; border: none; border-radius: 50%; width: 20px; height: 20px;
            font-size: 12px; text-align: center; cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h2>Chat with <?php echo ucfirst($selectedUser); ?></h2>
        </div>
        <div class="chat-body" id="chat-body"></div>
        
        <div id="file-preview-container">
            <button class="close-preview" onclick="clearFilePreview()">×</button>
            <img id="file-preview" src="" alt="Preview">
            <span id="file-name"></span>
        </div>

        <div class="chat-footer">
            <input type="hidden" id="sender" value="<?php echo $username; ?>">
            <input type="hidden" id="receiver" value="<?php echo $selectedUser; ?>">
            <div class="file-upload">
                <input type="file" id="file" accept="image/*, .pdf, .doc, .docx, .ppt, .pptx" onchange="previewFile()">
                <div class="upload-icon"><i class="fas fa-paperclip"></i></div>
            </div>
            <input type="text" id="message" placeholder="Type a message...">
            <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        function fetchMessages() {
            $.ajax({
                url: 'fetch_messages.php',
                type: 'POST',
                data: {
                    sender: $('#sender').val(),
                    receiver: $('#receiver').val()
                },
                success: function(data) {
                    $('#chat-body').html(data);
                    $('#chat-body').scrollTop($('#chat-body')[0].scrollHeight);
                }
            });
        }

        function previewFile() {
            var file = document.getElementById('file').files[0];
            if (file) {
                var fileName = file.name;
                var fileExt = fileName.split('.').pop().toLowerCase();
                $('#file-name').text(fileName);
                $('#file-preview-container').show();

                if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#file-preview').attr('src', e.target.result).show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#file-preview').hide();
                }
            }
        }

        function clearFilePreview() {
            $('#file').val('');
            $('#file-preview-container').hide();
        }

        function sendMessage() {
            var formData = new FormData();
            formData.append('sender', $('#sender').val());
            formData.append('receiver', $('#receiver').val());
            formData.append('message', $('#message').val().trim());

            if ($('#file')[0].files[0]) {
                formData.append('file', $('#file')[0].files[0]);
            }

            $.ajax({
                url: 'submit_message.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#message').val('');
                    clearFilePreview();
                    fetchMessages();
                }
            });
        }

        $(document).ready(function() { 
            fetchMessages();
            setInterval(fetchMessages, 3000);
        });
    </script>
</body>
</html>
