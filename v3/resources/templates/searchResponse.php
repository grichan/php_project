<?php
session_start();
include '../config.php';
include "../classes/Worker.php";

$login_status = false;
if ( $_SESSION && $_SESSION["logged_in"] == true){
    $login_status = true;
    $username = $_SESSION["name"];
}

$next_page = $_POST["page"];
$page = $next_page;

//calculate next page
if( $_POST["type"] == "Search"  )
{
    $page_left = 0;
    $next_page = 1;
} else if( $_POST["type"] == "next" )
{
    $next_page += 1;// increment the page number
} else if ( $_POST["type"] == "prev" )
{
    $next_page -=1;
} else if( is_int((int)$_POST["type"]))
{
    $next_page = $_POST["type"];
}

$limit = 6;
$page_right = $next_page*$limit;
$page_left = $page_right - $limit; // starting point for query
if  ($next_page <= 0 ){ // if nextpage less than 0
    $page_left = 0;
    $next_page = 1;
}

include 'connection.php';
$conn = OpenCon();
if (!$conn) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
$search = $_POST['search']; // gets the search string


echo "<h1> Searched for: ". $search ."</h1>";
$totalRows = dbCountSearchRows( "workers", $search);
$workers = dbSearchWorkers( $search, $page_left, $limit );


echo "<table class='table'>";
echo "<tr>";
echo "<th>Id </th>";
echo "<th>First Name  </th>";
echo "<th>Last Name </th>";
echo "<th>Title </th>";
echo "<th>Depart </th>";
echo "</tr>";
foreach ( $workers as $row ) {
    echo "<tr>";
    echo "<td>" . $row->id . "</td>";
    echo "<td>" . $row->firstName . "</td>";
    echo "<td>" . $row->lastName . "</td>";
    echo "<td>" . $row->department . "</td>";
    echo "<td>" . $row->title . "</td>";
    if ($login_status) {
        echo '<td>
                <form action="edit.php" method="post">
                    <button name="Edit" value=" ' . $row->id . ' " class="btn">Edit</button>
               </form>
                </td>
                ';
        echo "<td>
                <form action=\"../" . TEMPLATES_PATH . "/delete.php \" method=\"post\">
                    <button name=\"Delete\" value=\"" . $row->id . "\" class='btn btn-danger'>Delete</button>
               </form>
                </td>
                ";
    }
}
    echo "</tr>";
    echo "</table>";


// PAGE NAVIGATION
if ( $totalRows != 0 )
{
    echo("
        <form id=\"page_btns\" method=\"post\"  >
            <nav aria-label=\"Page navigation \">
                <ul class=\"pagination\">         
    ");
            if($next_page > 3) // print previous
            {
                echo (" <li id=\"1\" class=\"page-item\"><a class=\"page-link\" href=\"#\">First</a></li> ");
            }
            $max_page = ceil($totalRows / $limit); // max page count

            // ECHO PAGINATION
            for( $i =1; $i <= $max_page; $i++ )
            {
                 if ( $i == $next_page-1 ) // print before current page
                {
                    echo ("  <li id=\"$i\" class=\"page-item \"><a class=\"page-link\" href=\"#\">$i</a></li> ");
                }else if( $i == $next_page-2) // print as active current page
                 {
                     echo ("  <li id=\"$i\" class=\"page-item \"><a class=\"page-link\" href=\"#\">$i</a></li> ");
                 }
                else if( $i == $next_page) // print as active current page
                {
                    echo ("  <li id=\"$i\" class=\"page-item active\"><a class=\"page-link\" href=\"#\">$i</a></li> ");
                }
                else if ( $i == $next_page+1 ) // print active after page
                {
                    echo ("  <li id=\"$i\" class=\"page-item\"><a class=\"page-link\" href=\"#\">$i</a></li> ");
                }else if ( $i == $next_page+2 ) // print active after page
                {
                    echo ("  <li id=\"$i\" class=\"page-item\"><a class=\"page-link\" href=\"#\">$i</a></li> ");
                }
            }

             if ( $next_page < $max_page-2 ) // print before current page
            {
                echo ("  <li id=\"$max_page\" class=\"page-item \"><a class=\"page-link\" href=\"#\">Last</a></li> ");
            }

    echo (" 
                </ul>
            </nav>
        </form>
        <script>
            localStorage.setItem(\"page\", \"$next_page\");
            localStorage.setItem( 'search', '$search' )
            $(document).ready(onDocumentReady);
        </script>
     ");
}
?>