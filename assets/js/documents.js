/**
 * @fileoverview Documents/Invoices Management Module
 * Handles invoice listing, creation, and management functionality
 * @author System
 * @version 1.0.0
 */

/**
 * Global array storing all available products and services
 * @type {Array<Object>}
 */
let allProducts = [];

/**
 * Initialize the documents module on page load
 * Loads invoices, clients, and products data
 */
document.addEventListener('DOMContentLoaded', () => {
    loadDocuments();
    loadClientsSelect();
    loadProducts();

    // Attach form submit handler
    const form = document.getElementById('createInvoiceForm');
    if (form) {
        form.addEventListener('submit', handleInvoiceSubmit);
    }
});

/**
 * Fetch and display the list of invoices from the API
 * @async
 * @returns {Promise<void>}
 */
async function loadDocuments() {
    const tbody = document.querySelector('#documentsTable tbody');
    if (!tbody) return; // Guard clause if table doesn't exist
    try {
        const response = await fetch('assets/api/document_api.php');
        const result = await response.json();

        tbody.innerHTML = '';

        if (result.status === 'success' && result.data.length > 0) {
            result.data.forEach(doc => {
                let statusClass = 'en_attente';
                if (doc.status === 'PAYE') statusClass = 'payee';
                if (doc.status === 'IMPAYE') statusClass = 'annulee';

                const dateDisplay = new Date(doc.date_creation).toLocaleDateString('fr-FR');
                const montantDisplay = parseFloat(doc.montant_total).toLocaleString('fr-FR');

                tbody.innerHTML += `
                    <tr>
                        <td>${dateDisplay}</td>
                        <td><strong>${doc.numero_d}</strong></td>
                        <td>${doc.nom} ${doc.prenom || ''}</td>
                        <td>${montantDisplay} FCFA</td>
                        <td><span class="status-badge ${statusClass}">${doc.status}</span></td>
                        <td>
                            <button class="btn-small view" onclick="alert('Voir détail ID: ${doc.id_document}')">
                                <i class="fa fa-eye"></i>
                            </button>
                            ${doc.status !== 'PAYE' ? `<button class="btn-small print" onclick="markAsPaid(${doc.id_document})" title="Marquer comme payé"><i class="fa fa-check"></i></button>` : ''}
                        </td>
                    </tr>`;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center">Aucune facture trouvée.</td></tr>';
        }
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" style="color:red">Erreur chargement.</td></tr>';
    }
}

/**
 * Load clients into the select dropdown for invoice creation
 * @async
 * @returns {Promise<void>}
 */
async function loadClientsSelect() {
    try {
        const select = document.getElementById('selectClient');
        if (!select) return; // Guard clause

        const response = await fetch('assets/api/client_api.php');
        const result = await response.json();

        if (result.status === 'success') {
            result.data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = `${c.nom} ${c.prenom || ''}`;
                select.appendChild(opt);
            });
        }
    } catch (e) {
        console.error('Error loading clients:', e);
    }
}

/**
 * Load products and services from API for use in invoice line items
 * @async
 * @returns {Promise<void>}
 */
async function loadProducts() {
    try {
        const response = await fetch('assets/api/service_api.php');
        const result = await response.json();
        if (result.status === 'success') {
            allProducts = result.data;
        }
    } catch (e) {
        console.error('Error loading products:', e);
    }
}

/**
 * Toggle between invoice list view and creation form
 * Shows/hides the appropriate sections
 * @returns {void}
 */
function toggleInvoiceForm() {
    const list = document.getElementById('invoiceList');
    const form = document.getElementById('invoiceFormContainer');
    const btn = document.getElementById('btnNewInvoice');

    if (form.style.display === 'none') {
        form.style.display = 'block';
        list.style.display = 'none';
        btn.style.display = 'none';
        if (document.querySelector('#itemsTable tbody').children.length === 0) addItemRow();
    } else {
        form.style.display = 'none';
        list.style.display = 'block';
        btn.style.display = 'block';
    }
}

/**
 * Add a new line item row to the invoice items table
 * Generates dropdown options grouped by Services and Products
 * @returns {void}
 */
