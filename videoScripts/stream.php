<?php
header("Content-Type: multipart/x-mixed-replace; boundary=ffserver");

// Path to webcam device (Linux: `/dev/video0`, Windows might need `dshow` instead)
$webcamDevice = "/dev/video0";

// Check if ffmpeg is running, if not, start it
exec("pgrep -f 'ffmpeg -f v4l2 -i $webcamDevice'", $output, $status);

if ($status !== 0) {
    // Start ffmpeg to stream webcam
    $command = "ffmpeg -f v4l2 -i $webcamDevice -vf 'scale=640:480' -r 30 -f mjpeg -q:v 5 -";
    passthru($command);
} else {
    echo "Streaming already running.";
}
?>
