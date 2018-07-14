var express = require('express');
var app = express();
var fs = require('fs');
var index = "html/index.html";

    app.use('/', express.static(__dirname + '/html'));
    app.use('/img', express.static(__dirname + '/html/img'));

     
     app.listen(8080);


















