// Function for generating a table
function NextBIT_renderTableFromContents(data) {
    const table = document.createElement('table');
    const thead = document.createElement('thead');
    const tbody = document.createElement('tbody');
    // Create table header
    const headerRow = document.createElement('tr');
    const headers = data[0].split(',');

    const editHeader = document.createElement('th');
    editHeader.textContent = '';
    editHeader.style.background = "white";
    headerRow.appendChild(editHeader);
    headers.forEach(headerText => {
        const header = document.createElement('th');
        header.textContent = headerText;
        headerRow.appendChild(header);
    });
    const deleteHeader = document.createElement('th');
    deleteHeader.textContent = '';
    deleteHeader.style.background = "white";
    headerRow.appendChild(deleteHeader);
    thead.appendChild(headerRow);

    // Create table rows
    for (let i = 1; i < data.length; i++) {
        const row = document.createElement('tr');
        const values = data[i].split(',');

        values.forEach((valueText, index) => {
            const cell = document.createElement('td');
            cell.textContent = valueText;
            row.appendChild(cell);
        });

        // Create an "Edit" button (pencil icon) for each row
        const editButton = document.createElement('button');
        editButton.textContent = '✏️'; // Pencil icon
        editButton.className = 'edit-row-btn';
        row.prepend(editButton); // Add the edit button at the beginning of the row

        // Create a "Delete" button (represented by 'x') for each row
        const deleteButton = document.createElement('button');
        deleteButton.textContent = '❌'; // 'x' symbol
        deleteButton.className = 'delete-row-btn';
        row.appendChild(deleteButton); // Add the delete button at the end of the row

        tbody.appendChild(row);
    }
    table.appendChild(thead);
    table.appendChild(tbody);
    return table;
}
function isValidUrl(url) {
    // Regular expression for URL validation
    let urlPattern = /^(?:https?:\/\/)?(?:www\.)?([a-zA-Z0-9-]+)(\.[a-zA-Z]{2,})+(?:\/[\w-]+)*(?:\/?|\/\S+)$/i;
    return urlPattern.test(url);
    // return true;
}
function NextBIT_generateAndDownloadCSVFile(fileName) {
    const table = document.querySelector('.record-table');
    const headers = Array.from(table.querySelectorAll('th')).map(th => th.textContent);
    const rows = table.querySelectorAll('tbody tr');
    let csvContent = '';

    // Include headers in CSV content
    csvContent += headers.join(',') + '\n';
    // Include rows in CSV content
    rows.forEach(row => {
        const rowData = [];
        row.querySelectorAll('td').forEach(cell => {
            rowData.push(cell.textContent);
        });
        csvContent += rowData.join(',') + '\n';
    });
    // Create a Blob containing the CSV content
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    // Create a link element to trigger the download
    const link = document.createElement('a');
    link.setAttribute('href', URL.createObjectURL(blob));
    link.setAttribute('download', fileName);
    link.style.display = 'none';
    // Append the link to the document body and trigger the download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function NextBit_AddPaginationToTable(rowsPerPage = 50) {
    const rows = document.querySelectorAll('#display-csv-table table tbody tr');
    const rowsCount = rows.length;

    if (rowsCount > 500) {
        rowsPerPage = 100;
    }

    const numbers = document.getElementById('page-numbersP');
    numbers.innerHTML = '';

    if (rowsCount < 50) {
        return;
    }

    const pageCount = Math.ceil(rowsCount / rowsPerPage); // avoid decimals

    // Generate the pagination.
    for (let i = 0; i < pageCount; i++) {
        const listItem = document.createElement('li');
        const link = document.createElement('a');
        link.href = '#';
        link.textContent = i + 1;
        listItem.appendChild(link);
        numbers.appendChild(listItem);
    }

    // Mark the first page link as active.
    numbers.querySelector('li:first-child a').classList.add('active');

    // Display the first set of rows.
    displayRows(1);

    // On pagination click.
    numbers.addEventListener('click', function (e) {
        if (e.target.tagName === 'A') {
            e.preventDefault();

            // Remove the active class from the links.
            const activeLinks = numbers.querySelectorAll('li a');
            activeLinks.forEach(link => link.classList.remove('active'));

            // Add the active class to the current link.
            e.target.classList.add('active');

            // Show the rows corresponding to the clicked page ID.
            displayRows(e.target.textContent);
        }
    });

    // Function that displays rows for a specific page.
    function displayRows(index) {
        const start = (index - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        // Hide all rows.
        rows.forEach(row => row.style.display = 'none');

        // Show the proper rows for this page.
        for (let i = start; i < end && i < rowsCount; i++) {
            rows[i].style.display = '';
        }
    }
}

function NextBIT_downloadSampleCSVFile() {
    const fileName = 'bulk-interlinking-tool_sample_data.csv'
    NextBIT_generateAndDownloadCSVFile(fileName)
}