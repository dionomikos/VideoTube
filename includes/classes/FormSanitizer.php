<?php 
    class FormSanitizer {
        public static function sanitizeFormString($inputText) {
            $inputText = strip_tags($inputText);
            $inputText = str_replace(" ", "", $inputText); 
            // If you wish the user to be able to enter name and middlename you can use trim. Trim method takes out the spaces from start & beginning
            $inputText = strtolower($inputText);
            $inputText = ucfirst($inputText); // Turns the first letter of our name to uppercase
            return $inputText;
        }

        public static function sanitizeFormUsername($inputText) {
            $inputText = strip_tags($inputText);
            $inputText = str_replace(" ", "", $inputText); 
        
            return $inputText;
        }

        public static function sanitizeFormPassword($inputText) {
            $inputText = strip_tags($inputText);
            return $inputText;
        }

        public static function sanitizeFormEmail($inputText) {
            $inputText = strip_tags($inputText);
            $inputText = str_replace(" ", "", $inputText);
            return $inputText;
        }
    }
?>