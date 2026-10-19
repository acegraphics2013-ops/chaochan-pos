'use strict';
(function(){
 const f=document.getElementById('product-editor');if(!f)return;
 const data=JSON.parse(document.getElementById('products-admin-data').textContent);
 const by=id=>document.getElementById(id);
 const base=f.action;
 const reset=()=>{f.reset();f.action=base;by('method-slot').innerHTML='';by('product-form-heading').textContent='Add Menu Item';by('save-product').textContent='Save Menu Item'};
 document.querySelectorAll('.edit-product').forEach(btn=>btn.addEventListener('click',()=>{const p=data.find(x=>Number(x.id)===Number(btn.dataset.id));if(!p)return;f.action=`/menu/${p.id}`;by('method-slot').innerHTML='<input type="hidden" name="_method" value="PUT">';by('menu-sku').value=p.sku;by('menu-name').value=p.name;by('menu-category').value=p.category_id;by('menu-price').value=p.price;by('menu-description').value=p.description||'';by('menu-available').checked=!!p.available;by('product-form-heading').textContent='Edit: '+p.sku;by('save-product').textContent='Update Menu Item';f.scrollIntoView({behavior:'smooth',block:'start'})}));
 by('reset-product').addEventListener('click',reset);
 document.querySelectorAll('.delete-product-form').forEach(form=>form.addEventListener('submit',e=>{const name=form.dataset.name||'this item';if(!confirm(`Delete "${name}"? This cannot be undone.`))e.preventDefault();}));

 const search=by('menu-search');
 const filters=document.querySelectorAll('#menu-filter [data-filter]');
 let stateFilter='all';
 const applyFilter=()=>{
  const q=(search?.value||'').toLowerCase().trim();let shown=0;
  document.querySelectorAll('.menu-row').forEach(row=>{
   const textOk=!q||(row.dataset.search||'').includes(q);
   const stateOk=stateFilter==='all'||(stateFilter==='active'&&row.dataset.available==='1')||(stateFilter==='hidden'&&row.dataset.available==='0');
   const show=textOk&&stateOk;row.hidden=!show;if(show)shown++;
  });
  by('menu-empty')?.classList.toggle('hidden',shown>0);
 };
 search?.addEventListener('input',applyFilter);
 filters.forEach(b=>b.addEventListener('click',()=>{stateFilter=b.dataset.filter;filters.forEach(x=>x.classList.toggle('active',x===b));applyFilter();}));
})();
