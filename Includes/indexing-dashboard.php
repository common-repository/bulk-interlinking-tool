<?php
// Display the custom menu for Bulk indexing Menu creating
function Bulk_Interlinking_Tool_indexing_api_page()
{
    ?>
    <div class="wrap">
        <div class="tabs">
            <div class="tab-links">
                <a href="#setup-guide">Setup guide</a>
                <a href="#service-account">Upload Service Account</a>
                <a href="#instant-indexing">Instant Indexing</a>
                <a href="#instant-indexing-history">Instant Indexing History</a>
            </div>

            <div class="tab-content">
                <div id="setup-guide" class="tab">
                    <h2>Get Your Web Pages Indexed Instantly with the Google Indexing API</h2>
                    <div>
                        <h3>Why use the Google Indexing API?</h3>
                        <ul>
                            <li><strong>Speed:</strong> Dramatically reduce the time between updating your content and it is
                                showing up in search results.</li>
                            <li><strong>Control:</strong> Especially useful for critical website changes that shouldn’t wait
                                for Google’s regular crawl cycle.</li>
                        </ul>
                        <h3><strong>Steps for Using the Google Indexing API</strong></h3>
                        <p><strong>1. Set up a Google Cloud Project</strong></p>
                        <ul>
                            <li>Go to the Google Cloud Console (<a href="https://console.cloud.google.com/"
                                    rel="noopener ugc nofollow" target="_blank">https://console.cloud.google.com/</a>).</li>
                            <li>Create a new project or select an existing one.</li>
                        </ul>
                        <p><strong>2. Enable the Indexing API</strong></p>
                        <ul>
                            <li>Search for and enable the “Google Search Indexing API”.</li>
                        </ul>
                        <p><strong>3. Create a Service Account</strong></p>
                        <ul>
                            <li>Navigate to “IAM &amp; Admin” -&gt; “Service Accounts”.</li>
                            <li>Create a service account with the role “Owner”.</li>
                            <li>Generate a JSON key file for this service account — you’ll need it later.</li>
                        </ul>
                        <p><strong>4. Verify Ownership in Google Search Console</strong></p>
                        <ul>
                            <li>Go to Google Search Console (<a href="https://search.google.com/search-console/"
                                    rel="noopener ugc nofollow"
                                    target="_blank">https://search.google.com/search-console/</a>).</li>
                            <li>Add and verify ownership of your <a href=" <?php echo home_url(); ?>"
                                    rel="noopener ugc nofollow" target="_blank">website</a>.</li>
                            <li>Add the service account you created as an “Owner” in the Search Console settings.</li>
                        </ul>
                        <p><strong>Important Notes:</strong></p>
                        <ul>
                            <li><strong>Quota:</strong> Google has a quota for API requests. Start with the default and
                                request more if needed.</li>
                            <li><strong>URL Types:</strong></li>
                            <li><code>URL_UPDATED</code>: For existing pages with modified content.</li>
                            <li><code>URL_DELETED</code>: For pages that have been removed.</li>
                            <li><strong>Status Check:</strong> You can check the status of indexing requests in the Google
                                Search Console.</li>
                            <li><strong>This doesn’t guarantee immediate indexing</strong>, but it strongly signals to
                                Google that a re-crawl is needed.</li>
                        </ul>
                        <h3><strong>Conclusion</strong></h3>
                        <p>By following the steps outlined in this guide, you can take advantage of
                            the Google API for instant indexing of your web pages, ensuring that they appear in search
                            results quickly. This
                            can lead to improved visibility, increased traffic, and ultimately, better online success for
                            your website.
                            Instant indexing empowers webmasters to keep their content fresh and relevant in the
                            ever-changing landscape of
                            the internet.</p>
                    </div>
                </div>
                <div id="service-account" class="tab">
                    <!-- Input  -->
                    <div>
                        <!-- Input boxes and file import options -->
                        <div class="service-account-input-box">
                            <label for="input-service-text">Upload the Service Account JSON key file you obtained from
                                Google
                                API Console or paste its contents in the field.</label>
                            <textarea name="input-service-text" id="input-service-text" rows="8"
                                placeholder="Paste the service account data"></textarea>
                            <div>
                                <label for="user-service-file">Or upload JSON file:</label>
                                <input type="file" name="user-service-file" id="user-service-file"
                                    placeholder="Upload the JSON file for the service account" accept=".json" />
                            </div>
                            <button id="save-service-account">Upload Service Account</button>
                        </div>
                        <div class="display-response-box" id="display-service-account-results">
                        </div>
                        <div>
                            <h3 style="color:#0073e6; margin-top:4rem">Here's an example of a Service Account JSON file that
                                you'll need to upload.</h3>

                            <pre style="background:#fff;overflow-x: scroll;">
    {
      "type": "service_account",
      "project_id": "indexing-apis",
      "private_key_id": "dda93ca856166d2e77c9c1e797d316efc61b9571",
      "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAVy+7ohdWHQJLOQIOxQKiyVo\nJ2Tm4t55xiOdlt9d7elwk=\n-----END PRIVATE KEY-----",
      "client_email": "google-indexing-data@indexing-apis.iam.gserviceaccount.com",
      "client_id": "118305960321177817266",
      "auth_uri": "https://accounts.google.com/o/oauth2/auth",
      "token_uri": "https://oauth2.googleapis.com/token",
      "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
      "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/google-indexing-data%40indexing-apis.iam.gserviceaccount.com",
      "universe_domain": "googleapis.com"
    }</pre>
                        </div>
                    </div>
                </div>
                <div id="instant-indexing" class="tab">
                    <form class="indexing-input-box" id="indexing-input-form">
                        <label for="user-urls-input">Upload the CSV file or Paste URLs into the textbox field.</label>
                        <textarea name="user-urls-input" id="user-urls-input" rows="8"
                            placeholder="https://blog.nextgrowthlabs.com/how-to-use-google-advanced-image-search&#10;One URL per line. (Max 100)"></textarea>
                        <div>
                            <label for="user-urls-file">Or upload JSON file:</label>
                            <input type="file" name="user-urls-file" id="user-urls-file"
                                placeholder="Upload the JSON file for the service account" accept=".csv,.tsv" />
                        </div>
                        <div>
                            <legend>Select URL Update Type</legend>
                            <div class="radio-group">
                                <input type="radio" id="post-update" name="update-type" value="URL_UPDATED" checked
                                    required>
                                <label for="post-update" class="radio-label">
                                    <div class="radio-custom"></div>
                                    <span class="radio-text">Post Update</span>
                                </label>
                            </div>
                            <div class="radio-group">
                                <input type="radio" id="post-deleted" name="update-type" value="URL_DELETED" required>
                                <label for="post-deleted" class="radio-label">
                                    <div class="radio-custom"></div>
                                    <span class="radio-text">Post Deleted</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" id="request-indexing">Request Indexing</button>
                    </form>
                    <div class="display-response-box" id="display-indexing-results">

                    </div>
                </div>
                <div id="instant-indexing-history" class="tab">
                    <div class="pagination-link">
                        <ol id="page-numbersP"></ol>
                    </div>
                    <div class="display-response-box" id="display-csv-table"
                        style="display:flex;align-items: center;justify-content: center;height: 60vh;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <?php
}

function Bulk_Interlinking_Tool_Saving_service_account()
{
    // Verify nonce
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'my_ajax_nonce')) {
        die('Security check unsuccessful.');
    }

    // Check if the request is a POST request and if 'data' is set.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
        // Check if was successful returned an array
        $decoded_data = wp_unslash($_POST['data']);
        if (is_array($decoded_data)) {
            $saveType = wp_unslash($_POST['type']);
            if ($saveType === "ServiceAccount") {
                // Specify the file path where you want to save the JSON data.
                $directory_path = dirname(plugin_dir_path(__FILE__)) . '/BIT-data/';
                $file_path = $directory_path . 'service-account.json';

                // Check if the directory exists, if not create it.
                if (!file_exists($directory_path)) {
                    wp_mkdir_p($directory_path);
                }
                $json_data = json_encode($decoded_data);
                // Write the JSON data to the file.
                $result = file_put_contents($file_path, $json_data);
                if ($result === false) {
                    // Handle the error - data was not written to the file
                    echo "Error: Failed to write data to the file!";
                } else {
                    // Success - data was written to the file
                    echo "Service account file has been saved successfully.";
                }
            } else {
                echo "Error Invalid method type detected!";
            }
        } else {
            // Handle the case when decoding fails
            echo 'Error decoding JSON data!';
        }

        // This is used to terminate the response and prevent any additional content from being added to the response.
        wp_die();
    } else {
        // Handle the case when 'data' is not provided in the POST request
        echo 'Error No data received!';
        wp_die();
    }
}
add_action('wp_ajax_Bulk_Interlinking_Tool_Saving_service_account', 'Bulk_Interlinking_Tool_Saving_service_account');


