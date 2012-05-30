/*************************************
 * jQuery SuperModal v1.2.1
 *************************************/
;(function($){var box,box_selector,el,caption,master_selector,options={},url={},width,height,the_rel,modal_id,items;var img_preloader,img_next_preloader,img_prev_preloader;var img_gallery=[];$.fn.supermodal=function(options){function _init(){options=$.extend($.fn.supermodal.defaults,options);master_selector=options.selector||$(this).selector;box_selector='.'+options.modalClass;if($(box_selector).size()==0){$('body').append('<div class="'+options.modalClass+'" style="display: none;"></div>');$(box_selector).dialog(options);}
box=$(box_selector);}
function _display_content(){box.dialog('option','width',width);box.dialog('option','height',height);box.dialog('option','position',box.dialog('option','position'));box.dialog('open');}
function _gallery_init(){if(the_rel){if(!img_gallery[the_rel]){img_gallery[the_rel]=[];items=$(el.selector).find('*[rel='+the_rel+']');items.each(function(i){var the_ab=$(this).attr('modal_id');img_gallery[the_rel][the_ab]={item:$(this),title:caption+' (Image '+(Number(the_ab)+1)+' of '+items.size()+')'};});}
box.dialog('option','title',img_gallery[the_rel][modal_id].title);}}
function _image_loaded(){var viewport=$.fn.supermodal.getViewport();var divisor=Math.min(Math.min(viewport[0]-150,img_preloader.width)/img_preloader.width,Math.min(viewport[1]-150,img_preloader.height)/img_preloader.height);var img_width=Math.round(divisor*img_preloader.width);var img_height=Math.round(divisor*img_preloader.height);width=((Number(img_width)>width)?Number(img_width):width);height=(((Number(img_height)+50)>height)?(Number(img_height)+50):height);var content='<div class="ab-gallery-container" style="position:relative;text-align:center;"><img src="'+url.full+'" width="'+img_width+'" height="'+img_height+'" alt="'+caption+'"/>';if(options.gallery){if(the_rel){content+='<a href="#" class="ui-state-default ab-gallery-nav ab-gallery-prev" style="display:none;padding:0.5em;position:absolute;top:0;left:0;"><span class="ui-icon ui-icon-seek-prev" style="display: inline-block;"></span> Prev</a>';content+='<a href="#" class="ui-state-default ui-widget-content ab-gallery-nav ab-gallery-next" style="display:none;padding:0.5em;position:absolute;top:0;right:0;">Next <span class="ui-icon ui-icon-seek-next" style="display: inline-block;"></span></a>';}}
content+='</div>';box.html(content);box.mouseover(function(){$(this).find('.ab-gallery-nav').show();}).mouseout(function(){$(this).find('.ab-gallery-nav').hide();});$('.ab-gallery-container .ab-gallery-prev').click(function(){var nav=(modal_id<=0)?(items.size()-1):(modal_id-1);items.eq(nav).click();return false;});$('.ab-gallery-container .ab-gallery-next').click(function(){var nav=(modal_id>=items.size()-1)?0:(modal_id+1);items.eq(nav).click();return false;});_display_content();}
function _open_modal(){el=$(this);modal_id=Number(el.attr('modal_id'));the_rel=el.attr('rel');box.html('<div style="text-align:center;" class="throbber-container"><img src="'+options.throbber+'" class="throbber"></div>');caption=el.attr('title')||el.attr('alt')||'&nbsp;';box.dialog('option','title',caption);try{var the_url=options.url||el.attr('href')||el.attr('src')||el.attr('action')||'';if(options.event=='submit'){var form_data=el.serialize();the_url+=(the_url.indexOf('?')!==-1)?('&'+form_data):('?'+form_data);}
_parse_url(the_url);width=url.params.width||options.width||options.minWidth;height=url.params.height||options.height||options.minHeight;if(url.type=='image'){if(options.gallery){_gallery_init();}
img_preloader=new Image();$(img_preloader).unbind().bind('load',_image_loaded);img_preloader.src=url.full;}
else{if(url.params.inlineId){box.html($('#'+url.params.inlineId).html());}else if(!url.params.PM_iframe&&options.modalType!='iframe'&&document.domain==url.domain){box.load(url.full);}else{box.html(box.html()+'<iframe src="'+url.full+'" width="'+width+'" height="'+height+'" style="display:none;"></iframe>').dialog('option','resizeStop',function(){box.find('iframe').attr('width',box.css('width'));});box.find('iframe').unbind().bind('load',function(){$(box_selector+' .throbber-container').remove();$(this).show();});}
options.selector=master_selector;box.find(master_selector).supermodal(options);_display_content();}}catch(e){}
return false;}
function _parse_url($url){url={full:$url};if(!url.full)return true;url.base=(url.full.indexOf('?')!==-1)?url.full.substr(0,url.full.indexOf('?')):url.full;url.domain=(new RegExp("^(?:http://)?([^/]+)")).exec(url.full)[1];url.type=(url.base.toLowerCase().match(/\.jpg$|\.jpeg$|\.png$|\.gif$|\.bmp$/))?'image':'other';url.query_string=url.full.split('?')[1]+"";url.params={};var pairs=url.query_string.split('&');for(var i=0;i<pairs.length;i++){var pair=pairs[i].split('=');url.params[pair[0]]=pair[1];}
if(url.params.width)url.params.width=(url.params.width<options.minWidth)?options.minWidth:url.params.width;if(url.params.height)url.params.height=(url.params.height<options.minHeight)?options.minHeight:url.params.height;}
function _throb(){}
_init();$(this.selector).live(options.event,_open_modal);return this.each(function(modal_id){$(this).attr('modal_id',modal_id);});};$.fn.supermodal.getViewport=function(){return[$(window).width(),$(window).height(),$(document).scrollLeft(),$(document).scrollTop()];};$.fn.supermodal.defaults={event:'click',modalClass:'supermodal',gallery:true,modalType:true,selector:'',throbber:'images/throbber.gif',autoOpen:false,height:300,minHeight:150,minWidth:150,modal:true,resizeable:false,width:500,url:false};})(jQuery);
/*
 * jQuery Cycle Plugin (with Transition Definitions)
 * Examples and documentation at: http://jquery.malsup.com/cycle/
 * Copyright (c) 2007-2009 M. Alsup
 * Version: 2.63 (17-MAR-2009)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * Requires: jQuery v1.2.6 or later
 *
 * Originally based on the work of:
 *	1) Matt Oakes
 *	2) Torsten Baldes (http://medienfreunde.com/lab/innerfade/)
 *	3) Benjamin Sterling (http://www.benjaminsterling.com/experiments/jqShuffle/)
 */
