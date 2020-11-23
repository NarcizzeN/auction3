<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php include_once('auction_functions.php')?>
<?php
//This part of the code stores the connection to DB and we store in a separate file
include_once 'db_con/db_li.php'?>

<div class="container">
<h2 class="my-3">My listings</h2>

<div class="container mt-5">

<ul class="list-group">

<?php

//  This part of the code checks for users credentials and checks which listings they have, if any
    if (isset($_SESSION['logged_in']) and $_SESSION['account_type'] == 'seller') {
        $user_id = $_SESSION['user_id'];
        $seller_id = $_SESSION['seller_id'];}

        // This page is for showing a user the auction listings they've made.
        // It will be pretty similar to browse.php, except there is no search bar.
        // This can be started after browse.php is working with a database.
        // Feel free to extract out useful functions from browse.php and put them in
        // the shared "utilities.php" where they can be shared by multiple files.

// Pagination part of the query
    if (!isset($GET['page']))
    {$curr_page = 1;}
    else
        {$curr_page = $_GET['page'];}

//    This query fetches all of the listings for a specific user and counts how many listings they have
        $query_no_of_listings = "SELECT * FROM AuctionItems WHERE sellerID = {$seller_id}";
        $getting_array = sqlsrv_query($conn, $query_no_of_listings, array(), array("Scrollable" => SQLSRV_CURSOR_KEYSET));
        $num_results = sqlsrv_num_rows($getting_array);
        $results_per_page = 10;
        $max_page = ceil($num_results / $results_per_page);
        sqlsrv_free_stmt($getting_array);



//Perform a query to pull up their auctions.

    $results_for_current_page = ($curr_page-1)*$results_per_page;
    $query = "SELECT AI.itemId, AI.itemTitle, CAST(AI.itemDescription AS VARCHAR(1000)) Description, AI.itemEndDate,
    AI.itemStartingPrice, AI.itemReservePrice, MAX(B.bidValue) MaxBid, COUNT(B.bidValue) NoOfBids
    FROM AuctionItems AI
    LEFT JOIN Bids B ON AI.itemID = B.itemID
    WHERE AI.sellerID = {$seller_id}
    GROUP BY AI.itemId, AI.itemTitle, CAST(AI.itemDescription AS VARCHAR(1000)), AI.itemEndDate, 
    AI.itemStartingPrice, AI.itemReservePrice ORDER BY itemEndDate DESC OFFSET {$results_for_current_page} ROWS FETCH NEXT {$results_per_page} ROWS ONLY";

// TODO: Loop through results and print them out as list items.

    $getResults = sqlsrv_query($conn,$query);
        while ($row = sqlsrv_fetch_array($getResults)){
        $item_id = $row['itemId'];
        $title = $row['itemTitle'];
        $desc = $row['Description'];
        $end_time = $row['itemEndDate'];
        $price = $row['MaxBid'];
        $num_bids = $row['NoOfBids'];
        $starting_price = $row['itemStartingPrice'];
        $reserve_price = $row['itemReservePrice'];
        $auction_status = getauctionstatus($item_id);

        print_my_listings_li($item_id, $title, $desc, $price, $num_bids, $end_time, $starting_price, $reserve_price, $auction_status);}
    ?>

    </ul>
    <!-- Pagination for results listings -->
    <nav aria-label="Search results pages" class="mt-5">
        <ul class="pagination justify-content-center">
<?php
    // Copy any currently-set GET variables to the URL.

    // Copy any currently-set GET variables to the URL.
    $querystring = "";
    foreach ($_GET as $key => $value) {
        if ($key != "page") {
            $querystring .= "$key=$value&amp;";
        }
    }

    $high_page_boost = max(3 - $curr_page, 0);
    $low_page_boost = max(2 - ($max_page - $curr_page), 0);
    $low_page = max(1, $curr_page - 2 - $low_page_boost);
    $high_page = min($max_page, $curr_page + 2 + $high_page_boost);

    if ($curr_page != 1) {
        echo('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');}

    for ($i = $low_page; $i <= $high_page; $i++){
        if ($i == $curr_page) {
        // Highlight the link
        echo('
        <li class="page-item active">');}
        else {// Non-highlighted link
        echo('
        <li class="page-item">');}

        // Do this in any case
        echo('<a class="page-link" href="mylistings.php?' . $querystring . 'page=' . $i . '">' . $i . '</a></li>');}

    if ($num_results != 0) {
        if ($curr_page != $max_page) {
            echo('<li class="page-item">
    <a class="page-link" href="mylistings.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
    <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
    <span class="sr-only">Next</span>
    </a>
    </li>');}}
    else {echo '<div class="text-center">You have no listings! <a href="create_auction.php">Create one!</a></div>';}


    sqlsrv_close($conn);}
    ?>

        </ul>
    </nav>


</div>



<?php include_once("footer.php")?>