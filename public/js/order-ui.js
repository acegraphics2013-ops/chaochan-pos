'use strict';
(function(){
 const root=document.getElementById('order-app');if(!root)return;
 const api=window.ChaoChan;
 const products=JSON.parse(document.getElementById('product-data').textContent);
 const indexed=new Map(products.map(x=>[Number(x.id),x]));
 const bySku=new Map(products.map(x=>[String(x.sku||'').toLowerCase().trim(),x]));
 const state={cart:new Map(),category:'all',service:'dine_in',payment:'pay_first',pending:false,done:false,idempotency:api.key()};
 const by=id=>document.getElementById(id);
 const money=api.php;
 let refreshOrders=()=>{},openTracker=()=>{},closeCart=()=>{};
 const render=()=>{
  let total=0,count=0;const parts=[];
  for(const [id,qty] of state.cart){const p=indexed.get(id);if(!p)continue;const line=Number(p.price)*qty;total+=line;count+=qty;
   parts.push(`<div class="cart-item"><div><div class="cart-item-name">${api.escape(p.name)}</div><small>${money(p.price)} × ${qty} = ${money(line)}</small></div><div class="cart-controls"><button type="button" data-change="${id}" data-delta="-1" aria-label="Subtract">−</button><strong>${qty}</strong><button type="button" data-change="${id}" data-delta="1" aria-label="Add">+</button></div></div>`);
  }
  by('cart-items').innerHTML=parts.length?parts.join(''):'<p class="empty-state">Your cart is empty. Choose a meal to start.</p>';
  by('cart-total').textContent=money(total);
  by('submit-order').disabled=state.cart.size===0||state.pending||state.done;
  const bar=by('cartbar');
  if(bar){bar.hidden=count===0||state.done;by('cartbar-count').textContent=count+(count===1?' item':' items');by('cartbar-total').textContent=money(total);}
  root.querySelectorAll('.food-card').forEach(card=>{const q=state.cart.get(Number(card.dataset.productId))||0;const badge=card.querySelector('.food-qty');if(badge){badge.hidden=q<=0;badge.textContent=q;}});
 };
 const addToCart=(id,delta=1)=>{if(state.done||!indexed.has(id))return false;const qty=Math.min(50,(state.cart.get(id)||0)+delta);if(qty<=0)state.cart.delete(id);else state.cart.set(id,qty);render();return true;};
 by('products-grid').addEventListener('click',e=>{const card=e.target.closest('[data-product-id]');if(!card||state.done)return;addToCart(Number(card.dataset.productId));});
 by('cart-items').addEventListener('click',e=>{const btn=e.target.closest('[data-change]');if(!btn||state.done)return;addToCart(Number(btn.dataset.change),Number(btn.dataset.delta));});
 if(root.dataset.mode==='pos'){
  let buffer='',last=0;
  document.addEventListener('keydown',e=>{
   if(e.key==='Enter'){
    const code=buffer.trim();buffer='';last=Date.now();
    if(code.length<4)return;
    const product=bySku.get(code.toLowerCase());
    if(product){e.preventDefault();addToCart(Number(product.id));by('order-status').textContent=`Added ${product.name} (${product.sku})`;}
    else by('order-status').textContent=`No item matches barcode ${code}`;
    return;
   }
   if(e.ctrlKey||e.altKey||e.metaKey||e.key.length!==1)return;
   const now=Date.now();if(now-last>120)buffer='';last=now;buffer+=e.key;
  });
 }
 if(root.dataset.mode==='qr'){
  const feed=root.dataset.ordersFeed;
  const badge=by('orders-count');
  const listEl=by('orders-list');
  const modal=by('orders-modal');
  const updatedEl=by('track-updated');
  const fab=by('track-fab');
  const fabCount=by('track-fab-count');
  const label=s=>String(s||'').replaceAll('_',' ');
  const statusClass=s=>(s==='completed'||s==='accepted')?'green-pill':(s==='cancelled'?'red-pill':'orange-pill');
  const STAGES=['Placed','Confirmed','Cooking','Ready','Served'];
  const HINTS=['Waiting for the cashier to confirm your order.','Order confirmed and queued for the kitchen.','Your food is being cooked fresh.','Your order is ready — it will be served shortly.','Served. Enjoy your meal!'];
  const stageOf=o=>{if(o.status==='cancelled')return -1;if(o.status==='completed'||o.kitchen_status==='served')return 4;if(o.kitchen_status==='ready')return 3;if(o.kitchen_status==='preparing')return 2;if(o.kitchen_status==='new')return 1;if(o.status==='awaiting_payment')return 1;return 0;};
  const stepper=o=>{const idx=stageOf(o);return `<div class="track-steps">${STAGES.map((s,i)=>`<div class="track-step ${i<idx?'done':''} ${i===idx?'current':''}"><span class="track-node">${i<idx?'✓':i+1}</span><small>${s}</small></div>`).join('')}</div>`;};
  const orderCard=o=>{const idx=stageOf(o);const pay=o.payment_mode==='pay_first'?'Pay first at cashier':'Pay later';const hint=o.status==='cancelled'?'This order was cancelled. Please ask our staff for help.':(o.status==='awaiting_payment'&&o.payment_mode==='pay_first'?'Please pay at the cashier to start cooking.':HINTS[idx]);
   return `<div class="order-history ${idx===4?'is-served':idx===-1?'is-cancelled':''}"><div class="order-history-head"><strong>#${api.escape(o.number)}</strong><span class="pill ${statusClass(o.status)}">${api.escape(label(o.status))}</span></div>${o.status==='cancelled'?'':stepper(o)}<p class="track-hint">${api.escape(hint)}</p><small class="muted">${api.escape(pay)} · Kitchen: ${api.escape(label(o.kitchen_status))}</small><ul>${o.items.map(i=>`<li><span>${i.qty} × ${api.escape(i.name)}</span><strong>${money(i.line_total)}</strong></li>`).join('')}</ul><div class="order-history-total"><span>Total</span><strong>${money(o.total)}</strong></div></div>`;};
  const renderOrders=orders=>{const active=orders.filter(o=>o.status!=='completed'&&o.status!=='cancelled').length;
   if(badge)badge.textContent=orders.length;
   if(fabCount)fabCount.textContent=active;
   if(fab)fab.hidden=orders.length===0;
   if(!listEl)return;
   if(!orders.length){listEl.innerHTML='<p class="empty-state">No orders yet for this table. Add something from the menu to get started.</p>';return;}
   listEl.innerHTML=[...orders].sort((a,b)=>Number(a.id)-Number(b.id)).map(orderCard).join('');};
  const stamp=()=>{if(updatedEl)updatedEl.textContent=new Date().toLocaleTimeString();};
  const loadOrders=async()=>{if(!feed)return;try{const data=await api.api(feed);renderOrders(data.orders||[]);stamp();}catch{badge&&(badge.textContent='!');}};
  refreshOrders=loadOrders;
  const cartSheet=by('qr-cart');
  const hideCart=()=>cartSheet?.classList.add('hidden');
  const openCart=()=>{if(!cartSheet)return;cartSheet.classList.remove('hidden');const panel=cartSheet.querySelector('.qr-sheet-panel');if(panel)panel.scrollTop=0;};
  closeCart=hideCart;
  openTracker=()=>{modal?.classList.remove('hidden');loadOrders();};
  by('view-orders')?.addEventListener('click',openTracker);
  fab?.addEventListener('click',openTracker);
  by('cartbar-view')?.addEventListener('click',openCart);
  by('qr-cart-close')?.addEventListener('click',hideCart);
  cartSheet?.addEventListener('click',e=>{if(e.target===cartSheet)hideCart();});
  by('orders-close')?.addEventListener('click',()=>modal?.classList.add('hidden'));
  modal?.addEventListener('click',e=>{if(e.target===modal)modal.classList.add('hidden');});
  document.addEventListener('keydown',e=>{if(e.key==='Escape'){modal?.classList.add('hidden');hideCart();}});
  loadOrders();setInterval(loadOrders,8000);
 }
 document.querySelectorAll('.category-btn').forEach(b=>b.addEventListener('click',()=>{state.category=b.dataset.category;document.querySelectorAll('.category-btn').forEach(x=>x.classList.toggle('active',x===b));filter();}));
 by('product-search')?.addEventListener('input',filter);
 function filter(){const q=(by('product-search')?.value||'').toLowerCase().trim();let shown=0;root.querySelectorAll('.food-card').forEach(el=>{const match=(state.category==='all'||el.dataset.category===state.category)&&el.dataset.search.includes(q);el.classList.toggle('hidden',!match);if(match)shown++;});const empty=by('products-empty');if(empty)empty.classList.toggle('hidden',shown>0);}
 root.querySelectorAll('.payment-mode').forEach(b=>b.addEventListener('click',()=>{state.payment=b.dataset.payment;root.querySelectorAll('.payment-mode').forEach(x=>x.classList.toggle('active',x===b))}));
 root.querySelectorAll('.order-type').forEach(b=>b.addEventListener('click',()=>{state.service=b.dataset.service;root.querySelectorAll('.order-type').forEach(x=>x.classList.toggle('active',x===b));by('table-select-row').classList.toggle('hidden',state.service==='take_out');}));
 by('clear-cart')?.addEventListener('click',()=>{if(confirm('Clear all items?')){state.cart.clear();render()}});
 by('submit-order').addEventListener('click',async()=>{
  if(state.cart.size===0)return;
  const table=by('order-table')?.value;
  if(root.dataset.mode==='pos'&&state.service==='dine_in'&&!table){by('order-status').textContent='Please select a dine-in table.';return;}
  state.pending=true;render();by('order-status').textContent='Submitting securely…';
  const data={items:[...state.cart].map(([product_id,qty])=>({product_id,qty})),notes:by('order-notes').value,payment_mode:state.payment,service_type:state.service,idempotency_key:state.idempotency};
  if(root.dataset.mode==='pos'&&state.service==='dine_in')data.table_id=Number(table);
  try{const out=await api.api(root.dataset.submit,'POST',data);state.done=true;
   const msg=root.dataset.mode==='qr'?`<div class="success-order" role="status"><strong>Order ${api.escape(out.number)} submitted!</strong><p>Waiting for cashier confirmation. ${state.payment==='pay_first'?'Please proceed to the cashier for payment.':'You can pay after your meal.'}</p><p id="tracking-message">Checking order status…</p><button type="button" class="btn btn-primary full" id="success-track">📡 Track My Order</button></div>`:`<div class="success-order" role="status"><strong>${api.escape(out.number)} saved</strong><p>Status: ${api.escape(out.status.replaceAll('_',' '))}. Review the order queue for payment and kitchen progress.</p><a class="btn btn-primary" href="${root.dataset.orders}">Open Live Orders →</a></div>`;
   by('order-status').innerHTML=msg;by('submit-order').textContent='Order Placed';
    if(root.dataset.mode==='qr'){
      refreshOrders();by('order-status')?.scrollIntoView({behavior:'smooth',block:'center'});
      by('success-track')?.addEventListener('click',()=>{closeCart();openTracker();});
     const link=`${root.dataset.trackPrefix}/${out.id}`;
     const poll=async()=>{const el=by('tracking-message');if(!el)return;try{const s=await api.api(link);el.textContent=`Order: ${s.status.replaceAll('_',' ')} · Kitchen: ${s.kitchen_status.replaceAll('_',' ')}`}catch{el.textContent='Tracking temporarily unavailable.'}};
     poll();setInterval(poll,6000);
   }
  }catch(err){by('order-status').textContent=err.message;}finally{state.pending=false;render();}
 });render();
})();
