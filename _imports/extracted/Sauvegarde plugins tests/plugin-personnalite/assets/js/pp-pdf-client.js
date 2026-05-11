/**
 * pp-pdf-client.js — Génération PDF côté client via jsPDF
 * Design premium — style rapport professionnel
 */
(function() {
  'use strict';

  var NOIR   = [22,  28,  40];
  var GRIS_F = [80,  90, 110];
  var GRIS_M = [140,150,165];
  var GRIS_L = [210,215,225];
  var BLANC  = [255,255,255];
  var FOND   = [248,249,252];

  var DIM_COLORS = {
    O:[99,102,241], C:[14,165,233], E:[245,158,11], A:[16,185,129], N:[239,68,68],
  };
  var DIM_LABELS = { O:'Ouverture', C:'Conscience', E:'Extraversion', A:'Agréabilité', N:'Stabilité' };

  function loadJsPDF(cb) {
    if (window.jspdf && window.jspdf.jsPDF) { cb(window.jspdf.jsPDF); return; }
    var s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
    s.onload = function() { if (window.jspdf && window.jspdf.jsPDF) cb(window.jspdf.jsPDF); else ppToast('❌ Impossible de charger jsPDF.'); };
    s.onerror = function() { ppToast('❌ Erreur chargement jsPDF.'); };
    document.head.appendChild(s);
  }

  function hexToRgb(h) { return [parseInt(h.slice(1,3),16),parseInt(h.slice(3,5),16),parseInt(h.slice(5,7),16)]; }
  function mix(a,b,t) { return [Math.round(a[0]+(b[0]-a[0])*t),Math.round(a[1]+(b[1]-a[1])*t),Math.round(a[2]+(b[2]-a[2])*t)]; }
  function fc(doc,c){doc.setFillColor(c[0],c[1],c[2]);}
  function dc(doc,c){doc.setDrawColor(c[0],c[1],c[2]);}
  function tc(doc,c){doc.setTextColor(c[0],c[1],c[2]);}

  function drawBar(doc,x,y,w,h,pct,color){
    fc(doc,GRIS_L); doc.roundedRect(x,y,w,h,h/2,h/2,'F');
    if(pct>0){var fw=Math.max(h,w*pct/100); fc(doc,color); doc.roundedRect(x,y,fw,h,h/2,h/2,'F');}
  }

  function wrap(doc,text,x,y,maxW,lineH){
    if(!text) return y;
    var lines=doc.splitTextToSize(text,maxW);
    lines.forEach(function(l,i){doc.text(l,x,y+i*lineH);});
    return y+lines.length*lineH;
  }

  function grad(doc,x,y,w,h,c1,c2){
    var steps=25;
    for(var i=0;i<steps;i++){
      var t=i/steps,c=mix(c1,c2,t);
      fc(doc,c); doc.rect(x+(w/steps)*i,y,(w/steps)+0.5,h,'F');
    }
  }

  // ── COUVERTURE ─────────────────────────────────────────────────────────────
  function drawCover(doc,W,H,prenom,arch,date,site,profurl,c1,c2){
    fc(doc,BLANC); doc.rect(0,0,W,H,'F');
    var blockW=72;

    // Bloc coloré gauche
    for(var i=0;i<30;i++){var t=i/30,c=mix(c1,c2,t); fc(doc,c); doc.rect(0,(H/30)*i,blockW,(H/30)+0.5,'F');}

    // Nom archétype en haut du bloc
    doc.setFontSize(7); tc(doc,BLANC); doc.setFont('helvetica','normal');
    if(arch.nom) doc.text(arch.nom.toUpperCase(), blockW/2, 20, {align:'center',maxWidth:blockW-10});



    // Ligne déco
    dc(doc,[255,255,255]); doc.setLineWidth(0.3);
    doc.line(10, H*0.38, blockW-10, H*0.38);

    // Traits
    if(arch.traits&&arch.traits.length){
      var ty=H*0.41;
      arch.traits.forEach(function(t){
        fc(doc,BLANC); doc.circle(12,ty-1.2,1.2,'F');
        doc.setFontSize(7.5); tc(doc,BLANC); doc.setFont('helvetica','normal');
        doc.text(t, 17, ty); ty+=8.5;
      });
    }

    // Pastille rareté
    if(arch.rarete){
      fc(doc,[255,255,255]); doc.setGState&&doc.setGState(doc.GState({opacity:0.18}));
      doc.roundedRect(8,H-40,blockW-16,14,2,2,'F');
      doc.setGState&&doc.setGState(doc.GState({opacity:1.0}));
      doc.setFontSize(14); tc(doc,BLANC); doc.setFont('helvetica','bold');
      doc.text(arch.rarete+'%', blockW/2, H-32, {align:'center'});
      doc.setFontSize(6.5); doc.setFont('helvetica','normal');
      doc.text('des profils', blockW/2, H-27, {align:'center'});
    }

    // Zone droite
    var rx=blockW+18;

    // Logo
    doc.setFontSize(8.5); tc(doc,c1); doc.setFont('helvetica','bold');
    doc.text(site||'PraxiMum', W-14, 15, {align:'right'});
    dc(doc,GRIS_L); doc.setLineWidth(0.3); doc.line(rx,19,W-14,19);
    doc.setFontSize(7); tc(doc,GRIS_M); doc.setFont('helvetica','normal');
    doc.text('Profil de personnalité', W-14, 24, {align:'right'});

    // Bloc principal centré (H*0.32)
    var cy=H*0.30;
    doc.setFontSize(7.5); tc(doc,GRIS_M); doc.setFont('helvetica','normal');
    doc.text('RAPPORT DE PERSONNALITÉ', rx, cy); cy+=13;

    doc.setFontSize(32); tc(doc,NOIR); doc.setFont('helvetica','bold');
    doc.text(prenom, rx, cy); cy+=5;

    fc(doc,c1); doc.rect(rx,cy,40,1.5,'F'); cy+=10;

    if(arch.nom){doc.setFontSize(15); tc(doc,c1); doc.setFont('helvetica','bold'); doc.text(arch.nom,rx,cy); cy+=8;}
    if(arch.tagline){doc.setFontSize(9.5); tc(doc,GRIS_F); doc.setFont('helvetica','italic'); doc.text(arch.tagline,rx,cy); cy+=7;}

    doc.setFontSize(8); tc(doc,GRIS_M); doc.setFont('helvetica','normal');
    doc.text('Test passé le '+date, rx, cy); cy+=12;

    if(arch.description){
      fc(doc,mix(c1,BLANC,0.94)); doc.roundedRect(rx,cy,W-rx-14,52,2,2,'F');
      fc(doc,c1); doc.roundedRect(rx,cy,3,52,1,1,'F');
      doc.setFontSize(8.5); tc(doc,GRIS_F); doc.setFont('helvetica','normal');
      var dl=doc.splitTextToSize(arch.description,W-rx-24);
      dl.slice(0,8).forEach(function(l,i){doc.text(l,rx+8,cy+7+i*5);});
    }

    dc(doc,GRIS_L); doc.setLineWidth(0.3); doc.line(rx,H-20,W-14,H-20);
    doc.setFontSize(6.5); tc(doc,GRIS_M); doc.setFont('helvetica','normal');
    doc.text('Rapport personnalisé — '+(site||'PraxiMum')+' — Document confidentiel', rx, H-14);
    if(profurl){tc(doc,c1); doc.text(profurl,rx,H-8); doc.link(rx,H-11,doc.getTextWidth(profurl),5,{url:profurl});}
  }

  // ── HEADER pages intérieures ───────────────────────────────────────────────
  function drawHeader(doc,W,prenom,archNom,pageLabel,c1){
    // Bande fine couleur tout en haut
    fc(doc,c1); doc.rect(0,0,W,2,'F');
    // Zone header blanche
    fc(doc,BLANC); doc.rect(0,2,W,13,'F');
    dc(doc,GRIS_L); doc.setLineWidth(0.3); doc.line(0,15,W,15);

    doc.setFontSize(7.5); tc(doc,NOIR); doc.setFont('helvetica','bold');
    doc.text(prenom, 12, 10);
    if(archNom){
      tc(doc,GRIS_M); doc.setFont('helvetica','normal');
      doc.text('  |  '+archNom, 12+doc.getTextWidth(prenom), 10);
    }
    if(pageLabel){
      tc(doc,GRIS_M); doc.setFont('helvetica','normal');
      doc.text(pageLabel, W-12, 10, {align:'right'});
    }
  }

  // ── FOOTER ────────────────────────────────────────────────────────────────
  function drawFooter(doc,W,H,p,total){
    dc(doc,GRIS_L); doc.setLineWidth(0.3); doc.line(12,H-10,W-12,H-10);
    doc.setFontSize(7); tc(doc,GRIS_M); doc.setFont('helvetica','normal');
    doc.text('Page '+p+' / '+total, W/2, H-5, {align:'center'});
  }

  // ── TITRE DE SECTION ──────────────────────────────────────────────────────
  function sectionTitle(doc,ML,y,W,MR,label,c1){
    doc.setFontSize(13); tc(doc,NOIR); doc.setFont('helvetica','bold');
    doc.text(label, ML, y); y+=2;
    dc(doc,c1); doc.setLineWidth(0.5); doc.line(ML,y,W-MR,y); y+=8;
    return y;
  }

  // ── RADAR ─────────────────────────────────────────────────────────────────
  function drawRadar(doc,cx,cy,r,scoresDim,c1){
    var dims=['O','C','E','A','N'];
    var angles=dims.map(function(_,i){return Math.PI/2-i*2*Math.PI/5;});
    [0.25,0.5,0.75,1.0].forEach(function(f){
      dc(doc,f===1?GRIS_L:[235,238,245]); doc.setLineWidth(f===1?0.4:0.15);
      var pts=angles.map(function(a){return[cx+r*f*Math.cos(a),cy-r*f*Math.sin(a)];});
      for(var i=0;i<pts.length;i++){var n=pts[(i+1)%pts.length]; doc.line(pts[i][0],pts[i][1],n[0],n[1]);}
    });
    angles.forEach(function(a){dc(doc,GRIS_L); doc.setLineWidth(0.15); doc.line(cx,cy,cx+r*Math.cos(a),cy-r*Math.sin(a));});
    var pts2=dims.map(function(d,i){var p=((scoresDim[d]&&scoresDim[d].pct)||50)/100; return[cx+r*p*Math.cos(angles[i]),cy-r*p*Math.sin(angles[i])];});
    dc(doc,c1); doc.setLineWidth(1.2);
    for(var i=0;i<pts2.length;i++){var n2=pts2[(i+1)%pts2.length]; doc.line(pts2[i][0],pts2[i][1],n2[0],n2[1]);}
    dims.forEach(function(d,i){
      var col=DIM_COLORS[d];
      var px=pts2[i][0], py=pts2[i][1];
      // Cercle blanc + cercle coloré plus grand
      fc(doc,BLANC); doc.circle(px,py,3.5,'F');
      fc(doc,col);   doc.circle(px,py,3.5,'S');
      doc.setLineWidth(0.8);
      // Lettre centrée dans le cercle
      doc.setFontSize(5); tc(doc,col); doc.setFont('helvetica','bold');
      doc.text(d, px, py+1.5, {align:'center'});
      // Label % à l'extérieur
      var lx=cx+(r+10)*Math.cos(angles[i]), ly=cy-(r+10)*Math.sin(angles[i]);
      doc.setFontSize(6); tc(doc,col); doc.setFont('helvetica','bold');
      doc.text(((scoresDim[d]&&scoresDim[d].pct)||50)+'%', lx, ly, {align:'center'});
      doc.setFontSize(5.5); doc.setFont('helvetica','normal');
      tc(doc,GRIS_M);
      doc.text(DIM_LABELS[d], lx, ly+4, {align:'center'});
    });
  }

  // ── BUILD PDF ─────────────────────────────────────────────────────────────
  function buildPDF(JsPDF,profileData){
    var doc=new JsPDF({orientation:'portrait',unit:'mm',format:'a4'});
    var W=210,H=297,ML=14,MR=14,CW=W-ML-MR;

    var arch=profileData.archetype||{};
    var dim=profileData.scores_dim||{};
    var facettes=profileData.scores_facette||{};
    var fmap=profileData.facettes_map||{};
    var prenom=profileData.prenom||'';
    var site=profileData.site_name||'';
    var rdv_url=profileData.rdv_url||'';
    var profurl=profileData.profil_url||'';
    var date=profileData.date||new Date().toLocaleDateString('fr-FR');
    var c1=arch.couleur1?hexToRgb(arch.couleur1):[99,102,241];
    var c2=arch.couleur2?hexToRgb(arch.couleur2):[139,92,246];

    // ── PAGE 1 : Couverture ────────────────────────────────────────────────
    drawCover(doc,W,H,prenom,arch,date,site,profurl,c1,c2);

    // ── PAGE 2 : Vue d'ensemble ────────────────────────────────────────────
    doc.addPage();
    fc(doc,BLANC); doc.rect(0,0,W,H,'F');
    drawHeader(doc,W,prenom,arch.nom||'','Vue d\'ensemble',c1);
    var y=sectionTitle(doc,ML,22,W,MR,'Vue d\'ensemble — Les 5 dimensions',c1);

    // Radar gauche
    var radarCX=ML+32, radarCY=y+42, radarR=26;
    drawRadar(doc,radarCX,radarCY,radarR,dim,c1);

    // Barres droite — colonnes fixes propres
    var bx=ML+78, barW=CW-78, pctColW=14, lblColW=18;
    var barTrackW=barW-pctColW-lblColW-4;

    ['O','C','E','A','N'].forEach(function(d,i){
      var dd=dim[d]||{},pct=dd.pct||50,lbl=dd.label||'',col=DIM_COLORS[d];
      var by=y+i*12.5;

      // Nom + % + label sur la même ligne
      doc.setFontSize(8); tc(doc,NOIR); doc.setFont('helvetica','bold');
      doc.text(DIM_LABELS[d], bx, by+4);

      doc.setFontSize(9); tc(doc,col); doc.setFont('helvetica','bold');
      doc.text(pct+'%', bx+barTrackW+pctColW, by+4, {align:'right'});

      doc.setFontSize(7); tc(doc,GRIS_M); doc.setFont('helvetica','normal');
      doc.text(lbl, bx+barTrackW+pctColW+lblColW+2, by+4, {align:'right'});

      // Barre ultra fine dessous
      drawBar(doc,bx,by+6.5,barTrackW,2,pct,col);
    });

    y+=82;

    // Encadré archétype description
    if(arch.description){
      fc(doc,FOND); doc.roundedRect(ML,y,CW,48,2,2,'F');
      fc(doc,c1); doc.roundedRect(ML,y,3,48,1,1,'F');
      doc.setFontSize(8.5); tc(doc,GRIS_F); doc.setFont('helvetica','italic');
      var dl=doc.splitTextToSize(arch.description,CW-12);
      dl.slice(0,7).forEach(function(l,i){doc.text(l,ML+7,y+7+i*5);});
      y+=46;
    }

    // ── PAGES 3-N : Facettes ───────────────────────────────────────────────
    var dimDescs={
      O:"Curiosité intellectuelle, imagination et ouverture à de nouvelles expériences.",
      C:"Organisation, fiabilité, autodiscipline et persévérance.",
      E:"Sociabilité, assertivité et énergie tirée des interactions.",
      A:"Coopération, empathie et confiance envers autrui.",
      N:"Gestion des émotions. Un score bas indique une bonne stabilité émotionnelle.",
    };

    ['O','C','E','A','N'].forEach(function(dimKey,di){
      if(di%2===0){
        doc.addPage(); fc(doc,BLANC); doc.rect(0,0,W,H,'F');
        drawHeader(doc,W,prenom,arch.nom||'','Détail — '+DIM_LABELS[dimKey],c1);
        y=di===0?sectionTitle(doc,ML,22,W,MR,'Détail par dimension',c1):22;
      }

      var dd=dim[dimKey]||{},pct=dd.pct||50,lbl=dd.label||'',col=DIM_COLORS[dimKey];

      // En-tête dimension sobre
      var dimBg=mix(col,BLANC,0.93);
      fc(doc,dimBg); doc.roundedRect(ML,y,CW,16,2,2,'F');
      fc(doc,col); doc.roundedRect(ML,y,4,16,2,2,'F');

      // Nom + score
      doc.setFontSize(11); tc(doc,col); doc.setFont('helvetica','bold');
      doc.text(DIM_LABELS[dimKey], ML+9, y+7);
      doc.setFontSize(16); tc(doc,col); doc.setFont('helvetica','bold');
      doc.text(pct+'%', W-MR-2, y+9, {align:'right'});

      // Label niveau + description — ligne séparée
      doc.setFontSize(7.5); tc(doc,GRIS_M); doc.setFont('helvetica','normal');
      doc.text(lbl, ML+9, y+13);
      var descShort=(dimDescs[dimKey]||'').substring(0,75);
      doc.text(descShort, ML+9+doc.getTextWidth(lbl)+5, y+13);

      y+=19;

      // Barre principale pleine largeur
      drawBar(doc,ML,y,CW,2,pct,col); y+=7;

      // 6 facettes en 2 colonnes
      var fkeys=Object.keys(fmap).filter(function(k){return fmap[k].dim===dimKey;});
      var colW=(CW-5)/2;

      fkeys.forEach(function(fk,fi){
        var cx2=ML+(fi%2)*(colW+5), ry=y+Math.floor(fi/2)*24;
        var fs=facettes[fk]||{},fpct=fs.pct||50,flbl=fs.label||'';

        // Carte facette
        fc(doc,fi%4<2?BLANC:FOND);
        doc.roundedRect(cx2,ry,colW,22,2,2,'F');
        dc(doc,GRIS_L); doc.setLineWidth(0.2);
        doc.roundedRect(cx2,ry,colW,22,2,2,'S');
        fc(doc,col); doc.roundedRect(cx2,ry,2.5,22,1,1,'F');

        // Nom facette
        doc.setFontSize(8); tc(doc,NOIR); doc.setFont('helvetica','bold');
        var facLabel=(fmap[fk].label||fk);
        if(facLabel.length>20) facLabel=facLabel.substring(0,18)+'..';
        doc.text(facLabel, cx2+6, ry+6);

        // Score
        doc.setFontSize(10); tc(doc,col); doc.setFont('helvetica','bold');
        doc.text(fpct+'%', cx2+colW-4, ry+6, {align:'right'});

        // Barre
        drawBar(doc,cx2+6,ry+8.5,colW-12,2,fpct,col);

        // Niveau + description courte — 2 lignes séparées
        doc.setFontSize(6.5); tc(doc,col); doc.setFont('helvetica','bold');
        doc.text(flbl, cx2+6, ry+15.5);

        var desc=(fmap[fk].desc||'').substring(0,50);
        tc(doc,GRIS_M); doc.setFont('helvetica','normal');
        doc.text(desc, cx2+6, ry+19.5, {maxWidth:colW-12});
      });

      y+=Math.ceil(fkeys.length/2)*24+8;
    });

    // ── PAGE FINALE : Pistes + CTA ─────────────────────────────────────────
    doc.addPage(); fc(doc,BLANC); doc.rect(0,0,W,H,'F');
    drawHeader(doc,W,prenom,arch.nom||'','Pistes de développement',c1);
    y=sectionTitle(doc,ML,22,W,MR,'Pistes de développement',c1);

    var recos={
      O:{bas:['Explorateur en devenir',"Votre ancrage dans le concret est une force. Testez une pratique créative 30 jours ou lisez dans un domaine inconnu."],haut:['Canalisez votre curiosité',"Définissez 2-3 projets par trimestre et finalisez avant d'en lancer de nouveaux."]},
      C:{bas:['Structurer sans se contraindre',"Un peu plus de structure libère de l'énergie mentale. Essayez une to-do list de 3 priorités quotidiennes."],haut:['Lâcher prise sur le contrôle',"Veillez à ne pas transformer l'exigence en perfectionnisme paralysant."]},
      E:{bas:['Énergie introvertie',"Préparez vos interventions, cherchez les formats 1-1 plutôt que de groupe."],haut:['Écoute et présence',"Travaillez l'écoute active : reformulez avant de répondre."]},
      A:{bas:['Diplomatie stratégique',"Explorez la communication non-violente pour maintenir votre directness."],haut:['Affirmation de soi',"Pratiquez l'assertivité : exprimez vos besoins clairement, sans agressivité."]},
      N:{haut:['Régulation émotionnelle',"Identifiez vos déclencheurs et pratiquez cohérence cardiaque ou journaling."],bas:['Résilience acquise',"Votre stabilité vous permet de performer sous pression."]},
    };

    ['O','C','E','A','N'].forEach(function(d){
      var pct=(dim[d]&&dim[d].pct)||50,side=pct>=50?'haut':'bas';
      var rec=recos[d][side],col=DIM_COLORS[d];
      var rowH=20;

      // Carte piste
      fc(doc,FOND); doc.roundedRect(ML,y,CW,rowH,2,2,'F');
      fc(doc,col); doc.roundedRect(ML,y,3.5,rowH,1.5,1.5,'F');

      // Badge dimension rond — label sur 2 lignes si trop long
      fc(doc,col); doc.circle(ML+14,y+rowH/2,7,'F');
      doc.setFontSize(5.5); tc(doc,BLANC); doc.setFont('helvetica','bold');
      // Initiale centrée dans le cercle (jsPDF: baseline est en bas du texte, +3.5 ≈ centrage visuel)
      doc.setFontSize(9); tc(doc,BLANC); doc.setFont('helvetica','bold');
      var circleY = y + rowH/2;  // centre Y du cercle
      var fontH = 9 * 0.352;     // hauteur approx de la police en mm
      doc.text(d, ML+14, circleY + fontH/2, {align:'center'});

      // Titre piste
      doc.setFontSize(9); tc(doc,NOIR); doc.setFont('helvetica','bold');
      var title=rec[0];
      while(doc.getTextWidth(title)>CW-52&&title.length>8) title=title.substring(0,title.length-4)+'...';
      doc.text(title, ML+25, y+7);

      // Score à droite
      doc.setFontSize(9); tc(doc,col); doc.setFont('helvetica','bold');
      doc.text(pct+'%', W-MR-2, y+7, {align:'right'});

      // Description
      doc.setFontSize(7.5); tc(doc,GRIS_F); doc.setFont('helvetica','normal');
      var descLines=doc.splitTextToSize(rec[1], CW-32);
      doc.text(descLines[0]||'', ML+25, y+13.5);
      if(descLines[1]) doc.text(descLines[1], ML+25, y+18.5);

      y+=rowH+3;
    });

    // CTA final sobre
    y+=6;
    var ctaH=42;
    fc(doc,mix(c1,BLANC,0.94)); doc.roundedRect(ML,y,CW,ctaH,3,3,'F');
    fc(doc,c1); doc.roundedRect(ML,y,4,ctaH,2,2,'F');
    dc(doc,mix(c1,BLANC,0.7)); doc.setLineWidth(0.4);
    doc.roundedRect(ML,y,CW,ctaH,3,3,'S');

    doc.setFontSize(12); tc(doc,c1); doc.setFont('helvetica','bold');
    doc.text('Prochaine étape', ML+10, y+11);

    doc.setFontSize(8.5); tc(doc,GRIS_F); doc.setFont('helvetica','italic');
    doc.text('Ce rapport est un point de départ, pas une étiquette.', ML+10, y+19);

    // Bouton RDV simulé
    if(rdv_url){
      fc(doc,c1); doc.roundedRect(ML+10,y+23,60,10,5,5,'F');
      doc.setFontSize(8); tc(doc,BLANC); doc.setFont('helvetica','bold');
      doc.text('Prendre rendez-vous', ML+40, y+29.5, {align:'center'});
      doc.link(ML+10,y+23,60,10,{url:rdv_url});
    }

    if(profurl){
      doc.setFontSize(7); tc(doc,GRIS_M); doc.setFont('helvetica','normal');
      doc.text('Profil en ligne : '+profurl, ML+10, y+38);
      doc.link(ML+10,y+34,doc.getTextWidth('Profil en ligne : '+profurl),5,{url:profurl});
    }

    // Pieds de page
    var total=doc.internal.getNumberOfPages();
    for(var p=1;p<=total;p++){
      doc.setPage(p);
      if(p>1) drawFooter(doc,W,H,p,total);
    }
    return doc;
  }

  window.ppGeneratePDF=function(profileData){
    ppToast('⏳ Génération du rapport PDF…');
    loadJsPDF(function(JsPDF){
      try{
        var doc=buildPDF(JsPDF,profileData);
        var prenom=profileData.prenom||'profil';
        var date=new Date().toISOString().slice(0,10);
        doc.save('rapport-'+prenom.toLowerCase().replace(/[^a-z0-9]/g,'')+'-'+date+'.pdf');
        ppToast('✅ Rapport PDF téléchargé !');
      }catch(e){console.error('[PP PDF]',e); ppToast('❌ Erreur lors de la génération.');}
    });
  };
})();
