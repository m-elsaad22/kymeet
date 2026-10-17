/* ══ إعدادات التواصل: ضع رقم واتساب (بصيغة دولية بدون +) ورقم الهاتف، وإلا تُخفى أزرار التواصل ══ */
const CFG=Object.assign({WA:"",TEL:""},window.KYMEET_CFG||{});
const KD=window.KYMEET_DATA||{products:[],BR:[],BEST:[],NEW:[]};
const products=KD.products;
const BR=KD.BR,BEST=KD.BEST,NEW=KD.NEW;
products.forEach((p,i)=>p.n=i);
const HOME=(window.KYMEET_CFG&&window.KYMEET_CFG.HOME)||'/';
const HAS_HERO=!!document.querySelector('.hero,.phero');
const $=id=>document.getElementById(id)||document.createElement('input');
const esc=t=>String(t).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const byId=id=>products.find(p=>p.id===id);
const money=v=>Number(v).toLocaleString('en-US');
function imgErr(el){const d=document.createElement('div');d.className='btl';d.dataset.brand=el.dataset.brand||'';el.replaceWith(d)}
function pimg(p){return `<img src="${p.image}" alt="${esc(p.name)}" data-brand="${esc(p.brand)}" loading="lazy" onerror="imgErr(this)">`}
function priceHTML(p){
  const reg=p.regular&&p.regular>p.price;
  if(reg)return `<div class="prod-price"><span class="was">${money(p.regular)}<small>ج.م</small></span><span class="now">${money(p.price)}<small>ج.م</small></span></div>`;
  return `<div class="prod-price"><span class="now">${money(p.price)}<small>ج.م</small></span></div>`;
}
function productHTML(p,on){
  return `<article class="prod rv${on?' on':''}">
    <div class="prod-img" onclick="openP('${p.id}')">
      ${p.tag?`<span class="badge ${/جديد/.test(p.tag)?'new':''}">${esc(p.tag)}</span>`:''}
      <button class="prod-wish" aria-label="المفضلة" onclick="wish(this,event)"><i class="far fa-heart"></i></button>
      ${pimg(p)}
    </div>
    <div class="prod-info">
      <span class="prod-brand">${esc(p.brand)}</span>
      <h3 class="prod-name">${esc(p.name)}</h3>
      ${priceHTML(p)}
      <div class="prod-actions">
        <button class="add" onclick="addToCart('${p.id}',event)"><i class="fas fa-bag-shopping"></i> أضف للسلة</button>
        <button class="view" onclick="openP('${p.id}')" aria-label="تفاصيل ${esc(p.name)}"><i class="far fa-eye"></i></button>
      </div>
    </div></article>`;
}
function renderProducts(){
  $('bsGrid').innerHTML=BEST.map(byId).filter(Boolean).map(p=>productHTML(p)).join('');
  $('newGrid').innerHTML=NEW.map(byId).filter(Boolean).map(p=>productHTML(p)).join('');
  document.querySelectorAll('#bsGrid .rv,#newGrid .rv').forEach(el=>io.observe(el));
  const bsHead=document.querySelector('#bestsellers .shead h2');
  if(bsHead)bsHead.innerHTML='مختارات <em>كيميت</em>';
  const bsSub=document.querySelector('#bestsellers .shead p');
  if(bsSub)bsSub.textContent='عطور نبرزها لك — اختيار تحريري وليس ترتيب مبيعات.';
}
/* ══ الكتالوج: بحث وتصفية وترتيب ══ */
const fb=$('fb');
BR.forEach(b=>{const o=document.createElement('option');o.value=b;o.textContent=b;fb.appendChild(o)});
function renderCatalog(){
  const q=$('q').value.trim().toLowerCase(),b=fb.value,mx=+$('mx').value,so=$('so').value;
  $('mv').textContent='حتى '+money(mx)+' ج.م';
  let l=products.filter(p=>(!b||p.brand===b)&&p.price<=mx&&(p.name+' '+p.brand).toLowerCase().includes(q));
  if(so==='a')l.sort((x,y)=>x.price-y.price);
  if(so==='d')l.sort((x,y)=>y.price-x.price);
  if(so==='n')l.sort((x,y)=>x.name.localeCompare(y.name,'ar'));
  $('cnt').textContent=l.length?`${l.length} عطرًا`:'';
  $('catGrid').innerHTML=l.length?l.map(p=>productHTML(p,true)).join(''):'<p class="empty">لا توجد نتائج. غيّر البحث أو التصفية.</p>';
}
['q','fb','so','mx'].forEach(i=>$(i).addEventListener('input',renderCatalog));
function pickBrand(b){fb.value=b;$('q').value='';renderCatalog();goTo('#catalog')}
function goSearch(){
  if(document.querySelector('#catalog')||document.querySelector('.kx-wc .kx-filters')||document.querySelector('#q')){
    const el=document.querySelector('#catalog')||document.querySelector('.kx-wc')||document.querySelector('#q');
    if(el){window.scrollTo({top:el.getBoundingClientRect().top+scrollY-90,behavior:'smooth'});setTimeout(()=>{const i=$('q')||document.querySelector('input[type=search]');if(i)i.focus({preventScroll:true})},500);return}
  }
  const shop=(window.KYMEET_CFG&&window.KYMEET_CFG.SHOP)||(HOME.replace(/\/?$/,'')+'/shop/');
  location.href=shop;
}
function goTo(id){const t=document.querySelector(id);if(t)window.scrollTo({top:t.getBoundingClientRect().top+scrollY-90,behavior:'smooth'})}
/* ══ تفاصيل المنتج ══ */
function openP(id){
  const p=byId(id);if(!p)return;
  $('pdImg').innerHTML=pimg(p);
  $('pdTxt').innerHTML=`<span class="prod-brand">${esc(p.brand)}</span><h3>${esc(p.name)}</h3>${p.tag?`<span class="pd-tag">${esc(p.tag)}</span>`:''}<div class="pd-price">${money(p.price)} <small>ج.م</small></div><button class="btn btn-gold" onclick="addToCart('${p.id}',event)"><i class="fas fa-bag-shopping"></i> أضف للسلة</button>`;
  $('pd').showModal();
}
document.querySelectorAll('dialog').forEach(d=>d.addEventListener('click',e=>{if(e.target===d)d.close()}));
/* ══ المفضلة ══ */
const WKEY=(window.KYMEET_UI&&KYMEET_UI.wishKey)||'kymeet_wish_v1';
function wishStore(){try{return JSON.parse(localStorage.getItem(WKEY)||'[]')}catch(e){return []}}
function wishSave(a){try{localStorage.setItem(WKEY,JSON.stringify(a))}catch(e){}}
function wishPidFromBtn(btn){
  const img=btn.closest('.prod-img');
  const onclick=(img&&img.getAttribute('onclick'))||'';
  let m=onclick.match(/openP\('([^']+)'\)/);
  if(m)return m[1];
  const add=btn.closest('.prod')?.querySelector('.add');
  m=add&&add.getAttribute('onclick')?.match(/addToCart\('([^']+)'/);
  return m&&m[1];
}
function wishSync(){
  const ids=new Set(wishStore());
  document.querySelectorAll('.prod-wish').forEach(btn=>{
    const id=wishPidFromBtn(btn);if(!id)return;
    const on=ids.has(id);btn.classList.toggle('active',on);
    const i=btn.querySelector('i');if(i){i.classList.toggle('far',!on);i.classList.toggle('fas',on)}
  });
}
function wish(btn,e){
  e.stopPropagation();
  const id=wishPidFromBtn(btn);
  btn.classList.toggle('active');
  const i=btn.querySelector('i');i.classList.toggle('far');i.classList.toggle('fas');
  if(id){
    let list=wishStore();
    if(btn.classList.contains('active')){if(!list.includes(id))list.push(id);toast('أُضيف للمفضلة','');}
    else{list=list.filter(x=>x!==id);toast('أُزيل من المفضلة','');}
    wishSave(list);
  }else if(btn.classList.contains('active'))toast('أُضيف للمفضلة','');
}
document.addEventListener('DOMContentLoaded',()=>setTimeout(wishSync,300));
/* ══ السلة ══ */
let cart=[];
const WC=!!CFG.WC;let wcs={count:0,items:[],subtotal:'',checkout:'',cart:''};
function wcCall(op,data){return fetch(CFG.AJAX,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams(Object.assign({op:op},data||{}))}).then(r=>r.json()).then(s=>{if(s&&!s.error){wcs=s;renderWC()}return s}).catch(()=>null)}
function renderWC(){
  $('cartCount').textContent=wcs.count;
  if(!wcs.items.length){$('cartBody').innerHTML='<div class="cart-empty"><i class="fas fa-bag-shopping"></i><p>سلتك فارغة حاليًا</p></div>';$('cartTotal').textContent='0';return}
  $('cartBody').innerHTML=wcs.items.map(x=>`<div class="ci"><div class="ci-img">${x.img?`<img src="${esc(x.img)}" alt="">`:'<i class="fas fa-gem"></i>'}</div><div class="ci-info"><b><a href="${esc(x.url)}">${esc(x.name)}</a></b><small>${esc(x.brand)}</small><div class="price">${esc(x.line)}</div><div class="qty"><button onclick="qty('${esc(x.key)}',-1)" aria-label="إنقاص">−</button><span>${x.qty}</span><button onclick="qty('${esc(x.key)}',1)" aria-label="زيادة">+</button></div></div><button class="ci-rm" onclick="removeFromCart('${esc(x.key)}')" aria-label="حذف"><i class="fas fa-trash"></i></button></div>`).join('');
  $('cartTotal').textContent=wcs.subtotal;
}
function addToCart(id,e,pid){
  if(e)e.stopPropagation();
  if(WC){const p=byId(id);const n=pid||(p&&p.pid)||0;if(!n)return;
    wcCall('add',{pid:n,qty:1}).then(s=>{if(!s)return toast('تعذّر الإضافة','حاول مرة أخرى');if(s.redirect){location.href=s.redirect;return}if(s.msg)return toast('تعذّرت الإضافة',s.msg);toast('أُضيف إلى السلة',p?`${p.name} — ${p.brand}`:'');openCart()});return}
  const p=byId(id);if(!p)return;
  const it=cart.find(x=>x.id===id);it?it.qty++:cart.push({id,qty:1});
  updateCart();toast('أُضيف إلى السلة',`${p.name} — ${p.brand}`);
}
function qty(id,d){if(WC){const it=wcs.items.find(x=>x.key===id);if(it)wcCall('set',{key:id,qty:it.qty+d});return}const it=cart.find(x=>x.id===id);if(!it)return;it.qty+=d;if(it.qty<1)cart=cart.filter(x=>x.id!==id);updateCart()}
function removeFromCart(id){if(WC){wcCall('remove',{key:id});return}cart=cart.filter(x=>x.id!==id);updateCart()}
const cartSum=()=>cart.reduce((s,x)=>s+byId(x.id).price*x.qty,0);
function updateCart(){
  if(WC){renderWC();return}
  $('cartCount').textContent=cart.reduce((s,x)=>s+x.qty,0);
  if(!cart.length){$('cartBody').innerHTML='<div class="cart-empty"><i class="fas fa-bag-shopping"></i><p>سلتك فارغة حاليًا</p></div>';$('cartTotal').textContent='0 ج.م';return}
  $('cartBody').innerHTML=cart.map(x=>{const p=byId(x.id);return `<div class="ci"><div class="ci-img">${pimg(p)}</div><div class="ci-info"><b>${esc(p.name)}</b><small>${esc(p.brand)}</small><div class="price">${money(p.price*x.qty)} ج.م</div><div class="qty"><button onclick="qty('${p.id}',-1)" aria-label="إنقاص">−</button><span>${x.qty}</span><button onclick="qty('${p.id}',1)" aria-label="زيادة">+</button></div></div><button class="ci-rm" onclick="removeFromCart('${p.id}')" aria-label="حذف"><i class="fas fa-trash"></i></button></div>`}).join('');
  $('cartTotal').textContent=money(cartSum())+' ج.م';
}
function openCart(){$('cartDrawer').classList.add('open');$('cartOverlay').classList.add('open')}
function closeCart(){$('cartDrawer').classList.remove('open');$('cartOverlay').classList.remove('open')}
function checkout(e){
  e.preventDefault();
  if(WC){if(!wcs.items.length){toast('السلة فارغة','أضف عطرًا أولًا');return}location.href=wcs.checkout;return}
  if(!cart.length){toast('السلة فارغة','أضف عطرًا أولًا');return}
  $('ot').textContent=money(cartSum())+' ج.م';$('om').textContent='';$('cod').showModal();
}
$('cof').addEventListener('submit',e=>{
  e.preventDefault();
  const l=cart.map(x=>byId(x.id).name+' × '+x.qty).join('، ');
  const txt=`طلب جديد من KYMEET\nالاسم: ${$('on').value}\nالهاتف: ${$('op').value}\nالعنوان: ${$('oa').value}\nالطلب: ${l}\nالإجمالي: ${$('ot').textContent}`;
  if(CFG.WA){window.open('https://wa.me/'+CFG.WA+'?text='+encodeURIComponent(txt),'_blank');$('om').textContent='تم تجهيز طلبك. أكمل الإرسال من واتساب.';cart=[];updateCart();setTimeout(()=>{$('cod').close();closeCart()},1200)}
  else{$('om').textContent='نسخة تجريبية: لم يُربط الطلب بقناة استقبال بعد.'}
});
/* ══ إشعار ══ */
let tt;
function toast(title,msg){$('toastTitle').textContent=title;$('toastMsg').textContent=msg;const t=$('toast');t.classList.add('show');clearTimeout(tt);tt=setTimeout(()=>t.classList.remove('show'),2800)}
/* ══ أزرار التواصل حسب الإعدادات ══ */
document.querySelectorAll('.cfg-wa').forEach(a=>{CFG.WA?a.href='https://wa.me/'+CFG.WA:a.style.display='none'});
document.querySelectorAll('.cfg-tel').forEach(a=>{CFG.TEL?a.href='tel:+'+CFG.TEL:a.style.display='none'});
const mc=document.querySelector('.mcta');if(mc&&!CFG.WA&&!CFG.TEL)mc.style.display='none';
/* ══ المحمّل والهيدر والقائمة ══ */
const hdr=$('hdr'),fab=$('fab');
const onScroll=()=>{hdr.classList.toggle('scrolled',scrollY>40||!HAS_HERO);if(fab)fab.classList.toggle('show',scrollY>500)};
addEventListener('scroll',onScroll,{passive:true});onScroll();
function toggleMob(o){$('mob').classList.toggle('open',o)}
/* ══ فقاعات معطّلة في وضع السلايدر فقط — أخف أداءً ══ */
(function(){const box=$('bubbles');if(box)box.remove();})();
/* ══ ظهور الأقسام من الأسفل عند التمرير ══ */
const io=new IntersectionObserver(en=>{en.forEach(e=>{if(e.isIntersecting){e.target.classList.add('on');io.unobserve(e.target)}})},{threshold:0,rootMargin:'0px 0px -40px 0px'});
document.querySelectorAll('.rv,.rv-l').forEach(el=>io.observe(el));
function chk(){const h=innerHeight;document.querySelectorAll('.rv:not(.on),.rv-l:not(.on)').forEach(el=>{if(el.getBoundingClientRect().top<h*.95)el.classList.add('on')})}
addEventListener('scroll',chk,{passive:true});addEventListener('load',chk);
/* ══ الأسئلة الشائعة ══ */
function faqT(q){
  const it=q.parentElement,a=it.querySelector('.faq-a'),o=it.classList.contains('faq-open');
  document.querySelectorAll('.faq-item.faq-open').forEach(i=>{i.classList.remove('faq-open');i.querySelector('.faq-a').style.maxHeight=null});
  if(!o){it.classList.add('faq-open');a.style.maxHeight=a.scrollHeight+'px'}
}
/* ══ تمرير سلس للروابط ══ */
document.querySelectorAll('a[href^="#"]').forEach(a=>a.addEventListener('click',e=>{
  const id=a.getAttribute('href');if(!id||id==='#'||id.length<2)return;
  if(document.querySelector(id)){e.preventDefault();goTo(id)}
}));
document.addEventListener('click',e=>{
  const b=e.target.closest('.btn');if(!b)return;
  const r=document.createElement('span');r.className='rp';const rc=b.getBoundingClientRect(),s=Math.max(rc.width,rc.height);
  r.style.cssText=`width:${s}px;height:${s}px;left:${e.clientX-rc.left-s/2}px;top:${e.clientY-rc.top-s/2}px`;b.appendChild(r);setTimeout(()=>r.remove(),650);
});
try{renderProducts();renderCatalog()}catch(e){console.error('KYMEET render error:',e)}
updateCart();
if(WC)wcCall('get');

