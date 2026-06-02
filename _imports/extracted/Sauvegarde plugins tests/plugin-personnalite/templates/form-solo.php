<?php if ( ! defined( 'ABSPATH' ) ) exit;
$_c1 = sanitize_hex_color( get_option('pp_color_primary',   '#E8541A') ) ?: '#E8541A';
$_c2 = sanitize_hex_color( get_option('pp_color_secondary', '#1E2A3A') ) ?: '#1E2A3A';
?>
<style>
#pp-test-container.pp-solo-mode *{box-sizing:border-box;}
#pp-test-container.pp-solo-mode{max-width:860px;margin:0 auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;padding:0 40px 60px;}





#pp-progress-wrap{margin-top:24px;margin-bottom:28px;}#pp-progress-wrap.pp-hidden{display:none !important;}.pp-hidden{display:none !important;}
#pp-progress-meta{display:flex !important;justify-content:space-between !important;align-items:center !important;margin-bottom:8px !important;width:100% !important;}
#pp-progress-dim{display:block !important;font-size:12px;font-weight:600;color:<?php echo esc_attr($_c2); ?>;}
#pp-progress-pct{display:block !important;font-size:12px;font-weight:700;color:<?php echo esc_attr($_c1); ?>;}
#pp-progress-track{height:6px;background:#dde8f0;border-radius:999px;overflow:hidden;}
#pp-test-container .pp-progress-bar{height:100%;background:<?php echo esc_attr($_c1); ?>;border-radius:999px;transition:width .5s cubic-bezier(.4,0,.2,1);width:0%;}
#pp-test-container .pp-solo-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
#pp-test-container .pp-solo-back{background:none;border:none;font-size:14px;font-weight:600;color:<?php echo esc_attr($_c1); ?>;cursor:pointer;padding:8px 0;display:flex;align-items:center;gap:4px;font-family:inherit;}
#pp-test-container .pp-solo-counter{font-size:12px;font-weight:700;color:#8FA8BE;background:#EEF3F8;padding:5px 14px;border-radius:999px;}
#pp-test-container .pp-solo-card{background:#fff;border:1.5px solid #dde8f0;border-radius:20px;padding:52px 80px 48px;box-shadow:0 6px 30px rgba(30,42,58,.08);margin-bottom:16px;}
#pp-test-container .pp-solo-qtext{font-size:26px;font-weight:700;color:<?php echo esc_attr($_c2); ?>;line-height:1.5;margin:0 0 40px;text-align:center;}
#pp-test-container .pp-solo-choices{display:flex;flex-wrap:wrap;gap:12px;justify-content:center;list-style:none;padding:0;margin:0;}
#pp-test-container .pp-solo-choice{padding:12px 28px;border:2px solid #dde8f0;border-radius:999px;background:#fff;cursor:pointer;font-family:inherit;font-size:15px;font-weight:600;color:#2E4A6A;white-space:nowrap;transition:border-color .15s,background .15s,color .15s,transform .12s,box-shadow .15s;display:inline-block;line-height:1;}
#pp-test-container .pp-solo-choice:hover{border-color:<?php echo esc_attr($_c1); ?>;color:<?php echo esc_attr($_c1); ?>;transform:translateY(-2px);box-shadow:0 4px 14px rgba(232,84,26,.15);}
#pp-test-container .pp-solo-choice-sel{border-color:<?php echo esc_attr($_c1); ?> !important;background:<?php echo esc_attr($_c1); ?> !important;color:#fff !important;transform:translateY(-2px);box-shadow:0 6px 18px rgba(232,84,26,.3) !important;}
#pp-test-container .pp-step-title{font-size:24px;font-weight:800;color:<?php echo esc_attr($_c2); ?>;margin-bottom:12px;}
#pp-test-container .pp-step-intro{font-size:15px;color:#4B5563;line-height:1.7;margin-bottom:20px;}
#pp-test-container .pp-label{display:block;font-size:14px;font-weight:600;color:<?php echo esc_attr($_c2); ?>;margin-bottom:16px;}
#pp-test-container .pp-input{display:block;width:100%;padding:12px 16px;border:1.5px solid #dde8f0;border-radius:12px;font-family:inherit;font-size:15px;color:#1E2A3A;margin-top:6px;background:#fff;transition:border-color .15s;}
#pp-test-container .pp-input:focus{outline:none;border-color:<?php echo esc_attr($_c1); ?>;}
#pp-test-container .pp-checkbox{display:flex;gap:10px;align-items:flex-start;font-size:13px;color:#8FA8BE;margin-bottom:20px;cursor:pointer;line-height:1.5;}
#pp-test-container .pp-checkbox a{color:<?php echo esc_attr($_c1); ?>;}
#pp-test-container .pp-btn{display:inline-block;padding:13px 32px;border-radius:999px;border:none;cursor:pointer;font-family:inherit;font-size:14px;font-weight:700;transition:opacity .15s;}
#pp-test-container .pp-btn-next{background:<?php echo esc_attr($_c1); ?>;color:#fff;box-shadow:0 4px 16px rgba(232,84,26,.3);}
#pp-test-container .pp-btn-outline{background:#fff;border:2px solid <?php echo esc_attr($_c1); ?>;color:<?php echo esc_attr($_c1); ?>;}
#pp-test-container .pp-reprise-banner{background:#EEF3F8;border:1.5px solid #C8D9E8;border-left:4px solid <?php echo esc_attr($_c1); ?>;border-radius:12px;padding:14px 20px;margin-top:32px;margin-bottom:24px;}
#pp-test-container .pp-reprise-inner{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;}
#pp-test-container .pp-reprise-text{display:flex;flex-direction:column;gap:3px;font-size:13px;color:#1E2A3A;}
#pp-test-container .pp-reprise-actions{display:flex;gap:8px;}
#pp-test-container .pp-loader{display:flex;flex-direction:column;align-items:center;padding:60px 20px;gap:16px;color:#8FA8BE;font-size:14px;}
#pp-test-container .pp-spinner{width:40px;height:40px;border:3px solid #EEF3F8;border-top-color:<?php echo esc_attr($_c1); ?>;border-radius:50%;animation:pp-spin .7s linear infinite;}
@keyframes pp-spin{to{transform:rotate(360deg);}}
@keyframes pp-solo-slide-in    {from{opacity:0;transform:translateX(40px)} to{opacity:1;transform:translateX(0)}}
@keyframes pp-solo-slide-out   {from{opacity:1;transform:translateX(0)} to{opacity:0;transform:translateX(-40px)}}
@keyframes pp-solo-slide-out-back{from{opacity:1;transform:translateX(0)} to{opacity:0;transform:translateX(40px)}}
@keyframes pp-solo-slide-in-back {from{opacity:0;transform:translateX(-40px)} to{opacity:1;transform:translateX(0)}}
#pp-test-container .pp-solo-card.pp-solo-in      {animation:pp-solo-slide-in .24s cubic-bezier(.22,.61,.36,1) both;}
#pp-test-container .pp-solo-card.pp-solo-out     {animation:pp-solo-slide-out .20s ease both;pointer-events:none;}
#pp-test-container .pp-solo-card.pp-solo-out-back{animation:pp-solo-slide-out-back .20s ease both;pointer-events:none;}
#pp-test-container .pp-solo-card.pp-solo-in-back {animation:pp-solo-slide-in-back .24s cubic-bezier(.22,.61,.36,1) both;}
@media(max-width:600px){
  #pp-test-container.pp-solo-mode{padding:0 14px 40px;}
  
  #pp-test-container .pp-solo-card{padding:28px 20px 24px;}
  #pp-test-container .pp-solo-qtext{font-size:17px;margin-bottom:24px;}

  #pp-test-container .pp-solo-choices{gap:8px;flex-wrap:wrap;}
  #pp-test-container .pp-solo-choice{padding:10px 14px;font-size:13px;}
}
</style>

