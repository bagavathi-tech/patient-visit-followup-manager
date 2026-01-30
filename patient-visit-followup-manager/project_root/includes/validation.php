<?php
// Check leap year
function isLeapYear($year) {
    return ($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0);
}

// Get birthday date for current year (handles Feb 29)
function getBirthdayThisYear($dob) {
    $dobDate = new DateTime($dob);
    $currentYear = date('Y');

    // If DOB is Feb 29 and current year is NOT leap year
    if ($dobDate->format('m-d') === '02-29' && !isLeapYear($currentYear)) {
        return $currentYear . '-02-28';
    }

    return $currentYear . '-' . $dobDate->format('m-d');
}