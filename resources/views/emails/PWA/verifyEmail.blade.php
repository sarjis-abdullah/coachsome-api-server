@php
    $emailContent = __($translation['pwa_email_verification_content'], [
        'otp'=>$otp,
    ]);
@endphp

{!!   $emailContent  !!}