;(function($){var ver="2.63";if($.support==undefined){$.support={opacity:!($.browser.msie)};}function log(){if(window.console&&window.console.log){window.console.log("[cycle] "+Array.prototype.join.call(arguments," "));}}$.fn.cycle=function(options,arg2){var o={s:this.selector,c:this.context};if(this.length==0&&options!="stop"){if(!$.isReady&&o.s){log("DOM not ready, queuing slideshow");$(function(){$(o.s,o.c).cycle(options,arg2);});return this;}log("terminating; zero elements found by selector"+($.isReady?"":" (DOM not ready)"));return this;}return this.each(function(){options=handleArguments(this,options,arg2);if(options===false){return;}if(this.cycleTimeout){clearTimeout(this.cycleTimeout);}this.cycleTimeout=this.cyclePause=0;var $cont=$(this);var $slides=options.slideExpr?$(options.slideExpr,this):$cont.children();var els=$slides.get();if(els.length<2){log("terminating; too few slides: "+els.length);return;}var opts=buildOptions($cont,$slides,els,options,o);if(opts===false){return;}if(opts.timeout||opts.continuous){this.cycleTimeout=setTimeout(function(){go(els,opts,0,!opts.rev);},opts.continuous?10:opts.timeout+(opts.delay||0));}});};function handleArguments(cont,options,arg2){if(cont.cycleStop==undefined){cont.cycleStop=0;}if(options===undefined||options===null){options={};}if(options.constructor==String){switch(options){case"stop":cont.cycleStop++;if(cont.cycleTimeout){clearTimeout(cont.cycleTimeout);}cont.cycleTimeout=0;$(cont).removeData("cycle.opts");return false;case"pause":cont.cyclePause=1;return false;case"resume":cont.cyclePause=0;if(arg2===true){options=$(cont).data("cycle.opts");if(!options){log("options not found, can not resume");return false;}if(cont.cycleTimeout){clearTimeout(cont.cycleTimeout);cont.cycleTimeout=0;}go(options.elements,options,1,1);}return false;default:options={fx:options};}}else{if(options.constructor==Number){var num=options;options=$(cont).data("cycle.opts");if(!options){log("options not found, can not advance slide");return false;}if(num<0||num>=options.elements.length){log("invalid slide index: "+num);return false;}options.nextSlide=num;if(cont.cycleTimeout){clearTimeout(this.cycleTimeout);cont.cycleTimeout=0;}if(typeof arg2=="string"){options.oneTimeFx=arg2;}go(options.elements,options,1,num>=options.currSlide);return false;}}return options;}function removeFilter(el,opts){if(!$.support.opacity&&opts.cleartype&&el.style.filter){try{el.style.removeAttribute("filter");}catch(smother){}}}function buildOptions($cont,$slides,els,options,o){var opts=$.extend({},$.fn.cycle.defaults,options||{},$.metadata?$cont.metadata():$.meta?$cont.data():{});if(opts.autostop){opts.countdown=opts.autostopCount||els.length;}var cont=$cont[0];$cont.data("cycle.opts",opts);opts.$cont=$cont;opts.stopCount=cont.cycleStop;opts.elements=els;opts.before=opts.before?[opts.before]:[];opts.after=opts.after?[opts.after]:[];opts.after.unshift(function(){opts.busy=0;});if(!$.support.opacity&&opts.cleartype){opts.after.push(function(){removeFilter(this,opts);});}if(opts.continuous){opts.after.push(function(){go(els,opts,0,!opts.rev);});}saveOriginalOpts(opts);if(!$.support.opacity&&opts.cleartype&&!opts.cleartypeNoBg){clearTypeFix($slides);}if($cont.css("position")=="static"){$cont.css("position","relative");}if(opts.width){$cont.width(opts.width);}if(opts.height&&opts.height!="auto"){$cont.height(opts.height);}if(opts.startingSlide){opts.startingSlide=parseInt(opts.startingSlide);}if(opts.random){opts.randomMap=[];for(var i=0;i<els.length;i++){opts.randomMap.push(i);}opts.randomMap.sort(function(a,b){return Math.random()-0.5;});opts.randomIndex=0;opts.startingSlide=opts.randomMap[0];}else{if(opts.startingSlide>=els.length){opts.startingSlide=0;}}opts.currSlide=opts.startingSlide=opts.startingSlide||0;var first=opts.startingSlide;$slides.css({position:"absolute",top:0,left:0}).hide().each(function(i){var z=first?i>=first?els.length-(i-first):first-i:els.length-i;$(this).css("z-index",z);});$(els[first]).css("opacity",1).show();removeFilter(els[first],opts);if(opts.fit&&opts.width){$slides.width(opts.width);}if(opts.fit&&opts.height&&opts.height!="auto"){$slides.height(opts.height);}var reshape=opts.containerResize&&!$cont.innerHeight();if(reshape){var maxw=0,maxh=0;for(var i=0;i<els.length;i++){var $e=$(els[i]),e=$e[0],w=$e.outerWidth(),h=$e.outerHeight();if(!w){w=e.offsetWidth;}if(!h){h=e.offsetHeight;}maxw=w>maxw?w:maxw;maxh=h>maxh?h:maxh;}if(maxw>0&&maxh>0){$cont.css({width:maxw+"px",height:maxh+"px"});}}if(opts.pause){$cont.hover(function(){this.cyclePause++;},function(){this.cyclePause--;});}if(supportMultiTransitions(opts)===false){return false;}if(!opts.multiFx){var init=$.fn.cycle.transitions[opts.fx];if($.isFunction(init)){init($cont,$slides,opts);}else{if(opts.fx!="custom"&&!opts.multiFx){log("unknown transition: "+opts.fx,"; slideshow terminating");return false;}}}var requeue=false;options.requeueAttempts=options.requeueAttempts||0;$slides.each(function(){var $el=$(this);this.cycleH=(opts.fit&&opts.height)?opts.height:$el.height();this.cycleW=(opts.fit&&opts.width)?opts.width:$el.width();if($el.is("img")){var loadingIE=($.browser.msie&&this.cycleW==28&&this.cycleH==30&&!this.complete);var loadingOp=($.browser.opera&&this.cycleW==42&&this.cycleH==19&&!this.complete);var loadingOther=(this.cycleH==0&&this.cycleW==0&&!this.complete);if(loadingIE||loadingOp||loadingOther){if(o.s&&opts.requeueOnImageNotLoaded&&++options.requeueAttempts<100){log(options.requeueAttempts," - img slide not loaded, requeuing slideshow: ",this.src,this.cycleW,this.cycleH);setTimeout(function(){$(o.s,o.c).cycle(options);},opts.requeueTimeout);requeue=true;return false;}else{log("could not determine size of image: "+this.src,this.cycleW,this.cycleH);}}}return true;});if(requeue){return false;}opts.cssBefore=opts.cssBefore||{};opts.animIn=opts.animIn||{};opts.animOut=opts.animOut||{};$slides.not(":eq("+first+")").css(opts.cssBefore);if(opts.cssFirst){$($slides[first]).css(opts.cssFirst);}if(opts.timeout){opts.timeout=parseInt(opts.timeout);if(opts.speed.constructor==String){opts.speed=$.fx.speeds[opts.speed]||parseInt(opts.speed);}if(!opts.sync){opts.speed=opts.speed/2;}while((opts.timeout-opts.speed)<250){opts.timeout+=opts.speed;}}if(opts.easing){opts.easeIn=opts.easeOut=opts.easing;}if(!opts.speedIn){opts.speedIn=opts.speed;}if(!opts.speedOut){opts.speedOut=opts.speed;}opts.slideCount=els.length;opts.currSlide=opts.lastSlide=first;if(opts.random){opts.nextSlide=opts.currSlide;if(++opts.randomIndex==els.length){opts.randomIndex=0;}opts.nextSlide=opts.randomMap[opts.randomIndex];}else{opts.nextSlide=opts.startingSlide>=(els.length-1)?0:opts.startingSlide+1;}var e0=$slides[first];if(opts.before.length){opts.before[0].apply(e0,[e0,e0,opts,true]);}if(opts.after.length>1){opts.after[1].apply(e0,[e0,e0,opts,true]);}if(opts.next){$(opts.next).click(function(){return advance(opts,opts.rev?-1:1);});}if(opts.prev){$(opts.prev).click(function(){return advance(opts,opts.rev?1:-1);});}if(opts.pager){buildPager(els,opts);}exposeAddSlide(opts,els);return opts;}function saveOriginalOpts(opts){opts.original={before:[],after:[]};opts.original.cssBefore=$.extend({},opts.cssBefore);opts.original.cssAfter=$.extend({},opts.cssAfter);opts.original.animIn=$.extend({},opts.animIn);opts.original.animOut=$.extend({},opts.animOut);$.each(opts.before,function(){opts.original.before.push(this);});$.each(opts.after,function(){opts.original.after.push(this);});}function supportMultiTransitions(opts){var txs=$.fn.cycle.transitions;if(opts.fx.indexOf(",")>0){opts.multiFx=true;opts.fxs=opts.fx.replace(/\s*/g,"").split(",");for(var i=0;i<opts.fxs.length;i++){var fx=opts.fxs[i];var tx=txs[fx];if(!tx||!txs.hasOwnProperty(fx)||!$.isFunction(tx)){log("discarding unknown transition: ",fx);opts.fxs.splice(i,1);i--;}}if(!opts.fxs.length){log("No valid transitions named; slideshow terminating.");return false;}}else{if(opts.fx=="all"){opts.multiFx=true;opts.fxs=[];for(p in txs){var tx=txs[p];if(txs.hasOwnProperty(p)&&$.isFunction(tx)){opts.fxs.push(p);}}}}if(opts.multiFx&&opts.randomizeEffects){var r1=Math.floor(Math.random()*20)+30;for(var i=0;i<r1;i++){var r2=Math.floor(Math.random()*opts.fxs.length);opts.fxs.push(opts.fxs.splice(r2,1)[0]);}log("randomized fx sequence: ",opts.fxs);}return true;}function exposeAddSlide(opts,els){opts.addSlide=function(newSlide,prepend){var $s=$(newSlide),s=$s[0];if(!opts.autostopCount){opts.countdown++;}els[prepend?"unshift":"push"](s);if(opts.els){opts.els[prepend?"unshift":"push"](s);}opts.slideCount=els.length;$s.css("position","absolute");$s[prepend?"prependTo":"appendTo"](opts.$cont);if(prepend){opts.currSlide++;opts.nextSlide++;}if(!$.support.opacity&&opts.cleartype&&!opts.cleartypeNoBg){clearTypeFix($s);}if(opts.fit&&opts.width){$s.width(opts.width);}if(opts.fit&&opts.height&&opts.height!="auto"){$slides.height(opts.height);}s.cycleH=(opts.fit&&opts.height)?opts.height:$s.height();s.cycleW=(opts.fit&&opts.width)?opts.width:$s.width();$s.css(opts.cssBefore);if(opts.pager){$.fn.cycle.createPagerAnchor(els.length-1,s,$(opts.pager),els,opts);}if($.isFunction(opts.onAddSlide)){opts.onAddSlide($s);}else{$s.hide();}};}$.fn.cycle.resetState=function(opts,fx){fx=fx||opts.fx;opts.before=[];opts.after=[];opts.cssBefore=$.extend({},opts.original.cssBefore);opts.cssAfter=$.extend({},opts.original.cssAfter);opts.animIn=$.extend({},opts.original.animIn);opts.animOut=$.extend({},opts.original.animOut);opts.fxFn=null;$.each(opts.original.before,function(){opts.before.push(this);});$.each(opts.original.after,function(){opts.after.push(this);});var init=$.fn.cycle.transitions[fx];if($.isFunction(init)){init(opts.$cont,$(opts.elements),opts);}};function go(els,opts,manual,fwd){if(manual&&opts.busy&&opts.manualTrump){$(els).stop(true,true);opts.busy=false;}if(opts.busy){return;}var p=opts.$cont[0],curr=els[opts.currSlide],next=els[opts.nextSlide];if(p.cycleStop!=opts.stopCount||p.cycleTimeout===0&&!manual){return;}if(!manual&&!p.cyclePause&&((opts.autostop&&(--opts.countdown<=0))||(opts.nowrap&&!opts.random&&opts.nextSlide<opts.currSlide))){if(opts.end){opts.end(opts);}return;}if(manual||!p.cyclePause){var fx=opts.fx;curr.cycleH=curr.cycleH||$(curr).height();curr.cycleW=curr.cycleW||$(curr).width();next.cycleH=next.cycleH||$(next).height();next.cycleW=next.cycleW||$(next).width();if(opts.multiFx){if(opts.lastFx==undefined||++opts.lastFx>=opts.fxs.length){opts.lastFx=0;}fx=opts.fxs[opts.lastFx];opts.currFx=fx;}if(opts.oneTimeFx){fx=opts.oneTimeFx;opts.oneTimeFx=null;}$.fn.cycle.resetState(opts,fx);if(opts.before.length){$.each(opts.before,function(i,o){if(p.cycleStop!=opts.stopCount){return;}o.apply(next,[curr,next,opts,fwd]);});}var after=function(){$.each(opts.after,function(i,o){if(p.cycleStop!=opts.stopCount){return;}o.apply(next,[curr,next,opts,fwd]);});};if(opts.nextSlide!=opts.currSlide){opts.busy=1;if(opts.fxFn){opts.fxFn(curr,next,opts,after,fwd);}else{if($.isFunction($.fn.cycle[opts.fx])){$.fn.cycle[opts.fx](curr,next,opts,after);}else{$.fn.cycle.custom(curr,next,opts,after,manual&&opts.fastOnEvent);}}}opts.lastSlide=opts.currSlide;if(opts.random){opts.currSlide=opts.nextSlide;if(++opts.randomIndex==els.length){opts.randomIndex=0;}opts.nextSlide=opts.randomMap[opts.randomIndex];}else{var roll=(opts.nextSlide+1)==els.length;opts.nextSlide=roll?0:opts.nextSlide+1;opts.currSlide=roll?els.length-1:opts.nextSlide-1;}if(opts.pager){$.fn.cycle.updateActivePagerLink(opts.pager,opts.currSlide);}}var ms=0;if(opts.timeout&&!opts.continuous){ms=getTimeout(curr,next,opts,fwd);}else{if(opts.continuous&&p.cyclePause){ms=10;}}if(ms>0){p.cycleTimeout=setTimeout(function(){go(els,opts,0,!opts.rev);},ms);}}$.fn.cycle.updateActivePagerLink=function(pager,currSlide){$(pager).find("a").removeClass("activeSlide").filter("a:eq("+currSlide+")").addClass("activeSlide");};function getTimeout(curr,next,opts,fwd){if(opts.timeoutFn){var t=opts.timeoutFn(curr,next,opts,fwd);if(t!==false){return t;}}return opts.timeout;}$.fn.cycle.next=function(opts){advance(opts,opts.rev?-1:1);};$.fn.cycle.prev=function(opts){advance(opts,opts.rev?1:-1);};function advance(opts,val){var els=opts.elements;var p=opts.$cont[0],timeout=p.cycleTimeout;if(timeout){clearTimeout(timeout);p.cycleTimeout=0;}if(opts.random&&val<0){opts.randomIndex--;if(--opts.randomIndex==-2){opts.randomIndex=els.length-2;}else{if(opts.randomIndex==-1){opts.randomIndex=els.length-1;}}opts.nextSlide=opts.randomMap[opts.randomIndex];}else{if(opts.random){if(++opts.randomIndex==els.length){opts.randomIndex=0;}opts.nextSlide=opts.randomMap[opts.randomIndex];}else{opts.nextSlide=opts.currSlide+val;if(opts.nextSlide<0){if(opts.nowrap){return false;}opts.nextSlide=els.length-1;}else{if(opts.nextSlide>=els.length){if(opts.nowrap){return false;}opts.nextSlide=0;}}}}if($.isFunction(opts.prevNextClick)){opts.prevNextClick(val>0,opts.nextSlide,els[opts.nextSlide]);}go(els,opts,1,val>=0);return false;}function buildPager(els,opts){var $p=$(opts.pager);$.each(els,function(i,o){$.fn.cycle.createPagerAnchor(i,o,$p,els,opts);});$.fn.cycle.updateActivePagerLink(opts.pager,opts.startingSlide);}$.fn.cycle.createPagerAnchor=function(i,el,$p,els,opts){var a=($.isFunction(opts.pagerAnchorBuilder))?opts.pagerAnchorBuilder(i,el):'<a href="#">'+(i+1)+"</a>";if(!a){return;}var $a=$(a);if($a.parents("body").length==0){$a.appendTo($p);}$a.bind(opts.pagerEvent,function(){opts.nextSlide=i;var p=opts.$cont[0],timeout=p.cycleTimeout;if(timeout){clearTimeout(timeout);p.cycleTimeout=0;}if($.isFunction(opts.pagerClick)){opts.pagerClick(opts.nextSlide,els[opts.nextSlide]);}go(els,opts,1,opts.currSlide<i);return false;});if(opts.pauseOnPagerHover){$a.hover(function(){opts.$cont[0].cyclePause++;},function(){opts.$cont[0].cyclePause--;});}};$.fn.cycle.hopsFromLast=function(opts,fwd){var hops,l=opts.lastSlide,c=opts.currSlide;if(fwd){hops=c>l?c-l:opts.slideCount-l;}else{hops=c<l?l-c:l+opts.slideCount-c;}return hops;};function clearTypeFix($slides){function hex(s){s=parseInt(s).toString(16);return s.length<2?"0"+s:s;}function getBg(e){for(;e&&e.nodeName.toLowerCase()!="html";e=e.parentNode){var v=$.css(e,"background-color");if(v.indexOf("rgb")>=0){var rgb=v.match(/\d+/g);return"#"+hex(rgb[0])+hex(rgb[1])+hex(rgb[2]);}if(v&&v!="transparent"){return v;}}return"#ffffff";}$slides.each(function(){$(this).css("background-color",getBg(this));});}$.fn.cycle.commonReset=function(curr,next,opts,w,h,rev){$(opts.elements).not(curr).hide();opts.cssBefore.opacity=1;opts.cssBefore.display="block";if(w!==false&&next.cycleW>0){opts.cssBefore.width=next.cycleW;}if(h!==false&&next.cycleH>0){opts.cssBefore.height=next.cycleH;}opts.cssAfter=opts.cssAfter||{};opts.cssAfter.display="none";$(curr).css("zIndex",opts.slideCount+(rev===true?1:0));$(next).css("zIndex",opts.slideCount+(rev===true?0:1));};$.fn.cycle.custom=function(curr,next,opts,cb,speedOverride){var $l=$(curr),$n=$(next);var speedIn=opts.speedIn,speedOut=opts.speedOut,easeIn=opts.easeIn,easeOut=opts.easeOut;$n.css(opts.cssBefore);if(speedOverride){if(typeof speedOverride=="number"){speedIn=speedOut=speedOverride;}else{speedIn=speedOut=1;}easeIn=easeOut=null;}var fn=function(){$n.animate(opts.animIn,speedIn,easeIn,cb);};$l.animate(opts.animOut,speedOut,easeOut,function(){if(opts.cssAfter){$l.css(opts.cssAfter);}if(!opts.sync){fn();}});if(opts.sync){fn();}};$.fn.cycle.transitions={fade:function($cont,$slides,opts){$slides.not(":eq("+opts.currSlide+")").css("opacity",0);opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts);opts.cssBefore.opacity=0;});opts.animIn={opacity:1};opts.animOut={opacity:0};opts.cssBefore={top:0,left:0};}};$.fn.cycle.ver=function(){return ver;};$.fn.cycle.defaults={fx:"fade",timeout:4000,timeoutFn:null,continuous:0,speed:1000,speedIn:null,speedOut:null,next:null,prev:null,prevNextClick:null,pager:null,pagerClick:null,pagerEvent:"click",pagerAnchorBuilder:null,before:null,after:null,end:null,easing:null,easeIn:null,easeOut:null,shuffle:null,animIn:null,animOut:null,cssBefore:null,cssAfter:null,fxFn:null,height:"auto",startingSlide:0,sync:1,random:0,fit:0,containerResize:1,pause:0,pauseOnPagerHover:0,autostop:0,autostopCount:0,delay:0,slideExpr:null,cleartype:!$.support.opacity,nowrap:0,fastOnEvent:0,randomizeEffects:1,rev:0,manualTrump:true,requeueOnImageNotLoaded:true,requeueTimeout:250};})(jQuery);
/*************************************
 * jQuery Cycle Plugin Transition Definitions v2.52
 *************************************/