<div id="pp-test-container" class="pp-container pp-solo-mode">

  <!-- Barre de progression -->
  <div id="pp-progress-wrap" class="pp-hidden">
    <div id="pp-progress-meta">
      <span id="pp-progress-dim"></span>
      <span id="pp-progress-pct"></span>
    </div>
    <div id="pp-progress-track">
      <div class="pp-progress-bar" id="pp-progress"></div>
    </div>
  </div>

  <!-- Bandeau encouragement mi-parcours -->
  <div id="pp-encourage-banner" class="pp-hidden" style="background:#EEF3F8;border:1.5px solid #C8D9E8;border-left:4px solid <?php echo esc_attr($c1); ?>;border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px;">
    <div style="font-size:26px;flex-shrink:0;">💪</div>
    <div style="flex:1;">
      <p style="margin:0 0 3px;font-size:14px;font-weight:700;color:<?php echo esc_attr($c1); ?>;">Vous êtes à mi-chemin !</p>
      <p style="margin:0;font-size:13px;color:#475569;line-height:1.5;">Encore 64 questions et votre profil complet sera révélé. Vous faites du bon travail — continuez !</p>
    </div>
    <button type="button" onclick="document.getElementById('pp-encourage-banner').classList.add('pp-hidden')" style="background:none;border:none;cursor:pointer;font-size:18px;color:#94a3b8;padding:0;flex-shrink:0;" title="Fermer">×</button>
  </div>

  <form id="pp-form">
    <?php wp_nonce_field( 'pp_nonce', 'pp_nonce_field' ); ?>

    <!-- Étape 0 : infos -->
    <div class="pp-step pp-solo-intro" id="pp-step-intro">
      <h2 class="pp-step-title">Bienvenue 👋</h2>
      <p class="pp-step-intro"><?php echo wp_kses_post( get_option('pp_texte_intro', "Ceci est un outil de clarification, il ne remplace pas un accompagnement humain et ne constitue pas un diagnostic. Il est composé de 128 questions couvrant 5 grandes dimensions et 30 facettes. Comptez environ 12 minutes.") ); ?></p>
      <?php
        $c1 = sanitize_hex_color(get_option('pp_color_primary','#E8541A')) ?: '#E8541A';
        $c2 = sanitize_hex_color(get_option('pp_color_secondary','#1E2A3A')) ?: '#1E2A3A';
      ?>
      <div style="display:flex;gap:16px;align-items:flex-start;background:#EEF3F8;border:1px solid #C8D9E8;border-left:4px solid <?php echo esc_attr($c1); ?>;border-radius:12px;padding:20px;margin:18px 0 22px;">
        <div style="font-size:28px;flex-shrink:0;margin-top:2px;">🧭</div>
        <div style="flex:1;">
          <p style="font-size:15px;font-weight:700;color:<?php echo esc_attr($c1); ?>;margin:0 0 6px;">Ce test est un point de départ.</p>
          <p style="font-size:14px;color:#1E2A3A;margin:0 0 14px;line-height:1.6;">Pour aller plus loin — mieux vous connaître, clarifier votre orientation professionnelle et passer à l'action — le <strong>bilan de compétences</strong> est fait pour vous.</p>
          <a href="<?php echo esc_url( get_option('pp_rdv_url', home_url('/contact')) ); ?>" style="display:inline-block;background:<?php echo esc_attr($c1); ?>;color:#fff;font-size:14px;font-weight:700;padding:11px 22px;border-radius:999px;text-decoration:none;box-shadow:0 2px 10px rgba(0,0,0,.15);" target="_blank" rel="noopener">
            <?php echo esc_html( get_option('pp_texte_rdv_cta', 'Découvrir le bilan de compétences →') ); ?>
          </a>
        </div>
      </div>
      <label class="pp-label">Prénom <input type="text" name="prenom" class="pp-input" placeholder="Votre prénom" required></label>
      <label class="pp-label">Email  <input type="email" name="email" class="pp-input" placeholder="votre@email.com" required></label>
      <label class="pp-checkbox">
        <input type="checkbox" name="consentement" value="1" required>
        J'accepte que mes données soient traitées conformément à la <a href="<?php echo esc_url( get_option('pp_politique_url', home_url('/politique-de-confidentialite')) ); ?>" target="_blank">politique de confidentialité</a>.
      </label>
      <button type="button" class="pp-btn pp-btn-next" onclick="ppSoloStart()" style="margin-top:4px;">Commencer le test →</button>
    </div>

    <!-- Zone question unique -->
    <div id="pp-solo-q-wrap" class="pp-hidden">
      <div class="pp-solo-header">
        <button type="button" class="pp-solo-back" id="pp-solo-back" onclick="ppSoloPrev()">← Retour</button>
        <span class="pp-solo-counter" id="pp-solo-counter">1 / 128</span>
      </div>
      <div class="pp-solo-card" id="pp-solo-card">
        <p class="pp-solo-qtext" id="pp-solo-qtext"></p>
        <div class="pp-solo-choices" id="pp-solo-choices"></div>
      </div>
    </div>
  </form>

  <div id="pp-results" style="display:none;">
    <div class="pp-result-block" id="pp-result-block"></div>
  </div>

  <div id="pp-loader" style="display:none;" class="pp-loader">
    <div class="pp-spinner"></div>
    <p>Analyse de votre profil en cours…</p>
  </div>
