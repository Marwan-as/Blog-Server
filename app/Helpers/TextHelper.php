<?php

namespace App\Helpers;

class TextHelper
{
    public static function generateLorem($length = 100)
    {
        $lorem = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lacinia odio vitae vestibulum. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta. Mauris massa. Vestibulum lacinia arcu eget nulla.
        \n\n";

        // Split the lorem ipsum text into an array of words
        $words = explode(' ', $lorem);
        $wordCount = count($words);

        // Generate the desired length of lorem text
        $output = '';
        while (str_word_count($output) < $length) {
            $output .= $words[array_rand($words)] . ' ';
        }

        return trim($output);
    }
}