;(function($){$.fn.cycle.transitions.scrollUp=function($cont,$slides,opts){$cont.css("overflow","hidden");opts.before.push($.fn.cycle.commonReset);var h=$cont.height();opts.cssBefore={top:h,left:0};opts.cssFirst={top:0};opts.animIn={top:0};opts.animOut={top:-h};};$.fn.cycle.transitions.scrollDown=function($cont,$slides,opts){$cont.css("overflow","hidden");opts.before.push($.fn.cycle.commonReset);var h=$cont.height();opts.cssFirst={top:0};opts.cssBefore={top:-h,left:0};opts.animIn={top:0};opts.animOut={top:h};};$.fn.cycle.transitions.scrollLeft=function($cont,$slides,opts){$cont.css("overflow","hidden");opts.before.push($.fn.cycle.commonReset);var w=$cont.width();opts.cssFirst={left:0};opts.cssBefore={left:w,top:0};opts.animIn={left:0};opts.animOut={left:0-w};};$.fn.cycle.transitions.scrollRight=function($cont,$slides,opts){$cont.css("overflow","hidden");opts.before.push($.fn.cycle.commonReset);var w=$cont.width();opts.cssFirst={left:0};opts.cssBefore={left:-w,top:0};opts.animIn={left:0};opts.animOut={left:w};};$.fn.cycle.transitions.scrollHorz=function($cont,$slides,opts){$cont.css("overflow","hidden").width();opts.before.push(function(curr,next,opts,fwd){$.fn.cycle.commonReset(curr,next,opts);opts.cssBefore.left=fwd?(next.cycleW-1):(1-next.cycleW);opts.animOut.left=fwd?-curr.cycleW:curr.cycleW;});opts.cssFirst={left:0};opts.cssBefore={top:0};opts.animIn={left:0};opts.animOut={top:0};};$.fn.cycle.transitions.scrollVert=function($cont,$slides,opts){$cont.css("overflow","hidden");opts.before.push(function(curr,next,opts,fwd){$.fn.cycle.commonReset(curr,next,opts);opts.cssBefore.top=fwd?(1-next.cycleH):(next.cycleH-1);opts.animOut.top=fwd?curr.cycleH:-curr.cycleH;});opts.cssFirst={top:0};opts.cssBefore={left:0};opts.animIn={top:0};opts.animOut={left:0};};$.fn.cycle.transitions.slideX=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$(opts.elements).not(curr).hide();$.fn.cycle.commonReset(curr,next,opts,false,true);opts.animIn.width=next.cycleW;});opts.cssBefore={left:0,top:0,width:0};opts.animIn={width:"show"};opts.animOut={width:0};};$.fn.cycle.transitions.slideY=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$(opts.elements).not(curr).hide();$.fn.cycle.commonReset(curr,next,opts,true,false);opts.animIn.height=next.cycleH;});opts.cssBefore={left:0,top:0,height:0};opts.animIn={height:"show"};opts.animOut={height:0};};$.fn.cycle.transitions.shuffle=function($cont,$slides,opts){var w=$cont.css("overflow","visible").width();$slides.css({left:0,top:0});opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,true,true,true);});opts.speed=opts.speed/2;opts.random=0;opts.shuffle=opts.shuffle||{left:-w,top:15};opts.els=[];for(var i=0;i<$slides.length;i++){opts.els.push($slides[i]);}for(var i=0;i<opts.currSlide;i++){opts.els.push(opts.els.shift());}opts.fxFn=function(curr,next,opts,cb,fwd){var $el=fwd?$(curr):$(next);$(next).css(opts.cssBefore);var count=opts.slideCount;$el.animate(opts.shuffle,opts.speedIn,opts.easeIn,function(){var hops=$.fn.cycle.hopsFromLast(opts,fwd);for(var k=0;k<hops;k++){fwd?opts.els.push(opts.els.shift()):opts.els.unshift(opts.els.pop());}if(fwd){for(var i=0,len=opts.els.length;i<len;i++){$(opts.els[i]).css("z-index",len-i+count);}}else{var z=$(curr).css("z-index");$el.css("z-index",parseInt(z)+1+count);}$el.animate({left:0,top:0},opts.speedOut,opts.easeOut,function(){$(fwd?this:curr).hide();if(cb){cb();}});});};opts.cssBefore={display:"block",opacity:1,top:0,left:0};};$.fn.cycle.transitions.turnUp=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,true,false);opts.cssBefore.top=next.cycleH;opts.animIn.height=next.cycleH;});opts.cssFirst={top:0};opts.cssBefore={left:0,height:0};opts.animIn={top:0};opts.animOut={height:0};};$.fn.cycle.transitions.turnDown=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,true,false);opts.animIn.height=next.cycleH;opts.animOut.top=curr.cycleH;});opts.cssFirst={top:0};opts.cssBefore={left:0,top:0,height:0};opts.animOut={height:0};};$.fn.cycle.transitions.turnLeft=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,false,true);opts.cssBefore.left=next.cycleW;opts.animIn.width=next.cycleW;});opts.cssBefore={top:0,width:0};opts.animIn={left:0};opts.animOut={width:0};};$.fn.cycle.transitions.turnRight=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,false,true);opts.animIn.width=next.cycleW;opts.animOut.left=curr.cycleW;});opts.cssBefore={top:0,left:0,width:0};opts.animIn={left:0};opts.animOut={width:0};};$.fn.cycle.transitions.zoom=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,false,false,true);opts.cssBefore.top=next.cycleH/2;opts.cssBefore.left=next.cycleW/2;opts.animIn={top:0,left:0,width:next.cycleW,height:next.cycleH};opts.animOut={width:0,height:0,top:curr.cycleH/2,left:curr.cycleW/2};});opts.cssFirst={top:0,left:0};opts.cssBefore={width:0,height:0};};$.fn.cycle.transitions.fadeZoom=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,false,false);opts.cssBefore.left=next.cycleW/2;opts.cssBefore.top=next.cycleH/2;opts.animIn={top:0,left:0,width:next.cycleW,height:next.cycleH};});opts.cssBefore={width:0,height:0};opts.animOut={opacity:0};};$.fn.cycle.transitions.blindX=function($cont,$slides,opts){var w=$cont.css("overflow","hidden").width();opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts);opts.animIn.width=next.cycleW;opts.animOut.left=curr.cycleW;});opts.cssBefore={left:w,top:0};opts.animIn={left:0};opts.animOut={left:w};};$.fn.cycle.transitions.blindY=function($cont,$slides,opts){var h=$cont.css("overflow","hidden").height();opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts);opts.animIn.height=next.cycleH;opts.animOut.top=curr.cycleH;});opts.cssBefore={top:h,left:0};opts.animIn={top:0};opts.animOut={top:h};};$.fn.cycle.transitions.blindZ=function($cont,$slides,opts){var h=$cont.css("overflow","hidden").height();var w=$cont.width();opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts);opts.animIn.height=next.cycleH;opts.animOut.top=curr.cycleH;});opts.cssBefore={top:h,left:w};opts.animIn={top:0,left:0};opts.animOut={top:h,left:w};};$.fn.cycle.transitions.growX=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,false,true);opts.cssBefore.left=this.cycleW/2;opts.animIn={left:0,width:this.cycleW};opts.animOut={left:0};});opts.cssBefore={width:0,top:0};};$.fn.cycle.transitions.growY=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,true,false);opts.cssBefore.top=this.cycleH/2;opts.animIn={top:0,height:this.cycleH};opts.animOut={top:0};});opts.cssBefore={height:0,left:0};};$.fn.cycle.transitions.curtainX=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,false,true,true);opts.cssBefore.left=next.cycleW/2;opts.animIn={left:0,width:this.cycleW};opts.animOut={left:curr.cycleW/2,width:0};});opts.cssBefore={top:0,width:0};};$.fn.cycle.transitions.curtainY=function($cont,$slides,opts){opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,true,false,true);opts.cssBefore.top=next.cycleH/2;opts.animIn={top:0,height:next.cycleH};opts.animOut={top:curr.cycleH/2,height:0};});opts.cssBefore={left:0,height:0};};$.fn.cycle.transitions.cover=function($cont,$slides,opts){var d=opts.direction||"left";var w=$cont.css("overflow","hidden").width();var h=$cont.height();opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts);if(d=="right"){opts.cssBefore.left=-w;}else{if(d=="up"){opts.cssBefore.top=h;}else{if(d=="down"){opts.cssBefore.top=-h;}else{opts.cssBefore.left=w;}}}});opts.animIn={left:0,top:0};opts.animOut={opacity:1};opts.cssBefore={top:0,left:0};};$.fn.cycle.transitions.uncover=function($cont,$slides,opts){var d=opts.direction||"left";var w=$cont.css("overflow","hidden").width();var h=$cont.height();opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,true,true,true);if(d=="right"){opts.animOut.left=w;}else{if(d=="up"){opts.animOut.top=-h;}else{if(d=="down"){opts.animOut.top=h;}else{opts.animOut.left=-w;}}}});opts.animIn={left:0,top:0};opts.animOut={opacity:1};opts.cssBefore={top:0,left:0};};$.fn.cycle.transitions.toss=function($cont,$slides,opts){var w=$cont.css("overflow","visible").width();var h=$cont.height();opts.before.push(function(curr,next,opts){$.fn.cycle.commonReset(curr,next,opts,true,true,true);if(!opts.animOut.left&&!opts.animOut.top){opts.animOut={left:w*2,top:-h/2,opacity:0};}else{opts.animOut.opacity=0;}});opts.cssBefore={left:0,top:0};opts.animIn={left:0};};$.fn.cycle.transitions.wipe=function($cont,$slides,opts){var w=$cont.css("overflow","hidden").width();var h=$cont.height();opts.cssBefore=opts.cssBefore||{};var clip;if(opts.clip){if(/l2r/.test(opts.clip)){clip="rect(0px 0px "+h+"px 0px)";}else{if(/r2l/.test(opts.clip)){clip="rect(0px "+w+"px "+h+"px "+w+"px)";}else{if(/t2b/.test(opts.clip)){clip="rect(0px "+w+"px 0px 0px)";}else{if(/b2t/.test(opts.clip)){clip="rect("+h+"px "+w+"px "+h+"px 0px)";}else{if(/zoom/.test(opts.clip)){var t=parseInt(h/2);var l=parseInt(w/2);clip="rect("+t+"px "+l+"px "+t+"px "+l+"px)";}}}}}}opts.cssBefore.clip=opts.cssBefore.clip||clip||"rect(0px 0px 0px 0px)";var d=opts.cssBefore.clip.match(/(\d+)/g);var t=parseInt(d[0]),r=parseInt(d[1]),b=parseInt(d[2]),l=parseInt(d[3]);opts.before.push(function(curr,next,opts){if(curr==next){return;}var $curr=$(curr),$next=$(next);$.fn.cycle.commonReset(curr,next,opts,true,true,false);opts.cssAfter.display="block";var step=1,count=parseInt((opts.speedIn/13))-1;(function f(){var tt=t?t-parseInt(step*(t/count)):0;var ll=l?l-parseInt(step*(l/count)):0;var bb=b<h?b+parseInt(step*((h-b)/count||1)):h;var rr=r<w?r+parseInt(step*((w-r)/count||1)):w;$next.css({clip:"rect("+tt+"px "+rr+"px "+bb+"px "+ll+"px)"});(step++<=count)?setTimeout(f,13):$curr.css("display","none");})();});opts.cssBefore={display:"block",opacity:1,top:0,left:0};opts.animIn={left:0};opts.animOut={left:0};};})(jQuery);
/*************************************
 * jQuery Pop-Up Calendar 2.7
 *************************************/
