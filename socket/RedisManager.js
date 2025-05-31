const redis = require('redis');
class RedisManager {
    constructor(redisUrl = 'redis://127.0.0.1:6379') {
        this.client = redis.createClient({ url: redisUrl });
        this.subscriber = redis.createClient({ url: redisUrl });

        this.connect();
    }
    async connect() {
        try {
            await this.client.connect();
            await this.subscriber.connect();
            console.log('Connected to Redis');
        } catch (err) {
            console.error('Redis connection error:', err);
        }
    }
    publish(channel, message) {
        try {
            this.client.publish(channel, JSON.stringify(message));
            console.log(`Published to Redis channel "${channel}":`, message);
        } catch (err) {
            console.error('Redis publish error:', err);
        }
    }
    subscribe(channel, callback) {
        this.subscriber.subscribe(channel, (message) => {
            try {
                const data = JSON.parse(message);
                console.log(`Received from Redis channel "${channel}":`, data);
                callback(data);
            } catch (err) {
                console.error('Redis subscription error:', err);
            }
        });
    }
}

module.exports = RedisManager;