@php
    $emailContent = __($translation['pending_booking_request_mail_content'], [
        'fullName'=>$fullName,
        'link'=>$link,
    ]);
@endphp

{!!   $emailContent  !!}






