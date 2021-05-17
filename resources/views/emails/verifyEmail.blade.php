@php
    $emailContent = __($translation['email_verification_content'], [
        'fullName'=>$fullName,
        'link'=>$link,
    ]);
@endphp

{!!   $emailContent  !!}