/* ══ سلايدر الهيرو: أحدث المنتجات ══ */
(function(){const s=document.querySelector('[data-hv]');if(!s)return;
const sl=[...s.querySelectorAll('.hv-slide')],ds=[...s.querySelectorAll('.hv-dots button')];if(sl.length<2)return;
let i=0,t=null,x0=null;
const go=n=>{i=(n+sl.length)%sl.length;sl.forEach((e,k)=>e.classList.toggle('on',k===i));ds.forEach((e,k)=>e.classList.toggle('on',k===i))};
const stop=()=>{clearInterval(t);t=null};
const play=()=>{stop();if(matchMedia('(prefers-reduced-motion:reduce)').matches)return;t=setInterval(()=>go(i+1),4500)};
const nx=s.querySelector('.next'),pv=s.querySelector('.prev');
if(nx)nx.onclick=()=>{go(i+1);play()};if(pv)pv.onclick=()=>{go(i-1);play()};
ds.forEach((d,k)=>d.onclick=()=>{go(k);play()});
s.addEventListener('mouseenter',stop);s.addEventListener('mouseleave',play);
s.addEventListener('touchstart',e=>{x0=e.touches[0].clientX;stop()},{passive:true});
s.addEventListener('touchend',e=>{if(x0!==null){const dx=e.changedTouches[0].clientX-x0;if(Math.abs(dx)>40)go(i+(dx<0?1:-1))}x0=null;play()});
document.addEventListener('visibilitychange',()=>document.hidden?stop():play());
play()})();

