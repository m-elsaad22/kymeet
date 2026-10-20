/* ═══════════════ Loader ═══════════════ */
window.addEventListener('load',()=>{const ld=document.getElementById('loader');if(ld)setTimeout(()=>ld.classList.add('out'),350)});

/* ═══════════════ Sticky header ═══════════════ */
const hdr=document.getElementById('hdr');
const fab=document.getElementById('fab');
const onScroll=()=>{
  const y=window.scrollY;
  if(hdr)hdr.classList.toggle('scrolled',y>40);
  if(fab)fab.classList.toggle('show',y>500);
};
window.addEventListener('scroll',onScroll,{passive:true});onScroll();

/* ═══════════════ Mobile menu ═══════════════ */
function toggleMob(open){document.getElementById('mob').classList.toggle('open',open)}

/* ═══════════════ Hero particles ═══════════════ */
(function(){
  const box=document.getElementById('particles');if(!box)return;
  for(let i=0;i<12;i++){
    const p=document.createElement('span');p.className='particle';
    const s=Math.random()*4+2;
    p.style.width=p.style.height=s+'px';
    p.style.left=Math.random()*100+'%';
    p.style.top=Math.random()*100+'%';
    p.style.opacity=Math.random()*.5+.2;
    p.style.animationDelay=(Math.random()*8)+'s';
    p.style.animationDuration=(Math.random()*8+8)+'s';
    box.appendChild(p);
  }
})();

/* ═══════════════ Reveal on scroll ═══════════════ */
const io=new IntersectionObserver((entries)=>{
  entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('on');io.unobserve(e.target)}});
},{threshold:.12,rootMargin:'0px 0px -40px 0px'});
document.querySelectorAll('.rv,.rv-l').forEach(el=>io.observe(el));

/* ═══════════════ Animated counters ═══════════════ */
const counted=new Set();
function animateCount(el){
  if(counted.has(el))return;counted.add(el);
  const target=parseFloat(el.dataset.count);
  const dec=parseInt(el.dataset.dec||'0');
  const suffix=el.dataset.suffix||'';
  const dur=1600,start=performance.now();
  function step(now){
    const t=Math.min((now-start)/dur,1);
    const eased=1-Math.pow(1-t,3);
    const val=target*eased;
    el.textContent=(dec?val.toFixed(dec):Math.floor(val).toLocaleString('en-US'))+suffix;
    if(t<1)requestAnimationFrame(step);else el.textContent=(dec?target.toFixed(dec):Math.floor(target).toLocaleString('en-US'))+suffix;
  }
  requestAnimationFrame(step);
}
const cio=new IntersectionObserver((entries)=>{
  entries.forEach(e=>{if(e.isIntersecting){animateCount(e.target);cio.unobserve(e.target)}});
},{threshold:.4});
document.querySelectorAll('[data-count]').forEach(el=>cio.observe(el));

/* ═══════════════ FAQ accordion ═══════════════ */
function faqT(q){
  const item=q.parentElement;
  const ans=item.querySelector('.faq-a');
  const open=item.classList.contains('faq-open');
  document.querySelectorAll('.faq-item.faq-open').forEach(i=>{i.classList.remove('faq-open');i.querySelector('.faq-a').style.maxHeight=null});
  if(!open){item.classList.add('faq-open');ans.style.maxHeight=ans.scrollHeight+'px'}
}
/* FAQ category filter */
document.querySelectorAll('.faq-cats button').forEach(b=>b.addEventListener('click',()=>{
  document.querySelectorAll('.faq-cats button').forEach(x=>x.classList.remove('active'));
  b.classList.add('active');
  const f=b.dataset.fc;
  document.querySelectorAll('.faq-item').forEach(it=>{
    it.classList.toggle('hide',!(f==='all'||it.dataset.cat===f));
  });
}));

/* ═══════════════ Projects filter ═══════════════ */
document.querySelectorAll('.filters button').forEach(b=>b.addEventListener('click',()=>{
  document.querySelectorAll('.filters button').forEach(x=>x.classList.remove('active'));
  b.classList.add('active');
  const f=b.dataset.f;
  document.querySelectorAll('.proj').forEach(p=>{
    p.classList.toggle('hide',!(f==='all'||p.dataset.cat===f));
  });
}));

