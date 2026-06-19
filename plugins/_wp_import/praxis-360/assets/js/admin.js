/* Praxis 360 — Back-office (JavaScript vanilla). */
(function () {
    'use strict';
    if (typeof PRAXIS360_ADMIN === 'undefined') { return; }
    var A = PRAXIS360_ADMIN;

    // ---- Création de campagne ----
    var addBtn = document.getElementById('p360-add-eval');
    var tbody = document.querySelector('#p360-evaluators tbody');
    var createBtn = document.getElementById('p360-create');
    var msg = document.getElementById('p360-create-msg');

    var relations = [
        ['manager', 'Manager'],
        ['peer', 'Pair / collègue'],
        ['report', 'Collaborateur'],
        ['client', 'Client / partenaire']
    ];

    function addRow() {
        var tr = document.createElement('tr');
        var opts = relations.map(function (r) {
            return '<option value="' + r[0] + '">' + r[1] + '</option>';
        }).join('');
        tr.innerHTML =
            '<td><input type="text" class="regular-text ev-name" placeholder="Nom"></td>' +
            '<td><input type="email" class="regular-text ev-email" placeholder="email@exemple.com"></td>' +
            '<td><select class="ev-rel">' + opts + '</select></td>' +
            '<td><button class="button ev-del">Supprimer</button></td>';
        tbody.appendChild(tr);
        tr.querySelector('.ev-del').addEventListener('click', function () { tr.remove(); });
    }

    if (addBtn) {
        addBtn.addEventListener('click', function (e) { e.preventDefault(); addRow(); });
        addRow(); addRow(); addRow();
    }

    if (createBtn) {
        createBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var name = document.getElementById('p360-subject-name').value.trim();
            var email = document.getElementById('p360-subject-email').value.trim();
            var deadline = document.getElementById('p360-deadline').value;
            if (!name || !email) { setMsg('Renseignez le nom et l\'email du sujet.', true); return; }

            var evaluators = [];
            var rows = tbody.querySelectorAll('tr');
            for (var i = 0; i < rows.length; i++) {
                var n = rows[i].querySelector('.ev-name').value.trim();
                var em = rows[i].querySelector('.ev-email').value.trim();
                var rel = rows[i].querySelector('.ev-rel').value;
                if (n && em) { evaluators.push({ name: n, email: em, relation: rel }); }
            }

            createBtn.disabled = true;
            setMsg('Création en cours…', false);

            var body = 'action=p360_create_campaign&nonce=' + encodeURIComponent(A.nonce)
                + '&subject_name=' + encodeURIComponent(name)
                + '&subject_email=' + encodeURIComponent(email)
                + '&deadline=' + encodeURIComponent(deadline);
            for (var j = 0; j < evaluators.length; j++) {
                body += '&evaluators[' + j + '][name]=' + encodeURIComponent(evaluators[j].name);
                body += '&evaluators[' + j + '][email]=' + encodeURIComponent(evaluators[j].email);
                body += '&evaluators[' + j + '][relation]=' + encodeURIComponent(evaluators[j].relation);
            }
            ajax(body, function (ok, resp) {
                createBtn.disabled = false;
                if (ok && resp && resp.success) {
                    setMsg(resp.data.message + ' Vous pouvez gérer la campagne depuis la liste.', false);
                } else {
                    setMsg((resp && resp.data && resp.data.message) ? resp.data.message : 'Erreur lors de la création.', true);
                }
            });
        });
    }

    function setMsg(text, isErr) {
        if (!msg) { return; }
        msg.innerHTML = '<div class="notice ' + (isErr ? 'notice-error' : 'notice-success') + '"><p>' + text + '</p></div>';
    }

    // ---- Relances / clôture depuis la liste ----
    document.addEventListener('click', function (e) {
        var t = e.target;
        if (t.classList.contains('p360-remind')) {
            e.preventDefault();
            if (!confirm('Envoyer une relance à tous les évaluateurs n\'ayant pas répondu ?')) { return; }
            var cid = t.getAttribute('data-cid');
            ajax('action=p360_send_reminders&nonce=' + encodeURIComponent(A.nonce) + '&campaign_id=' + encodeURIComponent(cid), function (ok, resp) {
                alert(resp && resp.data ? resp.data.message : 'Terminé.');
            });
        }
        if (t.classList.contains('p360-close')) {
            e.preventDefault();
            if (!confirm('Clôturer la campagne et envoyer le rapport au sujet ?')) { return; }
            var cid2 = t.getAttribute('data-cid');
            ajax('action=p360_close_campaign&nonce=' + encodeURIComponent(A.nonce) + '&campaign_id=' + encodeURIComponent(cid2), function (ok, resp) {
                alert(resp && resp.data ? resp.data.message : 'Terminé.');
                location.reload();
            });
        }
    });

    function ajax(body, cb) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', A.ajaxUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                var resp = null;
                try { resp = JSON.parse(xhr.responseText); } catch (err) {}
                cb(xhr.status === 200, resp);
            }
        };
        xhr.send(body);
    }
})();
