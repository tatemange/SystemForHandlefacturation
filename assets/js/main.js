
/**
 * Generic Table Filter Function
 * filters rows based on text content
 * @param {string} inputId - ID of the search input
 * @param {string} tableId - ID of the table to filter
 */
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('keyup', function () {
        const filter = input.value.toLowerCase();
        const rows = table.getElementsByTagName('tr');

        // Start from 1 to skip header (assuming <thead> exists, searching in <tbody> usually safer but this works for simple tables)
        // Better: iterate tbody rows if possible.
        const tbody = table.querySelector('tbody');
        const trs = tbody ? tbody.getElementsByTagName('tr') : table.getElementsByTagName('tr');

        for (let i = 0; i < trs.length; i++) {
            const row = trs[i];
            // Skip loading/empty rows if they have specific classes or just check text
            if (row.getElementsByTagName('td').length === 0) continue;

            let text = row.textContent || row.innerText;
            if (text.toLowerCase().indexOf(filter) > -1) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    });
}
