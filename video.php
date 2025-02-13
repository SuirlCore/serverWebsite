<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webcam Stream</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }
        img { width: 640px; height: 480px; border: 2px solid #000; }
        button { margin: 10px; padding: 10px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Live Webcam Feed</h2>
    <img id="webcamFeed" src="" alt="Webcam Feed">
    <br>
    <button onclick="startStream()">Start Stream</button>
    <button onclick="stopStream()">Stop Stream</button>

    <script>
        function startStream() {
            document.getElementById("webcamFeed").src = "stream.php";
        }

        function stopStream() {
            document.getElementById("webcamFeed").src = "";
        }
    </script>
</body>
</html>
