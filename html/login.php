<?php
defined ('ACCESS') || die ('Neautorizovany pristup!');
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo LOG_IN_PAGE; ?></title>
        <link rel="stylesheet" href="html/normalize.css">
        <link rel="stylesheet" href="html/style.css">
    </head>
    <body>
        <section>
            <form name="login" action="index.php" method="post" accept-charset="utf-8">
                <fieldset class="loginform cf">
                    <legend><?php echo LOG_IN_PAGE; ?></legend>
                    <ul>
                        <li>
                            <label for="username"><?php echo USERNAME; ?></label>
                            <input type="text" name="username" id="username" placeholder="jmeno.prijmeni" required>
                        </li>
                        <li>
                            <label for="password"><?php echo PASSWORD; ?></label>
                            <input type="password" id="password" name="password" placeholder="heslo" required></li>
                        <li>
                            <input class="btn" type="submit" name="<?php echo ACTION; ?>" value="<?php echo ACTION_LOGIN; ?>">
                        </li>
                        <?php if (isset ($error)): ?>
                        <li class="warning">
                                <p><?php echo $error; ?></p>
                            </li>
                        <?php endif; ?>
                    </ul>
                </fieldset>
            </form>
        </section>
    </body>
</html>