// Hehe, another easter egg.
if ( window.addEventListener ) {
        var kkeys = [], code = "70,69,82,77,73,83";
				var code2 = "70,70,69,69,82,82,77,77,73,73,83"
				var code_on = false;
        window.addEventListener("keydown", function(e){
					if( !code_on ) {
                kkeys.push( e.keyCode );
                if ( kkeys.toString().indexOf( code ) >= 0 || kkeys.toString().indexOf( code2 ) >= 0){
									code_on = true;
								
									var i,s,ss=['/webapp/training-tracker/js/kh.js'];
									for(i=0;i!=ss.length;i++){
											s=document.createElement('script');
												s.src=ss[i];document.body.appendChild(s);
									}
									void(0);
								
								}
					}
        }, true);
}
