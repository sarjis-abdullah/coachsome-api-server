@php
    $emailContent = __($translations['pwa_password_reset_email'], [
         'otp'=> $otp,
    ]);
@endphp

{!!   $emailContent  !!}