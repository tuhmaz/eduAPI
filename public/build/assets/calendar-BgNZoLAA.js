document.addEventListener("DOMContentLoaded",function(){const d=document.querySelectorAll(".event"),t=document.getElementById("modalEventTitle"),e=document.getElementById("modalEventDescription"),n=document.getElementById("modalEventDate");d.forEach(o=>{o.addEventListener("click",function(){const a=this.dataset.title,c=this.dataset.description,i=this.dataset.date;t.textContent=a,e.textContent=c,n.textContent=`Date: ${i}`,new bootstrap.Modal(document.getElementById("eventModal")).show()})})});document.addEventListener("DOMContentLoaded",function(){document.querySelectorAll(".day.has-event").forEach(t=>{t.addEventListener("click",function(){const e=this.getAttribute("data-title")||"No Title",n=this.getAttribute("data-description")||"No Description",o=this.getAttribute("data-date")||"No Date";document.getElementById("modalEventTitle").textContent=e,document.getElementById("modalEventDescription").textContent=n,document.getElementById("modalEventDate").textContent=o})})});