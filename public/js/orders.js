'use strict';
(function(){const board=document.getElementById('orders-board');if(!board)return;const cc=window.ChaoChan;const esc=cc.escape;
 let orders=[];let billId=0;let payKey='';
 const pill=s=>`<span class="pill">${esc(String(s).replaceAll('_',' '))}</span>`;
 const render=()=>{board.innerHTML=orders.length?orders.map(o=>{const due=Math.max(0,Number(o.bill_total)-Number(o.paid_amount));const canAccept=o.status==='pending_confirmation';const cancelable=['pending_confirmation','awaiting_payment'].includes(o.status);
 return `<article class="order-card"><div class="section-heading"><h3>${esc(o.number)}</h3>${pill(o.status)}</div><p class="order-meta">${o.table_number?'Table '+esc(o.table_number):'Take-out'} • ${esc(o.created_at)}<br>${esc(o.source.toUpperCase())} • ${esc(o.payment_mode.replace('_',' '))}</p><ul>${(o.items||[]).map(i=>`<li>${Number(i.qty)} × ${esc(i.name_snapshot)}</li>`).join('')}</ul>${o.notes?`<p class="ticket-note">${esc(o.notes)}</p>`:''}<div class="cart-total"><span>Order ${cc.php(o.total)}</span><strong style="font-size:15px">Bill due ${cc.php(due)}</strong></div><div class="order-actions"><a class="btn btn-soft" target="_blank" rel="noopener" href="/bills/${o.bill_id}/receipt">🖨 Print Bill</a>${canAccept?`<button class="btn btn-primary" data-accept="${o.id}">✓ Confirm</button>`:''}${due>0&&!canAccept?`<button class="btn btn-primary" data-pay="${o.bill_id}">₱ Collect Payment</button>`:''}${cancelable?`<button class="btn btn-soft" data-cancel="${o.id}">Cancel</button>`:''}<span class="pill">Kitchen ${esc(o.kitchen_status.replaceAll('_',' '))}</span></div></article>`}).join(''):'<p class="empty-state">No current orders.</p>';};
 const refresh=async()=>{try{orders=await cc.api(board.dataset.feed);render()}catch(e){board.innerHTML=`<p class="flash flash-bad">${esc(e.message)}</p>`}};
 board.addEventListener('click',async e=>{const btn=e.target.closest('[data-accept],[data-pay],[data-cancel]');if(!btn)return;
 if(btn.dataset.pay){billId=Number(btn.dataset.pay);const item=orders.find(o=>Number(o.bill_id)===billId);const due=Math.max(0,Number(item.bill_total)-Number(item.paid_amount));payKey=cc.key();document.getElementById('payment-bill').textContent=item.bill_number;document.getElementById('payment-due').textContent=cc.php(due);document.getElementById('pay-amount').value=due.toFixed(2);document.getElementById('pay-tendered').value=due.toFixed(2);document.getElementById('payment-result').textContent='';updateChange();document.getElementById('payment-modal').classList.remove('hidden');return;}
 const action=btn.dataset.accept?'accept':'cancel';if(action==='cancel'&&!confirm('Cancel this unpaid unprepared order?'))return;btn.disabled=true;try{await cc.api(`/orders/${btn.dataset.accept||btn.dataset.cancel}/${action}`,'POST',{});await refresh()}catch(err){alert(err.message);btn.disabled=false;}
 });
 const modal=document.getElementById('payment-modal');document.getElementById('payment-close').addEventListener('click',()=>modal.classList.add('hidden'));
 document.getElementById('pay-method').addEventListener('change',e=>{document.querySelectorAll('.gcash-fields').forEach(x=>x.classList.toggle('hidden',e.target.value!=='gcash'));document.querySelectorAll('.cash-field').forEach(x=>x.classList.toggle('hidden',e.target.value!=='cash'));updateChange();});
 const changeBox=document.getElementById('change-box'),changeEl=document.getElementById('pay-change');
 const payAmount=document.getElementById('pay-amount'),payTendered=document.getElementById('pay-tendered'),payMethod=document.getElementById('pay-method');
 function updateChange(){
  if(payMethod.value!=='cash'){changeBox.classList.remove('short');return;}
  const amount=Number(payAmount.value||0),tendered=Number(payTendered.value||0);
  const short=tendered<amount;
  changeEl.textContent=short?`Short ${cc.php(amount-tendered)}`:cc.php(Math.max(0,tendered-amount));
  changeBox.classList.toggle('short',short);
 }
 ['input','change','keyup'].forEach(ev=>{payAmount.addEventListener(ev,updateChange);payTendered.addEventListener(ev,updateChange);});
 document.getElementById('payment-form').addEventListener('submit',async e=>{e.preventDefault();const form=e.target;const btn=form.querySelector('[type=submit]');btn.disabled=true;const data=new FormData(form);data.append('idempotency_key',payKey);try{const r=await cc.api(`/bills/${billId}/payments`,'POST',data);const chg=Number(r.change||0);document.getElementById('payment-result').textContent=chg>0?`${r.message} Change: ${cc.php(chg)}`:r.message;await refresh();setTimeout(()=>modal.classList.add('hidden'),1100);}catch(err){document.getElementById('payment-result').textContent=err.message;}finally{btn.disabled=false;}});
 document.getElementById('refresh-orders')?.addEventListener('click',refresh);refresh();setInterval(()=>{if(modal.classList.contains('hidden'))refresh()},5000);
})();
