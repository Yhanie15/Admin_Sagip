<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Video Call</title>
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f0f0f0;
        }
        #remote-video {
            width: 80%;
            height: 60%;
            background-color: black;
        }
        #controls {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        button.end-call {
            background-color: #dc3545;
        }
        button:disabled {
            background-color: #cccccc;
        }
    </style>
</head>
<body>

<div id="remote-video"></div>
<div id="controls">
    <button id="muteAudio">Mute Audio</button>
    <button id="endCall" class="end-call">End Call</button>
</div>

<script>
    const APP_ID = '9a8a11d5ff0a4f388d69ff7b5f803392';
    const TOKEN = '007eJxTYJgfd/JG6kOeV4rvzVX/b8650Zk4z22lxivBLR579vUUs71QYLBMtEg0NEwxTUszSDRJM7awSDGzTEszTzJNszAwNrY0OliQkt4QyMhg8DqKlZEBAkF8HobixPTMgvjizOycxCQGBgAAnCRh';
    const CHANNEL_NAME = 'sagip_siklab';

    const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

    let remoteStream;
    let localAudioTrack;
    let isMuted = false;

    // Join the channel
    async function joinChannel() {
        try {
            await client.join(APP_ID, CHANNEL_NAME, TOKEN, null);

            // Listen for remote user joining
            client.on("user-published", async (user, mediaType) => {
                await client.subscribe(user, mediaType);

                if (mediaType === "video") {
                    const remoteVideoTrack = user.videoTrack;
                    remoteVideoTrack.play("remote-video");
                }

                if (mediaType === "audio") {
                    const remoteAudioTrack = user.audioTrack;
                    remoteAudioTrack.play();
                }
            });

            // Listen for remote user leaving
            client.on("user-unpublished", (user) => {
                document.getElementById("remote-video").innerHTML = "";
            });

            // Publish local audio track
            localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
            await client.publish([localAudioTrack]);
        } catch (error) {
            console.error("Failed to join channel:", error);
        }
    }

    // Mute/Unmute audio
    document.getElementById("muteAudio").addEventListener("click", () => {
        isMuted = !isMuted;
        localAudioTrack.setEnabled(!isMuted);
        document.getElementById("muteAudio").textContent = isMuted ? "Unmute Audio" : "Mute Audio";
    });

    // End call
    document.getElementById("endCall").addEventListener("click", async () => {
        await client.leave();
        localAudioTrack.close();
        document.getElementById("remote-video").innerHTML = "";
        alert("Call ended.");
    });

    // Initialize the call on page load
    joinChannel();
</script>

</body>
</html>
