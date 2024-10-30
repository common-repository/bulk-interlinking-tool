<?php
/*
Plugin Name: Bulk Interlinking Tool
Description: Bulk Interlinking Tool is a powerful WordPress plugin designed to effortlessly transform keywords within your content into hyperlinks, enhancing the user experience and improving SEO. With Bulk Interlinking Tool, you can easily manage and track all your keyword-to-link conversions by storing comprehensive records in a dedicated table. Boost your website's interactivity and keep detailed records of your keyword hyperlink strategy with Bulk Interlinking Tool – the ultimate solution for effective content optimization and performance monitoring.
Author: NextGrowth Labs
Author URI: https://nextgrowthlabs.com/
License: GPL-3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: bulk-interlinking-tool
Domain Path: /languages
Tags: Keyword Linker, SEO Title Changer, Keyword-to-Link Converter, Meta Description Changer, Dynamic Title Meta Changer
Requires at least: 5.0
Tested up to: 6.6.2
Stable Tag: 1.0.3
Version: 1.0.3
*/

// Start up the plugin

//Lets you easily link to your old articles, pages or other sites to improve their rankings in search engines and generate more clicks.

// Enqueue jQuery for compatibility with classic editor
function NextBIT_enqueue_jquery()
{
    wp_enqueue_script('jquery');
}
add_action('admin_enqueue_scripts', 'NextBIT_enqueue_jquery');

add_action('init', array('Bulk_Interlinking_Tool_Create_Link', 'init'));

add_action('admin_head', array('Bulk_Interlinking_Tool_Create_Link', 'head'));

class Bulk_Interlinking_Tool_Create_Link
{

    // Declare the static property
    private static $todayDate;

    /**
     * Adds header scripts on the edit post page.
     */
    static function head()
    {
        wp_print_scripts('jquery-form');
    }

    /**
     * Adds filters to content.
     */
    static function init()
    {
        self::$todayDate = date('m/d/Y');
        // Add content filter to process and add custom keywords
        add_action('the_content', array('Bulk_Interlinking_Tool_Create_Link', 'NextBIT_filter'), 9);
        add_action('get_comment_text', array('Bulk_Interlinking_Tool_Create_Link', 'NextBIT_comment_filter'));
        add_action('the_excerpt', array('Bulk_Interlinking_Tool_Create_Link', 'NextBIT_filter'), 9);
    }
    /**
     * Passes comments to the post filter to add custom keywords.
     */
    static function NextBIT_comment_filter($content)
    {
        return Bulk_Interlinking_Tool_Create_Link::NextBIT_filter($content, 1);
    }

