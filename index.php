<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MathCalc</title>
    <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="icon.ico">

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar-inverse">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                <span class="sr-only">Przełącz nawigacje</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="?p=home">MathCalc</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="?">Strona główna</a></li>
                <li><a href="?p=examples">Przykłady wyrażeń</a></li>
            </ul>
        </div>
    </div>
</nav>

<?php
require_once "config.php";
require_once "MathCalc.php";
require_once "LatexRender.php";
require_once "Utils.inc.php";

$expInput = isset($_GET['i']) ? $_GET['i'] : null;

?>

<div class="container theme-showcase" role="main">

    <form method="get" action="">
        <div class="input-group" style="margin: 30px auto">
            <span class="input-group-addon hidden-xs">Wyrażenie:</span>
            <input type="text" name="i" class="form-control"
                   placeholder="1/4 * (4 - 1/2)" value="<?php print $expInput ?>">
            <span class="input-group-btn">
                <button class="btn btn-primary" type="submit">Oblicz</button>
            </span>
        </div>
    </form>

    <?php
    if ($expInput != null) {
        try {
            $result = MathCalc::compute($expInput);
        } catch(Exception $e) {
            $result = new stdClass;
            $result->error = $e->getMessage();
            $result->error_code = -1;
        }

        if (!isset($result->error)) {
            $dataImages = [
                "Wejście:" => renderLatexMath($result->input->as_latex)
            ];

            $resultLatex = $result->result->as_latex;
            if ($result->result->as_latex != $result->result->decimal) {
                $resultLatex .= endsWith($result->result->decimal, "...") ? " \\approx " : " = ";
                $resultLatex .= $result->result->decimal;
            }
            $dataImages["Wynik:"] = renderLatexMath($resultLatex);

            ?>
            <div class="panel panel-default" id="result_panel">
                <div class="panel-heading">Rezultat obliczeń</div>

                <ul class="list-group">
                    <?php foreach ($dataImages as $header => $image) { ?>
                        <li class="list-group-item">
                            <div class="list-group-item-heading"><?php print $header ?></div>
                            <img src="<?php print $image ?>" class="list-group-item-text"/>
                        </li>
                    <?php } // end of foreach dataImages ?>

                    <li class="list-group-item">
                        <samp class="list-group-item-text">
                            <?php
                            foreach ($result as $groupName => $group) {
                                print "<strong>$groupName</strong>:<br/>\n";
                                foreach ($group as $key => $value)
                                    if ($key != "tokens" && $key != "rpn") {
                                        print str_repeat("&nbsp;", 4)
                                            . "$key: <code>$value</code><br/>\n";
                                    } else {
                                        print str_repeat("&nbsp;", 4) . "$key: ";
                                        foreach (explode(" ", $value) as $token) {
                                            if (!empty($token))
                                                print "<code>$token</code>&nbsp;";
                                        }
                                        print "<br/>\n";
                                    }
                                print "<br/>\n";
                            }
                            ?>
                        </samp>
                    </li>
                </ul>

                <div class="panel-footer" style="padding: 0; text-align: right">
                    <button type="button" class="btn btn-link" style="font-size: 12px">Zgłoś błąd</button>
                </div>
            </div>
        <?php
        } else {
            $errorTips = [
                1 => "Sprawdź poprawność nawiasów.",
                2 => "Sprawdź czy gdzieś nie brakuje operatora.",
                3 => "Sprawdź czy gdzieś nie brakuje argumentów dla któregoś operatora.",
                4 => "Wygląda na to, że użyłeś nieznanego wyrażenia."
            ];
            ?>
            <?php if ($result->error_code != -1) { ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Niestety, wystąpił problem z wyrażeniem.</strong><br>
                    <div>
                        <code>0x<?php print dechex($result->error_code) ?></code>
                        <samp><?php print $result->error ?></samp>
                    </div>
                    <br>
                    <?php
                    print isset($errorTips[$result->error_code])
                        ? $errorTips[$result->error_code] : null;
                    ?>
                </div>
            <?php } else { // end if error_code != -1 ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Wystapił problem z aplikacją.</strong><br>
                    <div>
                        <samp><?php print $result->error ?></samp>
                    </div>
                </div>
            <?php } // end else ?>
        <?php
        }
    }
    ?>

</div>

</body>
</html>
