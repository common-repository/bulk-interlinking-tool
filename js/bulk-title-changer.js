console.log("Hello title changer");
jQuery(document).ready(function ($) {

    let editRow = null; // Track the currently edited row
    let table = null; // Reference to the table
    let editButton = null; // Edit button for the edited row
    const nonce = bulkTitleChangerToolData.nonce;

    //  Function for uploading a file and displaying its contents
    let NextBIT_uploadFileBtn = $('.upload-file-btn');
    NextBIT_uploadFileBtn.on('click', function (p) {
        p.preventDefault();
        let fileInput = $('#user-data-file')[0];
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const reader = new FileReader();
            reader.onload = function (event) {
                const fileContents = event.target.result;
                const lines = fileContents.split('\n');
                table = NextBIT_renderTableFromContents(lines);
                const displayTableDiv = document.getElementById('display-csv-table');
                displayTableDiv.innerHTML = '';
                const SubmitButton = document.createElement('button');
                SubmitButton.textContent = 'Save Table';
                SubmitButton.className = 'save-table-to-post';
                SubmitButton.addEventListener('click', NextBIT_processTitlesForSaving);
                displayTableDiv.appendChild(SubmitButton);
                displayTableDiv.appendChild(table);
                // Add event listeners for "Edit" buttons (pencil icon)
                $('.edit-row-btn').click(NextBIT_handleEditedRow);
                $('.delete-row-btn').click(NextBIT_removeRowHandler);
                NextBit_AddPaginationToTable();
            };
            reader.readAsText(file);
        } else {
            alert('Please select a valid CSV file.');
        }
    });

    // Event handler for the "Edit" button (pencil icon)
    function NextBIT_handleEditedRow(event) {
        event.preventDefault();
        const row = $(this).closest('tr');
        if (editRow !== row) {
            // If not editing the current row, save changes of the previous row
            NextBIT_applyRowChanges();
        }
        editRow = row;
        const cells = row.find('td');
        cells.each(function () {
            const cell = $(this);
            const value = cell.text();
            const input = $('<input type="text">').val(value);
            cell.html(input);
        });
        // Replace the edit button (pencil icon) with a "Save" button
        editButton = row.find('.edit-row-btn');
        editButton.replaceWith('<button class="save-row-btn">Save</button>');
        editButton = null;
        // Add event listener for the initial "Save" button
        row.find('.save-row-btn').click(NextBIT_handleSavedRow);
    }

    // Event handler for the "Save" button
    function NextBIT_handleSavedRow(event) {
        event.preventDefault();
        NextBIT_applyRowChanges();
    }

    // Function to save changes of the currently edited row
    function NextBIT_applyRowChanges() {
        if (editRow !== null) {
            const cells = editRow.find('td');
            cells.each(function () {
                const cell = $(this);
                const input = cell.find('input');
                cell.text(input.val());
            })
            // Replace the "Save" button with the edit button (pencil icon)
            editRow.find('.save-row-btn').replaceWith('<button class="edit-row-btn">✏️</button>');
            // Add event listener for the initial "edit" button
            editRow.find('.edit-row-btn').click(NextBIT_handleEditedRow);
            // Enable other "Edit" buttons
            $('.edit-row-btn').prop('disabled', false);
            editButton = null;
            editRow = null;
        }
    }
    // Function to remove selected row
    function NextBIT_removeRowHandler() {
        const row = $(this).closest('tr');
        row.remove();
    }


    function NextBIT_processTitlesForSaving() {
        // Getting the table data
        const data = NextBIT_gettingTableData();
        // Sorting data
        for (const url in data) {
            data[url].sort((a, b) => {
                const dateA = new Date(a.d);
                const dateB = new Date(b.d);
                return dateB - dateA;
            });
        }
        // dividing data into parts
        const numParts = Math.ceil(JSON.stringify(data).length / 20000);
        const finalData = NextBIT_segmentObjectByLength(data, numParts)
        console.log(finalData);
        NextBIT_storeKeywordIntoFile(finalData)
    }

    function NextBIT_segmentObjectByLength(obj, numParts) {
        const keys = Object.keys(obj);
        const totalLength = keys.reduce((sum, key) => sum + JSON.stringify(obj[key]).length, 0);
        const targetLength = Math.ceil(totalLength / numParts);

        let currentLength = 0, partIndex = 0;
        const parts = Array.from({ length: numParts }, () => ({}));

        for (const key of keys) {
            const valueLength = JSON.stringify(obj[key]).length;
            if (currentLength + valueLength > targetLength && partIndex < numParts - 1) {
                partIndex++;
                currentLength = 0;
            }
            parts[partIndex][key] = obj[key];
            currentLength += valueLength;
        }

        return parts;
    }

    function NextBIT_gettingTableData() {
        $('#display-csv-table').html('<div style="text-align:center;padding-bottom:25px;"><button class="spin circle">Loading</button></div>');
        let tableData = $('#display-csv-table table')[0];

        const rows = table.querySelectorAll('tr');
        const data = [];

        // Extracting column headers from the first row
        const columns = rows[0].querySelectorAll('td, th');
        const headers = [];
        columns.forEach((column) => {
            column.textContent.trim() !== "" ? headers.push(column.textContent.trim()) : '';
        });

        // Iterating over rows starting from index 1 to skip the header row
        for (let rowIndex = 1; rowIndex < rows.length; rowIndex++) {
            const row = rows[rowIndex];
            const columns = row.querySelectorAll('td');
            const rowData = {};

            if (columns[0].textContent == '') {
                continue;
            }

            columns.forEach((column, columnIndex) => {
                rowData[headers[columnIndex]] = column.textContent.trim();
            });

            data.push(rowData);
        }
        const dataObj = {};
        if (data[0]["Url"] == undefined) {
            // Clearing the display csv container
            $('#display-csv-table').html("");
            // Clearing the pagination container
            const numbers = $('#page-numbersP');
            numbers.html('');
            window.alert("No Url Column is found!")
            throw new Error("No Url Column is found!")
        }
        data.forEach((row) => {
            const url1 = row["Url"]
            const verifyURL1 = isValidUrl(url1);
            if (verifyURL1) {
                if (!dataObj[url1]) {
                    dataObj[url1] = [];
                }

                dataObj[url1].push({
                    "d": row["Date"],
                    "t": row["Title"],
                });
            }
        });

        console.log(dataObj);
        if (Object.keys(dataObj).length < 1) {
            $('#display-csv-table').html("");
        }
        return dataObj;
    }

    // Define the process of request for saving keywords into file.
    async function NextBIT_storeKeywordIntoFile(finalData) {
        let responseString = '';
        for (const ObjectData of finalData) {
            try {
                const isEmpty = Object.keys(ObjectData).length === 0;
                if (isEmpty) {
                    responseString += "Error: The CSV file you uploaded is empty, so there's nothing to save!";
                    continue;
                }
                // Clearing the pagination container
                const numbers = $('#page-numbersP');
                numbers.html('');
                // Wrap the AJAX request in a Promise
                const response = await new Promise((resolve, reject) => {
                    // Make an AJAX request to the PHP function.
                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'Bulk_Interlinking_Tool_Saving_data_into_File',
                            data: ObjectData,
                            security: nonce,
                            type: 'Title',
                        },
                        success: resolve,
                        error: reject,
                    });
                });

                // Adding response to the page
                console.log("Response: ", response);
                responseString += response + "";

            } catch (error) {
                // Handle errors
                console.error('Error:', error);
            }
        }
        const outPutDiv = document.getElementById('display-csv-table');
        if (responseString.includes("Error")) {
            const messageNew = "Error" + responseString.split('Error')[1].split('!')[0] + "!";
            outPutDiv.innerHTML = '<p class="red result-paragraph">' + messageNew + '</p>';
        } else {
            outPutDiv.innerHTML = '<p class="green result-paragraph"> Data saved to JSON file successfully! </p>';
        }
    }

    // Function to Render Stored Data as a Table
    $('#displayDataButton').on('click', function () {
        $('#page-numbersP').html("");
        $('#display-csv-table').html('<div style="text-align:center;padding-bottom:25px;"><button class="spin circle">Loading</button></div>');
        $.post(ajaxurl, {
            action: 'Bulk_Interlinking_Tool_Display_Data',
            security: nonce,
            type: "Title",
        }, function (response) {
            if (response.success) {
                const data = response?.data || {};
                let index = 1;
                if (Object.keys(data).length > 0) {
                    // Display the data as a table on the page
                    let tableContainer = '<table class="record-table"><thead><tr><th>Index</th><th>Url</th><th>Title</th><th>Date</th></tr></thead><tbody>';
                    for (const key in data) {
                        if (data.hasOwnProperty(key)) {
                            const section = data[key];
                            for (const item of section) {
                                tableContainer += `<tr><td>${index}</td><td>${key}</td><td>${item.t}</td><td>${item.d}</td></tr>`;
                                index++;
                            }
                        }
                    }
                    tableContainer += '</tbody></table>';
                    const downloadCSVBtn = $('<button class="save-table-to-post">Download CSV</button>');
                    downloadCSVBtn.on('click', NextBIT_downloadASCSVFile);
                    const clearButton = $('<button>')
                        .text('Remove Saved File')
                        .addClass('delete-file-button')
                        .on('click', NextBIT_deleteTitleChangerFile);
                    // Add the table container and submit button to the 'display-csv-table' div
                    $('#display-csv-table').html(downloadCSVBtn).append(tableContainer).append(clearButton);

                    // Split data into pages (each page contains up to 50 rows)
                    NextBit_AddPaginationToTable();
                } else {
                    $('#display-csv-table').html('<p class="red">No content to display. The file is empty.</p>');
                }
            } else {
                $('#display-csv-table').html('<p class="red">No content to display. Nothing has been saved.</p>');
                console.error('Error:', response.data);
            }
        });
    });

    // Function to Download Table as CSV
    function NextBIT_downloadASCSVFile() {
        const fileName = 'Bulk-interlinking-tool_title_data.csv';
        NextBIT_generateAndDownloadCSVFile(fileName);
    }

    async function NextBIT_deleteTitleChangerFile() {
        if (confirm('Are you certain you want to delete the stored Title changer file and its data?')) {
            $.ajax({
                url: ajaxurl, // This variable is available in WordPress admin
                type: 'POST',
                data: {
                    action: 'Bulk_Interlinking_Tool_Delete_saved_file',
                    security: nonce,
                    type: 'title-changer',
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.data.message || response.data.error || 'File Deleted Successfully.');
                    } else {
                        alert('Error on clearing history : ' + response.data);
                    }
                },
                error: function () {
                    alert('AJAX request failed.');
                }
            });
        }
    }
})