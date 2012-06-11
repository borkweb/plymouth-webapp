// Hehe.
if ( window.addEventListener ) {
        var kkeys = [], konami = "38,38,40,40,37,39,37,39,66,65";
        var konami2 = "38,38,38,38,40,40,40,40,37,37,39,39,37,37,39,39,66,66,65,65";
				var konami_on = false;
        window.addEventListener("keydown", function(e){
					if( !konami_on ) {
                kkeys.push( e.keyCode );
                if ( kkeys.toString().indexOf( konami ) >= 0 || kkeys.toString().indexOf( konami2 ) >= 0){
									konami_on = true;
									var s = document.createElement('script');s.type='text/javascript';document.body.appendChild(s);s.src=HOST + '/webapp/my/js/asteroids.min.js';void(0);
								}
					}
        }, true);
}
