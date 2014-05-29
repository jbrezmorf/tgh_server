<?php
defined ('ACCESS') || die ('Neautorizovany pristup!');
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo SEND_CODE_PAGE; ?></title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="html/normalize.css">
        <link rel="stylesheet" href="html/style.css">
        <script src="html/jquery.js"></script>

        <script>
            function isValid () {
                var problem = $ ('#problem').val ();
                var language = $ ('#language').val ();
                var source = $ ('#source').val ();

                if (source.trim().length == 0) {
                    $ ('#source').focus ();
                    return false;
                }


                var data = {};
                data.problem = problem;
                data.language = language;
                data.source = source;
                data.action = '<?php echo ACTION_SEND_CODE; ?>';
                data.ajax = 1;
                $.post ('index.php', data, function (data, status, xhr) {
                    $ ('#loading').hide ();        

                    console.log (data);
                    result = JSON.parse (data);
                    if (result.error) {
                        $('#result-status').text("Data parsing error.");
                        alert (result.error);
                        return;
                    }

                    $('#result-status').text(result['result']);
                    $('#result-status').addClass('status-wrong-answer');
                    $('#result-status').addClass('status-'+(result['result'].replace (/\s+/g, '-')));
                    $('#result-link').html(result['result_link']);
                    $('#result-link').find("a").attr("target","_blank");

                    $('#result-time').text(result['time']);
                    $('#result-mem').text(result['mem']);
                    $('#result-id').text(result['id']);
                    $('#result-date').text(result['date']);

                    $('#stdout-link').attr("href", result['directory']+"/stdout");
                    $('#judge-link').attr("href", result['directory']+"/judge.out");

                    $ ('#result').show ();

                });
                        
                //$ ('#submit-btn').attr('disabled','disabled');
                //$ ('#submit-btn').hide (); 
                $ ('#loading').show ();
                $ ('#loading').css ({'display':'inline-block'});
                return false;
            }
        </script>
    </head>
    <body>
        <?php view_logout (); ?>

        <section class="send-code">

            <form name="send-code" action="index.php" method="post" accept-charset="utf-8">
                <p> 
                <b> Aktualizováno 29.5.</b> Problémy hlaste na: jan.brezina at tul.cz. <br> 
                <a target="_blank" href="http://atrey.karlin.mff.cuni.cz/~morf/vyuka/tgh/index.html">Stránka předmětu TGH</a> -- zadání 2014 a další poznámky.
                </p>
                <p>
                    <script> function update_link() {
                        var e=document.getElementById("problem");  
                        document.getElementById("problem_link").href = "http://www.spoj.com/problems/" + e.options[e.selectedIndex].value;                         
                    }
                    </script>    
                    <label for="problem"><?php echo SELECT_PROBLEM; ?></label>
                    <select id="problem" name="problem" onchange="update_link()">
                        <?php foreach (getProblems () as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <a id="problem_link" target="_blank" href="">  Zadání </a>  anglicky (SPOJ) a příklad vstupu a výstupu.
                    <script> 
                      update_link();
                    </script>
                    
                </p>
                <p>
                    <label for="language"><?php echo SELECT_LANG; ?></label>
                    <select id="language" name="language">
                        <?php foreach (getLanguages () as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <label for="lang"><?php echo INSERT_CODE; ?></label>
                    <textarea id="source" name="source" required></textarea>
                </p>
                <p>
                    <input class="btn" id="submit-btn" type="submit" onclick="return isValid ();" name="<?php echo ACTION; ?>" value="<?php echo ACTION_SEND_CODE; ?>">
                    <span id="loading"><img src="./html/loading.gif" /><span>Loading</span></span>
                    <span class="clr"></span>
                </p>
            </form>
        </section>
        <section id="">

        </section>
        <section id="result">
            <div id="result-holder">
                <p> 
                  <span id="result-status-title"> Result:</span> 
                  <span id="result-status"></span></br>
                  Návratový kód: <span id="result-link"></span> 
                </p>
                <hr width="98%">
                <table>
                    <thead>
                        <tr>
                            <th width="25%"></th>
                            <th width="75%"></th>
                        </tr>
                    </thead>
                    <tbody id="result-table">
                       <tr> <td>doba běhu:</td> <td id="result-time"></td></tr>
                       <tr> <td>paměť:</td> <td id="result-mem"></td></tr>
                       <tr> <td>id:</td> <td id="result-id"></td></tr>
                       <tr> <td>datum:</td> <td id="result-date"></td></tr>
                    </tbody>
                </table>    
                <hr width="98%">  
                <p>
                  <a id="stdout-link" target="_blank" href="">STDOUT a STDERR</a>                  
                  <a id="judge-link" target="_blank" href="">Výsledky testů</a>
                </p>
            </div>
        </section>
    </body>
</html>