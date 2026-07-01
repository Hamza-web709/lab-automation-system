(function () {
    const createTesterRow = () => {
        const row = document.createElement('div');
        row.className = 'tester-row';
        row.innerHTML = `
            <div class="field">
                <label>Person Name</label>
                <input type="text" name="person_name[]" placeholder="Tester name">
            </div>
            <div class="field">
                <label>Designation</label>
                <input type="text" name="designation[]" placeholder="Engineer / Inspector">
            </div>
            <div class="field">
                <label>Remarks</label>
                <input type="text" name="person_remarks[]" placeholder="Optional remarks">
            </div>
            <button class="icon-btn" type="button" aria-label="Remove tester" data-remove-tester>
                <i data-lucide="trash-2"></i>
            </button>
        `;
        return row;
    };

    document.addEventListener('DOMContentLoaded', () => {
        const tableSearch = document.querySelector('[data-table-search]');
        if (tableSearch) {
            const rows = document.querySelectorAll('[data-search-row]');
            tableSearch.addEventListener('input', () => {
                const query = tableSearch.value.trim().toLowerCase();
                rows.forEach((row) => {
                    row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
                });
            });
        }

        document.querySelectorAll('[data-testing-type]').forEach((select) => {
            select.addEventListener('change', () => {
                const option = select.selectedOptions[0];
                const criteria = document.querySelector('[data-criteria]');
                const expected = document.querySelector('[data-expected-output]');
                if (option && criteria && !criteria.value) criteria.value = option.dataset.criteria || '';
                if (option && expected && !expected.value) expected.value = option.dataset.expected || '';
            });
        });

        const testerList = document.querySelector('[data-tester-list]');
        const addTester = document.querySelector('[data-add-tester]');
        if (testerList && addTester) {
            addTester.addEventListener('click', () => {
                testerList.appendChild(createTesterRow());
                if (window.lucide) window.lucide.createIcons();
            });

            testerList.addEventListener('click', (event) => {
                const button = event.target.closest('[data-remove-tester]');
                if (!button) return;
                const rows = testerList.querySelectorAll('.tester-row');
                if (rows.length > 1) {
                    button.closest('.tester-row').remove();
                }
            });
        }
    });
})();
