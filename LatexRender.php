<?php

interface LatexRender {

    function renderLatexMath($latexMath, $outputFilename);
}

class NativeLatexRender implements LatexRender {

    function renderLatexMath($latexMath, $outputFilename) {
        return self::createPng($latexMath, $outputFilename);
    }

    public static function createPng($latexMath, $outputFilename) {
        $tmpDir = self::createTmpDirectory();
        $old_cwd = getcwd();
        chdir($tmpDir);

        $latexMath = self::remakeLatexMath($latexMath);
        $latexDoc = self::getLatexDocument($latexMath);
        file_put_contents("doc.tex", $latexDoc);

        exec(NATIVE_LATEX_PATH . " doc.tex");
        exec(NATIVE_DVIPNG_PATH . " -T tight -x 1200 doc.dvi");

        chdir($old_cwd);
        if (file_exists($tmpDir . "doc1.png")) {
            copy($tmpDir . "doc1.png", $outputFilename);
            exec("rm -rf " . $tmpDir);
            return $outputFilename;
        }
        return false;
    }

    private static function createTmpDirectory() {
        $tmpFile = tempnam(sys_get_temp_dir(), 'latex_tmp_');
        unlink($tmpFile);

        $tmpDir = $tmpFile . "/";
        mkdir($tmpFile . "/");
        return $tmpDir;
    }

    private static function remakeLatexMath($latexMath) {
        $latexMath = str_replace("\\times", "\\textcolor{lgray}{\\times}", $latexMath);
        return $latexMath;
    }

    private static function getLatexDocument($latexMath) {
        return <<<LATEX
\\documentclass{article}
\\usepackage{amsmath}
\\usepackage{color}
\\usepackage[usenames,dvipsnames]{xcolor}
\\pagestyle{empty}

\\definecolor{lgray}{gray}{0.6}

\\begin{document}
\\[{$latexMath}\\]
\\end{document}
LATEX;
    }
} 

class JLatexMathRender implements LatexRender {

    function renderLatexMath($latexMath, $outputFilename) {
        $cmd = "java -jar " . JLATEXMATH_PATH
            . " " . escapeshellarg($latexMath)
            . " " . escapeshellarg($outputFilename);

        exec($cmd, $out, $ret);
        return file_exists($outputFilename);
    }
}