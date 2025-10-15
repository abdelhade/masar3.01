const TRACKING_INTERVAL = 30 * 60 * 1000; // 30 minutes
const TRACKING_DURATION = 10 * 60 * 60 * 1000; // 10 hours
let trackingIntervalId = null;
let trackingStartTime = null;

self.addEventListener('message', (event) => {
    if (event.data.type === 'START_TRACKING') {
        startLocationTracking(event.data.sessionId, event.data.userId);
    } else if (event.data.type === 'STOP_TRACKING') {
        stopLocationTracking();
    }
});

function startLocationTracking(sessionId, userId) {
    trackingStartTime = Date.now();
    
    // التتبع الفوري عند البدء
    trackLocation(sessionId, userId);
    
    // ثم كل 30 دقيقة
    trackingIntervalId = setInterval(() => {
        const elapsed = Date.now() - trackingStartTime;
        
        if (elapsed >= TRACKING_DURATION) {
            stopLocationTracking();
            return;
        }
        
        trackLocation(sessionId, userId);
    }, TRACKING_INTERVAL);
}

function stopLocationTracking() {
    if (trackingIntervalId) {
        clearInterval(trackingIntervalId);
        trackingIntervalId = null;
        trackingStartTime = null;
    }
}

async function trackLocation(sessionId, userId) {
    try {
        const position = await getCurrentPosition();
        await sendLocationToServer(position, sessionId, userId);
    } catch (error) {
        console.error('Location tracking error:', error);
    }
}

function getCurrentPosition() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Geolocation not supported'));
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            (position) => resolve(position),
            (error) => reject(error),
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
}

async function sendLocationToServer(position, sessionId, userId) {
    const data = {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
        accuracy: position.coords.accuracy,
        session_id: sessionId,
        user_id: userId,
        tracked_at: new Date().toISOString()
    };
    
    try {
        const response = await fetch('/api/location/track', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error('Failed to send location');
        }
    } catch (error) {
        console.error('Error sending location:', error);
    }
}
