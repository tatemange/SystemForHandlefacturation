/**
 * @fileoverview Documents/Invoices Management Module
 * Handles invoice listing, creation, and management functionality
 * @author System
 * @version 1.1.0
 */

/**
 * Global array storing all available products and services
 * @type {Array<Object>}
 */
let allProducts = [];
let editingInvoiceId = null; // Track if we are editing an existing invoice

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
                            <button class="btn-small view" onclick="viewInvoice(${doc.id_document})" title="Voir / Modifier">
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
            select.innerHTML = '<option value="">Sélectionner un client...</option>'; // Reset
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
 * @param {boolean} [reset=true] - Whether to reset the form (default true)
 * @returns {void}
 */
function toggleInvoiceForm(reset = true) {
    const list = document.getElementById('invoiceList');
    const formContainer = document.getElementById('invoiceFormContainer');
    const btn = document.getElementById('btnNewInvoice');
    const formTitle = document.querySelector('#createInvoiceForm legend');

    if (formContainer.style.display === 'none') {
        // OPEN FORM
        formContainer.style.display = 'block';
        list.style.display = 'none';
        btn.style.display = 'none';

        if (reset && !editingInvoiceId) {
            // New Invoice Mode
            resetFormState();
            formTitle.textContent = "Nouvelle Facture";
        }

    } else {
        // CLOSE FORM
        formContainer.style.display = 'none';
        list.style.display = 'block';
        btn.style.display = 'block';

        // Reset Logic
        resetFormState();
    }
}

function resetFormState() {
    const form = document.getElementById('createInvoiceForm');
    form.reset();
    document.querySelector('#itemsTable tbody').innerHTML = '';
    editingInvoiceId = null;
    document.getElementById('displayTotal').textContent = '0';

    // Re-enable everything by default
    document.getElementById('selectClient').disabled = false;
    document.getElementById('btnSaveInvoice').style.display = 'inline-block';
    if (document.querySelector('.items-add-button')) document.querySelector('.items-add-button').style.display = 'block';

    // Ensure we start with one empty row if opening new
    if (document.getElementById('invoiceFormContainer').style.display === 'block') {
        addItemRow();
    }
}

/**
 * Open the form in "View/Edit" mode
 * @param {number} id 
 */
async function viewInvoice(id) {
    try {
        const response = await fetch(`assets/api/document_api.php?id=${id}`);
        const result = await response.json();

        if (result.status === 'success') {
            const doc = result.data;
            editingInvoiceId = doc.id_document;

            // Switch to form view without resetting (reset=false)
            // But first, we need to ensure the toggle opens it.
            // We set editingInvoiceId BEFORE toggle ensures we can control behaviour if we tweaked toggle logic?
            // Actually reusing toggleInvoiceForm(false) is safer.

            // 1. Open form container
            const list = document.getElementById('invoiceList');
            const formContainer = document.getElementById('invoiceFormContainer');
            const btn = document.getElementById('btnNewInvoice');

            formContainer.style.display = 'block';
            list.style.display = 'none';
            btn.style.display = 'none';

            // 2. Clear current items
            document.querySelector('#itemsTable tbody').innerHTML = '';

            // 3. Populate Fields
            document.querySelector('#createInvoiceForm legend').textContent = `Facture #${doc.numero_d}`;
            const clientSelect = document.getElementById('selectClient');
            clientSelect.value = doc.client_id;

            // 4. Populate Items
            if (doc.details && doc.details.length > 0) {
                doc.details.forEach(item => {
                    addItemRow(item);
                });
            } else {
                addItemRow(); // Default empty row
            }

            calculateTotal();

            // 5. Handle Status (ReadOnly if PAID)
            const isPaid = doc.status === 'PAYE';
            setFormReadOnly(isPaid);

        } else {
            alert("Erreur: " + result.message);
        }
    } catch (e) {
        console.error(e);
        alert("Impossible de charger la facture.");
    }
}

function setFormReadOnly(isReadOnly) {
    const form = document.getElementById('createInvoiceForm');
    const inputs = form.querySelectorAll('input, select, button');

    inputs.forEach(input => {
        // Don't disable the cancel/close button (which triggers toggleInvoiceForm)
        if (input.innerText === 'Annuler' || input.textContent.includes('Annuler')) return;

        input.disabled = isReadOnly;
    });

    const saveBtn = document.getElementById('btnSaveInvoice');
    const addBtnCtx = document.querySelector('.items-add-button');

    if (isReadOnly) {
        saveBtn.style.display = 'none';
        if (addBtnCtx) addBtnCtx.style.display = 'none';

        // Also disable delete buttons explicitly if not caught above
        document.querySelectorAll('.delete-row-btn').forEach(b => b.disabled = true);
    } else {
        saveBtn.style.display = 'inline-block';
        if (addBtnCtx) addBtnCtx.style.display = 'block';
    }
}


