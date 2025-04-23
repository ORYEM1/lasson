<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title??'ForYou' ?></title>
    <link rel="icon" href="/resources/images/logo.png" style="width:80px;height:80px;margin:auto;">

    <!-- jQuery -->
    <script src="/resources/lib/jquery-3.7.1.min.js"></script>

    <!--Font Awesome-->
    <link rel="stylesheet" href="/resources/lib/font-awesome/css/font-awesome.min.css">

    <!--DataTables-->
    <link rel="stylesheet" href="/resources/lib/DataTables/datatables.min.css">
    <script src="/resources/lib/DataTables/datatables.min.js"></script>

    <!--jQuery Modal-->
    <link rel="stylesheet" href="/resources/lib/jQueryModal/jQuery.modal.min.css">
    <script src="/resources/lib/jQueryModal/jQuery.modal.min.js"></script>

    <!-- Custom Javascript -->
    <script src="/resources/js/main.js"></script>

    <!--Custom style sheets-->
    <link rel="stylesheet" href="/resources/css/menu.css">
    <link rel="stylesheet" href="/resources/css/style.css">
    <link rel="stylesheet" href="/resources/css/loader.css">
    <link rel="stylesheet" href="/resources/css/header.css">
    <link rel="stylesheet" href="/resources/css/footer.css">
    <link rel="stylesheet" href="/resources/css/data_tables.css">
    <link rel="stylesheet" href="/resources/css/buttons.css">
    <link rel="stylesheet" href="/resources/css/jquery_modal.css">
    <link rel="stylesheet" href="/resources/css/form.css">


    <?php
    if(isset($data_tables_config))
    {
        ?>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#data').DataTable(<?php echo $data_tables_config??'' ?>);
            } );
        </script>
        <?php
    }
    ?>
</head>
<body>
<!--Beginning of header-->
<header id="page_header">
    <div id="top_space"></div>
    <div id="header_container">
        <?php echo view('menu') ?>
        <!-- Beginning of #logo_containter -->

    </div>
    <!-- End of #logo_containter -->
</header>
<!-- End of header -->

<!--Beginning of #separator-->
<section id="separator">
</section>
<!--End of #separator-->

<!-- Beginning of #content -->
<section id="content">
    <?php echo isset($content_view)?view($content_view):'' ?>


</section>
<!-- End of #content -->

<!-- Beginning of #footer -->
<footer id="page_footer">
    <p>&copy; Copyrights Privacy and Protection <a href="">view the terms and conditions  </a>   </p>
</footer>
<!-- End of #footer -->

<!-- Beginning of #processing -->
<div id="processing">
    <div id="loader_container">
        <div class="loader"></div>
    </div>
</div>
<!-- End of #processing -->

<!-- Beginning of #uploading -->
<div id="uploading">
    <div id="uploading_loader_container">
        <div class="uploading_loader"></div>
    </div>
</div>
<!-- End of #uploading -->

<!-- Beginning of #jQuery Modal -->
<div id="jquery_modal" class="modal">
    <header></header>
    <main></main>
    <footer></footer>
</div>
<!-- End of #jQuery Modal -->

</body>

</html>