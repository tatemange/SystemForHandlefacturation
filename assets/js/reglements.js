// assets/js/reglements.js

document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si on est sur la page règlements
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('page') === 'reglements') {
        initCaisseModule();
    }
});

let currentCaisseId = null;
let allReglements = [];

function initCaisseModule() {
    chargerListeCaisses();
    chargerClients();

    // Ecouteur formulaire
    const form = document.getElementById('form-reglement');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            creerReglement();
        });
    }
}

// --- LOGIQUE CHARGEMENT DONNEES ---

function chargerListeCaisses() {
    fetch('assets/php/controllers/CaisseController.php?action=list_caisses')
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const select = document.getElementById('select-caisse-actif');
                select.innerHTML = '';

                if (res.data.length === 0) {
                    select.innerHTML = '<option value="">Aucune caisse</option>';
                    return;
                }

                res.data.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id_caisse;
                    opt.innerText = c.intitule_caisse + (c.responsable ? ` (${c.responsable})` : '');
                    select.appendChild(opt);
                });

                // Sélectionner le premier ou celui en mémoire
                if (res.data.length > 0) {
                    currentCaisseId = res.data[0].id_caisse;
                    actualiserCaisse();
                }

                select.addEventListener('change', (e) => {
                    currentCaisseId = e.target.value;
                    actualiserCaisse();
                });
            }
        });
}

function actualiserCaisse() {
    if (!currentCaisseId) return;

    fetch(`assets/php/controllers/CaisseController.php?action=details_caisse&id=${currentCaisseId}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                allReglements = res.reglements;
                afficherReglements(allReglements);
                mettreAJourStats(res);
            } else {
                alert("Erreur chargement caisse: " + res.message);
            }
        })
        .catch(err => console.error(err));
}

function chargerClients() {
    const select = document.getElementById('reg-client');
    fetch('assets/php/controllers/CaisseController.php?action=get_clients')
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                select.innerHTML = '<option value="">Sélectionner un client</option>';
                res.data.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.innerText = `${c.nom} ${c.prenom || ''}`;
                    select.appendChild(opt);
                });
            }
        });
}

function chargerFacturesClient(idClient) {
    const select = document.getElementById('reg-document');
    select.innerHTML = '<option value="">Aucune (Acompte / Solde global)</option>';

    if (!idClient) return;

    fetch(`assets/php/controllers/CaisseController.php?action=get_client_invoices&id_client=${idClient}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                res.data.forEach(doc => {
                    const opt = document.createElement('option');
                    opt.value = doc.id_document;
                    // Format: N° DOC - Montant Total (Date)
                    const mt = new Intl.NumberFormat('fr-FR').format(doc.montant_total);
                    opt.innerText = `${doc.numero_d} - ${mt} FCFA (${doc.status})`;
                    select.appendChild(opt);
                });
            }
        });
}

// --- RENDU UI ---

function afficherReglements(liste) {
    const tbody = document.getElementById('tbody-reglements');
    tbody.innerHTML = '';

    if (liste.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Aucun règlement.</td></tr>';
        return;
    }

    liste.forEach(r => {
        const tr = document.createElement('tr');

        // Badge statut
        let badgeClass = 'en_attente';
        if (r.statut_validation === 'VALIDE') badgeClass = 'payee';
        if (r.statut_validation === 'ANNULE') badgeClass = 'annulee';

        const montantFmt = new Intl.NumberFormat('fr-FR').format(r.montant);
        const dateFmt = new Date(r.date_reglement).toLocaleDateString() + ' ' + new Date(r.date_reglement).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        let actions = '';
        if (r.statut_validation === 'EN_ATTENTE') {
            actions = `
                <button class="btn-small view" onclick="validerReglement(${r.id_reglement})" title="Valider">
                    <i class="fa fa-check"></i>
                </button>
                <button class="btn-small active" onclick="annulerReglement(${r.id_reglement})" title="Annuler" style="background:var(--danger-color); color:white;">
                    <i class="fa fa-times"></i>
                </button>
            `;
        } else {
            actions = `<span style="color:#aaa;"><i class="fa fa-lock"></i></span>`;
        }

        tr.innerHTML = `
            <td>${dateFmt}</td>
            <td>${r.nom} ${r.prenom || ''}</td>
            <td><strong>${montantFmt}</strong></td>
            <td>${r.mode_paiement}</td>
            <td><span class="status-badge ${badgeClass}">${r.statut_validation}</span></td>
            <td>${actions}</td>
        `;
        tbody.appendChild(tr);
    });
}

function mettreAJourStats(data) {
    const total = data.total_encaisse;
    document.getElementById('lbl-total-caisse').innerText = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA';

    const valides = data.reglements.filter(r => r.statut_validation === 'VALIDE').length;
    const attente = data.reglements.filter(r => r.statut_validation === 'EN_ATTENTE').length;

    document.getElementById('lbl-nb-valides').innerText = valides;
    document.getElementById('lbl-nb-attente').innerText = attente;
}

function filtrerReglements(filterType, btnElement) {
    // Gestion de l'affichage actif
    if (btnElement) {
        // Retirer 'active' de tous les boutons du groupe
        const group = btnElement.parentElement;
        const buttons = group.querySelectorAll('button');
        buttons.forEach(b => b.classList.remove('active'));

        // Ajouter 'active' sur le cliqué
        btnElement.classList.add('active');
    }

    if (filterType === 'tous') {
        afficherReglements(allReglements);
    } else {
        const filtered = allReglements.filter(r => r.statut_validation === filterType);
        afficherReglements(filtered);
    }
}

// --- ACTIONS CRUD ---

function creerReglement() {
    const data = {
        id_caisse: currentCaisseId,
        id_client: document.getElementById('reg-client').value,
        montant: document.getElementById('reg-montant').value,
        mode_paiement: document.getElementById('reg-mode').value,
        reference: document.getElementById('reg-ref').value,
        id_document: document.getElementById('reg-document').value
    };

    if (!data.id_caisse) {
        alert("Erreur: Aucune caisse sélectionnée active. Veuillez sélectionner ou créer une caisse.");
        return;
    }
    if (!data.id_client) {
        alert("Veuillez sélectionner un client.");
        return;
    }
    if (!data.montant || parseFloat(data.montant) <= 0) {
        alert("Veuillez saisir un montant valide.");
        return;
    }

    fetch('assets/php/controllers/CaisseController.php?action=creer_reglement', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert("Règlement enregistré !");
                document.getElementById('form-reglement').reset();
                actualiserCaisse();
            } else {
                alert("Erreur : " + res.message);
            }
        });
}

function validerReglement(idReglement) {
    if (!confirm("Confirmer la validation de ce règlement ? Cette action mettra à jour le solde du client et est irréversible (sauf annulation).")) return;

    fetch('assets/php/controllers/CaisseController.php?action=valider_reglement', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_reglement: idReglement,
            id_caisse: currentCaisseId
        })
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                actualiserCaisse();
            } else {
                alert("Erreur : " + res.message);
            }
        });
}

function annulerReglement(idReglement) {
    if (!confirm("Annuler ce règlement ?")) return;

    fetch('assets/php/controllers/CaisseController.php?action=annuler_reglement', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_reglement: idReglement,
            id_caisse: currentCaisseId
        })
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                actualiserCaisse();
            } else {
                alert("Erreur : " + res.message);
            }
        });
}
