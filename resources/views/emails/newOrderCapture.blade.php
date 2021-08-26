@php
    $emailContent = __($translations['email_new_order_capture'], [
       'packageName' => $packageName,
       'orderId' => $orderId,
       'customerName' => $customerName,
       'coachName' => $coachName,
       'price' => $price,
       'packageName' => $packageName,
       'orderListUrl' => $orderListUrl,
       'coachsomeEmailAddress' => $coachsomeEmailAddress,
       'termsUrl' => $termsUrl,
       'clientHomeUrl' => $clientHomeUrl,
       'coachsomeLinkedinUrl' => $coachsomeLinkedinUrl,
       'coachsomeFacebookUrl' => $coachsomeFacebookUrl,
       'linkedinIconUrl' => $linkedinIconUrl,
       'facebookIconUrl' => $facebookIconUrl,
       'logoIconUrl' => $logoIconUrl,
       'logoUrl' => $logoUrl,
    ]);
@endphp
{!!  $emailContent !!}

{{--<table width="100%" bgcolor="#E5E5E5">--}}
{{--    <tbody>--}}
{{--    <tr>--}}
{{--        <td style="font-family: Roboto, RobotoDraft, Helvetica, Arial, Bangla422, sans-serif;">--}}
{{--            <br><br>--}}
{{--            <table width="650" align="center" cellspacing="10" cellpadding="15" bgcolor="#ffffff"--}}
{{--                   style="padding: 20px 10px">--}}
{{--                <tbody>--}}
{{--                <tr>--}}
{{--                    <td style="font-family: Roboto, RobotoDraft, Helvetica, Arial, Bangla422, sans-serif;">--}}
{{--                        <p>--}}
{{--                            <a href=":clientHomeUrl">--}}
{{--                                <img--}}
{{--                                    style="width: 200px; margin-top: 50px;"--}}
{{--                                    target="_blank"--}}
{{--                                    src=":logoUrl"--}}
{{--                                    alt="Logo"/>--}}
{{--                            </a>--}}
{{--                        </p>--}}
{{--                        <br>--}}
{{--                        <p style="font-size: 24px; line-height: 32px;">--}}
{{--                            New order recieved #:orderId--}}
{{--                        </p>--}}
{{--                        <p style="font-family: Open Sans;font-style: normal;font-weight: normal;font-size: 14px;line-height: 24px;color: #333333;">--}}
{{--                            Congratulations Team Coachsome - a new order has been placed on your platform!--}}
{{--                        </p>--}}
{{--                        <p>--}}
{{--                            <u>Order details:</u>--}}
{{--                            <br>--}}
{{--                            <span--}}
{{--                                style="color: #333333;font-family: Open Sans;font-style: normal;font-weight: bold;font-size: 14px;line-height: 24px;">--}}
{{--                            Order ID:--}}
{{--                            </span>--}}
{{--                            :orderId--}}
{{--                            <br>--}}
{{--                            <span--}}
{{--                                style="color: #333333;font-family: Open Sans;font-style: normal;font-weight: bold;font-size: 14px;line-height: 24px;">--}}
{{--                            Customer name:--}}
{{--                            </span>--}}
{{--                            :customerName--}}
{{--                            <br>--}}
{{--                            <span--}}
{{--                                style="color: #333333;font-family: Open Sans;font-style: normal;font-weight: bold;font-size: 14px;line-height: 24px;">--}}
{{--                            Coach name:--}}
{{--                            </span>--}}
{{--                            :coachName--}}
{{--                            <br>--}}
{{--                            <span--}}
{{--                                style="color: #333333;font-family: Open Sans;font-style: normal;font-weight: bold;font-size: 14px;line-height: 24px;">--}}
{{--                            Package name:--}}
{{--                            </span>--}}
{{--                            :packageName--}}
{{--                            <br>--}}
{{--                            <span--}}
{{--                                style="color: #333333;font-family: Open Sans;font-style: normal;font-weight: bold;font-size: 14px;line-height: 24px;">--}}
{{--                            Price:--}}
{{--                            </span>--}}
{{--                            :price--}}
{{--                        </p>--}}

{{--                        <p>--}}
{{--                            <a href=":orderListUrl"--}}
{{--                               style="text-align:center;font-family: Roboto, RobotoDraft, Helvetica, Arial, Bangla422, Open Sans, sans-serif; color: rgb(255, 255, 255);line-height: 24px; font-weight: 400; text-decoration: none; font-size: 13px; display: inline-block;padding: 8px 16px 8px 16px;background-color: #FDBF00; border-radius: 20px; min-width: 90px;"--}}
{{--                               target="_blank">--}}
{{--                                Go to order list</a>--}}
{{--                        </p>--}}

{{--                        <br>--}}
{{--                        <br>--}}

{{--                        <p style="border: 0.5px solid rgba(0, 0, 0, 0.24);"></p>--}}

{{--                        <p style="margin-top: 20px;">--}}
{{--                            <a href=":coachsomeLinkedinUrl" style="text-decoration: none;">--}}
{{--                                <img style="margin-right: 20px;" src=":linkedinIconUrl"/>--}}
{{--                            </a>--}}
{{--                            <a href=":coachsomeFacebookUrl" style="text-decoration: none;">--}}
{{--                                <img src=":facebookIconUrl"/>--}}
{{--                            </a>--}}
{{--                        </p>--}}
{{--                        <p>--}}
{{--                            <a href=":clientHomeUrl">--}}
{{--                                <img target="_blank" src=":logoIconUrl" alt="Logo Icon"/>--}}
{{--                            </a>--}}
{{--                        </p>--}}
{{--                        <p>--}}
{{--                            <span style="color: #999999; font-size: 12px; line-height: 16px;">--}}
{{--                                Copyright © 2020 Coachsome Aps.--}}
{{--                            </span>--}}
{{--                            <br>--}}
{{--                            <span style=" font-size: 12px; line-height: 16px;">--}}
{{--                                <span style="color: #999999;">--}}
{{--                                    Coachsome is your security for a well executed trainning. Please read our--}}
{{--                                </span>--}}
{{--                                <span style=" font-size: 12px; line-height: 16px;">--}}
{{--                                   <a href=":termsUrl" style="text-decoration: none;color:#102B52;">--}}
{{--                                        Terms of use.--}}
{{--                                   </a>--}}
{{--                                </span>--}}
{{--                            </span>--}}
{{--                            <br>--}}
{{--                        </p>--}}
{{--                    </td>--}}
{{--                </tr>--}}
{{--                </tbody>--}}
{{--            </table>--}}
{{--            <br><br>--}}
{{--        </td>--}}
{{--    </tr>--}}
{{--    </tbody>--}}
{{--</table>--}}