function Bulk_Interlinking_Tool_Retrieving_saved_data()
{
    // Verify nonce
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'my_ajax_nonce')) {
        die('Security check unsuccessful.');
    }
    $dataType = wp_unslash($_POST['type']);
    $directory_path = dirname(plugin_dir_path(__FILE__)) . '/BIT-data/';
    // Check if the directory exists, if not create it.
    if (!file_exists($directory_path)) {
        wp_mkdir_p($directory_path);
    }

    if ($dataType == "ServiceAccount") {
        $fileName = $directory_path . 'service-account.json';
        // Getting file data
        $data = NextBIT_getFileData($fileName);
        wp_send_json_success($data);

    } else if ($dataType == "indexing-history") {
        $fileName = $directory_path . 'indexing-log.json';

        // Getting file data
        $data = NextBIT_getFileData($fileName);
        wp_send_json_success($data);

    } else {
        wp_send_json_success(array('error' => "Unknown Service type found!"));
    }
}
add_action('wp_ajax_Bulk_Interlinking_Tool_Retrieving_saved_data', 'Bulk_Interlinking_Tool_Retrieving_saved_data');

function NextBIT_getFileData($fileName)
{
    // Check if the file exists before attempting to read it.
    if (file_exists($fileName)) {
        $jsonData = file_get_contents($fileName);
        $data = json_decode($jsonData, true);
        return $data;
    } else {
        // If the file doesn't exist, initialize $data as an empty array
        $data = [];
        return $data;
    }
}


