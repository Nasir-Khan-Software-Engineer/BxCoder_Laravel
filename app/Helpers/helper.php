<?php

use Illuminate\Support\Carbon;

function formatDate($date){
    return Carbon::parse($date)->setTimezone(session('accountInfo.timezone'))->format('d M Y');
}

function formatTime($date){
    return Carbon::parse($date)->setTimezone(session('accountInfo.timezone'))->format('g:i A');
}

function formatDateAndTime($date){
    return Carbon::parse($date)->setTimezone('Asia/Dhaka')->format('d M Y g:i A');
}

function formatMoney(){

}

