<?php

function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}
function endsWith($haystack, $needle) {
    return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== false;
}

function renderLatexMath($latexMath) {
    $hash = sha1($latexMath);
    $outputFilename = "cache/$hash.png";

    if (!NO_CACHE_LATEX && file_exists($outputFilename))
        return $outputFilename;

    $render = null;
    if (LATEX_RENDER == "NATIVE")
        $render = new NativeLatexRender();
    else if (LATEX_RENDER == "JLATEXMATH")
        $render = new JLatexMathRender();
    else
        throw new RuntimeException("Invalid LATEX_RENDER, must be 'NATIVE' or 'JLATEXMATH'");

    if ($render->renderLatexMath($latexMath, $outputFilename) === false)
        throw new Exception("Failed render LatexMath");

    return $outputFilename;
}