function PopUpCal() {
	this._nextId = 0; // Next ID for a calendar instance
	this._inst = []; // List of instances indexed by ID
	this._curInst = null; // The current instance in use
	this._disabledInputs = []; // List of calendar inputs that have been disabled
	this._popUpShowing = false; // True if the popup calendar is showing , false if not
	this._inDialog = false; // True if showing within a "dialog", false if not
	this.regional = []; // Available regional settings, indexed by language code
	this.regional[''] = { // Default regional settings
		clearText: 'Clear', // Display text for clear link
		closeText: 'Close', // Display text for close link
		prevText: '&lt;Prev', // Display text for previous month link
		nextText: 'Next&gt;', // Display text for next month link
		currentText: 'Today', // Display text for current month link
		dayNames: ['Su','Mo','Tu','We','Th','Fr','Sa'], // Names of days starting at Sunday
		monthNames: ['January','February','March','April','May','June',
			'July','August','September','October','November','December'], // Names of months
		dateFormat: 'DMY/' // First three are day, month, year in the required order,
			// fourth (optional) is the separator, e.g. US would be 'MDY/', ISO would be 'YMD-'
	};
	this._defaults = { // Global defaults for all the calendar instances
		autoPopUp: 'focus', // 'focus' for popup on focus,
			// 'button' for trigger button, or 'both' for either
		appendText: '', // Display text following the input box, e.g. showing the format
		buttonText: '...', // Text for trigger button
		buttonImage: '', // URL for trigger button image
		buttonImageOnly: false, // True if the image appears alone, false if it appears on a button
		closeAtTop: true, // True to have the clear/close at the top,
			// false to have them at the bottom
		hideIfNoPrevNext: false, // True to hide next/previous month links
			// if not applicable, false to just disable them
		changeMonth: true, // True if month can be selected directly, false if only prev/next
		changeYear: true, // True if year can be selected directly, false if only prev/next
		yearRange: '-10:+10', // Range of years to display in drop-down,
			// either relative to current year (-nn:+nn) or absolute (nnnn:nnnn)
		firstDay: 0, // The first day of the week, Sun = 0, Mon = 1, ...
		changeFirstDay: true, // True to click on day name to change, false to remain as set
		showOtherMonths: false, // True to show dates in other months, false to leave blank
		minDate: null, // The earliest selectable date, or null for no limit
		maxDate: null, // The latest selectable date, or null for no limit
		speed: 'medium', // Speed of display/closure
		customDate: null, // Function that takes a date and returns an array with
			// [0] = true if selectable, false if not,
			// [1] = custom CSS class name(s) or '', e.g. popUpCal.noWeekends
		fieldSettings: null, // Function that takes an input field and
			// returns a set of custom settings for the calendar
		onSelect: null // Define a callback function when a date is selected
	};
	$.extend(this._defaults, this.regional['']);
	this._calendarDiv = $('<div id="calendar_div"></div>');
	$(document.body).append(this._calendarDiv);
	$(document.body).mousedown(this._checkExternalClick);
}

