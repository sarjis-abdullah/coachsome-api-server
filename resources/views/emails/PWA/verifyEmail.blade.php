@php
    $emailContent = __($translation['pwa_email_verification_content'], [
        'link'=>$link,
    ]);
@endphp

{!!   $emailContent  !!}