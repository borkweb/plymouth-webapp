var i,s,ss=['/js/kh.js','http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js'];

for(i=0;i!=ss.length;i++){
	s=document.createElement('script');
	s.src=ss[i];document.body.appendChild(s);
}
void(0);