function addItemRow() {
    const tbody = document.querySelector('#itemsTable tbody');
    const tr = document.createElement('tr');

    // Group products by type (Services vs Products)
    let services = [];
    let produits = [];

    allProducts.forEach(p => {
        if (p.est_service == 1) services.push(p);
        else produits.push(p);
    });

    let options = '<option value="">Choisir...</option>';

    if (services.length > 0) {
        options += '<optgroup label="Services">';
        services.forEach(p => {
            options += `<option value="${p.id}" data-price="${p.prix_de_vente}">${p.libelle}</option>`;
        });
        options += '</optgroup>';
    }

    if (produits.length > 0) {
        options += '<optgroup label="Produits">';
        produits.forEach(p => {
            options += `<option value="${p.id}" data-price="${p.prix_de_vente}">${p.libelle}</option>`;
        });
        options += '</optgroup>';
    }

    tr.innerHTML = `
        <td>
            <div class="group" style="margin:0"><select class="item-select" onchange="updateRow(this)">${options}</select></div>
        </td>
        <td>
            <div class="group" style="margin:0"><input type="number" class="item-qty" value="1" min="1" onchange="updateRow(this)" onkeyup="updateRow(this)"></div>
        </td>
        <td>
            <div class="group" style="margin:0"><input type="number" class="item-price" value="0" onchange="updateRow(this)" onkeyup="updateRow(this)"></div>
        </td>
        <td class="item-total" style="vertical-align:middle; font-weight:bold;">0</td>
        <td>
            <button type="button" class="btn-sm delete-row-btn" onclick="this.closest('tr').remove(); calculateTotal();">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

/**
 * Update a line item's total when quantity or price changes
 * Auto-fills price when product is selected
 * @param {HTMLElement} input - The input element that triggered the update
 * @returns {void}
 */
function updateRow(input) {
    const tr = input.closest('tr');
    const select = tr.querySelector('.item-select');
    const priceInput = tr.querySelector('.item-price');
    const qtyInput = tr.querySelector('.item-qty');
    const totalCell = tr.querySelector('.item-total');

    // If product changed, update price from data attribute
    if (input === select) {
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption.getAttribute('data-price') || 0;
        priceInput.value = price;
    }

    const price = parseFloat(priceInput.value) || 0;
    const qty = parseInt(qtyInput.value) || 0;
    const total = price * qty;

    totalCell.textContent = total.toLocaleString('fr-FR');
    calculateTotal();
}

/**
 * Calculate and display the total amount for all invoice items
 * @returns {void}
 */
function calculateTotal() {
    let total = 0;
    document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
        const price = parseFloat(tr.querySelector('.item-price').value) || 0;
        const qty = parseInt(tr.querySelector('.item-qty').value) || 0;
        total += (price * qty);
    });
    document.getElementById('displayTotal').textContent = total.toLocaleString('fr-FR');
}

/**
 * Handle invoice form submission
 * Validates data and sends to API
 * @async
 * @param {Event} e - Form submit event
 * @returns {Promise<void>}
 */
async function handleInvoiceSubmit(e) {
    e.preventDefault();

    const clientId = document.getElementById('selectClient').value;
    if (!clientId) {
        alert('Veuillez sélectionner un client');
        return;
    }

    const items = [];
    document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
        const id = tr.querySelector('.item-select').value;
        const qty = tr.querySelector('.item-qty').value;
        const price = tr.querySelector('.item-price').value;

        if (id) {
            items.push({ id: id, qty: qty, price: price });
        }
    });

    if (items.length === 0) {
        alert('Veuillez ajouter au moins un article');
        return;
    }

    const btn = document.getElementById('btnSaveInvoice');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enregistrement...';
    btn.disabled = true;

    try {
        const response = await fetch('assets/api/document_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ client_id: clientId, items: items })
        });
        const result = await response.json();

        if (result.status === 'success') {
            if (window.showNotification) {
                showNotification('Facture créée avec succès', 'success');
            } else {
                alert('Facture créée !');
            }

            e.target.reset();
            document.querySelector('#itemsTable tbody').innerHTML = '';
            toggleInvoiceForm();
            loadDocuments();
        } else {
            alert('Erreur: ' + result.message);
        }
    } catch (err) {
        console.error('Error saving invoice:', err);
        alert('Erreur technique');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

/**
 * Mark an invoice as PAID
 * @param {number} id - The document ID
 */
async function markAsPaid(id) {
    if (!confirm('Voulez-vous marquer cette facture comme PAYÉE ?')) return;

    try {
        const response = await fetch('assets/api/document_api.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, status: 'PAYE' })
        });
        const result = await response.json();

        if (result.status === 'success') {
            // alert('Facture mise à jour !');
            loadDocuments(); // Reload table
        } else {
            alert('Erreur: ' + result.message);
        }
    } catch (e) {
        console.error('Error updating status:', e);
        alert('Erreur technique');
    }
}