/* ══ سلايدرات أفقية: أقسام الرئيسية + الهيرو ══ */
function kxGo(track,item){const rtl=getComputedStyle(track).direction==='rtl',t=track.getBoundingClientRect(),r=item.getBoundingClientRect(),pad=parseFloat(getComputedStyle(track).paddingInlineStart)||0;const d=rtl?(r.right-(t.right-pad)):(r.left-(t.left+pad));track.scrollBy({left:d,behavior:'smooth'})}
function kxCar(root,track,o){o=o||{};let i=0,t=null,vis=true,hold=false,raf=0;
  const items=()=>[...track.children];
  const cur=()=>{const a=items();if(!a.length)return 0;const tr=track.getBoundingClientRect(),rtl=getComputedStyle(track).direction==='rtl',pad=parseFloat(getComputedStyle(track).paddingInlineStart)||0;let b=0,bd=1e9;a.forEach((e,k)=>{const r=e.getBoundingClientRect(),d=Math.abs(rtl?(r.right-(tr.right-pad)):(r.left-(tr.left+pad)));if(d<bd){bd=d;b=k}});return b};
  const dots=()=>{if(o.dots)[...o.dots.children].forEach((d,k)=>d.classList.toggle('on',k===i))};
  const go=n=>{const a=items();if(!a.length)return;i=(n+a.length)%a.length;kxGo(track,a[i]);dots()};
  const stop=()=>{clearInterval(t);t=null};
  const play=()=>{stop();if(!o.auto||matchMedia('(prefers-reduced-motion:reduce)').matches)return;t=setInterval(()=>{if(vis&&!hold&&!document.hidden)go(cur()+1)},o.auto)};
  if(o.dots){o.dots.innerHTML=items().map((_,k)=>`<button type="button" class="${k?'':'on'}" aria-label="${k+1}"></button>`).join('');[...o.dots.children].forEach((d,k)=>d.onclick=()=>{go(k);play()})}
  if(o.arrows&&items().length>1){const mk=c=>{const b=document.createElement('button');b.type='button';b.className='ks-nav '+c;b.setAttribute('aria-label',c);b.innerHTML='<i class="fas fa-chevron-'+((c==='prev')===(getComputedStyle(track).direction==='rtl')?'right':'left')+'"></i>';return b};
    const p=mk('prev'),n=mk('next');p.onclick=()=>{go(cur()-1);play()};n.onclick=()=>{go(cur()+1);play()};root.appendChild(p);root.appendChild(n)}
  track.addEventListener('scroll',()=>{cancelAnimationFrame(raf);raf=requestAnimationFrame(()=>{i=cur();dots()})},{passive:true});
  track.addEventListener('touchstart',()=>{hold=true},{passive:true});track.addEventListener('touchend',()=>setTimeout(()=>{hold=false},2500),{passive:true});
  root.addEventListener('mouseenter',()=>{hold=true});root.addEventListener('mouseleave',()=>{hold=false});
  if('IntersectionObserver' in window)new IntersectionObserver(e=>{vis=e[0].isIntersecting},{threshold:.35}).observe(root);
  play()}
