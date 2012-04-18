if (typeof Meebo == 'undefined') {
	if( !$.browser.msie || ($.browser.version > 7.0 && $.browser.msie) ) {
		Meebo=function(){ (Meebo._=Meebo._||[]).push(arguments)};
		(function(q){
			var args = arguments;
			if (!document.body) { return setTimeout(function(){ args.callee.apply(this, args) }, 100); }
			var d=document, b=d.body, m=b.insertBefore(d.createElement('div'), b.firstChild), s=d.createElement('script');
			m.id='meebo'; m.style.display='none'; m.innerHTML='<iframe id="meebo-iframe"></iframe>';
			s.src='http'+(q.https?'s':'')+'://'+(q.stage?'stage-':'')+'cim.meebo.com/cim/cim.php?network='+q.network;
			b.insertBefore(s, b.firstChild);

		})({ network:'plymouthstateuniversity_ma43he', 'https':document.location.protocol=='https:'});
	} else {
		$(function(){
			$('#feedbackpage').css('bottom', '0px');
		});
	}//end else
}