</div>

<script>
(function(){
  var allQuestions = <?php echo wp_json_encode( PP_Questions::get_all() ); ?>;
  var ajaxUrl  = '<?php echo admin_url("admin-ajax.php"); ?>';
  var nonce    = '<?php echo wp_create_nonce("pp_nonce"); ?>';
  var STORAGE_KEY = 'pp_solo_v1';
  var answers  = {};
  var qIndex   = -1;
  var LIKERT = [{val:1,label:'Pas moi'},{val:2,label:'Un peu moi'},{val:3,label:'Assez moi'},{val:4,label:'Tout à fait moi'}];
  var DIM_LABELS = {O:'Ouverture',C:'Conscience',E:'Extraversion',A:'Agréabilité',N:'Stabilité',DS:'Désirabilité'};

  function save() {
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify({prenom:document.querySelector('[name="prenom"]').value.trim(),email:document.querySelector('[name="email"]').value.trim(),consent:document.querySelector('[name="consentement"]').checked,qIndex:qIndex,answers:answers,ts:Date.now()})); } catch(e) {}
  }
  function loadSave() {
    try { var d=JSON.parse(localStorage.getItem(STORAGE_KEY)||'null'); if(!d||Date.now()-d.ts>7*24*3600*1000)return null; return d; } catch(e){return null;}
  }
  function clearSave() { try{localStorage.removeItem(STORAGE_KEY);}catch(e){} }

  (function checkResume(){
    var d=loadSave();
    if(!d||d.qIndex<0||Object.keys(d.answers||{}).length===0)return;
    var pct=Math.round(Object.keys(d.answers).length/allQuestions.filter(function(q){return q.dim!=='DS';}).length*100);
    var banner=document.createElement('div');
    banner.className='pp-reprise-banner';
    banner.innerHTML='<div class="pp-reprise-inner"><div class="pp-reprise-text"><strong>🔖 Progression sauvegardée</strong><span>Question '+(d.qIndex+1)+' / '+allQuestions.length+' — '+pct+'% complété</span></div><div class="pp-reprise-actions"><button class="pp-btn pp-btn-next" id="pp-btn-reprendre">Reprendre →</button><button class="pp-btn pp-btn-outline" id="pp-btn-recommencer">Recommencer</button></div></div>';
    document.getElementById('pp-test-container').insertBefore(banner,document.getElementById('pp-test-container').firstChild);
    document.getElementById('pp-btn-reprendre').onclick=function(){banner.remove();document.querySelector('[name="prenom"]').value=d.prenom||'';document.querySelector('[name="email"]').value=d.email||'';if(d.consent)document.querySelector('[name="consentement"]').checked=true;answers=d.answers||{};showQuestion(d.qIndex);};
    document.getElementById('pp-btn-recommencer').onclick=function(){clearSave();banner.remove();};
  })();

  window.ppSoloStart=function(){
    var prenom=document.querySelector('[name="prenom"]').value.trim();
    var email=document.querySelector('[name="email"]').value.trim();
    var consent=document.querySelector('[name="consentement"]').checked;
    if(!prenom||!email){alert('Prénom et email sont obligatoires.');return;}
    if(!consent){alert('Veuillez accepter la politique de confidentialité.');return;}
    showQuestion(0);
  };

  function showQuestion(idx){
    document.getElementById('pp-step-intro').classList.add('pp-hidden');
    document.getElementById('pp-solo-q-wrap').classList.remove('pp-hidden');
    document.getElementById('pp-progress-wrap').classList.remove('pp-hidden');
    // Cacher le bandeau reprise s'il est encore présent
    var b = document.querySelector('.pp-reprise-banner');
    if (b) b.style.display = 'none';
    // Réinitialiser l'affichage de la carte (peut avoir été masquée par submitSolo)
    var card = document.getElementById('pp-solo-card');
    if (card) card.style.display = '';
    // Bandeau encouragement à la question 64 (index 63)
    var enc = document.getElementById('pp-encourage-banner');
    if (enc) {
      if (idx === 63) {
        enc.classList.remove('pp-hidden');
      } else if (idx !== 63) {
        // Ne cacher que si l'utilisateur ne l'a pas déjà fermé manuellement
        // On le laisse visible si idx > 63 jusqu'à fermeture manuelle
        if (idx < 63) enc.classList.add('pp-hidden');
      }
    }
    qIndex=idx;
    var q=allQuestions[idx];
    var total=allQuestions.length;
    var pct=Math.round(idx/total*100);
    document.getElementById('pp-solo-counter').textContent=(idx+1)+' / '+total;
    document.getElementById('pp-solo-back').style.visibility=idx===0?'hidden':'visible';
    document.getElementById('pp-progress').style.width=pct+'%';
    document.getElementById('pp-progress-dim').textContent=DIM_LABELS[q.dim]||'';
    document.getElementById('pp-progress-pct').textContent=pct+'%';
    document.getElementById('pp-solo-qtext').textContent=q.texte;
    var choicesEl=document.getElementById('pp-solo-choices');
    choicesEl.innerHTML='';
    var saved=answers[q.id];
    LIKERT.forEach(function(opt){
      var btn=document.createElement('button');
      btn.type='button';
      btn.className='pp-solo-choice'+(saved===opt.val?' pp-solo-choice-sel':'');
      btn.textContent=opt.label;
      btn.onclick=function(){ppSoloAnswer(q.id,opt.val,idx);};
      choicesEl.appendChild(btn);
    });
    var card=document.getElementById('pp-solo-card');
    card.classList.remove('pp-solo-in','pp-solo-in-back','pp-solo-out','pp-solo-out-back');
    void card.offsetWidth;
    card.classList.add('pp-solo-in');
    save();
  }

  window.ppSoloAnswer=function(qid,val,idx){
    answers[qid]=val;
    document.querySelectorAll('.pp-solo-choice').forEach(function(b){b.classList.remove('pp-solo-choice-sel');});
    event.currentTarget.classList.add('pp-solo-choice-sel');
    // Masquer le bandeau encouragement dès qu'on répond
    var enc = document.getElementById('pp-encourage-banner');
    if (enc) enc.classList.add('pp-hidden');
    save();
    setTimeout(function(){
      if(idx<allQuestions.length-1){
        var card=document.getElementById('pp-solo-card');
        card.classList.add('pp-solo-out');
        setTimeout(function(){showQuestion(idx+1);},220);
      } else {
        // Dernière question — masquer immédiatement avant soumission
        document.getElementById('pp-solo-q-wrap').style.display='none';
        document.getElementById('pp-progress-wrap').style.display='none';
        submitSolo();
      }
    },280);
  };

  window.ppSoloPrev=function(){
    if(qIndex>0){
      var card=document.getElementById('pp-solo-card');
      card.classList.add('pp-solo-out-back');
      setTimeout(function(){card.classList.remove('pp-solo-out-back');showQuestion(qIndex-1);},220);
    } else {
      document.getElementById('pp-solo-q-wrap').classList.add('pp-hidden');
      document.getElementById('pp-step-intro').classList.remove('pp-hidden');
      document.getElementById('pp-progress-wrap').classList.add('pp-hidden');
      qIndex=-1;
    }
  };

  function submitSolo(){
    document.getElementById('pp-solo-q-wrap').classList.add('pp-hidden');
    document.getElementById('pp-progress-wrap').classList.add('pp-hidden');
    var enc = document.getElementById('pp-encourage-banner');
    if (enc) enc.classList.add('pp-hidden');
    // Masquer explicitement la carte question pour éviter qu'elle reste visible
    var card = document.getElementById('pp-solo-card');
    if (card) card.style.display = 'none';
    document.getElementById('pp-loader').classList.remove('pp-hidden');document.getElementById('pp-loader').style.display='flex';
    var formData=new FormData();
    formData.append('action','pp_submit');formData.append('nonce',nonce);
    formData.append('prenom',document.querySelector('[name="prenom"]').value.trim());
    formData.append('email',document.querySelector('[name="email"]').value.trim());
    formData.append('consentement',document.querySelector('[name="consentement"]').checked?1:0);
    Object.keys(answers).forEach(function(k){formData.append('reponses['+k+']',answers[k]);});
    var urlParams=new URLSearchParams(window.location.search);
    var inviteTk=urlParams.get('pp_invite_tk')||'';if(inviteTk)formData.append('pp_invite_tk',inviteTk);
    var ppSource=urlParams.get('pp_source');if(ppSource)formData.append('source',ppSource);
    fetch(ajaxUrl,{method:'POST',body:formData})
      .then(function(r){return r.json();})
      .then(function(data){
        document.getElementById('pp-loader').classList.add('pp-hidden');
        if(data.success){clearSave();renderResults(data.data);}
        else{alert(data.data.message||'Une erreur est survenue.');showQuestion(qIndex);}
      })
      .catch(function(){alert('Erreur réseau. Votre progression est sauvegardée.');document.getElementById('pp-loader').classList.add('pp-hidden');showQuestion(qIndex);});
  }

  function renderResults(d){
    var arch=d.archetype,dims=d.scores_dim,rdv=d.rdv_url;
    var c1=arch.couleur1||'#E8541A',c2=arch.couleur2||'#1E2A3A';
    var dimCfg={O:{icon:'🔭',label:'Ouverture',color:'#E8541A'},C:{icon:'🗂️',label:'Conscience',color:'#1E2A3A'},E:{icon:'💬',label:'Extraversion',color:'#C4430F'},A:{icon:'🤝',label:'Agréabilité',color:'#2E4A6A'},N:{icon:'🌊',label:'Stabilité',color:'#8FA8BE'}};
    var bars='';
    Object.keys(dimCfg).forEach(function(k){
      var cfg=dimCfg[k],pct=dims[k]?dims[k].pct:50,lbl=dims[k]?dims[k].label:'';
      bars+='<div style="margin-bottom:13px;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;"><span style="font-size:13px;font-weight:700;color:#1e293b;">'+cfg.icon+' '+cfg.label+'</span><span style="font-size:13px;font-weight:800;color:'+cfg.color+';">'+pct+'%<span style="font-size:11px;font-weight:400;color:#94a3b8;margin-left:4px;">— '+lbl+'</span></span></div><div style="background:#e2e8f0;border-radius:999px;height:8px;overflow:hidden;"><div class="pp-bar-anim" style="background:'+cfg.color+';height:8px;width:0%;border-radius:999px;transition:width 1s cubic-bezier(.4,0,.2,1);" data-target="'+pct+'"></div></div></div>';
    });
    var traitsHtml='';
    if(arch.traits&&arch.traits.length){traitsHtml='<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">';arch.traits.forEach(function(tr){traitsHtml+='<span style="background:'+c2+';color:#fff;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:600;">'+escHtml(tr)+'</span>';});traitsHtml+='</div>';}
    var html=''
      +'<div style="background:'+c2+';border-radius:24px;padding:48px 28px 36px;text-align:center;color:#fff;margin-bottom:20px;position:relative;overflow:hidden;">'
      +'<div style="position:absolute;top:-50px;right:-50px;width:180px;height:180px;background:rgba(255,255,255,.04);border-radius:50%;"></div>'
      +'<div style="font-size:64px;line-height:1;margin-bottom:16px;position:relative;">'+escHtml(arch.emoji)+'</div>'
      +'<p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:.14em;opacity:.6;font-weight:600;">Le profil de</p>'
      +'<p style="margin:0 0 12px;font-size:15px;font-weight:700;opacity:.85;">'+escHtml(d.prenom)+'</p>'
      +'<p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:.14em;opacity:.6;font-weight:600;">Votre archétype</p>'
      +'<h2 style="font-size:28px;font-weight:900;margin:0 0 8px;letter-spacing:-.02em;color:#fff;">'+escHtml(arch.nom)+'</h2>'
      +'<p style="font-size:14px;font-style:italic;margin:0 0 20px;opacity:.8;">'+escHtml(arch.tagline)+'</p>'
      +'<div style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:6px 16px;font-size:12px;font-weight:600;">✦ Profil présent chez seulement '+arch.rarete+'% des personnes</div>'
      +'</div>'
      +'<div style="background:#fff;border-left:4px solid '+c1+';border-radius:0 16px 16px 0;padding:18px 22px;margin-bottom:16px;">'
      +'<p style="margin:0;color:#334155;font-size:14px;line-height:1.85;">'+escHtml(arch.description)+'</p></div>'
      +traitsHtml
      +'<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 24px;margin-bottom:16px;">'
      +'<p style="font-size:13px;font-weight:700;color:#1e293b;margin:0 0 16px;">📊 Vos 5 dimensions</p>'+bars+'</div>'
      +'<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 24px;margin-bottom:16px;">'
      +'<p style="font-size:13px;font-weight:700;color:#1e293b;margin:0 0 12px;">⬇ Télécharger</p>'
      +'<button onclick="ppGeneratePDFFromResultsSolo()" style="display:block;width:100%;background:'+c2+';color:#fff;border:none;cursor:pointer;padding:14px;border-radius:999px;font-size:14px;font-weight:700;font-family:inherit;margin-bottom:12px;">⬇ Télécharger mon rapport PDF complet</button>'
      +d.carte_html
      +'</div>'
      +'<div style="background:'+c2+';border-radius:20px;padding:28px 24px;margin-bottom:16px;color:#fff;">'
      +'<p style="font-size:10px;text-transform:uppercase;letter-spacing:.14em;color:#E8541A;font-weight:700;margin:0 0 10px;">Et maintenant ?</p>'
      +'<p style="font-size:18px;font-weight:800;line-height:1.3;margin:0 0 8px;">Transformez ce profil en projet professionnel concret</p>'
      +'<p style="font-size:13px;opacity:.7;line-height:1.65;margin:0 0 20px;">Le bilan de compétences vous aide à clarifier vos forces, vos motivations et construire un cap professionnel aligné avec qui vous êtes vraiment.</p>'
      +'<a href="'+escHtml(rdv)+'" style="display:block;text-align:center;background:'+c1+';color:#fff;padding:14px;border-radius:999px;text-decoration:none;font-size:14px;font-weight:700;margin-bottom:10px;">✦ Réserver mon entretien gratuit</a>'
      +'<a href="'+escHtml(d.profil_url||rdv)+'" style="display:block;text-align:center;font-size:12px;color:rgba(255,255,255,.45);text-decoration:none;">Voir mon profil public →</a>'
      +'</div>'
      +'<p style="text-align:center;font-size:11px;color:#94a3b8;line-height:1.8;margin-bottom:8px;">Réalisé sur <strong>'+escHtml(d.site_name||'Praxis Accompagnement')+'</strong></p>';
    document.getElementById('pp-result-block').innerHTML=html;
    document.getElementById('pp-solo-q-wrap').style.display='none';
    document.getElementById('pp-progress-wrap').style.display='none';
    document.getElementById('pp-results').style.display='block';
    requestAnimationFrame(function(){requestAnimationFrame(function(){document.querySelectorAll('#pp-result-block .pp-bar-anim').forEach(function(el){el.style.width=el.getAttribute('data-target')+'%';});});});
    document.getElementById('pp-result-block').scrollIntoView({behavior:'smooth',block:'start'});
    window.PP_CURRENT_PROFILE={prenom:d.prenom,archetype:d.archetype,scores_dim:d.scores_dim,scores_facette:d.scores_facette||{},facettes_map:d.facettes_map||{},rdv_url:d.rdv_url,profil_url:d.profil_url,site_name:d.site_name||document.title,date:new Date().toLocaleDateString('fr-FR')};
  }

  window.ppGeneratePDFFromResultsSolo=function(){if(window.PP_CURRENT_PROFILE&&window.ppGeneratePDF)ppGeneratePDF(window.PP_CURRENT_PROFILE);};

  function escHtml(str){var d=document.createElement('div');d.appendChild(document.createTextNode(str||''));return d.innerHTML;}
})();
</script>
