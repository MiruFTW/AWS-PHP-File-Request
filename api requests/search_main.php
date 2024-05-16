<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Search Main</title>
<!-- BOOTSTRAP STYLES-->
<link href="assets/css/bootstrap.css" rel="stylesheet" />
<!-- FONTAWESOME STYLES-->
<link href="assets/css/font-awesome.css" rel="stylesheet" />
   <!--CUSTOM BASIC STYLES-->
<link href="assets/css/basic.css" rel="stylesheet" />
<!--CUSTOM MAIN STYLES-->
<link href="assets/css/custom.css" rel="stylesheet" />
<!--[if lt IE 9]><script src="scripts/flashcanvas.js"></script><![endif]-->
<!-- JQUERY SCRIPTS -->
<script src="assets/js/jquery-1.10.2.js"></script>
<!-- BOOTSTRAP SCRIPTS -->
<script src="assets/js/bootstrap.js"></script>

<style>
    .default {background-color:#E1E1E1;}
</style>
<script>
    function addFocus(div){
        document.getElementById(div).classList.remove("default");
    }
    function removeFocus(div){
        document.getElementById(div).classList.add("default");
    }
</script>
</head>
<body>
<?php
echo '<div id="page-inner">';
echo '<h1 class="page-head-line">Select the type of seach criteria</h1>';
echo '<div class="panel-body">';
echo '<div id="1" class="alert alert-info default text-center" onmouseover="addFocus(this.id)" onmouseout="removeFocus(this.id)"><h3>Search By Loan Number</h3>';
echo '<a href="search_loan_num.php" class="btn btn-default">Search By Loan Number</a></div>';
echo '<div id="2" class="alert alert-info default text-center" onmouseover="addFocus(this.id)" onmouseout="removeFocus(this.id)"><h3>Search By File Type</h3>';
echo '<a href="search_file_type.php" class="btn btn-default">Search by File Type</a></div>';
echo '<div id="3" class="alert alert-info default text-center" onmouseover="addFocus(this.id)" onmouseout="removeFocus(this.id)"><h3>Search By File Date</h3>';
echo '<a href="search_file_date.php" class="btn btn-default">Search by File Date</a></div>';
echo '<div id="4" class="alert alert-info default text-center" onmouseover="addFocus(this.id)" onmouseout="removeFocus(this.id)"><h3>List all Files</h3>';
echo '<a href="search_all.php" class="btn btn-default">List all Files</a></div>';
echo '</div>';
echo '</div>';
?>
</body>
</html>