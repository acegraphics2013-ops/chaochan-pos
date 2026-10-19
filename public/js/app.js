'use strict';
window.ChaoChan={
 csrf:()=>document.querySelector('meta[name="csrf-token"]')?.content||'',
 escape:s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])),
 php:n=>'₱'+Number(n||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}),
 key:()=>`CH-${Date.now()}-${Math.random().toString(36).slice(2,12)}`,
 async api(url,method='GET',payload=null){const options={method,headers:{Accept:'application/json','X-CSRF-TOKEN':this.csrf()}};
  if(payload instanceof FormData)options.body=payload;else if(payload!==null){options.headers['Content-Type']='application/json';options.body=JSON.stringify(payload)};
  const resp=await fetch(url,options);let data={};try{data=await resp.json()}catch{};
  if(!resp.ok){const first=Object.values(data.errors||{}).flat()[0];throw new Error(first||data.message||`Request failed (${resp.status})`)}return data;
 }
};
document.getElementById('sidebarToggle')?.addEventListener('click',()=>document.getElementById('sidebar')?.classList.toggle('open'));
