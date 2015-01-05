<?php

class MathCalc {

    static public function compute($input) {
        if (!file_exists(MATH_CALC_PATH))
            throw new Exception("File to exec MathCalc not found");
    
        exec(MATH_CALC_PATH . " \"$input\" 2>&1", $output, $returnCode);
        
        if ($returnCode == 0) {
            $output_c = implode($output);
            $startJsonIndex = strpos($output_c, "{   \"system\"");
            $json = json_decode(substr($output_c, $startJsonIndex));
            
            if ($startJsonIndex != 0) {
                print "<div class='alert alert-warning'>" .
                    "<code>" . implode("<br>", $output) . "</code>" .
                    "</div>";
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = "Invalid MathCalc output: <br/>";
                $error .= "<code>" . implode("<br>", $output) . "</code>";
                throw new Exception($error);
            }

            return $json;
        } else {
            $error = "MathCalc crashed <code>$returnCode</code>: <br/>";
            $error .= "<code>" . implode("<br>", $output) . "</code>";
            throw new Exception($error);
        }
    }
} 
