var n=function(t){t.preventDefault,t.target.classList.remove("animate"),t.target.classList.add("animate"),setTimeout(function(){t.target.classList.remove("animate")},700)},e=document.getElementsByClassName("bubbly-button");for(var a=0;a<e.length;a++)e[a].addEventListener("click",n,!1);