//
// used for testing web requests. run using node:
//
//    bash$ node echo-server.js
//

var http = require('http');

http.createServer(function (request, response) {
	response.writeHead(200, {'Content-Type': 'text/plain'});
	var url = request.url;
	response.end( url.substr(1).toUpperCase() );
}).listen(2000);
