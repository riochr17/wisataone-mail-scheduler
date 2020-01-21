<?php

function wisataone_X1_time_diff_by_now($t1) {
    $date1 = new DateTime($t1, new DateTimeZone('Asia/Jakarta'));
    $date2 = new DateTime();
    return ($date1->getTimestamp() - $date2->getTimestamp());
}

function wisataone_X1_time_diff_add($t1, $seconds) {
    $date1 = new DateTime($t1, new DateTimeZone('Asia/Jakarta'));
    return ($date2->getTimestamp() + $seconds);
}

function wisataone_X1_formatRp($angka) {
    return 'Rp '.strrev(implode('.',str_split(strrev(strval((int)$angka)),3)));
}

function formatElapsedTS($ts) {
    if ($ts <= 0) {
        return "sekarang juga";
    }
    
    $seconds = $ts;
    $hours = floor($ts / 3600);
    $ts -= $hours * 3600;
    $minutes = floor($ts / 60);
    $ts -= $minutes * 60;

    $days = floor($seconds / (24 * 60 * 60));
    $hours %= 24;
    return "$days hari, $hours jam $minutes menit $ts detik";
}

function wisataone_X1_time_diff_add_and_by_now($t1, $seconds) {
    $date1 = new DateTime($t1);
    $dateAddedTs = new DateTime();
    $dateAddedTs->setTimestamp($date1->getTimestamp() + $seconds);
    $dateAddedTs->setTimezone(new DateTimeZone('Asia/Jakarta'));

    $currentDate = new DateTime();
    $currentDate->setTimezone(new DateTimeZone('Asia/Jakarta'));
    
    return $dateAddedTs->getTimestamp() - $currentDate->getTimestamp();
}

function wisataone_X1_time_diff_substract_and_by_now($t1, $seconds) {
    $date1 = new DateTime($t1);
    $dateSubstractedTs = new DateTime();
    $dateSubstractedTs->setTimestamp($date1->getTimestamp() - $seconds);
    $dateSubstractedTs->setTimezone(new DateTimeZone('Asia/Jakarta'));

    $currentDate = new DateTime();
    $currentDate->setTimezone(new DateTimeZone('Asia/Jakarta'));

    // echo $dateSubstractedTs->format(DATE_RFC2822);
    // echo '<br/>';
    // echo $currentDate->format(DATE_RFC2822);
    // echo '<br/>';
    
    return $dateSubstractedTs->getTimestamp() - $currentDate->getTimestamp();
}

function wisataone_X1_time_add_by_days($t1, $days) {
    $date1 = DateTime::createFromFormat('Y-m-d H:i:s', $t1);
    $date1->setTimestamp($date1->getTimestamp() + $days * 60 * 60 * 24);
    $date1->setTimezone(new DateTimeZone('Asia/Jakarta'));
    return $date1->format('d M Y');
}
