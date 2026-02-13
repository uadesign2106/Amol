document.querySelectorAll('nav a').forEach((a)=>{if(location.pathname.endsWith(a.getAttribute('href'))){a.style.background='rgba(255,255,255,0.2)';}});
