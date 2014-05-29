<?php
defined ('ACCESS') || die ('Neautorizovany pristup!');
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo DETAILS_PAGE; ?></title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="html/normalize.css">
        <link rel="stylesheet" href="html/style.css">
    </head>
    <body>
        <?php view_logout (); ?>

        <section class="details">
            <p>
                <?php
                printf ('Vysledek: %s', $details->status);
                ?>
            </p>
        </section>
    </body>
</html>