document.querySelectorAll('.kslider').forEach(r=>{const tr=r.querySelector('.kslider-track');if(tr)kxCar(r,tr,{auto:+r.dataset.auto||0,arrows:true})});
(function(){const hc=document.querySelector('[data-hc]');if(!hc)return;const tr=hc.querySelector('.hc-track'),dots=hc.querySelector('.hc-dots');if(tr&&tr.children.length>1)kxCar(hc,tr,{auto:6500,dots:dots})})();
/* بحث الهاتف */
function kxSearch(e){e.preventDefault();const q=($('kxq').value||'').trim();if(document.querySelector('#catalog')){$('q').value=q;try{renderCatalog()}catch(x){}goTo('#catalog')}else{location.href=HOME+(q?'?kxq='+encodeURIComponent(q):'')+'#catalog'}return false}
(function(){const p=new URLSearchParams(location.search).get('kxq');if(p&&document.getElementById('q')){document.getElementById('q').value=p;try{renderCatalog()}catch(e){}}})();
/* شارة السلة في الشريط السفلي */
(function(){const c=document.getElementById('cartCount'),b=document.getElementById('anBadge');if(!c||!b)return;const s=()=>{const n=parseInt(c.textContent,10)||0;b.textContent=n;b.dataset.n=n};new MutationObserver(s).observe(c,{childList:true,characterData:true,subtree:true});s()})();
/* تبويبات صفحة الدخول وإظهار كلمة المرور */
document.addEventListener('click',e=>{const t=e.target.closest('[data-ka]');if(t){document.querySelectorAll('[data-ka]').forEach(b=>b.classList.toggle('on',b===t));document.querySelectorAll('.ka-pane').forEach(p=>p.classList.toggle('on',p.dataset.pane===t.dataset.ka));return}const eye=e.target.closest('.ka-eye');if(eye){const i=eye.parentElement.querySelector('input');const s=i.type==='password';i.type=s?'text':'password';eye.firstElementChild.className=s?'far fa-eye-slash':'far fa-eye'}});
