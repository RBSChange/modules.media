// ObjectSwap - cf. http://neo-archaic.ie/blog/2008/02/06/more-ie-madness-object-swap-goes-oop/
var neoarchaic;if(neoarchaic==undefined){neoarchaic={}}
neoarchaic.ObjectSwap=function(){var ie=(((/MSIE/).test(window.navigator.userAgent)));var opera9=false;if(ie){var ver=navigator.appVersion.split("MSIE")
ver=parseFloat(ver[1])
ie=(ver>=6)}else if(navigator.userAgent.indexOf("Opera")!=-1){var versionindex=navigator.userAgent.indexOf("Opera")+6
var verint=parseInt(navigator.userAgent.charAt(versionindex));if(verint==9||verint==1){opera9=true;}}
this.oswap=(ie||opera9);this.ie=ie;if(this.oswap){document.write("<style id='hideObject'> object{display:none;} </style>");}
this.addLoadEvents()}
neoarchaic.ObjectSwap.prototype.addLoadEvents=function(){if(document.addEventListener){this.addEvent(document,"DOMContentLoaded","init")}else{this.addEvent(document,"readystatechange","init")}
this.addEvent(window,"load","init")}
neoarchaic.ObjectSwap.prototype.addEvent=function(obj,evType,fn,listener){if(listener==undefined){listener=this;}
var e={target:obj,type:evType}
this["handle"+fn]=function(){listener[fn](e);}
var handlefn=this["handle"+fn]
if(obj.addEventListener){obj.addEventListener(evType,handlefn,false);return true;}else if(obj.attachEvent){var r=obj.attachEvent("on"+evType,handlefn);return r;}else{return false;}}
neoarchaic.ObjectSwap.prototype.init=function(){if(this.isInit)return;this.isInit=true;if(!document.getElementsByTagName){return;}
var stripQueue=[];var objects=document.getElementsByTagName('object');for(var i=0;i<objects.length;i++){var o=objects[i];var h=o.outerHTML;var params="";this.hasFlash=true;for(var j=0;j<o.childNodes.length;j++){var p=o.childNodes[j];if(p.tagName=="PARAM"){if(p.name=="flashVersion"){this.hasFlash=this.detectFlash(p.value);if(!this.hasFlash){o.id=(o.id=="")?("stripFlash"+i):o.id;stripQueue.push(o.id);break;}}
params+=p.outerHTML;}}
if(!this.hasFlash){continue;}
if(!this.oswap){continue;}
if(o.className.toLowerCase().indexOf("noswap")!=-1){continue;}
var tag=h.split(">")[0]+">";var newObject=tag+params+o.innerHTML+" </OBJECT>";o.outerHTML=newObject;}
if(stripQueue.length){this.stripFlash(stripQueue);}
if(this.oswap){document.getElementById("hideObject").disabled=true;}}
neoarchaic.ObjectSwap.prototype.detectFlash=function(version){if(navigator.plugins&&navigator.plugins.length){var plugin=navigator.plugins["Shockwave Flash"];if(plugin==undefined){return false;}
var ver=navigator.plugins["Shockwave Flash"].description.split(" ")[2];return(Number(ver)>=Number(version))}else if(this.ie&&typeof(ActiveXObject)=="function"){var maxCount=Number(version)+3;for(var i=version;i<=maxCount;i++){try{var flash=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+i);return true;}
catch(e){}}
return false;}
return true;}
neoarchaic.ObjectSwap.prototype.stripFlash=function(stripQueue){if(!document.createElement){return;}
for(var i=0;i<stripQueue.length;i++){var o=document.getElementById(stripQueue[i]);var newHTML=o.innerHTML;newHTML=newHTML.replace(/<!--\s/g,"");newHTML=newHTML.replace(/\s-->/g,"");newHTML=newHTML.replace(/<embed/gi,"<span");var d=document.createElement("div");d.innerHTML=newHTML;d.className=o.className;d.id=o.id;o.parentNode.replaceChild(d,o);}}
if(neoarchaic.objswap==undefined){neoarchaic.objswap=new neoarchaic.ObjectSwap();}
