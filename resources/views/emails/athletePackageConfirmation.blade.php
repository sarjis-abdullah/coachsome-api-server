@php
    $emailContent = __($translations['email_athlete_confirmation_package_content'], [
        'packageName' => $packageName,
        'orderId' => $orderId,
        'packageBuyerName' => $packageBuyerName,
        'chatNowUrl' => $chatNowUrl,

        'packageQty' => $packageQty,
        'serviceFeeQty' => $serviceFeeQty,
        'packagePrice' => $packagePrice,
        'serviceFee' => $serviceFee,
        'grandTotal' => $grandTotal,
        'vat' => $vat,

        'paymentReceiveDate' => $paymentReceiveDate,

        'termsUrl' => $termsUrl,
        'clientHomeUrl' =>$clientHomeUrl,
        'coachsomeLinkedinUrl' =>$coachsomeLinkedinUrl,
        'coachsomeFacebookUrl' =>$coachsomeFacebookUrl,
        'coachsomeEmailAddress' =>$coachsomeEmailAddress,
        'linkedinIconUrl' =>$linkedinIconUrl,
        'facebookIconUrl' =>$facebookIconUrl,
        'logoIconUrl' =>$logoIconUrl,
        'logoUrl' =>$logoUrl,

    ]);
@endphp
{!!   $emailContent  !!}


{{--<table width="100%" bgcolor="#E5E5E5">--}}
{{--    <tbody>--}}
{{--    <tr>--}}
{{--        <td style="font-family: Roboto, RobotoDraft, Helvetica, Arial, Bangla422, sans-serif;">--}}
{{--            <br><br>--}}
{{--            <table width="650" align="center" cellspacing="10" cellpadding="15" bgcolor="#ffffff"--}}
{{--                   style="padding: 20px 10px">--}}
{{--                <tbody>--}}
{{--                <tr>--}}
{{--                    <td bgcolor="#ffffff"--}}
{{--                        style="font-family: Roboto, RobotoDraft, Helvetica, Arial, Bangla422, sans-serif;">--}}
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
{{--                            Your request: “:orderId” is confirmed!--}}
{{--                        </p>--}}
{{--                        <p>Dear :packageBuyerName, </p>--}}
{{--                        <p>--}}
{{--                            We are glad to inform that your order “:orderId” has been confirmed. Your next step is to--}}
{{--                            schedule--}}
{{--                            your first session with your coach.--}}
{{--                        </p>--}}
{{--                        <p>--}}
{{--                            <!--Go to chat page-->--}}
{{--                            <a href=":chatNowUrl"--}}
{{--                               style="text-align:center;font-family: Roboto, RobotoDraft, Helvetica, Arial, Bangla422, Open Sans, sans-serif; color: rgb(255, 255, 255);line-height: 24px; font-weight: 400; text-decoration: none; font-size: 13px; display: inline-block;padding: 8px 16px 8px 16px;background-color: #FDBF00; border-radius: 20px; min-width: 90px;"--}}
{{--                               target="_blank">--}}
{{--                                Go to chat</a>--}}
{{--                        </p>--}}
{{--                        <p style="border: 0.5px solid rgba(0, 0, 0, 0.24);"></p>--}}

{{--                        <p>--}}
{{--                            <b style="color: #666666;font-size: 16px;line-height: 24px;">Product Details</b>--}}
{{--                            <br>--}}
{{--                            <span style="color: #666666;font-size: 16px;line-height: 24px;">--}}
{{--                                Payment recieved: :paymentReceiveDate--}}
{{--                            </span>--}}
{{--                        </p>--}}
{{--                        <p>--}}
{{--                        <table style="width: 100%;">--}}
{{--                            <tbody>--}}
{{--                            <tr style="box-shadow: inset 0px -1px 0px rgba(0, 0, 0, 0.16);">--}}
{{--                                <td style="padding: 10px 0;">PRODUCT NAME</td>--}}
{{--                                <td style="text-align: center;">QTY</td>--}}
{{--                                <td style="text-align: right;">SUBTOTAL</td>--}}
{{--                            </tr>--}}
{{--                            <tr style="box-shadow: inset 0px -1px 0px rgba(0, 0, 0, 0.16);">--}}
{{--                                <td style="padding: 10px 0;">“:packageName”</td>--}}
{{--                                <td style="text-align: center;">:packageQty</td>--}}
{{--                                <td style="text-align: right;">:packagePrice</td>--}}
{{--                            </tr>--}}
{{--                            <tr style="box-shadow: inset 0px -1px 0px rgba(0, 0, 0, 0.16);">--}}
{{--                                <td style="padding: 10px 0;">Coachsome service fee</td>--}}
{{--                                <td style="text-align: center;">:serviceFeeQty</td>--}}
{{--                                <td style="text-align: right;">:serviceFee</td>--}}
{{--                            </tr>--}}
{{--                            <tr style="box-shadow: inset 0px -1px 0px rgba(0, 0, 0, 0.16);">--}}
{{--                                <td></td>--}}
{{--                                <td></td>--}}
{{--                                <td style="text-align: right; padding: 10px 0;color: #999999;font-size: 14px;line-height: 24px;">--}}
{{--                                    GRAND TOTAL--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td style="padding: 10px 0;"></td>--}}
{{--                                <td></td>--}}
{{--                                <td style="text-align: right;font-size: 24px;line-height: 32px;">--}}
{{--                                    :grandTotal--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td style="padding: 10px 0;"></td>--}}
{{--                                <td></td>--}}
{{--                                <td style="text-align: right; color: #EF4026;">HEROF VAT: :vat </td>--}}
{{--                            </tr>--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                        </p>--}}
{{--                        <p style="border: 0.5px solid rgba(0, 0, 0, 0.24);"></p>--}}

{{--                        <p style="margin-top: 50px">--}}
{{--                            <span style="font-size: 16px; color: #333333;">--}}
{{--                            Best Regards,--}}
{{--                            </span>--}}
{{--                            <br>--}}
{{--                            <span style="font-size: 20px; color: #333333;">--}}
{{--                            Team Coachsome--}}
{{--                            </span>--}}
{{--                        </p>--}}
{{--                        <p style="margin-bottom: 50px;">--}}
{{--                            <span style="color: #999999;">--}}
{{--                            For questions, you can reach out to us at:--}}

{{--                            </span>--}}
{{--                            <span style="text-decoration:none;color:#102B52">--}}
{{--                                :coachsomeEmailAddress--}}
{{--                            </span>--}}
{{--                        </p>--}}

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
