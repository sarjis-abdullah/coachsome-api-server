@php
    $user = \Illuminate\Support\Facades\Auth::user();
    $emailContent = __($translations['email_template_send_email_to_contact_user'], [
         'coachName'=> $user->first_name . " ".$user->last_name,
         'athleteName'=> $athleteName,
         'activateContactUserAccountUrl'=> env('APP_SERVER_DOMAIN')."/api/navigate-contact-user?email=".$contactUser['email']."&token=".$contactUser['token'],
         'termsUrl' => env('APP_CLIENT_DOMAIN_TERMS_PAGE'),
         'clientHomeUrl' => env('APP_CLIENT_DOMAIN'),
         'coachsomeLinkedinUrl' => "https://www.linkedin.com/company/coachsome/",
         'coachsomeFacebookUrl' => "https://www.facebook.com/coachsome/app/212104595551052/?ref=page_internal",
         'linkedinIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/linkedin.png',
         'facebookIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/facebook.png',
         'logoIconUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/icons/logo.png',
         'logoUrl' => env('APP_SERVER_DOMAIN_PUBLIC_PATH') . '/assets/images/logos/logo.png',
    ]);
@endphp

{!!   $emailContent  !!}