$.extend(PopUpCal.prototype, {
	/* Register a new calendar instance - with custom settings. */
	_register: function(inst) {
		var id = this._nextId++;
		this._inst[id] = inst;
		return id;
	},

	/* Retrieve a particular calendar instance based on its ID. */
	_getInst: function(id) {
		return this._inst[id] || id;
	},

	/* Override the default settings for all instances of the calendar. 
	   @param  settings  object - the new settings to use as defaults (anonymous object)
	   @return void */
	setDefaults: function(settings) {
		$.extend(this._defaults, settings || {});
	},

	/* Handle keystrokes. */
	_doKeyDown: function(e) {
		var inst = popUpCal._getInst(this._calId);
		if (popUpCal._popUpShowing) {
			switch (e.keyCode) {
				case 9:  popUpCal.hideCalendar(inst, '');
						break; // hide on tab out
				case 13: popUpCal._selectDate(inst);
						break; // select the value on enter
				case 27: popUpCal.hideCalendar(inst, inst._get('speed'));
						break; // hide on escape
				case 33: popUpCal._adjustDate(inst, -1, (e.ctrlKey ? 'Y' : 'M'));
						break; // previous month/year on page up/+ ctrl
				case 34: popUpCal._adjustDate(inst, +1, (e.ctrlKey ? 'Y' : 'M'));
						break; // next month/year on page down/+ ctrl
				case 35: if (e.ctrlKey) popUpCal._clearDate(inst);
						break; // clear on ctrl+end
				case 36: if (e.ctrlKey) popUpCal._gotoToday(inst);
						break; // current on ctrl+home
				case 37: if (e.ctrlKey) popUpCal._adjustDate(inst, -1, 'D');
						break; // -1 day on ctrl+left
				case 38: if (e.ctrlKey) popUpCal._adjustDate(inst, -7, 'D');
						break; // -1 week on ctrl+up
				case 39: if (e.ctrlKey) popUpCal._adjustDate(inst, +1, 'D');
						break; // +1 day on ctrl+right
				case 40: if (e.ctrlKey) popUpCal._adjustDate(inst, +7, 'D');
						break; // +1 week on ctrl+down
			}
		}
		else if (e.keyCode == 36 && e.ctrlKey) { // display the calendar on ctrl+home
			popUpCal.showFor(this);
		}
	},

	/* Filter entered characters. */
	_doKeyPress: function(e) {
		var inst = popUpCal._getInst(this._calId);
		var chr = String.fromCharCode(e.charCode == undefined ? e.keyCode : e.charCode);
		return (chr < ' ' || chr == inst._get('dateFormat').charAt(3) ||
			(chr >= '0' && chr <= '9')); // only allow numbers and separator
	},

	/* Attach the calendar to an input field. */
	_connectCalendar: function(target, inst) {
		var $input = $(target);
		var appendText = inst._get('appendText');
		if (appendText) {
			$input.after('<span class="calendar_append">' + appendText + '</span>');
		}
		var autoPopUp = inst._get('autoPopUp');
		if (autoPopUp == 'focus' || autoPopUp == 'both') { // pop-up calendar when in the marked field
			$input.focus(this.showFor);
		}
		if (autoPopUp == 'button' || autoPopUp == 'both') { // pop-up calendar when button clicked
			var buttonText = inst._get('buttonText');
			var buttonImage = inst._get('buttonImage');
			var buttonImageOnly = inst._get('buttonImageOnly');
			var trigger = $(buttonImageOnly ? '<img class="calendar_trigger" src="' +
				buttonImage + '" alt="' + buttonText + '" title="' + buttonText + '"/>' :
				'<button type="button" class="calendar_trigger">' + (buttonImage != '' ?
				'<img src="' + buttonImage + '" alt="' + buttonText + '" title="' + buttonText + '"/>' :
				buttonText) + '</button>');
			$input.wrap('<span class="calendar_wrap"></span>').after(trigger);
			trigger.click(this.showFor);
		}
		$input.keydown(this._doKeyDown).keypress(this._doKeyPress);
		$input[0]._calId = inst._id;
	},

	/* Attach an inline calendar to a div. */
	_inlineCalendar: function(target, inst) {
		$(target).append(inst._calendarDiv);
		target._calId = inst._id;
		var date = new Date();
		inst._selectedDay = date.getDate();
		inst._selectedMonth = date.getMonth();
		inst._selectedYear = date.getFullYear();
		popUpCal._adjustDate(inst);
	},

	/* Pop-up the calendar in a "dialog" box.
	   @param  dateText  string - the initial date to display (in the current format)
	   @param  onSelect  function - the function(dateText) to call when a date is selected
	   @param  settings  object - update the dialog calendar instance's settings (anonymous object)
	   @param  pos       int[2] - coordinates for the dialog's position within the screen
			leave empty for default (screen centre)
	   @return void */
	dialogCalendar: function(dateText, onSelect, settings, pos) {
		var inst = this._dialogInst; // internal instance
		if (!inst) {
			inst = this._dialogInst = new PopUpCalInstance({}, false);
			this._dialogInput = $('<input type="text" size="1" style="position: absolute; top: -100px;"/>');
			this._dialogInput.keydown(this._doKeyDown);
			$('body').append(this._dialogInput);
			this._dialogInput[0]._calId = inst._id;
		}
		$.extend(inst._settings, settings || {});
		this._dialogInput.val(dateText);
		
		/*	Cross Browser Positioning */
		if (self.innerHeight) { // all except Explorer
			windowWidth = self.innerWidth;
			windowHeight = self.innerHeight;
		} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
			windowWidth = document.documentElement.clientWidth;
			windowHeight = document.documentElement.clientHeight;
		} else if (document.body) { // other Explorers
			windowWidth = document.body.clientWidth;
			windowHeight = document.body.clientHeight;
		} 
		this._pos = pos || // should use actual width/height below
			[(windowWidth / 2) - 100, (windowHeight / 2) - 100];

		// move input on screen for focus, but hidden behind dialog
		this._dialogInput.css('left', this._pos[0] + 'px').css('top', this._pos[1] + 'px');
		inst._settings.onSelect = onSelect;
		this._inDialog = true;
		this._calendarDiv.addClass('calendar_dialog');
		this.showFor(this._dialogInput[0]);
		if ($.blockUI) {
			$.blockUI(this._calendarDiv);
		}
	},

	/* Enable the input field(s) for entry.
	   @param  inputs  element/object - single input field or jQuery collection of input fields
	   @return void */
	enableFor: function(inputs) {
		inputs = (inputs.jquery ? inputs : $(inputs));
		inputs.each(function() {
			this.disabled = false;
			$('../button.calendar_trigger', this).each(function() { this.disabled = false; });
			$('../img.calendar_trigger',
this).css({opacity:'1.0',cursor:''});
			var $this = this;
			popUpCal._disabledInputs = $.map(popUpCal._disabledInputs,
				function(value) { return (value == $this ? null : value); }); // delete entry
		});
	},

	/* Disable the input field(s) from entry.
	   @param  inputs  element/object - single input field or jQuery collection of input fields
	   @return void */
	disableFor: function(inputs) {
		inputs = (inputs.jquery ? inputs : $(inputs));
		inputs.each(function() {
			this.disabled = true;
			$('../button.calendar_trigger', this).each(function() { this.disabled = true; });
			$('../img.calendar_trigger', this).css({opacity:'0.5',cursor:'default'});
			var $this = this;
			popUpCal._disabledInputs = $.map(popUpCal._disabledInputs,
				function(value) { return (value == $this ? null : value); }); // delete entry
			popUpCal._disabledInputs[popUpCal._disabledInputs.length] = this;
		});
	},

	/* Update the settings for a calendar attached to an input field or division.
	   @param  control   element - the input field or div/span attached to the calendar
	   @param  settings  object - the new settings to update
	   @return void */
	reconfigureFor: function(control, settings) {
		var inst = this._getInst(control._calId);
		if (inst) {
			$.extend(inst._settings, settings || {});
			this._updateCalendar(inst);
		}
	},

	/* Set the date for a calendar attached to an input field or division.
	   @param  control  element - the input field or div/span attached to the calendar
	   @param  date     Date - the new date
	   @return void */
	setDateFor: function(control, date) {
		var inst = this._getInst(control._calId);
		if (inst) {
			inst._setDate(date);
		}
	},

	/* Retrieve the date for a calendar attached to an input field or division.
	   @param  control  element - the input field or div/span attached to the calendar
	   @return Date - the current date */
	getDateFor: function(control) {
		var inst = this._getInst(control._calId);
		return (inst ? inst._getDate() : null);
	},

	/* Pop-up the calendar for a given input field.
	   @param  target  element - the input field attached to the calendar
	   @return void */
	showFor: function(target) {
		var input = (target.nodeName && target.nodeName.toLowerCase() == 'input' ? target : this);
		if (input.nodeName.toLowerCase() != 'input') { // find from button/image trigger
			input = $('input', input.parentNode)[0];
		}
		if (popUpCal._lastInput == input) { // already here
			return;
		}
		for (var i = 0; i < popUpCal._disabledInputs.length; i++) {  // check not disabled
			if (popUpCal._disabledInputs[i] == input) {
				return;
			}
		}
		var inst = popUpCal._getInst(input._calId);
		popUpCal.hideCalendar(inst, '');
		popUpCal._lastInput = input;
		inst._setDateFromField(input);
		if (popUpCal._inDialog) { // hide cursor
			input.value = '';
		}
		if (!popUpCal._pos) { // position below input
			popUpCal._pos = popUpCal._findPos(input);
			popUpCal._pos[1] += input.offsetHeight;
		}
		inst._calendarDiv.css('position', (popUpCal._inDialog && $.blockUI ? 'static' : 'absolute')).
			css('left', popUpCal._pos[0] + 'px').css('top', popUpCal._pos[1] + 'px');
		popUpCal._pos = null;
		var fieldSettings = inst._get('fieldSettings');
		$.extend(inst._settings, (fieldSettings ? fieldSettings(input) : {}));
		popUpCal._showCalendar(inst);
	},

	/* Construct and display the calendar. */
	_showCalendar: function(id) {
		var inst = this._getInst(id);
		popUpCal._updateCalendar(inst);
		if (!inst._inline) {
			var speed = inst._get('speed');
			inst._calendarDiv.show(speed, function() {
				popUpCal._popUpShowing = true;
				popUpCal._afterShow(inst);
			});
			if (speed == '') {
				popUpCal._popUpShowing = true;
				popUpCal._afterShow(inst);
			}
			if (inst._input[0].type != 'hidden') {
				inst._input[0].focus();
			}
			this._curInst = inst;
		}
	},

	/* Generate the calendar content. */
	_updateCalendar: function(inst) {
		inst._calendarDiv.empty().append(inst._generateCalendar());
		if (inst._input && inst._input != 'hidden') {
			inst._input[0].focus();
		}
	},

	/* Tidy up after displaying the calendar. */
	_afterShow: function(inst) {
		if ($.browser.msie) { // fix IE < 7 select problems
			$('#calendar_cover').css({width: inst._calendarDiv[0].offsetWidth + 4,
				height: inst._calendarDiv[0].offsetHeight + 4});
		}
		/*// re-position on screen if necessary
		var calDiv = inst._calendarDiv[0];
		var pos = popUpCal._findPos(inst._input[0]);
		if ((calDiv.offsetLeft + calDiv.offsetWidth) >
				(document.body.clientWidth + document.body.scrollLeft)) {
			inst._calendarDiv.css('left', (pos[0] + inst._input[0].offsetWidth - calDiv.offsetWidth) + 'px');
		}
		if ((calDiv.offsetTop + calDiv.offsetHeight) >
				(document.body.clientHeight + document.body.scrollTop)) {
			inst._calendarDiv.css('top', (pos[1] - calDiv.offsetHeight) + 'px');
		}*/
	},

	/* Hide the calendar from view.
	   @param  id     string/object - the ID of the current calendar instance,
			or the instance itself
	   @param  speed  string - the speed at which to close the calendar
	   @return void */
	hideCalendar: function(id, speed) {
		var inst = this._getInst(id);
		if (popUpCal._popUpShowing) {
			speed = (speed != null ? speed : inst._get('speed'));
			inst._calendarDiv.hide(speed, function() {
				popUpCal._tidyDialog(inst);
			});
			if (speed == '') {
				popUpCal._tidyDialog(inst);
			}
			popUpCal._popUpShowing = false;
			popUpCal._lastInput = null;
			inst._settings.prompt = null;
			if (popUpCal._inDialog) {
				popUpCal._dialogInput.css('position', 'absolute').
					css('left', '0px').css('top', '-100px');
				if ($.blockUI) {
					$.unblockUI();
					$('body').append(this._calendarDiv);
				}
			}
			popUpCal._inDialog = false;
		}
		popUpCal._curInst = null;
	},

	/* Tidy up after a dialog display. */
	_tidyDialog: function(inst) {
		inst._calendarDiv.removeClass('calendar_dialog');
		$('.calendar_prompt', inst._calendarDiv).remove();
	},

	/* Close calendar if clicked elsewhere. */
	_checkExternalClick: function(event) {
		if (!popUpCal._curInst) {
			return;
		}
		var target = $(event.target);
		if( (target.parents("#calendar_div").length == 0)
			&& (target.attr('class') != 'calendar_trigger')
			&& popUpCal._popUpShowing 
			&& !(popUpCal._inDialog && $.blockUI) )
		{
			popUpCal.hideCalendar(popUpCal._curInst, '');
		}
	},

	/* Adjust one of the date sub-fields. */
	_adjustDate: function(id, offset, period) {
		var inst = this._getInst(id);
		inst._adjustDate(offset, period);
		this._updateCalendar(inst);
	},

	/* Action for current link. */
	_gotoToday: function(id) {
		var date = new Date();
		var inst = this._getInst(id);
		inst._selectedDay = date.getDate();
		inst._selectedMonth = date.getMonth();
		inst._selectedYear = date.getFullYear();
		this._adjustDate(inst);
	},

	/* Action for selecting a new month/year. */
	_selectMonthYear: function(id, select, period) {
		var inst = this._getInst(id);
		inst._selectingMonthYear = false;
		inst[period == 'M' ? '_selectedMonth' : '_selectedYear'] =
			select.options[select.selectedIndex].value - 0;
		this._adjustDate(inst);
	},

	/* Restore input focus after not changing month/year. */
	_clickMonthYear: function(id) {
		var inst = this._getInst(id);
		if (inst._input && inst._selectingMonthYear && !$.browser.msie) {
			inst._input[0].focus();
		}
		inst._selectingMonthYear = !inst._selectingMonthYear;
	},

	/* Action for changing the first week day. */
	_changeFirstDay: function(id, a) {
		var inst = this._getInst(id);
		var dayNames = inst._get('dayNames');
		var value = a.firstChild.nodeValue;
		for (var i = 0; i < 7; i++) {
			if (dayNames[i] == value) {
				inst._settings.firstDay = i;
				break;
			}
		}
		this._updateCalendar(inst);
	},

	/* Action for selecting a day. */
	_selectDay: function(id, td) {
		var inst = this._getInst(id);
		inst._selectedDay = $("a", td).html();
		this._selectDate(id);
	},

	/* Erase the input field and hide the calendar. */
	_clearDate: function(id) {
		this._selectDate(id, '');
	},

	/* Update the input field with the selected date. */
	_selectDate: function(id, dateStr) {
		var inst = this._getInst(id);
		dateStr = (dateStr != null ? dateStr : inst._formatDate());
		if (inst._input) {
			inst._input.val(dateStr);
		}
		var onSelect = inst._get('onSelect');
		if (onSelect) {
			onSelect(dateStr);  // trigger custom callback
		}
		else {
			inst._input.trigger('change'); // fire the change event
		}
		if (inst._inline) {
			this._updateCalendar(inst);
		}
		else {
			this.hideCalendar(inst, inst._get('speed'));
		}
	},

	/* Set as customDate function to prevent selection of weekends.
	   @param  date  Date - the date to customise
	   @return [boolean, string] - is this date selectable?, what is its CSS class? */
	noWeekends: function(date) {
		var day = date.getDay();
		return [(day > 0 && day < 6), ''];
	},

	/* Find an object's position on the screen. */
	_findPos: function(obj) {
		if (obj.type == 'hidden') {
			obj = obj.nextSibling;
		}
		var curleft = curtop = 0;
		if (obj.offsetParent) {
			curleft = obj.offsetLeft;
			curtop = obj.offsetTop;
			while (obj = obj.offsetParent) {
				var origcurleft = curleft;
				curleft += obj.offsetLeft;
				if (curleft < 0) {
					curleft = origcurleft;
				}
				curtop += obj.offsetTop;
			}
		}
		return [curleft,curtop];
	}
});

/* Individualised settings for calendars applied to one or more related inputs.
   Instances are managed and manipulated through the PopUpCal manager. */
function PopUpCalInstance(settings, inline) {
	this._id = popUpCal._register(this);
	this._selectedDay = 0;
	this._selectedMonth = 0; // 0-11
	this._selectedYear = 0; // 4-digit year
	this._input = null; // The attached input field
	this._inline = inline; // True if showing inline, false if used in a popup
	this._calendarDiv = (!inline ? popUpCal._calendarDiv :
		$('<div id="calendar_div_' + this._id + '" class="calendar_inline"></div>'));
	if (inline) {
		var date = new Date();
		this._currentDay = date.getDate();
		this._currentMonth = date.getMonth();
		this._currentYear = date.getFullYear();
	}
	// customise the calendar object - uses manager defaults if not overridden
	this._settings = $.extend({}, settings || {}); // clone
}

