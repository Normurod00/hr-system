<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $meeting->title }} | Видеовстреча</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    @vite(['resources/js/app.js'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --brb-red: #E52716;
            --bg-dark: #0f0f1a;
            --bg-darker: #0a0a12;
            --bg-card: #1a1a2e;
            --bg-hover: #252542;
            --text-primary: #ffffff;
            --text-secondary: #8888a0;
            --border: rgba(255,255,255,0.08);
            --success: #22c55e;
            --warning: #f59e0b;
            --accent: #E52716;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            height: 100vh;
            overflow: hidden;
        }

        /* Layout */
        .room-container {
            display: grid;
            grid-template-columns: 1fr 360px;
            height: 100vh;
        }

        /* Main Video Area */
        .video-section {
            display: flex;
            flex-direction: column;
            background: var(--bg-darker);
            position: relative;
        }

        .video-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
        }
        .meeting-info h1 {
            font-size: 1.1rem;
            font-weight: 600;
        }
        .meeting-info span {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .timer {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.05);
            padding: 10px 20px;
            border-radius: 30px;
        }
        .timer-dot {
            width: 10px;
            height: 10px;
            background: var(--accent);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        .timer-value {
            font-size: 1.1rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .video-grid {
            flex: 1;
            display: grid;
            gap: 16px;
            padding: 24px;
            grid-template-columns: 1fr;
            align-content: center;
        }
        .video-grid.grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }
        .video-grid.grid-3,
        .video-grid.grid-4 {
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
        }
        .video-grid.grid-5,
        .video-grid.grid-6 {
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
        }

        .video-tile {
            position: relative;
            background: var(--bg-card);
            border-radius: 20px;
            overflow: hidden;
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .video-tile video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .video-tile.local-pip {
            position: absolute;
            bottom: 120px;
            right: 24px;
            width: 280px;
            height: 158px;
            z-index: 50;
            border: 3px solid var(--bg-card);
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            aspect-ratio: auto;
        }
        .video-label {
            position: absolute;
            bottom: 16px;
            left: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            border-radius: 10px;
        }
        .video-label span {
            font-size: 0.9rem;
            font-weight: 500;
        }
        .video-label .muted-icon {
            color: var(--accent);
        }

        .video-placeholder {
            text-align: center;
            color: var(--text-secondary);
        }
        .video-placeholder .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #ff6b5b);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 2.5rem;
            color: white;
        }
        .video-placeholder p {
            font-size: 1rem;
        }

        /* Controls */
        .controls-bar {
            display: flex;
            justify-content: center;
            gap: 16px;
            padding: 20px;
            background: var(--bg-card);
            border-top: 1px solid var(--border);
        }
        .control-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            background: var(--bg-hover);
            color: white;
            font-size: 1.3rem;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        .control-btn:hover {
            background: rgba(255,255,255,0.15);
            transform: scale(1.05);
        }
        .control-btn.active {
            background: var(--accent);
        }
        .control-btn.end-call {
            background: var(--accent);
            width: 70px;
            height: 70px;
            font-size: 1.5rem;
        }
        .control-btn.end-call:hover {
            background: #c41e0a;
        }

        /* Sidebar */
        .sidebar {
            background: var(--bg-card);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-header h2 {
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-header h2 i {
            color: var(--accent);
        }
        .participants-list {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }
        .participant-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: var(--bg-hover);
            border-radius: 14px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .participant-card:hover {
            background: rgba(255,255,255,0.08);
        }
        .participant-card .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #ff6b5b);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            flex-shrink: 0;
        }
        .participant-card .info {
            flex: 1;
            min-width: 0;
        }
        .participant-card .name {
            font-weight: 500;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .participant-card .status {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        .participant-card .status.online {
            color: var(--success);
        }
        .participant-card .status.waiting {
            color: var(--warning);
        }
        .participant-card .icons {
            display: flex;
            gap: 8px;
            color: var(--text-secondary);
        }
        .participant-card .icons i.off {
            color: var(--accent);
        }

        /* Waiting Request Card */
        .waiting-card {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 16px;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .waiting-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .waiting-card-header .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--warning);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }
        .waiting-card-header .info h4 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .waiting-card-header .info p {
            font-size: 0.8rem;
            color: var(--warning);
        }
        .waiting-card-actions {
            display: flex;
            gap: 10px;
        }
        .waiting-card-actions button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-accept {
            background: var(--success);
            color: white;
        }
        .btn-accept:hover {
            background: #16a34a;
        }
        .btn-reject {
            background: rgba(255,255,255,0.1);
            color: var(--text-secondary);
        }
        .btn-reject:hover {
            background: var(--accent);
            color: white;
        }

        /* Notifications */
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--bg-card);
            border: 1px solid var(--border);
            padding: 16px 24px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 14px;
            z-index: 1000;
            animation: slideDown 0.3s ease;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
        .notification.success { border-left: 4px solid var(--success); }
        .notification.warning { border-left: 4px solid var(--warning); }
        .notification.error { border-left: 4px solid var(--accent); }

        @media (max-width: 1024px) {
            .room-container { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .video-tile.local-pip { width: 160px; height: 90px; bottom: 100px; right: 16px; }
        }
    </style>
</head>
<body>
    <div class="room-container">
        <div class="video-section">
            <div class="video-header">
                <div class="meeting-info">
                    <h1>{{ $meeting->title }}</h1>
                    <span id="participantCount">{{ $meeting->participants->count() }} участников</span>
                </div>
                <div class="timer">
                    <div class="timer-dot"></div>
                    <span class="timer-value" id="timer">00:00</span>
                </div>
            </div>

            <div class="video-grid" id="videoGrid">
                <!-- Remote video tiles will be dynamically added here -->
                <div class="video-tile" id="waitingTile">
                    <div class="video-placeholder">
                        <div class="avatar">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <p>Ожидание участников...</p>
                    </div>
                </div>
            </div>

            <!-- Local video (picture-in-picture) -->
            <div class="video-tile local-pip" id="localVideoTile">
                <video id="localVideo" autoplay muted playsinline></video>
                <div class="video-label">
                    <span>Вы</span>
                    <i class="fa-solid fa-microphone-slash muted-icon" id="localMutedIcon" style="display: none;"></i>
                </div>
            </div>

            <div class="controls-bar">
                <button class="control-btn" id="toggleMute" title="Микрофон">
                    <i class="fa-solid fa-microphone" id="muteIcon"></i>
                </button>
                <button class="control-btn" id="toggleVideo" title="Камера">
                    <i class="fa-solid fa-video" id="videoIcon"></i>
                </button>
                <button class="control-btn" id="toggleScreen" title="Демонстрация экрана">
                    <i class="fa-solid fa-display"></i>
                </button>
                <button class="control-btn end-call" id="endCall" title="Завершить">
                    <i class="fa-solid fa-phone-slash"></i>
                </button>
            </div>
        </div>

        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-users"></i> Участники</h2>
            </div>
            <div class="participants-list">
                <div id="waitingRequests"></div>
                <div id="participantsCards"></div>
            </div>
        </div>
    </div>

    <script>
    const MEETING_ID = @json($meeting->id);
    const USER_ID = @json(auth()->id());
    const USER_NAME = @json(auth()->user()->name);
    const IS_HOST = @json($meeting->isHost(auth()->user()));
    const ICE_SERVERS = @json($iceServers);
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    class VideoCall {
        constructor() {
            this.localStream = null;
            this.screenStream = null;
            this.peerConnections = {};
            this.remoteStreams = {};
            this.remoteNames = {};
            this.isMuted = false;
            this.isVideoOff = false;
            this.isScreenSharing = false;
            this.startTime = Date.now();
            this.pollingInterval = null;
            this.participantsInterval = null;
            this.waitingUsers = new Map();
            this.isPolling = false;

            this.init();
        }

        async init() {
            try {
                await this.getLocalMedia();
                this.setupControls();
                this.startTimer();
                this.startPolling();
                this.showNotification('Вы присоединились к встрече', 'success');
            } catch (error) {
                console.error('Init error:', error);
                this.showNotification('Ошибка: ' + error.message, 'error');
            }
        }

        async getLocalMedia() {
            try {
                this.localStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: { echoCancellation: true, noiseSuppression: true }
                });
                document.getElementById('localVideo').srcObject = this.localStream;
            } catch (e) {
                console.warn('Camera unavailable, trying audio only:', e);
                try {
                    this.localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    this.isVideoOff = true;
                    document.getElementById('videoIcon').className = 'fa-solid fa-video-slash';
                    document.getElementById('toggleVideo').classList.add('active');
                } catch (e2) {
                    throw new Error('Не удалось получить доступ к камере и микрофону');
                }
            }
        }

        createPeerConnection(userId, userName) {
            if (this.peerConnections[userId]) {
                return this.peerConnections[userId];
            }

            const pc = new RTCPeerConnection({ iceServers: ICE_SERVERS });

            if (this.localStream) {
                this.localStream.getTracks().forEach(track => {
                    pc.addTrack(track, this.localStream);
                });
            }

            pc.onicecandidate = (e) => {
                if (e.candidate) {
                    this.sendSignal('ice-candidate', { candidate: e.candidate.toJSON() }, userId);
                }
            };

            pc.ontrack = (e) => {
                if (!this.remoteStreams[userId]) {
                    this.remoteStreams[userId] = new MediaStream();
                }
                this.remoteStreams[userId].addTrack(e.track);
                this.remoteNames[userId] = userName || 'Участник';
                this.updateVideoGrid();
            };

            pc.onconnectionstatechange = () => {
                console.log(`Peer ${userId} connection state: ${pc.connectionState}`);
                if (pc.connectionState === 'failed') {
                    console.warn(`Connection to ${userId} failed, attempting restart`);
                    this.restartConnection(userId, userName);
                } else if (pc.connectionState === 'disconnected') {
                    // Wait a bit before treating as left — may reconnect
                    setTimeout(() => {
                        if (this.peerConnections[userId] &&
                            this.peerConnections[userId].connectionState === 'disconnected') {
                            this.handleUserLeft(userId);
                        }
                    }, 5000);
                }
            };

            pc.oniceconnectionstatechange = () => {
                console.log(`Peer ${userId} ICE state: ${pc.iceConnectionState}`);
            };

            this.peerConnections[userId] = pc;
            return pc;
        }

        async restartConnection(userId, userName) {
            // Close old connection
            if (this.peerConnections[userId]) {
                this.peerConnections[userId].close();
                delete this.peerConnections[userId];
            }
            delete this.remoteStreams[userId];

            // Recreate
            try {
                await this.createOffer(userId, userName);
            } catch (e) {
                console.error('Restart connection failed:', e);
                this.handleUserLeft(userId);
            }
        }

        async createOffer(userId, userName) {
            const pc = this.createPeerConnection(userId, userName);
            try {
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                this.sendSignal('offer', { sdp: pc.localDescription.toJSON(), name: USER_NAME }, userId);
            } catch (e) {
                console.error('Create offer error:', e);
            }
        }

        async handleOffer(senderId, data) {
            const pc = this.createPeerConnection(senderId, data.name || 'Участник');

            // Handle glare: if we already have a local description, compare IDs
            if (pc.signalingState !== 'stable' && pc.signalingState !== 'have-local-offer') {
                console.warn('Unexpected signaling state for offer:', pc.signalingState);
            }

            // If we're in have-local-offer state (glare), the lower ID yields
            if (pc.signalingState === 'have-local-offer') {
                if (USER_ID > senderId) {
                    // We yield — rollback and accept the offer
                    await pc.setLocalDescription({ type: 'rollback' });
                } else {
                    // They should yield — ignore their offer
                    return;
                }
            }

            try {
                await pc.setRemoteDescription(new RTCSessionDescription(data.sdp));
                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                this.sendSignal('answer', { sdp: pc.localDescription.toJSON(), name: USER_NAME }, senderId);
            } catch (e) {
                console.error('Handle offer error:', e);
            }
        }

        async handleAnswer(senderId, data) {
            const pc = this.peerConnections[senderId];
            if (!pc) return;

            try {
                if (pc.signalingState === 'have-local-offer') {
                    await pc.setRemoteDescription(new RTCSessionDescription(data.sdp));
                }
            } catch (e) {
                console.error('Handle answer error:', e);
            }
        }

        async handleIceCandidate(senderId, data) {
            const pc = this.peerConnections[senderId];
            if (pc && data.candidate) {
                try {
                    await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
                } catch (e) {
                    console.error('Add ICE candidate error:', e);
                }
            }
        }

        // ========== Video Grid Management ==========

        updateVideoGrid() {
            const grid = document.getElementById('videoGrid');
            const remoteUserIds = Object.keys(this.remoteStreams);
            const waitingTile = document.getElementById('waitingTile');

            if (remoteUserIds.length === 0) {
                // No remote streams — show waiting placeholder
                if (!waitingTile) {
                    grid.innerHTML = `
                        <div class="video-tile" id="waitingTile">
                            <div class="video-placeholder">
                                <div class="avatar"><i class="fa-solid fa-users"></i></div>
                                <p>Ожидание участников...</p>
                            </div>
                        </div>`;
                }
                grid.className = 'video-grid';
                return;
            }

            // Remove waiting tile
            if (waitingTile) {
                waitingTile.remove();
            }

            // Update grid class based on participant count
            const count = remoteUserIds.length;
            grid.className = 'video-grid';
            if (count >= 5) grid.classList.add('grid-6');
            else if (count >= 3) grid.classList.add('grid-4');
            else if (count === 2) grid.classList.add('grid-2');
            // count === 1: default single column

            // Add new tiles, update existing
            remoteUserIds.forEach(userId => {
                let tile = document.getElementById('remote-tile-' + userId);
                if (!tile) {
                    tile = document.createElement('div');
                    tile.className = 'video-tile';
                    tile.id = 'remote-tile-' + userId;

                    const video = document.createElement('video');
                    video.autoplay = true;
                    video.playsInline = true;
                    video.id = 'remote-video-' + userId;
                    tile.appendChild(video);

                    const label = document.createElement('div');
                    label.className = 'video-label';
                    label.innerHTML = `<span>${escapeHtml(this.remoteNames[userId] || 'Участник')}</span>
                        <i class="fa-solid fa-microphone-slash muted-icon" id="remote-muted-${userId}" style="display:none;"></i>`;
                    tile.appendChild(label);

                    grid.appendChild(tile);
                }

                const video = document.getElementById('remote-video-' + userId);
                if (video && video.srcObject !== this.remoteStreams[userId]) {
                    video.srcObject = this.remoteStreams[userId];
                }
            });

            // Remove tiles for disconnected users
            grid.querySelectorAll('.video-tile[id^="remote-tile-"]').forEach(tile => {
                const id = tile.id.replace('remote-tile-', '');
                if (!this.remoteStreams[id]) {
                    tile.remove();
                }
            });
        }

        handleUserLeft(userId) {
            if (this.peerConnections[userId]) {
                this.peerConnections[userId].close();
                delete this.peerConnections[userId];
            }
            delete this.remoteStreams[userId];
            delete this.remoteNames[userId];

            // Remove video tile
            const tile = document.getElementById('remote-tile-' + userId);
            if (tile) tile.remove();

            this.updateVideoGrid();
            this.showNotification('Участник покинул встречу', 'warning');
        }

        // ========== Waiting Requests (Host) ==========

        showWaitingRequest(userId, userName) {
            if (this.waitingUsers.has(userId)) return;
            this.waitingUsers.set(userId, userName);

            const container = document.getElementById('waitingRequests');
            const card = document.createElement('div');
            card.className = 'waiting-card';
            card.id = 'waiting-' + userId;
            card.innerHTML = `
                <div class="waiting-card-header">
                    <div class="avatar">${escapeHtml(userName.charAt(0).toUpperCase())}</div>
                    <div class="info">
                        <h4>${escapeHtml(userName)}</h4>
                        <p>Хочет присоединиться</p>
                    </div>
                </div>
                <div class="waiting-card-actions">
                    <button class="btn-accept" data-user-id="${userId}" data-user-name="${escapeHtml(userName)}">
                        <i class="fa-solid fa-check"></i> Принять
                    </button>
                    <button class="btn-reject" data-user-id="${userId}">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            `;
            card.querySelector('.btn-accept').addEventListener('click', function() {
                videoCall.acceptUser(this.dataset.userId, this.dataset.userName);
            });
            card.querySelector('.btn-reject').addEventListener('click', function() {
                videoCall.rejectUser(this.dataset.userId);
            });
            container.appendChild(card);

            this.playNotificationSound();
            this.showNotification(`${escapeHtml(userName)} хочет присоединиться`, 'warning');
        }

        async acceptUser(userId, userName) {
            const card = document.getElementById('waiting-' + userId);
            if (card) card.remove();
            this.waitingUsers.delete(userId);

            await this.createOffer(userId, userName);
            this.showNotification(`${escapeHtml(userName)} присоединился`, 'success');
        }

        rejectUser(userId) {
            const card = document.getElementById('waiting-' + userId);
            if (card) card.remove();
            this.waitingUsers.delete(userId);
        }

        // ========== Signaling ==========

        async sendSignal(type, data, recipientId = null) {
            try {
                await fetch(`/admin/meetings/${MEETING_ID}/signal`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({ type, data, recipient_id: recipientId })
                });
            } catch (e) {
                console.error('Signal send error:', e);
            }
        }

        async pollSignals() {
            if (this.isPolling) return;
            this.isPolling = true;

            try {
                const res = await fetch(`/admin/meetings/${MEETING_ID}/signals`);
                if (!res.ok) return;

                const { signals } = await res.json();

                for (const sig of signals) {
                    try {
                        switch (sig.type) {
                            case 'join-request':
                                if (IS_HOST) {
                                    this.showWaitingRequest(sig.sender_id, sig.data.name);
                                }
                                break;
                            case 'offer':
                                await this.handleOffer(sig.sender_id, sig.data);
                                break;
                            case 'answer':
                                await this.handleAnswer(sig.sender_id, sig.data);
                                break;
                            case 'ice-candidate':
                                await this.handleIceCandidate(sig.sender_id, sig.data);
                                break;
                        }
                    } catch (e) {
                        console.error('Signal processing error:', sig.type, e);
                    }
                }
            } catch (e) {
                console.error('Poll error:', e);
            } finally {
                this.isPolling = false;
            }
        }

        async loadParticipants() {
            try {
                const res = await fetch(`/admin/meetings/${MEETING_ID}/participants`);
                if (!res.ok) return;

                const { participants } = await res.json();

                // Update only the participants cards container (not the waiting requests)
                const cardsContainer = document.getElementById('participantsCards');
                cardsContainer.innerHTML = participants.map(p => `
                    <div class="participant-card">
                        <div class="avatar">${escapeHtml(p.name.charAt(0).toUpperCase())}</div>
                        <div class="info">
                            <div class="name">${escapeHtml(p.name)}${p.id === USER_ID ? ' (Вы)' : ''}</div>
                            <div class="status ${p.status === 'joined' ? 'online' : ''}">${p.status === 'joined' ? 'В сети' : 'Приглашен'}</div>
                        </div>
                        <div class="icons">
                            <i class="fa-solid fa-microphone${p.is_muted ? '-slash off' : ''}"></i>
                            <i class="fa-solid fa-video${p.is_video_off ? '-slash off' : ''}"></i>
                        </div>
                    </div>
                `).join('');

                // Update header counter
                const joinedCount = participants.filter(p => p.status === 'joined').length;
                document.getElementById('participantCount').textContent = joinedCount + ' участников в сети';

            } catch (e) {
                console.error('Participants load error:', e);
            }
        }

        startPolling() {
            // If not host, send join request
            if (!IS_HOST) {
                this.sendSignal('join-request', { name: USER_NAME });
            }

            // Try WebSocket first, fall back to polling
            this.useWebSocket = false;
            if (typeof Echo !== 'undefined') {
                try {
                    this.setupWebSocket();
                    this.useWebSocket = true;
                    console.log('Using WebSocket for signaling');
                } catch (e) {
                    console.warn('WebSocket unavailable, falling back to polling:', e);
                }
            }

            if (!this.useWebSocket) {
                console.log('Using polling for signaling');
                this.pollingInterval = setInterval(() => this.pollSignals(), 1000);
            }

            // Poll participants (less critical, keep polling at lower rate)
            this.participantsInterval = setInterval(() => this.loadParticipants(), 5000);

            // Initial load
            if (!this.useWebSocket) this.pollSignals();
            this.loadParticipants();
        }

        setupWebSocket() {
            const channel = Echo.join(`meeting.${MEETING_ID}`);

            // WebRTC signals via WebSocket (instant)
            channel.listen('.webrtc.signal', async (e) => {
                if (e.senderId === USER_ID) return;
                if (e.recipientId && e.recipientId !== USER_ID) return;

                try {
                    switch (e.type) {
                        case 'join-request':
                            if (IS_HOST) this.showWaitingRequest(e.senderId, e.data.name);
                            break;
                        case 'offer':
                            await this.handleOffer(e.senderId, e.data);
                            break;
                        case 'answer':
                            await this.handleAnswer(e.senderId, e.data);
                            break;
                        case 'ice-candidate':
                            await this.handleIceCandidate(e.senderId, e.data);
                            break;
                    }
                } catch (err) {
                    console.error('WebSocket signal error:', e.type, err);
                }
            });

            // Participant events
            channel.listen('.participant.joined', (e) => {
                if (e.userId !== USER_ID) {
                    this.showNotification(`${escapeHtml(e.userName)} присоединился`, 'success');
                    this.loadParticipants();
                }
            });

            channel.listen('.participant.left', (e) => {
                if (e.userId !== USER_ID) {
                    this.handleUserLeft(e.userId);
                    this.loadParticipants();
                }
            });

            channel.listen('.meeting.ended', () => {
                this.showNotification('Встреча завершена организатором', 'warning');
                setTimeout(() => { window.location.href = '/admin/meetings'; }, 2000);
            });

            // Presence: who's here
            channel.here((users) => {
                console.log('Users in meeting:', users);
            });
        }

        startTimer() {
            setInterval(() => {
                const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
                const h = Math.floor(elapsed / 3600);
                const m = Math.floor((elapsed % 3600) / 60).toString().padStart(2, '0');
                const s = (elapsed % 60).toString().padStart(2, '0');
                document.getElementById('timer').textContent = h > 0 ? `${h}:${m}:${s}` : `${m}:${s}`;
            }, 1000);
        }

        // ========== Controls ==========

        setupControls() {
            document.getElementById('toggleMute').addEventListener('click', () => {
                this.isMuted = !this.isMuted;
                if (this.localStream) {
                    this.localStream.getAudioTracks().forEach(t => t.enabled = !this.isMuted);
                }

                document.getElementById('muteIcon').className =
                    this.isMuted ? 'fa-solid fa-microphone-slash' : 'fa-solid fa-microphone';
                document.getElementById('toggleMute').classList.toggle('active', this.isMuted);
                document.getElementById('localMutedIcon').style.display = this.isMuted ? 'inline' : 'none';

                this.notifyMediaChange('mute');
            });

            document.getElementById('toggleVideo').addEventListener('click', () => {
                this.isVideoOff = !this.isVideoOff;
                if (this.localStream) {
                    this.localStream.getVideoTracks().forEach(t => t.enabled = !this.isVideoOff);
                }

                document.getElementById('videoIcon').className =
                    this.isVideoOff ? 'fa-solid fa-video-slash' : 'fa-solid fa-video';
                document.getElementById('toggleVideo').classList.toggle('active', this.isVideoOff);

                this.notifyMediaChange('video');
            });

            document.getElementById('toggleScreen').addEventListener('click', () => this.toggleScreenShare());

            document.getElementById('endCall').addEventListener('click', () => {
                if (confirm('Покинуть встречу?')) this.endCall();
            });

            // Use sendBeacon for reliable leave on tab close
            window.addEventListener('beforeunload', () => {
                const data = new Blob([JSON.stringify({ _token: CSRF_TOKEN })], { type: 'application/json' });
                navigator.sendBeacon(`/admin/meetings/${MEETING_ID}/leave`, data);
            });
        }

        async toggleScreenShare() {
            if (this.isScreenSharing) {
                // Stop screen sharing — switch back to camera
                if (this.screenStream) {
                    this.screenStream.getTracks().forEach(t => t.stop());
                    this.screenStream = null;
                }

                const cameraTrack = this.localStream ? this.localStream.getVideoTracks()[0] : null;
                if (cameraTrack) {
                    Object.values(this.peerConnections).forEach(pc => {
                        const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
                        if (sender) sender.replaceTrack(cameraTrack);
                    });
                }

                document.getElementById('localVideo').srcObject = this.localStream;
                document.getElementById('toggleScreen').classList.remove('active');
                this.isScreenSharing = false;
                return;
            }

            try {
                this.screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
                const screenTrack = this.screenStream.getVideoTracks()[0];

                Object.values(this.peerConnections).forEach(pc => {
                    const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
                    if (sender) sender.replaceTrack(screenTrack);
                });

                document.getElementById('localVideo').srcObject = this.screenStream;
                document.getElementById('toggleScreen').classList.add('active');
                this.isScreenSharing = true;

                // Handle user stopping screen share via browser UI
                screenTrack.onended = () => {
                    this.isScreenSharing = true; // set to true so toggleScreenShare will stop it
                    this.toggleScreenShare();
                };
            } catch (e) {
                console.error('Screen share error:', e);
            }
        }

        async notifyMediaChange(type) {
            try {
                await fetch(`/admin/meetings/${MEETING_ID}/toggle-media`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: JSON.stringify({ type })
                });
            } catch (e) {}
        }

        async endCall(redirect = true) {
            clearInterval(this.pollingInterval);
            clearInterval(this.participantsInterval);

            if (this.localStream) {
                this.localStream.getTracks().forEach(t => t.stop());
            }
            if (this.screenStream) {
                this.screenStream.getTracks().forEach(t => t.stop());
            }

            Object.values(this.peerConnections).forEach(pc => pc.close());
            this.peerConnections = {};
            this.remoteStreams = {};

            try {
                await fetch(`/admin/meetings/${MEETING_ID}/leave`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
            } catch (e) {}

            if (redirect) window.location.href = '/admin/meetings';
        }

        // ========== UI Helpers ==========

        showNotification(message, type = 'success') {
            const existing = document.querySelector('.notification');
            if (existing) existing.remove();

            const icons = { success: 'check-circle', warning: 'exclamation-circle', error: 'times-circle' };
            const el = document.createElement('div');
            el.className = 'notification ' + type;
            el.innerHTML = `<i class="fa-solid fa-${icons[type] || icons.success}"></i><span>${message}</span>`;
            document.body.appendChild(el);

            setTimeout(() => el.remove(), 4000);
        }

        playNotificationSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 600;
                gain.gain.value = 0.3;
                osc.start();
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
                osc.stop(ctx.currentTime + 0.5);
            } catch (e) {}
        }
    }

    const videoCall = new VideoCall();
    </script>
</body>
</html>