// require_once dirname(plugin_dir_path(__FILE__)) . '/vendor/autoload.php';

function Bulk_Interlinking_Tool_Indexing_URLsSingleProcess()
{

    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'my_ajax_nonce')) {
        die('Security check unsuccessful.');
    }

    // Check if the required parameters are passed
    if (!isset($_POST['data']) || !isset($_POST['submissionType']) || !isset($_POST['type'])) {
        wp_send_json_error('Missing parameters.');
        return;
    }

    // Retrieve URLs and type from the AJAX request
    $urls = json_decode(stripslashes($_POST['data']), true);
    $type = sanitize_text_field($_POST['submissionType']); // URL_UPDATED or URL_DELETED

    $log_data = NextBIT_requestForIndexing($urls, $type);
    if (isset($log_data['error'])) {
        wp_send_json_error(array('error' => $log_data['error']));
        return;
    }

    // Path to the JSON log file
    $directory_path = dirname(plugin_dir_path(__FILE__)) . '/BIT-data/';
    $log_file_path = $directory_path . 'indexing-log.json';

    // Append data to the JSON file
    if (file_exists($log_file_path)) {
        $existing_data = json_decode(file_get_contents($log_file_path), true);
        $existing_data[] = $log_data;
    } else {
        $existing_data = [$log_data];
    }

    file_put_contents($log_file_path, json_encode($existing_data, JSON_PRETTY_PRINT));

    // Send response to the front end
    wp_send_json_success([
        'message' => 'URLs processed successfully.',
        'data' => $log_data
    ]);
}

add_action('wp_ajax_Bulk_Interlinking_Tool_Indexing_URLs', 'Bulk_Interlinking_Tool_Indexing_URLsSingleProcess');


function NextBIT_requestForIndexing($urls, $type)
{
    // Path to your service account key file
    $directory_path = dirname(plugin_dir_path(__FILE__)) . '/BIT-data/';
    $service_account_key_file = $directory_path . 'service-account.json';
    if (!file_exists($service_account_key_file)) {
        return array('error' => 'Service account is not saved.');
    }
    // Initialize the client
    $client = new Google_Client();
    $client->setAuthConfig($service_account_key_file);
    $client->addScope('https://www.googleapis.com/auth/indexing');

    // Initialize the Indexing API
    $indexing_service = new Google_Service_Indexing($client);

    $responses = [];
    $errors = [];

    foreach ($urls as $url) {
        try {
            // Prepare the request
            $content = new Google_Service_Indexing_UrlNotification();
            $content->setType($type);
            $content->setUrl(esc_url_raw($url));

            // Send the request
            $response = $indexing_service->urlNotifications->publish($content);
            $responses[] = ['url' => $url, 'response' => $response];
        } catch (Exception $e) {
            $errors[] = ['url' => $url, 'error' => $e->getMessage()];
        }
    }

    // Prepare the data for JSON file
    $log_data = [
        'id' => NextBIT_generateRandomIdWithDate(),
        'date' => date('Y-m-d H:i:s'),
        'type' => $type,
        'url_count' => count($urls),
        'url' => $urls[0],
        'responses' => $responses,
        'errors' => $errors,
    ];
    return $log_data;
}


// Generating random 3 Digit Id
function NextBIT_generateRandomIdWithDate()
{
    // Get the current date in 'Ymd' format (e.g., 20240828)
    $date = date('Ymd');

    // Generate a 5-digit random number
    $randomNumber = mt_rand(10000, 99999);

    // Combine the date with the random number
    $randomId = $date . $randomNumber;

    return $randomId;
}
