const moment = require('moment');

function formatMessage(username, text, socket,type) {
    const socket_ip = socket.handshake.address.split(':').pop();
    return {
        username,
        text,
        time: moment().format('h:mm a'),
        ip: socket_ip, // Include the client's IP address
        type:type,
    };
}

module.exports = formatMessage;
