var app = require('express')(); 
var server = require('http').Server(app);
var io = require('socket.io')(server);
var debug = require('debug')('Gentack:Chat');
var request = require('request');
var dotenv = require('dotenv').config();

var port = process.env.PORT || '3003';

var socket_url = process.env.APP_URL;

process.env.DEBUG = '*';

server.listen(port);

io.on('connection', function (socket) {

    // console.log(socket.id);

    console.log('new connection established');
    
    socket.join(socket.handshake.query.user_id);

    socket.emit('connected', {'sessionID' : socket.id});

    /*socket.on('update sender', function(data) {

        console.log("Update Sender START");

        console.log('update sender', data);

        socket.handshake.query.myid = data.myid;

        socket.handshake.query.reqid = data.reqid;

        socket.reqid = socket.handshake.query.reqid;

        socket.join(socket.handshake.query.myid);

        socket.emit('sender updated', 'Sender Updated ID:'+data.myid, 'Request ID:'+data.reqid);

        console.log("Update Sender END");

    });*/

    socket.on('save_continue_watching_video', function(data) {

        // var sent_status = socket.broadcast.to(receiver).emit('message', data);

        console.log(data);

        url = socket_url+'userApi/save/watching/video?id='+data.id
        +'&token='+data.token
        +'&sub_profile_id='+data.sub_profile_id
        +'&admin_video_id='+data.admin_video_id
        +'&duration='+data.duration;

        console.log(url);

        request.get(url, function (error, response, body) {

        });

        console.log("send message END");

    });

    socket.on('signout_from_all_device', function(data) {

        receiver = data.id;

        socket.broadcast.to(receiver).emit('signout_profiles', data);

       // socket.emit('signout_profiles', data);

    });

    socket.on('disconnect', function(data) {

        console.log(data);

        console.log('disconnect close');

    });

});