$.extend(PopUpCalInstance.prototype, {
	/* Get a setting value, defaulting if necessary. */
	_get: function(name) {
		return (this._settings[name] != null ? this._settings[name] : popUpCal._defaults[name]);
	},

	/* Parse existing date and initialise calendar. */
	_setDateFromField: function(input) {
		this._input = $(input);
		var dateFormat = this._get('dateFormat');
		var currentDate = this._input.val().split(dateFormat.charAt(3));
		if (currentDate.length == 3) {
			this._currentDay = parseInt(currentDate[dateFormat.indexOf('D')], 10);
			this._currentMonth = parseInt(currentDate[dateFormat.indexOf('M')], 10) - 1;
			this._currentYear = parseInt(currentDate[dateFormat.indexOf('Y')], 10);
		}
		else {
			var date = new Date();
			this._currentDay = date.getDate();
			this._currentMonth = date.getMonth();
			this._currentYear = date.getFullYear();
		}
		this._selectedDay = this._currentDay;
		this._selectedMonth = this._currentMonth;
		this._selectedYear = this._currentYear;
		this._adjustDate();
	},

	/* Set the date directly. */
	_setDate: function(date) {
		this._selectedDay = this._currentDay = date.getDate();
		this._selectedMonth = this._currentMonth = date.getMonth();
		this._selectedYear = this._currentYear = date.getFullYear();
		this._adjustDate();
	},

	/* Retrieve the date directly. */
	_getDate: function() {
		return new Date(this._currentYear, this._currentMonth, this._currentDay);
	},

	/* Generate the HTML for the current state of the calendar. */
	_generateCalendar: function() {
		var today = new Date();
		today = new Date(today.getFullYear(), today.getMonth(), today.getDate()); // clear time
		// build the calendar HTML
		var controls = '<div class="calendar_control">' +
			'<a class="calendar_clear" onclick="popUpCal._clearDate(' + this._id + ');">' +
			this._get('clearText') + '</a>' +
			'<a class="calendar_close" onclick="popUpCal.hideCalendar(' + this._id + ');">' +
			this._get('closeText') + '</a></div>';
		var prompt = this._get('prompt');
		var closeAtTop = this._get('closeAtTop');
		var hideIfNoPrevNext = this._get('hideIfNoPrevNext');
		// controls and links
		var html = (prompt ? '<div class="calendar_prompt">' + prompt + '</div>' : '') +
			(closeAtTop && !this._inline ? controls : '') + '<div class="calendar_links">' +
			(this._canAdjustMonth(-1) ? '<a class="calendar_prev" ' +
			'onclick="popUpCal._adjustDate(' + this._id + ', -1, \'M\');">' + this._get('prevText') + '</a>' :
			(hideIfNoPrevNext ? '' : '<label class="calendar_prev">' + this._get('prevText') + '</label>')) +
			(this._isInRange(today) ? '<a class="calendar_current" ' +
			'onclick="popUpCal._gotoToday(' + this._id + ');">' + this._get('currentText') + '</a>' : '') +
			(this._canAdjustMonth(+1) ? '<a class="calendar_next" ' +
			'onclick="popUpCal._adjustDate(' + this._id + ', +1, \'M\');">' + this._get('nextText') + '</a>' :
			(hideIfNoPrevNext ? '' : '<label class="calendar_next">' + this._get('nextText') + '</label>')) +
			'</div><div class="calendar_header">';
		var minDate = this._get('minDate');
		var maxDate = this._get('maxDate');
		// month selection
		var monthNames = this._get('monthNames');
		if (!this._get('changeMonth')) {
			html += monthNames[this._selectedMonth] + '&nbsp;';
		}
		else {
			var inMinYear = (minDate && minDate.getFullYear() == this._selectedYear);
			var inMaxYear = (maxDate && maxDate.getFullYear() == this._selectedYear);
			html += '<select class="calendar_newMonth" ' +
				'onchange="popUpCal._selectMonthYear(' + this._id + ', this, \'M\');" ' +
				'onclick="popUpCal._clickMonthYear(' + this._id + ');">';
			for (var month = 0; month < 12; month++) {
				if ((!inMinYear || month >= minDate.getMonth()) &&
						(!inMaxYear || month <= maxDate.getMonth())) {
					html += '<option value="' + month + '"' +
						(month == this._selectedMonth ? ' selected="selected"' : '') +
						'>' + monthNames[month] + '</option>';
				}
			}
			html += '</select>';
		}
		// year selection
		if (!this._get('changeYear')) {
			html += this._selectedYear;
		}
		else {
			// determine range of years to display
			var years = this._get('yearRange').split(':');
			var year = 0;
			var endYear = 0;
			if (years.length != 2) {
				year = this._selectedYear - 10;
				endYear = this._selectedYear + 10;
			}
			else if (years[0].charAt(0) == '+' || years[0].charAt(0) == '-') {
				year = this._selectedYear + parseInt(years[0], 10);
				endYear = this._selectedYear + parseInt(years[1], 10);
			}
			else {
				year = parseInt(years[0], 10);
				endYear = parseInt(years[1], 10);
			}
			year = (minDate ? Math.max(year, minDate.getFullYear()) : year);
			endYear = (maxDate ? Math.min(endYear, maxDate.getFullYear()) : endYear);
			html += '<select class="calendar_newYear" onchange="popUpCal._selectMonthYear(' +
				this._id + ', this, \'Y\');" ' + 'onclick="popUpCal._clickMonthYear(' +
				this._id + ');">';
			for (; year <= endYear; year++) {
				html += '<option value="' + year + '"' +
					(year == this._selectedYear ? ' selected="selected"' : '') +
					'>' + year + '</option>';
			}
			html += '</select>';
		}
		html += '</div><table class="calendar" cellpadding="0" cellspacing="0"><thead>' +
			'<tr class="calendar_titleRow">';
		var firstDay = this._get('firstDay');
		var changeFirstDay = this._get('changeFirstDay');
		var dayNames = this._get('dayNames');
		for (var dow = 0; dow < 7; dow++) { // days of the week
			html += '<td>' + (!changeFirstDay ? '' : '<a onclick="popUpCal._changeFirstDay(' +
				this._id + ', this);">') + dayNames[(dow + firstDay) % 7] +
				(changeFirstDay ? '</a>' : '') + '</td>';
		}
		html += '</tr></thead><tbody>';
		var daysInMonth = this._getDaysInMonth(this._selectedYear, this._selectedMonth);
		this._selectedDay = Math.min(this._selectedDay, daysInMonth);
		var leadDays = (this._getFirstDayOfMonth(this._selectedYear, this._selectedMonth) - firstDay + 7) % 7;
		var currentDate = new Date(this._currentYear, this._currentMonth, this._currentDay);
		var selectedDate = new Date(this._selectedYear, this._selectedMonth, this._selectedDay);
		var printDate = new Date(this._selectedYear, this._selectedMonth, 1 - leadDays);
		var numRows = Math.ceil((leadDays + daysInMonth) / 7); // calculate the number of rows to generate
		var customDate = this._get('customDate');
		var showOtherMonths = this._get('showOtherMonths');
		for (var row = 0; row < numRows; row++) { // create calendar rows
			html += '<tr class="calendar_daysRow">';
			for (var dow = 0; dow < 7; dow++) { // create calendar days
				var customSettings = (customDate ? customDate(printDate) : [true, '']);
				var otherMonth = (printDate.getMonth() != this._selectedMonth);
				var unselectable = otherMonth || !customSettings[0] ||
					(minDate && printDate < minDate) || (maxDate && printDate > maxDate);
				html += '<td class="calendar_daysCell' +
					((dow + firstDay + 6) % 7 >= 5 ? ' calendar_weekEndCell' : '') + // highlight weekends
					(otherMonth ? ' calendar_otherMonth' : '') + // highlight days from other months
					(printDate.getTime() == selectedDate.getTime() ? ' calendar_daysCellOver' : '') + // highlight selected day
					(unselectable ? ' calendar_unselectable' : '') +  // highlight unselectable days
					(!otherMonth || showOtherMonths ? ' ' + customSettings[1] : '') + // highlight custom dates
					(printDate.getTime() == currentDate.getTime() ? ' calendar_currentDay' : // highlight current day
					(printDate.getTime() == today.getTime() ? ' calendar_today' : '')) + '"' + // highlight today (if different)
					(unselectable ? '' : ' onmouseover="$(this).addClass(\'calendar_daysCellOver\');"' +
					' onmouseout="$(this).removeClass(\'calendar_daysCellOver\');"' +
					' onclick="popUpCal._selectDay(' + this._id + ', this);"') + '>' + // actions
					(otherMonth ? (showOtherMonths ? printDate.getDate() : '&nbsp;') : // display for other months
					(unselectable ? printDate.getDate() : '<a>' + printDate.getDate() + '</a>')) + '</td>'; // display for this month
				printDate.setDate(printDate.getDate() + 1);
			}
			html += '</tr>';
		}
		html += '</tbody></table>' + (!closeAtTop && !this._inline ? controls : '') +
			'<div style="clear: both;"></div>' + (!$.browser.msie ? '' :
			'<!--[if lte IE 6.5]><iframe src="javascript:false;" class="calendar_cover"></iframe><![endif]-->');
		return html;
	},

	/* Adjust one of the date sub-fields. */
	_adjustDate: function(offset, period) {
		var date = new Date(this._selectedYear + (period == 'Y' ? offset : 0),
			this._selectedMonth + (period == 'M' ? offset : 0),
			this._selectedDay + (period == 'D' ? offset : 0));
		// ensure it is within the bounds set
		var minDate = this._get('minDate');
		var maxDate = this._get('maxDate');
		date = (minDate && date < minDate ? minDate : date);
		date = (maxDate && date > maxDate ? maxDate : date);
		this._selectedDay = date.getDate();
		this._selectedMonth = date.getMonth();
		this._selectedYear = date.getFullYear();
	},

	/* Find the number of days in a given month. */
	_getDaysInMonth: function(year, month) {
		return 32 - new Date(year, month, 32).getDate();
	},

	/* Find the day of the week of the first of a month. */
	_getFirstDayOfMonth: function(year, month) {
		return new Date(year, month, 1).getDay();
	},

	/* Determines if we should allow a "next/prev" month display change. */
	_canAdjustMonth: function(offset) {
		var date = new Date(this._selectedYear, this._selectedMonth + offset, 1);
		if (offset < 0) {
			date.setDate(this._getDaysInMonth(date.getFullYear(), date.getMonth()));
		}
		return this._isInRange(date);
	},

	/* Is the given date in the accepted range? */
	_isInRange: function(date) {
		var minDate = this._get('minDate');
		var maxDate = this._get('maxDate');
		return ((!minDate || date >= minDate) && (!maxDate || date <= maxDate));
	},

	/* Format the given date for display. */
	_formatDate: function() {
		var day = this._currentDay = this._selectedDay;
		var month = this._currentMonth = this._selectedMonth;
		var year = this._currentYear = this._selectedYear;
		month++; // adjust javascript month
		var dateFormat = this._get('dateFormat');
		var dateString = '';
		for (var i = 0; i < 3; i++) {
			dateString += dateFormat.charAt(3) +
				(dateFormat.charAt(i) == 'D' ? (day < 10 ? '0' : '') + day :
				(dateFormat.charAt(i) == 'M' ? (month < 10 ? '0' : '') + month :
				(dateFormat.charAt(i) == 'Y' ? year : '?')));
		}
		return dateString.substring(dateFormat.charAt(3) ? 1 : 0);
	}
});

/* Attach the calendar to a jQuery selection.
   @param  settings  object - the new settings to use for this calendar instance (anonymous)
   @return jQuery object - for chaining further calls */
$.fn.calendar = function(settings) {
	return this.each(function() {
		// check for settings on the control itself - in namespace 'cal:'
		var inlineSettings = null;
		for (attrName in popUpCal._defaults) {
			var attrValue = this.getAttribute('cal:' + attrName);
			if (attrValue) {
				inlineSettings = inlineSettings || {};
				try {
					inlineSettings[attrName] = eval(attrValue);
				}
				catch (err) {
					inlineSettings[attrName] = attrValue;
				}
			}
		}
		var nodeName = this.nodeName.toLowerCase();
		if (nodeName == 'input') {
			var instSettings = (inlineSettings ? $.extend($.extend({}, settings || {}),
				inlineSettings || {}) : settings); // clone and customise
			var inst = (inst && !inlineSettings ? inst :
				new PopUpCalInstance(instSettings, false));
			popUpCal._connectCalendar(this, inst);
		} 
		else if (nodeName == 'div' || nodeName == 'span') {
			var instSettings = $.extend($.extend({}, settings || {}),
				inlineSettings || {}); // clone and customise
			var inst = new PopUpCalInstance(instSettings, true);
			popUpCal._inlineCalendar(this, inst);
		}
	});
};

