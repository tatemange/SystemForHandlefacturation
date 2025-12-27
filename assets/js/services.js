// assets/js/services.js

document.addEventListener('DOMContentLoaded', () => {
    // Check if on services page
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('page') === 'services') {
        initServicesModule();
    }
});

let allServices = [];

function initServicesModule() {
    chargerServices();

    // Form listener
    const form = document.getElementById('form-service');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            sauvegarderService();
        });
    }
}

// --- CRUD ---

function chargerServices() {
    fetch('assets/api/service_api.php')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                allServices = res.data;
                afficherServices(allServices);
            } else {
                console.error("Erreur chargement:", res.message);
            }
        })
        .catch(err => console.error("API Error", err));
}

function sauvegarderService() {
    const id = document.getElementById('service-id').value;
    const isEdit = !!id;

    const data = {
        libelle: document.getElementById('service-libelle').value,
        est_service: document.getElementById('service-type').value, // 1 ou 0
        description: document.getElementById('service-desc').value,
        prix_achat: document.getElementById('service-pa').value,
        prix_de_vente: document.getElementById('service-pv').value,
        quantite_stock: document.getElementById('service-stock').value
    };

    // Validation basique
    if (!data.libelle || !data.prix_de_vente) {
        alert("Veuillez remplir le libellé et le prix de vente.");
        return;
    }

    const method = isEdit ? 'PUT' : 'POST';
    if (isEdit) data.id = id;

    fetch('assets/api/service_api.php', {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                alert(res.message);
                resetForm();
                chargerServices();
            } else {
                alert("Erreur: " + res.message);
            }
        })
        .catch(err => console.error(err));
}

function supprimerService(id) {
    if (!confirm("Voulez-vous vraiment supprimer cet élément ?")) return;

    fetch(`assets/api/service_api.php?id=${id}`, {
        method: 'DELETE'
    })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                chargerServices(); // Refresh
            } else {
                alert("Erreur: " + res.message);
            }
        })
        .catch(err => console.error(err));
}

// --- UI ---

function afficherServices(liste) {
    const tbody = document.getElementById('tbody-services');
    tbody.innerHTML = '';

    if (liste.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Aucun élément.</td></tr>';
        return;
    }

    liste.forEach(item => {
        const tr = document.createElement('tr');

        // Badge Type
        const isService = item.est_service == 1;
        const typeBadge = isService
            ? '<span class="badge-type badge-service">Service</span>'
            : '<span class="badge-type badge-produit">Produit</span>';

        // Stock Logic
        let stockDisplay = '-';
        if (!isService) {
            const stk = parseInt(item.quantite_stock);
            let stkClass = 'stock-ok';
            if (stk <= 0) stkClass = 'stock-out';
            else if (stk < 5) stkClass = 'stock-low';
            stockDisplay = `<span class="${stkClass}">${stk}</span>`;
        }

        const prixFmt = new Intl.NumberFormat('fr-FR').format(item.prix_de_vente);

        tr.innerHTML = `
            <td>
                <strong>${item.libelle}</strong><br>
                <small style="color:var(--label-second)">${item.description || ''}</small>
            </td>
            <td>${typeBadge}</td>
            <td>${prixFmt} FCFA</td>
            <td>${stockDisplay}</td>
            <td>
                <button class="btn-small view" onclick='editerService(${JSON.stringify(item)})' title="Modifier">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="btn-small" onclick="supprimerService(${item.id})" title="Supprimer" style="color:var(--danger); border-color:var(--danger)">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function editerService(item) {
    // Remplir le formulaire
    document.getElementById('service-id').value = item.id;
    document.getElementById('service-type').value = item.est_service;
    document.getElementById('service-libelle').value = item.libelle;
    document.getElementById('service-desc').value = item.description;
    document.getElementById('service-pa').value = item.prix_achat;
    document.getElementById('service-pv').value = item.prix_de_vente;
    document.getElementById('service-stock').value = item.quantite_stock;

    // UI Updates
    document.getElementById('form-title').innerHTML = '<i class="fa fa-edit"></i> Modifier Produit';
    toggleStockField();
}

function resetForm() {
    document.getElementById('form-service').reset();
    document.getElementById('service-id').value = '';
    document.getElementById('form-title').innerHTML = '<i class="fa fa-plus-circle"></i> Nouveau Produit';
    toggleStockField();
}

function toggleStockField() {
    const type = document.getElementById('service-type').value;
    const groupStock = document.getElementById('group-stock');
    if (type == "1") { // Service
        groupStock.classList.add('hidden');
    } else {
        groupStock.classList.remove('hidden');
    }
}

function filtrerTableau() {
    const term = document.getElementById('search-service').value.toLowerCase();
    const filtered = allServices.filter(s =>
        s.libelle.toLowerCase().includes(term) ||
        (s.description && s.description.toLowerCase().includes(term))
    );
    afficherServices(filtered);
}
