<?php

function wisataone_X1_get_template($filename, $datas) {
    $filepath = plugin_dir_path(__FILE__) . "/mail-template/{$filename}";
    $myfile = fopen($filepath, "r") or die("Unable to open file!");
    $filedata = fread($myfile, filesize($filepath));
    fclose($myfile);

    $pattern = '/{{\s*([A-Za-z0-9_]+)\s*}}/';
    preg_match_all($pattern, $filedata, $output_array);

    $reps = [];
    $strs = [];
    foreach ($output_array[0] as $value) { array_push($strs, '/' . $value . '/'); }
    foreach ($output_array[1] as $value) { array_push($reps, $datas[$value]); }
    return preg_replace($strs, $reps, $filedata);
}