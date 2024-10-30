<?php
// Display the custom menu for Meta changer Menu creating
function Bulk_Interlinking_Tool_meta_changer_page()
{
    ?>
    <div class="wrap">
        <h2>Bulk Meta Description Changer Tool</h2>
        <div class="introduction">
            <p>Welcome to your Bulk Meta Description Changer Tool! To begin using it, please follow these simple instructions.</p>
            <ol>
                <li>You will start by uploading a CSV file. This file should contain the following data: Url, Meta Description, and
                    Date to change. </li>
                <li>To upload your file, click on the 'Upload' button, then select your file. </li>
                <li>Once the file is uploaded, the tool will process your data. It will consider the Url and change Meta Description
                    according to date. </li>
                <li>The tool will allow multiple Urls, according to the date you specified in the file. </li>
            </ol>
            <p> Remember to carefully configure your CSV file, as the tool will strictly follow the data inputs. Once all
                steps are executed, you will have seamlessly interlinked your pages according to your specifications. Don't
                forget to utilise the 'Save Table' option to preserve the bulk Meta Description table for future reference. Enjoy the
                functionality of your Bulk Meta Description Changer!</p>
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
                        <th>Meta Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>https://stagingngls.wpengine.com/400-popular-hashtags-for-instagram-reels-to-get-more-views/</td>
                        <td>Meta description One</td>
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

// Merger Old and New Upload file data for meta changer menu
function Bulk_Interlinking_Tool_MergeTwoObjectsMeta($obj1, $obj2)
{

    $NextBIT_mergedObject = $obj1;

    foreach ($obj2 as $url => $items) {
        if (isset($NextBIT_mergedObject[$url])) {
            foreach ($items as $newItem) {
                $found = false;
                foreach ($NextBIT_mergedObject[$url] as &$existingItem) {
                    if ($existingItem['d'] === $newItem['d']) {
                        // Update the title if the dates match
                        $existingItem['md'] = $newItem['md'];
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

add_action('init', array('NextBIT_meta_description_changer', 'init'));

class NextBIT_meta_description_changer
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
    public static function init()
    {
        add_action('admin_head', array('NextBIT_meta_description_changer', 'head'));

        self::$todayDate = date('m/d/Y');

        // Change the meta description for Yoast SEO, Rank Math, and AIOSEO Plugins
        add_action('wp', array('NextBIT_meta_description_changer', 'NextBIT_meta_filter'));
    }

    static function NextBIT_meta_filter()
    {
        $directory_path = dirname(plugin_dir_path(__FILE__)) . '/BIT-data/';
        $file_path = $directory_path . 'bulk-meta-changer-data.json';
        // Check if the JSON file exists and load data from it
        if (file_exists($file_path)) {
            $sheet_data = json_decode(file_get_contents($file_path), true);
        } else {
            $sheet_data = array(); // Fallback if the file does not exist
        }

        // Get the current post/page URL
        $post_url = get_permalink();
        if (empty($post_url)) {
            return; // Bail early if post URL is empty
        }

        $date = self::$todayDate;

        // Check if the current post/page URL exists in the sheet data
        if (isset($sheet_data[$post_url])) {
            // Iterate over the array to check dates
            foreach ($sheet_data[$post_url] as $item) {
                // Ensure the date is valid and check against today’s date
                if (isset($item['d']) && strtotime($date) >= strtotime($item['d'])) {
                    // Escape the meta description if it's available
                    $meta_description = isset($item['md']) ? $item['md'] : '';
                    if (!empty($meta_description)) {
                        $meta_description_escaped = esc_attr($meta_description);

                        // Handle Yoast SEO
                        if (defined('WPSEO_VERSION')) {
                            add_filter('wpseo_metadesc', function() use ($meta_description_escaped) {
                                return $meta_description_escaped;
                            });
                            add_filter('wpseo_opengraph_desc', function() use($meta_description_escaped) {
                                return $meta_description_escaped;
                            });
                        }

                        // Handle Rank Math
                        if (defined('RANK_MATH_VERSION')) {
                            add_filter('rank_math/frontend/description', function() use ($meta_description_escaped) {
                                return $meta_description_escaped;
                            });
                        }

                        // Handle All in One SEO
                        if (defined('AIOSEO_VERSION')) {
                            add_filter('aioseo_description', function() use ($meta_description_escaped) {
                                return $meta_description_escaped;
                            });
                        }

                        // If none of the SEO plugins are active, output the meta description manually
                        if (!defined('WPSEO_VERSION') && !defined('RANK_MATH_VERSION') && !defined('AIOSEO_VERSION')) {
                            echo '<meta name="description" content="' . $meta_description_escaped . '">' . "\n";
                            echo '<meta name="og:description" content="' . $meta_description_escaped . '">' . "\n";
                        }
                        break;
                    }
                }
            }
        }
    }
}

// Add this line to trigger the action hooks properly
NextBIT_meta_description_changer::init();
