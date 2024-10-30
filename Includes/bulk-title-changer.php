<?php
// Display the custom menu for Title changer Menu creating
function Bulk_Interlinking_Tool_title_changer_page()
{
    ?>
    <div class="wrap">
        <h2>Bulk Title Changer Tool</h2>
        <div class="introduction">
            <p>Welcome to your Bulk Title Changer Tool! To begin using it, please follow these simple instructions.</p>
            <ol>
                <li>You will start by uploading a CSV file. This file should contain the following data: Url, Title, and
                    Date to change. </li>
                <li>To upload your file, click on the 'Upload' button, then select your file. </li>
                <li>Once the file is uploaded, the tool will process your data. It will consider the Url and change Title
                    according to date. </li>
                <li>The tool will allow multiple Urls, according to the date you specified in the file. </li>
            </ol>
            <p> Ensure your CSV file is accurately configured, as the tool will adhere precisely to the provided data. 
                After completing all steps, your pages will be updated with the meta descriptions according to your specifications. 
                Be sure to use the 'Save Table' option to store the bulk meta description table for future use. 
                Enjoy the powerful capabilities of your Bulk Meta Description Changer!</p>
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
                        <th>Url</th>
                        <th>Title</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>https://stagingngls.wpengine.com/400-popular-hashtags-for-instagram-reels-to-get-more-views/</td>
                        <td>Title One</td>
                        <td>23-08-2024</td>
                    </tr>
                    <tr>
                        <td>https://stagingngls.wpengine.com/an-in-depth-guide-for-a-successful-app-store-optimization/</td>
                        <td>app store optimization strategy</td>
                        <td>28-08-2024</td>
                    </tr>
                    <tr>
                        <td>https://stagingngls.wpengine.com/keyword-density-tool/</td>
                        <td>keyword density tool</td>
                        <td>25-08-2024</td>
                    </tr>
                    <tr>
                        <td>https://stagingngls.wpengine.com/ultimate-aso-guide-what-is-app-store-optimization/</td>
                        <td>app store optimization</td>
                        <td>30-08-2024</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// Merger Old and New Upload file data for title changer menu
function Bulk_Interlinking_Tool_MergeTwoObjectsTitle($obj1, $obj2)
{

    $NextBIT_mergedObject = $obj1;

    foreach ($obj2 as $url => $items) {
        if (isset($NextBIT_mergedObject[$url])) {
            foreach ($items as $newItem) {
                $found = false;
                foreach ($NextBIT_mergedObject[$url] as &$existingItem) {
                    if ($existingItem['d'] === $newItem['d']) {
                        // Update the title if the dates match
                        $existingItem['t'] = $newItem['t'];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    // If no matching date, add the new item
                    $NextBIT_mergedObject[$url][] = $newItem;
                }
            }
        } else {
            // If the URL doesn't exist, add it directly
            $NextBIT_mergedObject[$url] = $items;
        }
    }
    return $NextBIT_mergedObject;
}


// Updating page/post title for Yoast SEO, Rank Math, and AIOSEO Pack plugins
add_action('init', array('NextBit_Title_Changer_main', 'init'));
add_action('admin_head', array('NextBit_Title_Changer_main', 'head'));

class NextBit_Title_Changer_main
{

    // Declare the static property
    private static $todayDate;
    /**
     * Adds filters to content.
     */
    static function head()
    {
        wp_print_scripts('jquery-form');
    }

    static function init()
    {
        self::$todayDate = date('m/d/Y');
        // Change the title of yoast SEO, rnak math, and AIOSEO Plugins
        add_filter('wpseo_title', array('NextBit_Title_Changer_main', 'NextBIT_title_filter'));
        add_filter('rank_math/frontend/title', array('NextBit_Title_Changer_main', 'NextBIT_title_filter'));
        add_filter('aioseo_title', array('NextBit_Title_Changer_main', 'NextBIT_title_filter'));
    }

    /**
     * Filters posts to change yoast SEO and rank math plugin title
     */
    static function NextBIT_title_filter($title)
    {
        $directory_path = dirname(plugin_dir_path(__FILE__)) . '/BIT-data/';
        $file_path = $directory_path . 'bulk-title-changer-data.json';

        // Check if the JSON file exists and load data from it
        if (file_exists($file_path)) {
            $sheet_data = json_decode(file_get_contents($file_path), true);
        } else {
            $sheet_data = array(); // Fallback if the file does not exist
        }

        // Get the current post/page URL
        $post_url = get_permalink();

        $date = self::$todayDate;
        
        // Check if the current post/page URL exists in the sheet data
        if (isset($sheet_data[$post_url])) {
            // Iterate over the array to check dates
            foreach ($sheet_data[$post_url] as $item) {
                // Update the title if the condition is match
                if (strtotime($date) >= strtotime($item['d'])) {

                    $title = $item['t'];
                    return $title;
                }
            }
        }
        return $title;
    }
}