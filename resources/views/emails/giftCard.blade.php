@php
    $pdf = __($translations['pdf_template_gift_card'], [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'recipentName' => $recipentName,
        'code' => $code,
        'value' => $value,
        'currency' => $currency,
    ]);
@endphp

{!!  $pdf !!}