/* Initialise the calendar. */
$(document).ready(function() {
   popUpCal = new PopUpCal(); // singleton instance
});
/*************************************
 * jQuery selectBox v2.1 
 *************************************/
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(6($){$.u.G=6(){4 e=6(a,v,t,b){4 c=P.Q("R");c.j=v,c.C=t;4 o=a.x;4 d=o.l;3(!a.n){a.n={};p(4 i=0;i<d;i++){a.n[o[i].j]=i}}3(8 a.n[v]=="M")a.n[v]=d;a.x[a.n[v]]=c;3(b){c.k=9}};4 a=N;3(a.l==0)7 5;4 f=9;4 m=q;4 g,v,t;3(8(a[0])=="z"){m=9;g=a[0]}3(a.l>=2){3(8(a[1])=="H")f=a[1];h 3(8(a[2])=="H")f=a[2];3(!m){v=a[0];t=a[1]}}5.y(6(){3(5.A.s()!="B")7;3(m){p(4 a S g){e(5,a,g[a],f)}}h{e(5,v,t,f)}});7 5};$.u.T=6(b,c,d,e,f){3(8(b)!="D")7 5;3(8(c)!="z")c={};3(8(d)!="H")d=9;5.y(6(){4 a=5;$.U(b,c,6(r){$(a).G(r,d);3(8 e=="6"){3(8 f=="z"){e.V(a,f)}h{e.I(a)}}})});7 5};$.u.W=6(){4 a=N;3(a.l==0)7 5;4 d=8(a[0]);4 v,i;3(d=="D"||d=="z"||d=="6")v=a[0];h 3(d=="X")i=a[0];h 7 5;5.y(6(){3(5.A.s()!="B")7;3(5.n)5.n=O;4 b=q;4 o=5.x;3(!!v){4 c=o.l;p(4 i=c-1;i>=0;i--){3(v.J==K){3(o[i].j.L(v)){b=9}}h 3(o[i].j==v){b=9}3(b&&a[1]===9)b=o[i].k;3(b){o[i]=O}b=q}}h{3(b&&a[1]===9)b=o[i].k;3(b){5.Y(i)}}});7 5};$.u.Z=6(f){4 a=8(f)=="M"?9:!!f;5.y(6(){3(5.A.s()!="B")7;4 o=5.x;4 d=o.l;4 e=[];p(4 i=0;i<d;i++){e[i]={v:o[i].j,t:o[i].C}}e.10(6(b,c){E=b.t.s(),F=c.t.s();3(E==F)7 0;3(a){7 E<F?-1:1}h{7 E>F?-1:1}});p(4 i=0;i<d;i++){o[i].C=e[i].t;o[i].j=e[i].v}});7 5};$.u.11=6(b,d){4 v=b;4 e=8(b);4 c=d||q;3(e!="D"&&e!="6"&&e!="z")7 5;5.y(6(){3(5.A.s()!="B")7 5;4 o=5.x;4 a=o.l;p(4 i=0;i<a;i++){3(v.J==K){3(o[i].j.L(v)){o[i].k=9}h 3(c){o[i].k=q}}h{3(o[i].j==v){o[i].k=9}h 3(c){o[i].k=q}}}});7 5};$.u.12=6(b,c){4 w=c||"k";3($(b).13()==0)7 5;5.y(6(){3(5.A.s()!="B")7 5;4 o=5.x;4 a=o.l;p(4 i=0;i<a;i++){3(w=="14"||(w=="k"&&o[i].k)){$(b).G(o[i].j,o[i].C)}}});7 5};$.u.15=6(b,c){4 d=q;4 v=b;4 e=8(v);4 f=8(c);3(e!="D"&&e!="6"&&e!="z")7 f=="6"?5:d;5.y(6(){3(5.A.s()!="B")7 5;3(d&&f!="6")7 q;4 o=5.x;4 a=o.l;p(4 i=0;i<a;i++){3(v.J==K){3(o[i].j.L(v)){d=9;3(f=="6")c.I(o[i])}}h{3(o[i].j==v){d=9;3(f=="6")c.I(o[i])}}}});7 f=="6"?5:d}})(16);',62,69,'|||if|var|this|function|return|typeof|true||||||||else||value|selected|length||cache||for|false||toLowerCase||fn|||options|each|object|nodeName|select|text|string|o1t|o2t|addOption|boolean|call|constructor|RegExp|match|undefined|arguments|null|document|createElement|option|in|ajaxAddOption|getJSON|apply|removeOption|number|remove|sortOptions|sort|selectOptions|copyOptions|size|all|containsOption|jQuery'.split('|'),0,{}))
/*************************************
 * jQuery jTip
 *************************************/
function JT_init(){$("a.jTip").hover(function(){JT_show(this.href,this.id,this.name)},function(){$('#JT').remove()}).click(function(){return false});}
function JT_show(url,linkId,title){if(title==false)title="&nbsp;";var de=document.documentElement;var w=self.innerWidth||(de&&de.clientWidth)||document.body.clientWidth;var hasArea=w-getAbsoluteLeft(linkId);var clickElementy=getAbsoluteTop(linkId)-3;var queryString=url.replace(/^[^\?]+\??/,'');var params=parseQuery(queryString);if(params['width']===undefined){params['width']=250};if(params['link']!==undefined){$('#'+linkId).bind('click',function(){window.location=params['link']});$('#'+linkId).css('cursor','pointer');}
if(hasArea>((params['width']*1)+75)){$("body").append("<div id='JT' style='width:"+params['width']*1+"px'><div id='JT_arrow_left'></div><div id='JT_close_left'>"+title+"</div><div id='JT_copy'><div class='JT_loader'><div></div></div>");var arrowOffset=getElementWidth(linkId)+11;var clickElementx=getAbsoluteLeft(linkId)+arrowOffset;}else{$("body").append("<div id='JT' style='width:"+params['width']*1+"px'><div id='JT_arrow_right' style='left:"+((params['width']*1)+1)+"px'></div><div id='JT_close_right'>"+title+"</div><div id='JT_copy'><div class='JT_loader'><div></div></div>");var clickElementx=getAbsoluteLeft(linkId)-((params['width']*1)+15);}
$('#JT').css({left:clickElementx+"px",top:clickElementy+"px"});$('#JT').show();$('#JT_copy').load(url);}
function getElementWidth(objectId){x=document.getElementById(objectId);return x.offsetWidth;}
function getAbsoluteLeft(objectId){o=document.getElementById(objectId)
oLeft=o.offsetLeft
while(o.offsetParent!=null){oParent=o.offsetParent
oLeft+=oParent.offsetLeft
o=oParent}
return oLeft}
function getAbsoluteTop(objectId){o=document.getElementById(objectId)
oTop=o.offsetTop
while(o.offsetParent!=null){oParent=o.offsetParent
oTop+=oParent.offsetTop
o=oParent}
return oTop}
function parseQuery(query){var Params=new Object();if(!query)return Params;var Pairs=query.split(/[;&]/);for(var i=0;i<Pairs.length;i++){var KeyVal=Pairs[i].split('=');if(!KeyVal||KeyVal.length!=2)continue;var key=unescape(KeyVal[0]);var val=unescape(KeyVal[1]);val=val.replace(/\+/g,' ');Params[key]=val;}
return Params;}
function blockEvents(evt){if(evt.target){evt.preventDefault();}else{evt.returnValue=false;}}
/*************************************
 * jQuery imagefit
  *************************************/
(function($) {
	$.fn.imagefit = function(options) {
		var fit = {
			all : function(imgs){
				imgs.each(function(){
					fit.one(this);
					})
				},
			one : function(img){
				$(img)
					.width('100%').each(function()
					{
						$(this).height(Math.round(
							$(this).attr('startheight')*($(this).width()/$(this).attr('startwidth')))
						);
					})
				}
		};
		
		this.each(function(){
				var container = this;
				
				// store list of contained images (excluding those in tables)
				var imgs = $('img', container).not($("table img"));
				
				// store initial dimensions on each image 
				imgs.each(function(){
					$(this).attr('startwidth', $(this).width())
						.attr('startheight', $(this).height())
						.css('max-width', $(this).attr('startwidth')+"px");
				
					fit.one(this);
				});
				// Re-adjust when window width is changed
				$(window).bind('resize', function(){
					fit.all(imgs);
				});
			});
		return this;
	};
})(jQuery);
/********
 * liveQuery v1.1.1 (this is needed due to bugs with live/delegate binds on form submits)
 ********/
(function(a){a.extend(a.fn,{livequery:function(e,d,c){var b=this,f;if(a.isFunction(e)){c=d,d=e,e=undefined}a.each(a.livequery.queries,function(g,h){if(b.selector==h.selector&&b.context==h.context&&e==h.type&&(!d||d.$lqguid==h.fn.$lqguid)&&(!c||c.$lqguid==h.fn2.$lqguid)){return(f=h)&&false}});f=f||new a.livequery(this.selector,this.context,e,d,c);f.stopped=false;f.run();return this},expire:function(e,d,c){var b=this;if(a.isFunction(e)){c=d,d=e,e=undefined}a.each(a.livequery.queries,function(f,g){if(b.selector==g.selector&&b.context==g.context&&(!e||e==g.type)&&(!d||d.$lqguid==g.fn.$lqguid)&&(!c||c.$lqguid==g.fn2.$lqguid)&&!this.stopped){a.livequery.stop(g.id)}});return this}});a.livequery=function(b,d,f,e,c){this.selector=b;this.context=d;this.type=f;this.fn=e;this.fn2=c;this.elements=[];this.stopped=false;this.id=a.livequery.queries.push(this)-1;e.$lqguid=e.$lqguid||a.livequery.guid++;if(c){c.$lqguid=c.$lqguid||a.livequery.guid++}return this};a.livequery.prototype={stop:function(){var b=this;if(this.type){this.elements.unbind(this.type,this.fn)}else{if(this.fn2){this.elements.each(function(c,d){b.fn2.apply(d)})}}this.elements=[];this.stopped=true},run:function(){if(this.stopped){return}var d=this;var e=this.elements,c=a(this.selector,this.context),b=c.not(e);this.elements=c;if(this.type){b.bind(this.type,this.fn);if(e.length>0){a.each(e,function(f,g){if(a.inArray(g,c)<0){a.event.remove(g,d.type,d.fn)}})}}else{b.each(function(){d.fn.apply(this)});if(this.fn2&&e.length>0){a.each(e,function(f,g){if(a.inArray(g,c)<0){d.fn2.apply(g)}})}}}};a.extend(a.livequery,{guid:0,queries:[],queue:[],running:false,timeout:null,checkQueue:function(){if(a.livequery.running&&a.livequery.queue.length){var b=a.livequery.queue.length;while(b--){a.livequery.queries[a.livequery.queue.shift()].run()}}},pause:function(){a.livequery.running=false},play:function(){a.livequery.running=true;a.livequery.run()},registerPlugin:function(){a.each(arguments,function(c,d){if(!a.fn[d]){return}var b=a.fn[d];a.fn[d]=function(){var e=b.apply(this,arguments);a.livequery.run();return e}})},run:function(b){if(b!=undefined){if(a.inArray(b,a.livequery.queue)<0){a.livequery.queue.push(b)}}else{a.each(a.livequery.queries,function(c){if(a.inArray(c,a.livequery.queue)<0){a.livequery.queue.push(c)}})}if(a.livequery.timeout){clearTimeout(a.livequery.timeout)}a.livequery.timeout=setTimeout(a.livequery.checkQueue,20)},stop:function(b){if(b!=undefined){a.livequery.queries[b].stop()}else{a.each(a.livequery.queries,function(c){a.livequery.queries[c].stop()})}}});a.livequery.registerPlugin("append","prepend","after","before","wrap","attr","removeAttr","addClass","removeClass","toggleClass","empty","remove","html");a(function(){a.livequery.play()})})(jQuery);
/*************************************
 * jQuery PSU
 *************************************/