    /**
     * Filters posts to add custom keywords.
     */
    static function NextBIT_filter($content, $iscomment = 0)
    {
        $content = ' ' . $content . ' ';
        $pageURL = get_permalink();
        $directory_path = plugin_dir_path(__FILE__) . 'BIT-data/';
        // Check if the directory exists, if not create it.
        if (!file_exists($directory_path)) {
            wp_mkdir_p($directory_path);
        }

        $filename = $directory_path . 'bulk-interlinking-data.json';
        if (!file_exists($filename)) {
            return $content;
        }
        $jsonData = file_get_contents($filename);
        $mainData;
        // Check if JSON decoding is successful
        if ($jsonData !== false) {
            $fullData = json_decode($jsonData);
            // Check if JSON decoding was successful and the property exists
            if ($fullData !== null && property_exists($fullData, $pageURL)) {
                $mainData = $fullData->$pageURL;
            } else {
                return $content;
            }
        } else {
            return $content;
        }

        if (empty($mainData) || !is_array($mainData)) {
            return $content; // No keywords to process.
        }

        $used_keywords = array();

        $content = Bulk_Interlinking_Tool_Protecting_Tags::NextBIT_Find_Tags($content); //We do NOT want to replace content inside HTML blocks.

        foreach ($mainData as $item) {

            $keyword = $item->k;
            $url = $item->u;
            $key_count = $item->n;
            $case = strtolower($item->c) == "yes" ? '' : 'i';
            $new_tab = ($item->nt);

            $regex = '/\b' . preg_quote($keyword, '/') . '\b/';

            $replace1 = '<a href="' . $url . '"';
            if (strtolower($new_tab) == "yes") {
                $replace1 .= ' target="_blank"';
            }
            $replace1 .= '>';
            $replace2 = '</a>';

            $content = preg_replace($regex . $case, $replace1 . '$0' . $replace2, $content, $key_count);

            $content = Bulk_Interlinking_Tool_Protecting_Tags::NextBIT_Find_Tags($content); // We do NOT want to replace content inside HTML blocks.

            $used_keywords[$keyword] = [
                'url' => $url,
                'times' => $key_count,
                'case' => $case,
                'newwindow' => $new_tab,
            ];
        }

        // Additional code for caching and post meta can be added here.

        $content = Bulk_Interlinking_Tool_Protecting_Tags::NextBIT_Find_Blocks($content); // Return the HTML blocks
        $content = Bulk_Interlinking_Tool_Protecting_Tags::NextBIT_Find_Blocks($content); // Return the HTML blocks
        $content = Bulk_Interlinking_Tool_Protecting_Tags::NextBIT_Find_Blocks($content); // Return the HTML blocks
        $NextBIT_ProtectBlocks = array();

        return trim($content, ' ');
    }

}