/**
 * Add a new line item row to the invoice items table
 * @param {Object|null} itemData - Existing data to populate (for edit mode)
 */
function addItemRow(itemData = null) {
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

    // Helpers to check selected
    const selectedId = itemData ? (itemData.id_service_produit || itemData.id) : null;

    if (services.length > 0) {
        options += '<optgroup label="Services">';
        services.forEach(p => {
            const isSel = (p.id == selectedId) ? 'selected' : '';
            options += `<option value="${p.id}" data-price="${p.prix_de_vente}" data-service="1" ${isSel}>${p.libelle}</option>`;
        });
        options += '</optgroup>';
    }

    if (produits.length > 0) {
        options += '<optgroup label="Produits">';
        produits.forEach(p => {
            const isSel = (p.id == selectedId) ? 'selected' : '';
            options += `<option value="${p.id}" data-price="${p.prix_de_vente}" data-service="${p.est_service}" data-stock="${p.quantite_stock}" ${isSel}>${p.libelle} (Stock: ${p.quantite_stock})</option>`;
        });
        options += '</optgroup>';
    }

    const qtyVal = itemData ? itemData.quantite : 1;
    const priceVal = itemData ? itemData.prix_unitaire : 0;
    const totalVal = (qtyVal * priceVal).toLocaleString('fr-FR');

    tr.innerHTML = `
        <td>
            <div class="group" style="margin:0"><select class="item-select" onchange="updateRow(this)">${options}</select></div>
        </td>
        <td>
            <div class="group" style="margin:0"><input type="number" class="item-qty" value="${qtyVal}" min="1" onchange="updateRow(this)" onkeyup="updateRow(this)"></div>
        </td>
        <td>
            <div class="group" style="margin:0"><input type="number" class="item-price" value="${priceVal}" onchange="updateRow(this)" onkeyup="updateRow(this)"></div>
        </td>
        <td class="item-total" style="vertical-align:middle; font-weight:bold;">${totalVal}</td>
        <td>
            <button type="button" class="btn-sm delete-row-btn" onclick="this.closest('tr').remove(); calculateTotal();">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);

    // Trigger updateRow for stock validation if needed, or if it's new
    if (itemData) {
        // Ensure visual state is correct
    }
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

    const selectedOption = select.options[select.selectedIndex];
    const isService = selectedOption.getAttribute('data-service') == '1';
    const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;

    // Check Stock validity
    const qty = parseInt(qtyInput.value) || 0;
    if (!isService && qty > stock && select.value !== "") {
        qtyInput.style.borderColor = "red";
        qtyInput.title = `Stock insuffisant (Max: ${stock})`;
        // Optional: show a small error message nearby
    } else {
        qtyInput.style.borderColor = "#ddd"; // Reset to default (or css class)
        qtyInput.title = "";
    }

    const price = parseFloat(priceInput.value) || 0;
    // const qty already defined above
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
    let valid = true;
    document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
        const id = tr.querySelector('.item-select').value;
        const qty = tr.querySelector('.item-qty').value;
        const price = tr.querySelector('.item-price').value;

        if (id) {
            // Re-validate stock before submitting
            const selectedOption = tr.querySelector('.item-select').selectedOptions[0];
            const isService = selectedOption.getAttribute('data-service') == '1';
            const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
            const currentQty = parseInt(qty);

            if (!isService && currentQty > stock) {
                alert(`Stock insuffisant pour le produit "${selectedOption.text.split('(')[0].trim()}"\nDemandé: ${currentQty}, Dispo: ${stock}`);
                valid = false;
                return;
            }

            items.push({ id: id, qty: qty, price: price });
        }
    });

    if (!valid) return;

    if (items.length === 0) {
        alert('Veuillez ajouter au moins un article');
        return;
    }

    const btn = document.getElementById('btnSaveInvoice');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enregistrement...';
    btn.disabled = true;

    // Determine Method and URL
    const isEdit = !!editingInvoiceId;
    const method = isEdit ? 'PUT' : 'POST';

    const payload = {
        client_id: clientId,
        items: items
    };

    if (isEdit) {
        payload.id = editingInvoiceId;
    }

    try {
        const response = await fetch('assets/api/document_api.php', {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();

        if (result.status === 'success') {
            if (window.showNotification) {
                showNotification(isEdit ? 'Facture mise à jour' : 'Facture créée avec succès', 'success');
            } else {
                alert(isEdit ? 'Facture mise à jour !' : 'Facture créée !');
            }

            e.target.reset();
            document.querySelector('#itemsTable tbody').innerHTML = '';
            toggleInvoiceForm(); // Close form
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
