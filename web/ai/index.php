<html>
<head>
    <title>InCallytics</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/style.css" media="all">
    <link rel="stylesheet" href="../css/dia.css" media="all">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="Description" content="InCallytics">
    <meta http-equiv="Content-language" content="ru-RU">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Roboto&display=swap" rel="stylesheet">
</head>
<body>
<div class="wrapper">
    <div class="sideBar">
        <div class="menu">
            <a href="/"><img class="icon" src="/images/speedometer.svg" alt="Главная"></a>
            <a href="/talks"><img class="icon" src="/images/telephone-inbound-fill.svg" alt="Стенограммы"></a>
            <a href="/scripts"><img class="icon" src="/images/file-earmark-text-fill.svg" alt="Скрипты"></a>
            <a href="/ai"><img class="icon" src="/images/stars.svg" alt="AI-помощник"></a>
        </div>
    </div>
    <div class="mheader">
        <a href="/"><img class="icon" src="/images/speedometer.svg" alt="Главная"></a>
        <a href="/talks"><img class="icon" src="/images/telephone-inbound-fill.svg" alt="Стенограммы"></a>
        <a href="/scripts"><img class="icon" src="/images/file-earmark-text-fill.svg" alt="Скрипты"></a>
        <a href="/ai"><img class="icon" src="/images/stars.svg" alt="AI-помощник"></a>
    </div>
    <div class="page">
        <div class="titleFlex">
            <div class="title">Тестирование AI-помощника</div>
        </div>
        <div class="content">
            <div>
                <div class="controls">
                    <div class="circle-button s0" onclick="startRecording(0)">0</div>
                    <div class="circle-button s1" onclick="startRecording(1)">1</div>
                </div>
                <div id="chat" class="script"></div>
                <div style="display: flex; justify-content: center; margin-top: 20px;"><button class="end-button" onclick="endDialog()">Завершить диалог</button></div>

                <script>
                    let mediaRecorder;
                    let chunks = [];
                    let currentSpeaker = null;
                    let recordingInterval;
                    let messageInterval;

                    async function initMediaRecorder() {
                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                            mediaRecorder = new MediaRecorder(stream);

                            mediaRecorder.ondataavailable = event => {
                                chunks.push(event.data);
                            };

                            mediaRecorder.onstop = () => {
                                if (chunks.length) {
                                    const blob = new Blob(chunks, { 'type': 'audio/wav' });
                                    chunks = [];
                                    const formData = new FormData();
                                    formData.append('voice', blob);
                                    fetch(`https://psai.tw1.su/ai/sendWav.php?speaker=${currentSpeaker}`, {
                                        method: 'POST',
                                        body: formData,
                                        credentials: 'include'
                                    })
                                        .then(response => response.text())
                                        .then(data => console.log(data))
                                        .catch(error => console.error('Error:', error));
                                }
                            };
                        } catch (error) {
                            console.error('Error accessing microphone:', error);
                        }
                    }

                    function startRecording(speaker) {
                        if (!mediaRecorder) {
                            console.error('MediaRecorder not initialized');
                            return;
                        }

                        if (currentSpeaker !== speaker) {
                            clearInterval(recordingInterval);
                            mediaRecorder.stop();
                        }

                        currentSpeaker = speaker;

                        // Обновление активного спикера
                        document.querySelectorAll('.circle-button').forEach(button => {
                            button.classList.remove('active');
                        });
                        document.querySelector(`.circle-button.s${speaker}`).classList.add('active');

                        if (mediaRecorder.state === 'inactive') {
                            mediaRecorder.start();
                            recordingInterval = setInterval(() => {
                                mediaRecorder.stop();
                                mediaRecorder.start();
                            }, 10000);
                        }
                    }

                    function endRecording() {
                        clearInterval(recordingInterval);
                        if (mediaRecorder && mediaRecorder.state === 'recording') {
                            mediaRecorder.stop();
                        }
                    }

                    function formatTimestamp(timestamp) {
                        const date = new Date(timestamp * 1000);
                        const hours = date.getHours().toString().padStart(2, '0');
                        const minutes = date.getMinutes().toString().padStart(2, '0');
                        return `${hours}:${minutes}`;
                    }

                    function fetchMessages() {
                        fetch('https://psai.tw1.su/ai/getMessages.php', { credentials: 'include' })
                            .then(response => response.json())
                            .then(data => {
                                const chat = document.getElementById('chat');
                                chat.innerHTML = '';
                                data.chunks.forEach(chunk => {
                                    const message = document.createElement('div');
                                    message.className = `message s${chunk.speaker}`;
                                    message.innerHTML = `
                                        <span class="avatar">${chunk.speaker}</span>
                                        <span class="text">${chunk.text}</span>
                                        <span class="timestamp">${formatTimestamp(chunk.timestamp)}</span>
                                    `;
                                    chat.appendChild(message);
                                });
                            })
                            .catch(error => console.error('Error fetching messages:', error));
                    }

                    function endDialog() {
                        endRecording();
                        clearInterval(messageInterval); // Остановить получение сообщений
                        fetch('https://psai.tw1.su/ai/end.php', { credentials: 'include' })
                            .then(response => response.json())
                            .then(data => {
                                if (data.id) {
                                    window.location.href = `https://psai.tw1.su/talks/talk.php?id=${data.id}`;
                                } else {
                                    alert('Ошибка завершения диалога');
                                }
                            })
                            .catch(error => console.error('Error ending dialog:', error));
                    }

                    window.onload = () => {
                        initMediaRecorder();
                        messageInterval = setInterval(fetchMessages, 3000);
                    };
                </script>
            </div>
            <div class="blockinfo">
                <p class="info-title">
                    1. Выберите роль, которую вы озвучиваете.<br>
                    2. Говорите, ваша речь автоматически транскрибируется в текст.<br>
                    3. Получайте подсказки от AI-помощника.<br>
                    4. Завершите диалог и он автоматически сохранится.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