/* ═══════════════ Reviews slider ═══════════════ */
const track=document.getElementById('rvTrack');
if(track&&track.children.length){
  let rvIdx=0;
  function rvPer(){return window.innerWidth<=640?1:window.innerWidth<=1024?2:3}
  function rvMax(){return Math.max(0,track.children.length-rvPer())}
  function rvApply(){
    rvIdx=Math.min(Math.max(rvIdx,0),rvMax());
    const card=track.children[0];
    if(!card)return;
    const step=card.getBoundingClientRect().width+16;
    track.style.transform=`translateX(${rvIdx*step}px)`;
  }
  function rvMove(dir){rvIdx+=dir;if(rvIdx>rvMax())rvIdx=0;if(rvIdx<0)rvIdx=rvMax();rvApply()}
  window.addEventListener('resize',rvApply);rvApply();
  let rvTimer=setInterval(()=>rvMove(1),5000);
  const rvWrap=track.parentElement;
  if(rvWrap){
    rvWrap.addEventListener('mouseenter',()=>clearInterval(rvTimer));
    rvWrap.addEventListener('mouseleave',()=>rvTimer=setInterval(()=>rvMove(1),5000));
  }
}

/* ═══════════════ Before / After slider ═══════════════ */
document.querySelectorAll('[data-ba]').forEach(stage=>{
  const before=stage.querySelector('.ba-before');
  const handle=stage.querySelector('.ba-handle');
  let dragging=false;
  function setPos(clientX){
    const rect=stage.getBoundingClientRect();
    let pct=((clientX-rect.left)/rect.width)*100;
    pct=Math.max(2,Math.min(98,pct));
    before.style.clipPath=`inset(0 0 0 ${pct}%)`;
    handle.style.left=pct+'%';
  }
  const start=()=>dragging=true;
  const end=()=>dragging=false;
  const move=e=>{if(!dragging)return;const x=e.touches?e.touches[0].clientX:e.clientX;setPos(x)};
  handle.addEventListener('mousedown',start);
  handle.addEventListener('touchstart',start,{passive:true});
  window.addEventListener('mouseup',end);
  window.addEventListener('touchend',end);
  window.addEventListener('mousemove',move);
  window.addEventListener('touchmove',move,{passive:true});
  stage.addEventListener('click',e=>{if(e.target.closest('.ba-handle'))return;setPos(e.clientX)});
});

/* ═══════════════ Smooth anchor scroll ═══════════════ */
document.querySelectorAll('a[href^="#"]').forEach(a=>{
  a.addEventListener('click',e=>{
    const id=a.getAttribute('href');
    if(id==='#'||id.length<2)return;
    const t=document.querySelector(id);
    if(t){e.preventDefault();window.scrollTo({top:t.getBoundingClientRect().top+window.scrollY-90,behavior:'smooth'})}
  });
});

/* ═══════════════ Smart service finder ═══════════════ */
(function(){
  const btn=document.getElementById('fnBtn');if(!btn)return;
  const svc=document.getElementById('fnSvc'),city=document.getElementById('fnCity'),res=document.getElementById('fnResult');
  const times={};
  if(city){
    Array.from(city.options).forEach(o=>{
      if(o.value)times[o.value]=o.dataset.time||'';
    });
  }
  const fallbackTime='خلال ساعتين';
  btn.addEventListener('click',()=>{
    const s=svc.value,c=city.value;
    document.getElementById('frTitle').textContent=s+' — '+c;
    document.getElementById('frSub').textContent='فريق متخصص جاهز لخدمتك في '+c;
    document.getElementById('frTime').textContent=times[c]||fallbackTime;
    res.hidden=false;
    res.scrollIntoView({behavior:'smooth',block:'center'});
  });
})();

/* ═══════════════ Button ripple (micro-interaction) ═══════════════ */
document.addEventListener('click',e=>{
  const b=e.target.closest('.btn');if(!b)return;
  const r=document.createElement('span');r.className='ripple';
  const rect=b.getBoundingClientRect();
  const size=Math.max(rect.width,rect.height);
  r.style.width=r.style.height=size+'px';
  r.style.left=(e.clientX-rect.left-size/2)+'px';
  r.style.top=(e.clientY-rect.top-size/2)+'px';
  b.appendChild(r);
  setTimeout(()=>r.remove(),600);
});