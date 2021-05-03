<!DOCTYPE HTML>
<html>
    <?php include('../src/head.inc'); ?>
    <?php include('../src/session.inc'); ?>
    <body>
        <!-- Alert Div -->
        <div class="alert alert-primary alert-style" data-id="alert-notif" role="alert"></div>

        <!-- Overlay. "Block" actions with the webpage -->
        <div class="overlay" data-id="overlay"></div>

        <!-- Header -->
        <nav class="navbar sticky-top navbar-dark bg-header-navbar mt-n1 shadow-lg p-35">
            <a class="navbar-brand" href="javascript:void(0)">
                <div class="header-logo"></div>
                <div class="header-title">
                    Autogestión para Matriculados
                    <span class="header-subtitle">Colegio de Psicopedagogos de Córdoba</span>
                </div>
            </a>
        </nav>

        <!-- Page Content -->
        <div class="container mt-3" data-id="container">
            <?php include('../src/system.php'); ?>
        </div>
        <?php include('../src/footer.inc'); ?>
    </body> 
</html>