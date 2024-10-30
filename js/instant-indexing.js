jQuery(document).ready(function ($) {
    const nonce = NextBITinstantIndexingTool.nonce;
    //  Function for uploading a file and displaying its contents
    let NextBIT_uploadServiceAccount = $('#save-service-account');
    NextBIT_uploadServiceAccount.on('click', async function (p) {
        p.preventDefault();
        let fileInput = $('#user-service-file')[0];
        let NextBIT_fileDataText = $('#input-service-text')[0].value;
        const outPutDiv = $('#display-service-account-results');
        if (fileInput.files.length > 0) {
            outPutDiv.html('<div style="text-align:center;padding-bottom:25px;"><button class="spin circle">Loading</button></div>');
            const file = fileInput.files[0];
            const reader = new FileReader();
            reader.onload = async function (event) {
                try {
                    const fileContents = event.target.result;
                    const data = JSON.parse(fileContents);
                    const validate = NextBIT_validateServiceAccount(data);
                    if (validate) {
                        const responseString = await NextBIT_saveServiceAccount(data);
                        outPutDiv.html(`<p class="${responseString.includes("Error") ? "red" : "green"} result-paragraph">${responseString}</p>`);
                    }
                } catch (err) {
                    alert(err.message)
                }
            };
            reader.readAsText(file);
        } else if (NextBIT_fileDataText.length > 10) {
            outPutDiv.html('<div style="text-align:center;padding-bottom:25px;"><button class="spin circle">Loading</button></div>');
            try {
                const data = JSON.parse(NextBIT_fileDataText.trim());
                const validate = NextBIT_validateServiceAccount(data);
                if (validate) {
                    console.log(validate)
                    const responseString = await NextBIT_saveServiceAccount(data);
                    outPutDiv.html(`<p class="${responseString.includes("Error") ? "red" : "green"} result-paragraph">${responseString}</p>`);
                }
            } catch (err) {
                alert(err.message)
            }
        } else {
            alert('Upload the Service Account JSON key file you obtained from Google API Console or paste its contents in the field!');
        }
    });

    function NextBIT_validateServiceAccount(jsonData) {
        const requiredFields = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email', 'client_id', 'auth_uri', 'token_uri',];
        const missingFields = requiredFields.filter(field => !jsonData.hasOwnProperty(field));
        if (missingFields.length > 0) {
            alert(`Missing fields: ${missingFields.join(', ')}`);
            return false;
        } else {
            console.log("Service account file is valid.");
            return true
        }
    }

    async function NextBIT_saveServiceAccount(jsonData) {
        let responseString = "";
        try {
            const isEmpty = Object.keys(jsonData).length === 0;
            if (isEmpty) {
                responseString += "Error: The JSON file you uploaded is empty, so there's nothing to save!";
            }
            // Wrap the AJAX request in a Promise
            const response = await new Promise((resolve, reject) => {
                // Make an AJAX request to the PHP function.
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'Bulk_Interlinking_Tool_Saving_service_account',
                        data: jsonData,
                        security: nonce,
                        type: 'ServiceAccount',
                    },
                    success: resolve,
                    error: reject,
                });
            });

            // Adding response to the page
            console.log("Response: ", response);
            responseString += response + "";
            return responseString;
        } catch (error) {
            // Handle errors
            console.error('Error:', error);
            return responseString;
        }
    }


    NextBIT_getSavedDataFromFile("ServiceAccount").then(data => {
        if (data.client_email) {
            $('#display-service-account-results').html('<p style="font-size: 16px;font-weight: bold;color: green;text-align: center;line-height: 2rem;"> You already upload this service account <br>' + data.client_email + '</p>')
        }
    });

    async function NextBIT_getSavedDataFromFile(type) {
        try {
            // Wrap the AJAX request in a Promise
            const response = await new Promise((resolve, reject) => {
                // Make an AJAX request to the PHP function.
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'Bulk_Interlinking_Tool_Retrieving_saved_data',
                        security: nonce,
                        type: type,
                    },
                    success: resolve,
                    error: reject,
                });
            });

            // Adding response to the page
            return response.data;
        } catch (error) {
            console.error('Error:', error.message);
            return false;
        }
    }

    $('#user-urls-file').on('change', function (event) {
        event.preventDefault();
        $('#user-urls-input').val("");
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const text = e.target.result;
                const delimiter = file.name.endsWith('.tsv') ? '\t' : ',';
                const rows = text.split('\n');
                const headers = rows[0].split(delimiter).map(h => h.trim().toLowerCase());
                const urlIndex = headers.indexOf('url') || headers.indexOf('urls') || headers.indexOf('source url');
                let urls = [];

                if (urlIndex === -1) {
                    // If no URL column found, use the first column
                    rows.slice(1).forEach(row => {
                        const firstCol = row.split(delimiter)[0].trim();
                        if (isValidUrl(firstCol)) urls.push(firstCol);
                    });
                    if (urls.length == 0) {
                        rows.slice(1).forEach(row => {
                            const secondCol = row.split(delimiter)?.[1]?.trim();
                            if (isValidUrl(secondCol)) urls.push(secondCol);
                        });
                    }
                } else {
                    // Process rows to extract URLs from the 'Url' column
                    rows.slice(1).forEach(row => {
                        const columns = row.split(delimiter);
                        if (columns.length == headers.length) {
                            const url = columns[urlIndex].trim();
                            if (isValidUrl(url)) urls.push(url);
                        }
                    });
                }
                const uniqueURLs = [...new Set(urls)];
                // Write validated URLs into the textarea
                $('#user-urls-input').val(uniqueURLs.join('\n'));
            };
            reader.readAsText(file);
        }
    });

    // Submit URL for indexing to google
    let NextBIT_uploadURLsForIndexing = $('#indexing-input-form');
    NextBIT_uploadURLsForIndexing.on('submit', async function (p) {
        p.preventDefault();
        const selectedValue = $('input[name="update-type"]:checked').val();
        if (!selectedValue) {
            alert("Please Select URL Update Type!")
        }
        let NextBIT_inputURLs = $('#user-urls-input')[0].value;
        const outPutDiv = $('#display-indexing-results');
        outPutDiv.html('<div style="text-align:center;padding-bottom:25px;"><button class="spin circle">Loading</button></div>');
        let urlsArray = NextBIT_inputURLs.split('\n');
        // Remove whitespace and empty strings
        urlsArray = urlsArray.map(item => item.trim()).filter(item => item !== '');
        urlsArray = [...new Set(urlsArray)];
        const validatedURLsArray = [];
        urlsArray.forEach(url => {
            if (isValidUrl(url)) validatedURLsArray.push(url);
        });
        const top100Urls = validatedURLsArray.slice(0, 100);
        if (top100Urls.length >= 1) {
            try {
                const result = await NextBIT_requestForIndexing(top100Urls, selectedValue);
                if (result.data) {
                    const table = NextBIT_createTableFromIndexingResponse(result.data, selectedValue);
                    const downloadCSVBtn = $('<button class="save-table-to-post">Download CSV</button>');
                    // Add the table container and Download CSV Button to the 'display-csv-table' div
                    downloadCSVBtn.on('click', NextBIT_downloadASCSVFile);
                    outPutDiv.html(downloadCSVBtn).append(table);
                } else {
                    if (result.error) {
                        outPutDiv.html("");
                        window.alert(result.error);
                    }
                }
            } catch (err) {
                alert(err.message)
            }
        } else {
            alert('Enter URLs directly into the box, or upload a .csv or .tsv file for indexing.');
        }
    });

    // Function to Download Table as CSV
    function NextBIT_downloadASCSVFile() {
        const fileName = 'Bulk-interlinking-tool_indexing_records.csv';
        NextBIT_generateAndDownloadCSVFile(fileName);
    }

    // Function to request for indexing
    async function NextBIT_requestForIndexing(urls, selectedValue, submission = "Manual") {
        try {
            const response = await new Promise((resolve, reject) => {
                // Make an AJAX request to the PHP function.
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'Bulk_Interlinking_Tool_Indexing_URLs',
                        security: nonce,
                        data: JSON.stringify(urls),
                        type: "indexing",
                        submissionType: selectedValue,
                        submission: submission
                    },
                    success: resolve,
                    error: reject,
                });
            });
            const data = response.data;
            console.log(data);
            return data;
        } catch (err) {
            console.log(err.message);
            return false;
        }
    }

    // Generate a response table for the indexing request
    function NextBIT_createTableFromIndexingResponse(data, updateType = "URL_UPDATED") {
        let selectedValue = (updateType === "URL_DELETED") ? "latestRemove" : "latestUpdate";
        const table = document.createElement('table');
        table.classList.add('record-table');
        const thead = document.createElement('thead');
        const tbody = document.createElement('tbody');
        // Create table header
        const headerRow = document.createElement('tr');
        const headers = ['Index', "URL", "Status", "Message"];
        let count = 1;
        headers.forEach(headerText => {
            const header = document.createElement('th');
            header.textContent = headerText;
            headerRow.appendChild(header);
        });
        thead.appendChild(headerRow);
        const resultRows = data?.responses || [];
        if (resultRows.length >= 1) {
            resultRows.map(item => {
                const status = (item.response.urlNotificationMetadata?.[selectedValue].type === updateType) ? 200 : "";
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${count}</td><td>${item.response.urlNotificationMetadata.url}</td><td>${status}</td><td ${status !== 200 ? 'class="red"' : ''}> ${status == 200 ? "Success" : "Failed"}</td > `
                tbody.appendChild(tr);
                count++;
            })
        }
        const errorRows = data?.errors || [];
        if (errorRows.length >= 1) {
            errorRows.map(item => {
                const error = JSON.parse(item.error);
                const tr = document.createElement('tr');
                tr.innerHTML = `<td> ${count}</td><td>${item.url}</td><td class="red">${error?.error?.code}</td><td class="red">${error?.error?.message}</td>`
                tbody.appendChild(tr);
                count++;
            })
        }
        table.appendChild(thead);
        table.appendChild(tbody);
        return table;
    }

    NextBIT_getIndexingHistoryAndCreateTable();
    $('a[href="#instant-indexing-history"]').on('click', NextBIT_getIndexingHistoryAndCreateTable);

    // Main function for generating indexing history table
    async function NextBIT_getIndexingHistoryAndCreateTable() {
        $('#page-numbersP').html("");
        $('#display-csv-table').html('<div style="text-align:center;padding-bottom:25px;"><button class="spin circle">Loading</button>').attr('style', 'display:flex;align-items: center;justify-content: center;height: 60vh;')
        const data = await NextBIT_getSavedDataFromFile('indexing-history');
        const clearButton = $('<button>')
            .text('Clear History')
            .addClass('delete-file-button')
            .on('click', NextBIT_clearIndexingHistoryFile);
        const table = NextBIT_createIndexingHistoryTable(data);
        $('#display-csv-table').html(table).append(clearButton).attr('style', '');;
        // Create delete button
        NextBit_AddPaginationToTable();
    }

    // Generate a response table for the indexing request history
    function NextBIT_createIndexingHistoryTable(data) {
        const table = document.createElement('table');
        const thead = document.createElement('thead');
        const tbody = document.createElement('tbody');
        // Create table header
        const headerRow = document.createElement('tr');
        const headers = [" ", "Time", "URL", "Type", "Success Count", "Error Count"];
        let count = 1;
        headers.forEach(headerText => {
            const header = document.createElement('th');
            header.textContent = headerText;
            headerRow.appendChild(header);
        });
        thead.appendChild(headerRow);
        if (data.length >= 1) {
            data.map(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${count}</td><td>${item.date}</td><td>${item.url} ${item.url_count > 1 ? " [+" + (item.url_count - 1) + "]" : ""}</td><td>${item.type}</td><td>${item.responses.length}</td><td>${item.errors.length}</td> `
                tbody.appendChild(tr);
                count++;
            })
        } else {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="6">No submissions yet.</td>`;
            tbody.appendChild(tr);
        } 1
        table.appendChild(thead);
        table.appendChild(tbody);
        return table;
    }

    // Use to delete indexing request history file
    async function NextBIT_clearIndexingHistoryFile() {
        if (confirm('Are you sure you want to clear this indexing history?')) {
            $.ajax({
                url: ajaxurl, // This variable is available in WordPress admin
                type: 'POST',
                data: {
                    action: 'Bulk_Interlinking_Tool_Delete_saved_file',
                    security: nonce,
                    type: 'indexing-history',

                },
                success: function (response) {
                    if (response.success) {
                        alert(response.data.message || response.data.error || 'History Cleared Successfully.');
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

});