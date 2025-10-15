class LocationTracker {
    constructor() {
        this.serviceWorker = null;
        this.sessionId = this.generateSessionId();
        this.trackingDuration = 10 * 60 * 60 * 1000; // 10 hours
        this.trackingInterval = 30 * 60 * 1000; // 30 minutes
        this.isTracking = false;
        this.googleApiKey = null;
    }
    
    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    async init(googleApiKey = null) {
        this.googleApiKey = googleApiKey;
        
        try {
            await this.registerServiceWorker();
            const permissionGranted = await this.requestPermission();
            
            if (permissionGranted) {
                await this.startTracking();
            }
        } catch (error) {
            console.error('LocationTracker: خطأ في التهيئة:', error);
        }
    }
    
    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            const registration = await navigator.serviceWorker.register('/service-worker.js');
            this.serviceWorker = registration.active || registration.installing || registration.waiting;
        }
    }
    
    async requestPermission() {
        try {
            if (!navigator.geolocation) {
                return false;
            }
            
            if ('permissions' in navigator) {
                const permission = await navigator.permissions.query({ name: 'geolocation' });
                
                if (permission.state === 'granted') {
                    return true;
                } else if (permission.state === 'prompt') {
                    return await this.tryDirectPrompt();
                } else {
                    return false;
                }
            } else {
                return await this.tryDirectPrompt();
            }
        } catch (error) {
            return await this.tryDirectPrompt();
        }
    }
    
    async tryDirectPrompt() {
        return new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(
                () => resolve(true),
                () => resolve(false),
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });
    }
    
    async startTracking() {
        if (this.isTracking) {
            return;
        }
        
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        if (!userId) {
            console.error('LocationTracker: لم يتم العثور على User ID');
            return;
        }
        
        this.isTracking = true;
        
        // التقاط الموقع فوراً عند بدء التتبع
        try {
            const position = await this.getCurrentPosition();
            await this.sendLocationToServer(position, 'login');
        } catch (error) {
            console.error('LocationTracker: فشل في التقاط الموقع الأول:', error);
        }
        
        // إرسال رسالة للـ Service Worker لبدء التتبع
        if (this.serviceWorker) {
            this.serviceWorker.postMessage({
                type: 'START_TRACKING',
                interval: this.trackingInterval,
                duration: this.trackingDuration
            });
        }
        
        // حفظ حالة التتبع
        localStorage.setItem('location_tracking', JSON.stringify({
            sessionId: this.sessionId,
            startTime: Date.now(),
            isTracking: true
        }));
        
        // إيقاف التتبع بعد المدة المحددة
        setTimeout(() => {
            this.stopTracking();
        }, this.trackingDuration);
    }
    
    stopTracking() {
        if (!this.isTracking) return;
        
        this.isTracking = false;
        localStorage.removeItem('location_tracking');
        
        if (this.serviceWorker) {
            this.serviceWorker.postMessage({
                type: 'STOP_TRACKING'
            });
        }

        if (this._fallbackIntervalId) {
            clearInterval(this._fallbackIntervalId);
            this._fallbackIntervalId = null;
        }
    }
    
    async captureLocationForAttendance(type) {
        try {
            const position = await this.getCurrentPosition();
            
            let locationData = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy,
                timestamp: new Date().toISOString(),
                type: type
            };
            
            if (this.googleApiKey) {
                const googleData = await this.getGoogleLocationData(
                    position.coords.latitude,
                    position.coords.longitude
                );
                if (googleData) {
                    locationData.address = googleData.address;
                    locationData.place_id = googleData.place_id;
                }
            }
            
            return locationData;
        } catch (error) {
            console.error('LocationTracker: فشل في التقاط الموقع:', error);
            return null;
        }
    }
    
    async getCurrentPosition() {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                resolve,
                reject,
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    }
    
    async sendLocationToServer(position, type = 'tracking') {
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        if (!userId) return;
        
        let locationData = {
            user_id: userId,
            session_id: this.sessionId,
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            tracked_at: new Date().toISOString(),
            type: type
        };
        
        if (this.googleApiKey) {
            const googleData = await this.getGoogleLocationData(
                position.coords.latitude,
                position.coords.longitude
            );
            if (googleData) {
                locationData.address = googleData.address;
                locationData.place_id = googleData.place_id;
            }
        }
        
        try {
            const response = await fetch('/api/location/track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(locationData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        } catch (error) {
            console.error('LocationTracker: فشل في حفظ الموقع:', error);
        }
    }
    
    async getGoogleLocationData(latitude, longitude) {
        if (!this.googleApiKey) return null;
        
        try {
            const response = await fetch(
                `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${this.googleApiKey}&language=ar`
            );
            
            if (!response.ok) return null;
            
            const data = await response.json();
            
            if (data.status === 'OK' && data.results.length > 0) {
                return {
                    address: data.results[0].formatted_address,
                    place_id: data.results[0].place_id
                };
            }
            
            return null;
        } catch (error) {
            return null;
        }
    }
    
    async restoreTracking() {
        const savedData = localStorage.getItem('location_tracking');
        if (!savedData) return false;
        
        try {
            const data = JSON.parse(savedData);
            const now = Date.now();
            const elapsed = now - data.startTime;
            
            if (elapsed < this.trackingDuration && data.isTracking) {
                this.sessionId = data.sessionId;
                this.isTracking = true;
                
                if (this.serviceWorker) {
                    this.serviceWorker.postMessage({
                        type: 'START_TRACKING',
                        interval: this.trackingInterval,
                        duration: this.trackingDuration - elapsed
                    });
                }
                
                return true;
            } else {
                localStorage.removeItem('location_tracking');
                return false;
            }
        } catch (error) {
            localStorage.removeItem('location_tracking');
            return false;
        }
    }
    
    getTrackingStatus() {
        return {
            isTracking: this.isTracking,
            sessionId: this.sessionId,
            trackingDuration: this.trackingDuration,
            trackingInterval: this.trackingInterval
        };
    }
}