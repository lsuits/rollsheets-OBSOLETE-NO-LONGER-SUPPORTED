<?php

// Converts '2009SummerAAAS205020799' to 'AAAS 2050' (for example)
function get_formatted_shortname($shortname) {
    $matches = array();

    preg_match('/([A-Z]{1,4})(\d{4})/', $shortname, $matches);

    $out = $matches[1] . ' ' . $matches[2];

    if ($out == ' ') {
        return $shortname;
    } else {
        return $out;
    }
}

if (php_sapi_name() == 'cli') {
    assert(get_formatted_shortname('2009SummerAAAS205020799') == 'AAAS 2050');
    assert(get_formatted_shortname('2009SummerAAA205020799') == 'AAA 2050');
    assert(get_formatted_shortname('2009SummerAA205020799') == 'AA 2050');
    assert(get_formatted_shortname('2009SummerA205020799') == 'A 2050');
}
