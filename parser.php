<?php

class Parser{

    // We make all functions / variables private except the getter / setter to encapsulate the parsing process

    // Use variables to store values instead of calling functions within other functions.  Avoids tight couplation between functions
    // Strings
    private $file_contents, $json_file_name;
    // Arrays
    private $file_array, $file_assoc_array;

    // Convert uploaded file into a string
    private function get_file_contents($file){
        $file_reference = fopen($file, "r") or die("Can't open file");
        $this->file_contents = fread($file_reference, filesize($file));
        // Find the # of instances where there are comments
        $count = substr_count($this->file_contents, "/*");
        // Delete comments from our string
        for($i = 0; $i < $count; $i++){
            $this->file_contents = $this->delete_all_between("/*", "*/", $this->file_contents);
        }
        fclose($file_reference);
    }

    // Convert our string into an associative array
    private function make_array(){
        // Make an "object" out of every element definition
        $this->file_array = explode("}", $this->file_contents);
        array_pop($this->file_array);
        foreach($this->file_array as $item){
            $css_key_pair = array();
            // Split the element name and the CSS properties that define it
            $element_name = explode("{", $item);
            // Split each individual CSS definition
            $element_css = explode(";", $element_name[1]);
            // Since we're using ";" to split up our definitions, last array is empty so we remove it
            array_pop($element_css);
            // Turn each CSS definition into an associative array; key is what is being defined while value is definition
            foreach($element_css as $other_item){
                $sub_key = explode(":", $other_item);
                $css_key_pair[trim($sub_key[0])] = trim($sub_key[1]);
            }
            $this->file_assoc_array[trim($element_name[0])] = $css_key_pair;
        }
    }

    private function save_json($file_name){
        // Tidy up the file name
        $length = strlen($file_name);
        $this->json_file_name = substr($file_name, 0, $length - 4);
        $this->json_file_name = $this->json_file_name . ".json";
        // Create and write to new file
        $file = fopen("processed/$this->json_file_name", "w");
        fwrite($file, json_encode($this->file_assoc_array));
        fclose($file);
    }

    // Helper function that deletes all content between beginning and ending points
    private function delete_all_between($beginning, $end, $string) {
        $beginningPos = strpos($string, $beginning);
        $endPos = strpos($string, $end);
        if($beginningPos === false || $endPos === false) {
            return $string;
        }
        $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
        return str_replace($textToDelete, '', $string);
    }

    // Getter / Setter
    public function parse($file, $file_name){
        $this->get_file_contents($file);
        $this->make_array();
        $this->save_json($file_name);
        // Organize returned JSON object into JSON compliant format
        $returned_json = array("success" => true, "status" => 200, "css_file" => $file_name, "json_file" => $this->json_file_name,
        "data" => $this->file_assoc_array);
        return json_encode($returned_json);
    }

}

?>