jQuery.fn.addClassBySrcRegexp=function(exp,el_class){return this.each(function(){var el=$(this);var reg=new RegExp(exp,"g");if(reg.exec(el.src()))
{el.addClass(el_class);}});};jQuery.fn.prepURLForAjax=function(){return this.each(function(){var el=$(this);var reg=new RegExp('\\?',"g");if(reg.exec(el.attr('href')))
{el.attr('href',el.attr('href')+'&suppress=1');}
else
{el.attr('href',el.attr('href')+'?suppress=1');}});};jQuery.fn.toggleBlock=function(url_reg){return this.each(function(){var do_stuff=true;if(url_reg)
{var regexp=new RegExp(url_reg,"g");do_stuff=regexp.exec(document.location);}
if(do_stuff)
{var el=$(this);if($.cookie('psu_toggle_'+el.attr('id'))=='open')
{el.toggle(function(){var el2=$(this);$.cookie('psu_toggle_'+el2.attr('id'),'closed');el2.next('ul').hide();},function(){var el2=$(this);$.cookie('psu_toggle_'+el2.attr('id'),'open');el2.next('ul').show();});el.next('ul').show();}
else
{el.toggle(function(){var el2=$(this);$.cookie('psu_toggle_'+el2.attr('id'),'open');el2.next('ul').show();},function(){var el2=$(this);$.cookie('psu_toggle_'+el2.attr('id'),'closed');el2.next('ul').hide();});el.next('ul').hide();}}});};(function(){var after_type_cast={};var before_type_cast={};var cached=false;jQuery.query=function(cast){if(!cached){var q=location.search.replace(/^\?/,'').replace(/\&$/,'').split('&');for(var i=q.length-1;i>=0;i--){var p=q[i].split('='),key=p[0],val=p[1];before_type_cast[key]=val;if(/^[0-9.]+$/.test(val))
val=parseFloat(val);if(/^(true|false)$/.test(val))
val=(val=='true');if(val)
after_type_cast[key]=val;}
cached=true;}
return cast===false?before_type_cast:after_type_cast;};})();
// ColorBox v1.3.15 - a full featured, light-weight, customizable lightbox based on jQuery 1.3+
// Copyright (c) 2010 Jack Moore - jack@colorpowered.com
// Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
(function(b,ib){var t="none",M="LoadedContent",c=false,v="resize.",o="y",q="auto",e=true,L="nofollow",m="x";function f(a,c){a=a?' id="'+i+a+'"':"";c=c?' style="'+c+'"':"";return b("<div"+a+c+"/>")}function p(a,b){b=b===m?n.width():n.height();return typeof a==="string"?Math.round(/%/.test(a)?b/100*parseInt(a,10):parseInt(a,10)):a}function U(b){return a.photo||/\.(gif|png|jpg|jpeg|bmp)(?:\?([^#]*))?(?:#(\.*))?$/i.test(b)}function cb(a){for(var c in a)if(b.isFunction(a[c])&&c.substring(0,2)!=="on")a[c]=a[c].call(l);a.rel=a.rel||l.rel||L;a.href=a.href||b(l).attr("href");a.title=a.title||l.title;return a}function w(c,a){a&&a.call(l);b.event.trigger(c)}function jb(){var b,e=i+"Slideshow_",c="click."+i,f,k;if(a.slideshow&&h[1]){f=function(){F.text(a.slideshowStop).unbind(c).bind(V,function(){if(g<h.length-1||a.loop)b=setTimeout(d.next,a.slideshowSpeed)}).bind(W,function(){clearTimeout(b)}).one(c+" "+N,k);j.removeClass(e+"off").addClass(e+"on");b=setTimeout(d.next,a.slideshowSpeed)};k=function(){clearTimeout(b);F.text(a.slideshowStart).unbind([V,W,N,c].join(" ")).one(c,f);j.removeClass(e+"on").addClass(e+"off")};a.slideshowAuto?f():k()}}function db(c){if(!O){l=c;a=cb(b.extend({},b.data(l,r)));h=b(l);g=0;if(a.rel!==L){h=b("."+G).filter(function(){return (b.data(this,r).rel||this.rel)===a.rel});g=h.index(l);if(g===-1){h=h.add(l);g=h.length-1}}if(!u){u=D=e;j.show();if(a.returnFocus)try{l.blur();b(l).one(eb,function(){try{this.focus()}catch(a){}})}catch(f){}x.css({opacity:+a.opacity,cursor:a.overlayClose?"pointer":q}).show();a.w=p(a.initialWidth,m);a.h=p(a.initialHeight,o);d.position(0);X&&n.bind(v+P+" scroll."+P,function(){x.css({width:n.width(),height:n.height(),top:n.scrollTop(),left:n.scrollLeft()})}).trigger("scroll."+P);w(fb,a.onOpen);Y.add(H).add(I).add(F).add(Z).hide();ab.html(a.close).show()}d.load(e)}}var gb={transition:"elastic",speed:300,width:c,initialWidth:"600",innerWidth:c,maxWidth:c,height:c,initialHeight:"450",innerHeight:c,maxHeight:c,scalePhotos:e,scrolling:e,inline:c,html:c,iframe:c,photo:c,href:c,title:c,rel:c,opacity:.9,preloading:e,current:"image {current} of {total}",previous:"previous",next:"next",close:"close",open:c,returnFocus:e,loop:e,slideshow:c,slideshowAuto:e,slideshowSpeed:2500,slideshowStart:"start slideshow",slideshowStop:"stop slideshow",onOpen:c,onLoad:c,onComplete:c,onCleanup:c,onClosed:c,overlayClose:e,escKey:e,arrowKey:e},r="colorbox",i="cbox",fb=i+"_open",W=i+"_load",V=i+"_complete",N=i+"_cleanup",eb=i+"_closed",Q=i+"_purge",hb=i+"_loaded",E=b.browser.msie&&!b.support.opacity,X=E&&b.browser.version<7,P=i+"_IE6",x,j,A,s,bb,T,R,S,h,n,k,J,K,Z,Y,F,I,H,ab,B,C,y,z,l,g,a,u,D,O=c,d,G=i+"Element";d=b.fn[r]=b[r]=function(c,f){var a=this,d;if(!a[0]&&a.selector)return a;c=c||{};if(f)c.onComplete=f;if(!a[0]||a.selector===undefined){a=b("<a/>");c.open=e}a.each(function(){b.data(this,r,b.extend({},b.data(this,r)||gb,c));b(this).addClass(G)});d=c.open;if(b.isFunction(d))d=d.call(a);d&&db(a[0]);return a};d.init=function(){var l="hover",m="clear:left";n=b(ib);j=f().attr({id:r,"class":E?i+"IE":""});x=f("Overlay",X?"position:absolute":"").hide();A=f("Wrapper");s=f("Content").append(k=f(M,"width:0; height:0; overflow:hidden"),K=f("LoadingOverlay").add(f("LoadingGraphic")),Z=f("Title"),Y=f("Current"),I=f("Next"),H=f("Previous"),F=f("Slideshow").bind(fb,jb),ab=f("Close"));A.append(f().append(f("TopLeft"),bb=f("TopCenter"),f("TopRight")),f(c,m).append(T=f("MiddleLeft"),s,R=f("MiddleRight")),f(c,m).append(f("BottomLeft"),S=f("BottomCenter"),f("BottomRight"))).children().children().css({"float":"left"});J=f(c,"position:absolute; width:9999px; visibility:hidden; display:none");b("body").prepend(x,j.append(A,J));s.children().hover(function(){b(this).addClass(l)},function(){b(this).removeClass(l)}).addClass(l);B=bb.height()+S.height()+s.outerHeight(e)-s.height();C=T.width()+R.width()+s.outerWidth(e)-s.width();y=k.outerHeight(e);z=k.outerWidth(e);j.css({"padding-bottom":B,"padding-right":C}).hide();I.click(d.next);H.click(d.prev);ab.click(d.close);s.children().removeClass(l);b("."+G).live("click",function(a){if(!(a.button!==0&&typeof a.button!=="undefined"||a.ctrlKey||a.shiftKey||a.altKey)){a.preventDefault();db(this)}});x.click(function(){a.overlayClose&&d.close()});b(document).bind("keydown",function(b){if(u&&a.escKey&&b.keyCode===27){b.preventDefault();d.close()}if(u&&a.arrowKey&&!D&&h[1])if(b.keyCode===37&&(g||a.loop)){b.preventDefault();H.click()}else if(b.keyCode===39&&(g<h.length-1||a.loop)){b.preventDefault();I.click()}})};d.remove=function(){j.add(x).remove();b("."+G).die("click").removeData(r).removeClass(G)};d.position=function(f,d){function b(a){bb[0].style.width=S[0].style.width=s[0].style.width=a.style.width;K[0].style.height=K[1].style.height=s[0].style.height=T[0].style.height=R[0].style.height=a.style.height}var e,h=Math.max(document.documentElement.clientHeight-a.h-y-B,0)/2+n.scrollTop(),g=Math.max(n.width()-a.w-z-C,0)/2+n.scrollLeft();e=j.width()===a.w+z&&j.height()===a.h+y?0:f;A[0].style.width=A[0].style.height="9999px";j.dequeue().animate({width:a.w+z,height:a.h+y,top:h,left:g},{duration:e,complete:function(){b(this);D=c;A[0].style.width=a.w+z+C+"px";A[0].style.height=a.h+y+B+"px";d&&d()},step:function(){b(this)}})};d.resize=function(b){if(u){b=b||{};if(b.width)a.w=p(b.width,m)-z-C;if(b.innerWidth)a.w=p(b.innerWidth,m);k.css({width:a.w});if(b.height)a.h=p(b.height,o)-y-B;if(b.innerHeight)a.h=p(b.innerHeight,o);if(!b.innerHeight&&!b.height){b=k.wrapInner("<div style='overflow:auto'></div>").children();a.h=b.height();b.replaceWith(b.children())}k.css({height:a.h});d.position(a.transition===t?0:a.speed)}};d.prep=function(m){var c="hidden";function l(s){var p,f,m,c,l=h.length,q=a.loop;d.position(s,function(){function s(){E&&j[0].style.removeAttribute("filter")}if(u){E&&o&&k.fadeIn(100);k.show();w(hb);Z.show().html(a.title);if(l>1){typeof a.current==="string"&&Y.html(a.current.replace(/\{current\}/,g+1).replace(/\{total\}/,l)).show();I[q||g<l-1?"show":"hide"]().html(a.next);H[q||g?"show":"hide"]().html(a.previous);p=g?h[g-1]:h[l-1];m=g<l-1?h[g+1]:h[0];a.slideshow&&F.show();if(a.preloading){c=b.data(m,r).href||m.href;f=b.data(p,r).href||p.href;c=b.isFunction(c)?c.call(m):c;f=b.isFunction(f)?f.call(p):f;if(U(c))b("<img/>")[0].src=c;if(U(f))b("<img/>")[0].src=f}}K.hide();a.transition==="fade"?j.fadeTo(e,1,function(){s()}):s();n.bind(v+i,function(){d.position(0)});w(V,a.onComplete)}})}if(u){var o,e=a.transition===t?0:a.speed;n.unbind(v+i);k.remove();k=f(M).html(m);k.hide().appendTo(J.show()).css({width:function(){a.w=a.w||k.width();a.w=a.mw&&a.mw<a.w?a.mw:a.w;return a.w}(),overflow:a.scrolling?q:c}).css({height:function(){a.h=a.h||k.height();a.h=a.mh&&a.mh<a.h?a.mh:a.h;return a.h}()}).prependTo(s);J.hide();b("#"+i+"Photo").css({cssFloat:t,marginLeft:q,marginRight:q});X&&b("select").not(j.find("select")).filter(function(){return this.style.visibility!==c}).css({visibility:c}).one(N,function(){this.style.visibility="inherit"});a.transition==="fade"?j.fadeTo(e,0,function(){l(0)}):l(e)}};d.load=function(u){var n,c,s,q=d.prep;D=e;l=h[g];u||(a=cb(b.extend({},b.data(l,r))));w(Q);w(W,a.onLoad);a.h=a.height?p(a.height,o)-y-B:a.innerHeight&&p(a.innerHeight,o);a.w=a.width?p(a.width,m)-z-C:a.innerWidth&&p(a.innerWidth,m);a.mw=a.w;a.mh=a.h;if(a.maxWidth){a.mw=p(a.maxWidth,m)-z-C;a.mw=a.w&&a.w<a.mw?a.w:a.mw}if(a.maxHeight){a.mh=p(a.maxHeight,o)-y-B;a.mh=a.h&&a.h<a.mh?a.h:a.mh}n=a.href;K.show();if(a.inline){f().hide().insertBefore(b(n)[0]).one(Q,function(){b(this).replaceWith(k.children())});q(b(n))}else if(a.iframe){j.one(hb,function(){var c=b("<iframe frameborder='0' style='width:100%; height:100%; border:0; display:block'/>")[0];c.name=i+ +new Date;c.src=a.href;if(!a.scrolling)c.scrolling="no";if(E)c.allowtransparency="true";b(c).appendTo(k).one(Q,function(){c.src="//about:blank"})});q(" ")}else if(a.html)q(a.html);else if(U(n)){c=new Image;c.onload=function(){var e;c.onload=null;c.id=i+"Photo";b(c).css({border:t,display:"block",cssFloat:"left"});if(a.scalePhotos){s=function(){c.height-=c.height*e;c.width-=c.width*e};if(a.mw&&c.width>a.mw){e=(c.width-a.mw)/c.width;s()}if(a.mh&&c.height>a.mh){e=(c.height-a.mh)/c.height;s()}}if(a.h)c.style.marginTop=Math.max(a.h-c.height,0)/2+"px";h[1]&&(g<h.length-1||a.loop)&&b(c).css({cursor:"pointer"}).click(d.next);if(E)c.style.msInterpolationMode="bicubic";setTimeout(function(){q(c)},1)};setTimeout(function(){c.src=n},1)}else n&&J.load(n,function(d,c,a){q(c==="error"?"Request unsuccessful: "+a.statusText:b(this).children())})};d.next=function(){if(!D){g=g<h.length-1?g+1:0;d.load()}};d.prev=function(){if(!D){g=g?g-1:h.length-1;d.load()}};d.close=function(){if(u&&!O){O=e;u=c;w(N,a.onCleanup);n.unbind("."+i+" ."+P);x.fadeTo("fast",0);j.stop().fadeTo("fast",0,function(){w(Q);k.remove();j.add(x).css({opacity:1,cursor:q}).hide();setTimeout(function(){O=c;w(eb,a.onClosed)},1)})}};d.element=function(){return b(l)};d.settings=gb;b(d.init)})(jQuery,this);