class Bulk_Interlinking_Tool_Protecting_Tags
{
    static function NextBIT_Find_Tags($content, $firstRun = true)
    {
        global $NextBIT_ProtectBlocks;

        //protects a tags
        $content = preg_replace_callback('!(\<a [^>]*>(.*?)<\/a>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);
        $content = preg_replace_callback('!(\<button [^>]*>(.*?)<\/button>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);
        if ($firstRun) {

            //protects style code
            $content = preg_replace_callback('!(\<style[^>]*>(.*?)<\/style>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            //protects script code
            $content = preg_replace_callback('!(\<script[^>]*>(.*?)<\/script>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            //protects code tags
            $content = preg_replace_callback('!(\<code\>[\S\s]*?\<\/code\>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            //protects simple tags tags
            $content = preg_replace_callback('!(\[tags*\][\S\s]*?\[\/tags*\])!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            //protects img tags
            $content = preg_replace_callback('!(\<img[^>]*\>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            //protects heading tags
            $content = preg_replace_callback('!(\<h1[^>]*>(.*?)<\/h1>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            $content = preg_replace_callback('!(\<h2[^>]*>(.*?)<\/h2>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            $content = preg_replace_callback('!(\<h3[^>]*>(.*?)<\/h3>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            $content = preg_replace_callback('!(\<h4[^>]*>(.*?)<\/h4>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            $content = preg_replace_callback('!(\<h5[^>]*>(.*?)<\/h5>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            $content = preg_replace_callback('!(\<h6[^>]*>(.*?)<\/h6>)!ims', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            //protects all correctly formatted URLS
            $content = preg_replace_callback('!(([A-Za-z]{3,9})://([-;:&=\+\$,\w]+@{1})?([-A-Za-z0-9\.]+)+:?(\d+)?((/[-\+~%/\.\w]+)?\??([-\+=&;%@\.\w]+)?#?([\w]+)?)?)!', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);

            //protects all other urls like google.com
            $content = preg_replace_callback('!([-A-Za-z0-9_]+\.[A-Za-z][A-Za-z][A-Za-z]?\W?)!', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Blocks'), $content);
        }
        return $content;
    }

    static function NextBIT_Return_Blocks($blocks)
    {
        global $NextBIT_ProtectBlocks;
        $NextBIT_ProtectBlocks[] = $blocks[1];
        return '[block]' . (count($NextBIT_ProtectBlocks) - 1) . '[/block]';
    }

    static function NextBIT_Find_Blocks($output)
    {
        global $NextBIT_ProtectBlocks;
        if (!empty($NextBIT_ProtectBlocks)) {
            $output = preg_replace_callback('!(\[block\]([0-9]*?)\[\/block\])!', array('Bulk_Interlinking_Tool_Protecting_Tags', 'NextBIT_Return_Tags'), $output);
        }

        return $output;
    }

    static function NextBIT_Return_Tags($blocks)
    {
        global $NextBIT_ProtectBlocks;
        return $NextBIT_ProtectBlocks[$blocks[2]];
    }
}

// Creating Plugin menu
function Bulk_Interlinking_Tool_adding_custom_menu_page()
{
    add_menu_page(
        'Bulk Interlinking Tool',   // Page title
        'Bulk Interlinking Tool',   // Menu title
        'manage_options',           // Capability required to access
        'bulk-interlinking-tool',   // Menu slug
        'Bulk_Interlinking_Tool_creating_new_menu', // Callback function to render the page
        plugin_dir_url(__FILE__) . 'nextLogo16.png' // Icon for the menu item
    );
    // Add submenu for Title Changer
    add_submenu_page(
        'bulk-interlinking-tool',   // Parent slug
        'Title Changer',            // Page title
        'Title Changer',            // Menu title
        'manage_options',           // Capability required to access
        'bulk-title-changer',       // Menu slug
        'Bulk_Interlinking_Tool_title_changer_page' // Callback function to render the title changer page
    );
     // Add submenu for Meta Descriptions
     add_submenu_page(
        'bulk-interlinking-tool',       // Parent slug
        'Meta Description Changer',     // Page title
        'Meta Description Changer',     // Menu title
        'manage_options',               // Capability required to access
        'bulk-meta-changer',            // Menu slug
        'Bulk_Interlinking_Tool_meta_changer_page' // Callback function to render the title changer page
    );
}
add_action('admin_menu', 'Bulk_Interlinking_Tool_adding_custom_menu_page');


// Display the custom menu page content
function Bulk_Interlinking_Tool_creating_new_menu()
{
    ?>
    <div class="wrap">
        <h2>Bulk Interlinking Tool</h2>
        <div class="introduction">
            <p>Welcome to our Bulk Interlinking Tool! To begin using it, please follow these simple instructions.</p>
            <ol>
                <li>You will start by uploading a CSV file. This file should contain the following data: source page url,
                    keywords, destination page url, the number of times each page needs to be linked, whether the keyword is
                    case sensitive or not, and whether the link should open in a new tab. </li>
                <li>To upload your file, click on the 'Upload' button, then select your file. </li>
                <li>Once the file is uploaded, the tool will process your data. It will consider the source page and the
                    destination page from your file, along with the keywords. </li>
                <li>The tool will allow multiple links, according to the number you specified in the file. </li>
                <li>If 'Case Sensitive' option is marked as 'Yes' for a keyword, the tool will consider the specific
                    requirement and interlink when the exact keyword is found. </li>
                <li>Finally, if 'Open in New Tab' is marked as 'Yes', any clicked link from the generated interlink will
                    open in a new tab, providing a better navigation experience for your users. </li>
            </ol>
            <p> Remember to carefully configure your CSV file, as the tool will strictly follow the data inputs. Once all
                steps are executed, you will have seamlessly interlinked your pages according to your specifications. Don't
                forget to utilise the 'Save Table' option to preserve the interlinking table for future reference. Enjoy the
                functionality of your Bulk Interlinking Tool!</p>
        </div>
        <!-- Input boxes and file import options -->
        <div class="main-box-bulk-interlinking-tool">
            <label for="csvFile">Upload CSV File:</label>
            <input type="file" name="csvFile" id="user-data-file" placeholder="Upload the csv file here" accept=".csv" />
            <button class="upload-file-btn">Upload</button>
            <button id="displayDataButton" style="float: right;">Display Data</button>
        </div>
        <!-- Pagination options here-->
        <div class="pagination-link">
            <ol id="page-numbersP"></ol>
        </div>
        <!-- Display table and other things -->
        <div id="display-csv-table">
            <div>
                <h3 style="color:#0073e6;">Here's a simple example of a CSV file that you can use for uploading.</h3>
            </div>
            <button class="save-table-to-post" onclick="NextBIT_downloadSampleCSVFile()">Download Sample CSV</button>
            <table class="record-table">
                <thead>
                    <tr>
                        <th>Source URL</th>
                        <th>Destination URL</th>
                        <th>Keyword</th>
                        <th>Number</th>
                        <th>Case Sensitive</th>
                        <th>New Tab</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>https://stagingngls.wpengine.com/400-popular-hashtags-for-instagram-reels-to-get-more-views/</td>
                        <td>https://stagingngls.wpengine.com/want-to-know-what-people-are-searching-on-youtube-globally/</td>
                        <td>seo keyword checker</td>
                        <td>2</td>
                        <td>No</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>https://stagingngls.wpengine.com/an-in-depth-guide-for-a-successful-app-store-optimization/</td>
                        <td>https://stagingngls.wpengine.com/the-ultimate-guide-to-app-store-optimization-strategies/</td>
                        <td>app store optimization strategy</td>
                        <td>2</td>
                        <td>No</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td>https://stagingngls.wpengine.com/a-guide-for-app-metadata-in-the-app-store-and-google-play/</td>
                        <td>https://stagingngls.wpengine.com/keyword-density-tool/</td>
                        <td>keyword density</td>
                        <td>3</td>
                        <td>No</td>
                        <td>Yes</td>
                    </tr>
                    <tr>
                        <td>https://stagingngls.wpengine.com/an-in-depth-guide-for-a-successful-app-store-optimization/</td>
                        <td>https://stagingngls.wpengine.com/ultimate-aso-guide-what-is-app-store-optimization/</td>
                        <td>app store optimization</td>
                        <td>3</td>
                        <td>No</td>
                        <td>No</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

function Bulk_Interlinking_Tool_Display_Data()
{
    // Verify nonce
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'my_ajax_nonce')) {
        die('Security check unsuccessful.');
    }

    $directory_path = plugin_dir_path(__FILE__) . 'BIT-data/';
    // Check if the directory exists, if not create it.
    if (!file_exists($directory_path)) {
        wp_mkdir_p($directory_path);
    }

    $dataType = wp_unslash($_POST['type']);
    if ($dataType == "Title") {
        $filename = $directory_path . 'bulk-title-changer-data.json';
        $jsonData = file_get_contents($filename);
        $data = json_decode($jsonData);
        wp_send_json_success($data);
    }else if($dataType == "Meta"){
        $filename = $directory_path . 'bulk-meta-changer-data.json';
        $jsonData = file_get_contents($filename);
        $data = json_decode($jsonData);
        wp_send_json_success($data);
    }else {
        $filename = $directory_path . 'bulk-interlinking-data.json';
        $jsonData = file_get_contents($filename);
        $data = json_decode($jsonData);
        wp_send_json_success($data);
    }
}

add_action('wp_ajax_Bulk_Interlinking_Tool_Display_Data', 'Bulk_Interlinking_Tool_Display_Data');
// add_action('wp_ajax_nopriv_Bulk_Interlinking_Tool_Display_Data', 'Bulk_Interlinking_Tool_Display_Data'); // Add for non-logged-in users

function Bulk_Interlinking_Tool_Saving_data_into_File()
{
    // Verify nonce
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'my_ajax_nonce')) {
        die('Security check unsuccessful.');
    }

    // Check if the request is a POST request and if 'data' is set.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
        // Check if was successful returned an array
        $decoded_data = wp_unslash($_POST['data']);

        $directory_path = plugin_dir_path(__FILE__) . 'BIT-data/';
        // Check if the directory exists, if not create it.
        if (!file_exists($directory_path)) {
            wp_mkdir_p($directory_path);
        }

        if (is_array($decoded_data)) {
            $saveType = wp_unslash($_POST['type']);
            if ($saveType === "Title") {
                // Specify the file path where you want to save the JSON data.
                $file_path = $directory_path . 'bulk-title-changer-data.json';

                // Check if the file exists and has data
                if (file_exists($file_path)) {
                    $jsonData1 = file_get_contents($file_path);
                    // Check if the existing data is valid JSON
                    $fileData = json_decode($jsonData1, true); // Decode to an array

                    if ($fileData !== null) {
                        // Merge the existing data with the new data
                        $newData = Bulk_Interlinking_Tool_MergeTwoObjectsTitle($fileData, $decoded_data);

                        // Convert the merged data back to JSON
                        $json_data2 = json_encode($newData);
                    } else {
                        // Handle the case when existing file return null
                        $new_data = json_encode($decoded_data);
                        file_put_contents($file_path, $new_data);
                        echo 'No data was found in the file. Consequently, the file was replaced, and fresh data has been stored.';
                        wp_die();
                    }
                } else {
                    // If the file doesn't exist, use the new data as-is
                    $json_data2 = json_encode($decoded_data);
                }
                // Used for scheduling auto indexing.
                // NextBIT_bulk_title_changer_indexing_schedule_event();
                // Write the JSON data to the file.
                $result = file_put_contents($file_path, $json_data2);
                if ($result === false) {
                    // Handle the error - data was not written to the file
                    echo "Failed to write data to the file.";
                } else {
                    // Success - data was written to the file
                    echo "Data saved to JSON file successfully!";
                }
            } else if($saveType == "Meta"){
                // Specify the file path where you want to save the JSON data.
                $file_path = $directory_path . 'bulk-meta-changer-data.json';

                // Check if the file exists and has data
                if (file_exists($file_path)) {
                    $jsonData1 = file_get_contents($file_path);
                    // Check if the existing data is valid JSON
                    $fileData = json_decode($jsonData1, true); // Decode to an array

                    if ($fileData !== null) {
                        // Merge the existing data with the new data
                        $newData = Bulk_Interlinking_Tool_MergeTwoObjectsMeta($fileData, $decoded_data);

                        // Convert the merged data back to JSON
                        $json_data2 = json_encode($newData);
                    } else {
                        // Handle the case when existing file return null
                        $new_data = json_encode($decoded_data);
                        file_put_contents($file_path, $new_data);
                        echo 'No data was found in the file. Consequently, the file was replaced, and fresh data has been stored.';
                        wp_die();
                    }
                } else {
                    // If the file doesn't exist, use the new data as-is
                    $json_data2 = json_encode($decoded_data);
                }
                NextBIT_bulk_title_changer_indexing_schedule_event();
                // Write the JSON data to the file.
                $result = file_put_contents($file_path, $json_data2);
                if ($result === false) {
                    // Handle the error - data was not written to the file
                    echo "Failed to write data to the file.";
                } else {
                    // Success - data was written to the file
                    echo "Data saved to JSON file successfully!";
                }
            } else {
                // Specify the file path where you want to save the JSON data.
                $file_path = $directory_path . 'bulk-interlinking-data.json';

                // Check if the file exists and has data
                if (file_exists($file_path)) {
                    $jsonData1 = file_get_contents($file_path);
                    // Check if the existing data is valid JSON
                    $fileData = json_decode($jsonData1, true); // Decode to an array

                    if ($fileData !== null) {
                        // Merge the existing data with the new data
                        $newData = Bulk_Interlinking_Tool_MergeTwoObjects($fileData, $decoded_data);

                        // Convert the merged data back to JSON
                        $json_data2 = json_encode($newData);
                    } else {
                        // Handle the case when existing file return null
                        $new_data = json_encode($decoded_data);
                        file_put_contents($file_path, $new_data);
                        echo 'No data was found in the file. Consequently, the file was replaced, and fresh data has been stored.';
                        wp_die();
                    }
                } else {
                    // If the file doesn't exist, use the new data as-is
                    $json_data2 = json_encode($decoded_data);
                }

                // Write the JSON data to the file.
                $result = file_put_contents($file_path, $json_data2);
                if ($result === false) {
                    // Handle the error - data was not written to the file
                    echo "Failed to write data to the file.";
                } else {
                    // Success - data was written to the file
                    echo "Data saved to JSON file successfully!";
                }
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

add_action('wp_ajax_Bulk_Interlinking_Tool_Saving_data_into_File', 'Bulk_Interlinking_Tool_Saving_data_into_File');

function Bulk_Interlinking_Tool_MergeTwoObjects($obj1, $obj2)
{

    function NextBIT_mergerInnerObject($arr1, $arr2)
    {
        $mergedArray = [];

        foreach ($arr2 as $obj2) {
            $found = false;

            foreach ($arr1 as $obj1) {
                // Compare inner objects based on the "url" and "keyword" properties
                if (strcasecmp($obj1['u'], $obj2['u']) === 0 && strcasecmp($obj1['k'], $obj2['k']) === 0) {
                    // Merge properties if the condition is met
                    $NextBIT_mergedObject = [
                        'u' => $obj2['u'],
                        'k' => $obj2['k'],
                        'n' => $obj2['n'],
                        'c' => $obj2['c'],
                        'nt' => $obj2['nt']
                    ];

                    $mergedArray[] = $NextBIT_mergedObject;
                    $found = true;
                    break;
                }
            }

            // If the inner object is not found, add it to the merged array
            if (!$found) {
                $mergedArray[] = $obj2;
            }
        }

        // Add any remaining objects from $arr1 that were not compared
        foreach ($arr1 as $obj1) {
            if (
                !array_reduce(
                    $mergedArray,
                    function ($carry, $obj) use ($obj1) {
                        return $carry || (strcasecmp($obj['u'], $obj1['u']) === 0 && strcasecmp($obj['k'], $obj1['k']) === 0);
                    },
                    false
                )
            ) {
                $mergedArray[] = $obj1;
            }
        }

        return $mergedArray;
    }
    $NextBIT_mergedObject = $obj1;
    foreach ($obj2 as $key => $value) {
        if (array_key_exists($key, $NextBIT_mergedObject)) {
            // Merge arrays of objects for the same key
            $NextBIT_mergedObject[$key] = NextBIT_mergerInnerObject($NextBIT_mergedObject[$key], $value);
        } else {
            // If the key doesn't exist in $obj1, add it
            $NextBIT_mergedObject[$key] = $value;
        }
    }
    return $NextBIT_mergedObject;
}



// Delete Saved Files and Data
function Bulk_Interlinking_Tool_Delete_saved_file()
{
    // Verify nonce
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'my_ajax_nonce')) {
        die('Security check unsuccessful.');
    }
    if (!isset($_POST['type'])) {
        wp_send_json_error('Missing parameters.');
        return;
    }

    $dataType = wp_unslash($_POST['type']);

    function deleteFiles($fileName)
    {
        // Check if the file exists before attempting to delete
        if (file_exists($fileName)) {
            // Delete the file
            if (unlink($fileName)) {
                return array('message' => "File deleted successfully!");
            } else {
                return array('error' => "File could not be deleted!");
            }
        } else {
            return array('error' => "File not found!");
        }
    }

    $directory_path = plugin_dir_path(__FILE__) . 'BIT-data/';
    if (!file_exists($directory_path)) {
        wp_mkdir_p($directory_path);
    }
    if ($dataType === "indexing-history") {
        $fileName = $directory_path . 'indexing-log.json';
        $data = deleteFiles($fileName);
        wp_send_json_success($data);

    } else if ($dataType === "title-changer") {
        $fileName = $directory_path . 'bulk-title-changer-data.json';
        $data = deleteFiles($fileName);
        NextBIT_bulk_interlinking_tool_deactivation();
        wp_send_json_success($data);
    } else if($dataType === "meta-changer") {
        $fileName = $directory_path . 'bulk-meta-changer-data.json';
        $data = deleteFiles($fileName);
        NextBIT_bulk_interlinking_tool_deactivation();
        wp_send_json_success($data);
    } else {
        wp_send_json_success(array('error' => "Unknown Service type found!"));
    }
}

add_action('wp_ajax_Bulk_Interlinking_Tool_Delete_saved_file', 'Bulk_Interlinking_Tool_Delete_saved_file');




// Include the Bulk Title Changer functionality from the 'bulk-title-changer.php' file
require_once(plugin_dir_path(__FILE__) . 'Includes/bulk-title-changer.php');

// Include the Bulk Meta Changer functionality from the 'bulk-meta-changer.php' file
require_once(plugin_dir_path(__FILE__) . 'Includes/bulk-meta-changer.php');

// Include the Indexing Dashboard functionality from the 'indexing-dashboard.php' file
// require_once(plugin_dir_path(__FILE__) . 'Includes/indexing-dashboard.php');




// Function to enqueue styles and scripts conditionally based on the current admin page
function bulk_interlinking_tool_enqueue_assets($hook)
{
    // Define an array of admin pages where the styles and scripts should be loaded
    $pages = array(
        'toplevel_page_bulk-interlinking-tool',          // Top-level menu page
        'bulk-interlinking-tool_page_bulk-title-changer', // Title Changer page
        'bulk-interlinking-tool_page_bulk-meta-changer',  // Meta changer page
        'bulk-interlinking-tool_page_bulk-indexing-tool' // Indexing Tool page
    );

    // Check if the current page is in the array of specified pages
    if (!in_array($hook, $pages)) {
        return; // Exit the function if not on one of the specified pages
    }

    // Enqueue the common CSS for all specified pages
    wp_enqueue_style('bulk-interlinking-tool-css', plugin_dir_url(__FILE__) . 'css/bulk-interlinking-tool.css', array(), '1.0.3');

    // Enqueue the common JavaScript file for all specified pages
    wp_enqueue_script('BIT-common-js', plugin_dir_url(__FILE__) . 'js/NextBIT-common-fn.js', array('jquery'), '1.0.3', true);

    // Conditionally enqueue scripts and styles for the top-level menu page
    if ($hook === 'toplevel_page_bulk-interlinking-tool') {
        wp_enqueue_script('bulk-interlinking-tool-js', plugin_dir_url(__FILE__) . 'js/bulk-interlinking-tool.js', array('jquery'), '1.0.3', true);

        // Localize the script to pass a nonce for AJAX security
        wp_localize_script('bulk-interlinking-tool-js', 'bulkInterlinkingToolData', array(
            'nonce' => wp_create_nonce('my_ajax_nonce')
        ));
    }

    // Conditionally enqueue scripts for the Title Changer page
    if ($hook === 'bulk-interlinking-tool_page_bulk-title-changer') {
        wp_enqueue_script('bulk-title-changer-js', plugin_dir_url(__FILE__) . 'js/bulk-title-changer.js', array('jquery'), '1.0.3', true);

        // Localize the script to pass a nonce for AJAX security
        wp_localize_script('bulk-title-changer-js', 'bulkTitleChangerToolData', array(
            'nonce' => wp_create_nonce('my_ajax_nonce')
        ));
    }

    // Conditionally enqueue styles and scripts for the Indexing Tool page
    if ($hook === 'bulk-interlinking-tool_page_bulk-indexing-tool') {
        wp_enqueue_style('my-plugin-tabs-style', plugin_dir_url(__FILE__) . 'css/tabs-style.css', array(), '1.0.3');
        wp_enqueue_script('my-plugin-tabs-script', plugin_dir_url(__FILE__) . 'js/tabs-script.js', array('jquery'), '1.0.3', true);
        wp_enqueue_script('instant-indexing-js', plugin_dir_url(__FILE__) . 'js/instant-indexing.js', array('jquery'), '1.0.3', true);

        // Localize the script to pass a nonce for AJAX security
        wp_localize_script('instant-indexing-js', 'NextBITinstantIndexingTool', array(
            'nonce' => wp_create_nonce('my_ajax_nonce')
        ));
    }

    if($hook === 'bulk-interlinking-tool_page_bulk-meta-changer') {
        wp_enqueue_script('bulk-meta-changer-js', plugin_dir_url(__FILE__) . 'js/bulk-meta-changer.js', array('jquery'), '1.0.3', true);

        // Localize the script to pass a nonce for AJAX security
        wp_localize_script('bulk-meta-changer-js', 'bulkMetaChangerToolData', array(
            'nonce' => wp_create_nonce('my_ajax_nonce')
        ));
    }

}

// Hook the function to the 'admin_enqueue_scripts' action to ensure it runs in the admin area
add_action('admin_enqueue_scripts', 'bulk_interlinking_tool_enqueue_assets');






// Schedule NextBIT Title changer indexing if not scheduled already
function NextBIT_bulk_title_changer_indexing_schedule_event()
{
    if (!wp_next_scheduled('NextBIT_bulk_title_changer_indexing')) {
        wp_schedule_event(strtotime('02:00:00'), 'daily', 'NextBIT_bulk_title_changer_indexing');
    }
}

add_action('NextBIT_bulk_title_changer_indexing', 'NextBIT_bulk_title_changer_indexing_cron');


// This function will everyday and check the conditions to run indexing everyday and extract the urls.
function NextBIT_bulk_title_changer_indexing_cron()
{
    $current_hour = date('G');
    // Get the urls
    $directory_path = plugin_dir_path(__FILE__) . 'BIT-data/';
    // Check if the directory exists, if not create it.
    if (!file_exists($directory_path)) {
        wp_mkdir_p($directory_path);
    }
    $fileName = $directory_path . 'bulk-title-changer-data.json';
    $fileData = NextBIT_getFileData($fileName);
    if (empty($fileData)) {
        // Nothing to do, object is empty
    } else {
        $todayDate = date('n/j/Y');
        // Array to store URLs with today's date
        $urlsWithTodayDate = [];

        // Loop through the $fileData array
        foreach ($fileData as $url => $entries) {
            foreach ($entries as $entry) {
                if ($entry['d'] === $todayDate) {
                    $urlsWithTodayDate[] = $url;
                    break; // No need to check further entries for this URL
                }
            }
        }
        $response = NextBIT_requestForIndexing($urlsWithTodayDate, "URL_UPDATED");
        if (isset($response['error'])) {
            return array('error' => $response['error']);
        }
        // Path to the JSON log file
        $directory_path = plugin_dir_path(__FILE__) . 'BIT-data/';
        $log_file_path = $directory_path . 'indexing-log.json';
        // Append data to the JSON file
        if (file_exists($log_file_path)) {
            $existing_data = json_decode(file_get_contents($log_file_path), true);
            $existing_data[] = $response;
        } else {
            $existing_data = [$response];
        }

        file_put_contents($log_file_path, json_encode($existing_data, JSON_PRETTY_PRINT));
    }
}

// Delete the scheduled events this is also called when the title file is deleted.
function NextBIT_bulk_interlinking_tool_deactivation()
{
    $timestamp = wp_next_scheduled('NextBIT_bulk_title_changer_indexing');
    wp_unschedule_event($timestamp, 'NextBIT_bulk_title_changer_indexing');
}
register_deactivation_hook(__FILE__, 'NextBIT_bulk_interlinking_tool_deactivation');
