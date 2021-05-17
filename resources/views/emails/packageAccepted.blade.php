@php
    $emailContent = __($translations['package_accepted_email'], [
         'packageName'=> $packageName,
         'packageBuyerFirstName'=> $packageBuyerFirstName,
         'packageSellerFullName'=> $packageSellerFullName,
         'packageSellerProfileImage'=>$packageSellerProfileImage,
         'chatNowUrl'=>$chatNowUrl,
         'stairCaseUrl'=> $stairCaseUrl,
         'invoiceNumber'=> $invoiceNumber,
         'paymentMethod'=>$paymentMethod,
         'packagePrice'=>$packagePrice,
         'serviceFee'=>$serviceFee,
         'totalPrice'=> $totalPrice,
         'vat'=>$vat,
         'hashTagUrl'=> $hashTagUrl,
         'termsUrl'=>$termsUrl,
         'conditionUrl'=> $conditionUrl,
    ]);
@endphp

{!!   $emailContent  !!}


{{--<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">--}}
{{--<style>--}}

{{--    .email-box {--}}
{{--        display: flex;--}}
{{--        flex-direction: column;--}}
{{--        justify-content: center;--}}
{{--        align-items: center;--}}
{{--    }--}}

{{--    .email {--}}
{{--        border: 1px solid black;--}}
{{--    }--}}

{{--    /*Small devices (landscape phones, 576px and up)*/--}}
{{--    @media (min-width: 576px) {--}}
{{--        .email {--}}
{{--            width: 100%;--}}
{{--        }--}}
{{--        .charge-box{--}}
{{--            width: 100%;--}}
{{--        }--}}
{{--        .email-body-top .message{--}}
{{--            width: 50%;--}}
{{--        }--}}
{{--    }--}}

{{--    /*Medium devices (tablets, 768px and up)*/--}}
{{--    @media (min-width: 768px) {--}}
{{--        .email {--}}
{{--            width: 100%;--}}
{{--        }--}}
{{--        .charge-box{--}}
{{--            width: 100%;--}}
{{--        }--}}
{{--        .email-body-top .message{--}}
{{--            width: 100%;--}}
{{--        }--}}
{{--    }--}}

{{--    /*Large devices (desktops, 992px and up)*/--}}
{{--    @media (min-width: 992px) {--}}
{{--        .email {--}}
{{--            width: 60%;--}}
{{--        }--}}
{{--        .charge-box{--}}
{{--            width: 60%;--}}
{{--        }--}}
{{--        .email-body-top .message{--}}
{{--            width: 50%;--}}
{{--        }--}}
{{--    }--}}

{{--    /*Extra large devices (large desktops, 1200px and up)*/--}}
{{--    @media (min-width: 1200px) {--}}
{{--        .email {--}}
{{--            width: 60%;--}}
{{--        }--}}
{{--        .charge-box{--}}
{{--            width: 60%;--}}
{{--        }--}}

{{--        .email-body-top .message{--}}
{{--           width: 50%;--}}
{{--        }--}}
{{--    }--}}

{{--    .email .email-header {--}}
{{--        display: flex;--}}
{{--        justify-content: center;--}}
{{--        align-items: center;--}}
{{--        height: 69px;--}}
{{--        background: #ECF2F7;--}}
{{--    }--}}

{{--    .email-header .logo{--}}
{{--        height: 27px;--}}
{{--    }--}}

{{--    .email-body .email-body-top {--}}
{{--        display: flex;--}}
{{--        background: rgba(21, 87, 124, 0.8);--}}
{{--        font-family: Helvetica;--}}
{{--        font-style: normal;--}}
{{--        font-size: 14px;--}}
{{--        line-height: 16px;--}}
{{--        color: #ECF2F7;--}}
{{--    }--}}

{{--    .email-body-top{--}}
{{--        padding-top: 80px;--}}
{{--        padding-bottom: 150px;--}}
{{--        display: flex;--}}
{{--        justify-content: center;--}}
{{--    }--}}

{{--    .email-body-top .message{--}}
{{--        display: flex;--}}
{{--        justify-content: center;--}}
{{--        flex-direction: column;--}}
{{--        align-items: center;--}}
{{--    }--}}

{{--    .message .message-name{--}}
{{--        font-family: Helvetica;--}}
{{--        font-style: normal;--}}
{{--        font-weight: bold;--}}
{{--        font-size: 24px;--}}
{{--        line-height: 28px;--}}
{{--        margin-top: 10px;--}}
{{--        margin-bottom: 10px;--}}
{{--    }--}}

{{--    .email-body .email-body-bottom {--}}
{{--        display: flex;--}}
{{--        justify-content: center;--}}
{{--        flex-direction: column;--}}
{{--        align-items: center;--}}
{{--        background: #ECF2F7;--}}
{{--    }--}}

{{--    .profile-image{--}}
{{--        margin-top: -80px;--}}
{{--        height: 150px;--}}
{{--        filter: drop-shadow(0px 4px 4px rgba(0, 0, 0, 0.25));--}}
{{--        border-radius: 16px;--}}
{{--    }--}}

{{--    .email-footer {--}}
{{--        display: flex;--}}
{{--        justify-content: center;--}}
{{--    }--}}

{{--    .email-footer .email-footer-content{--}}
{{--        font-family: Roboto;--}}
{{--        font-style: normal;--}}
{{--        font-weight: normal;--}}
{{--        font-size: 12px;--}}
{{--        line-height: 14px;--}}
{{--        width: 60%;--}}
{{--        text-align: center;--}}
{{--        color: #000000;--}}
{{--        margin-top: 30px;--}}
{{--        margin-bottom: 40px;--}}
{{--    }--}}

{{--    .btn--chat-now {--}}
{{--        background: #D61BA2;--}}
{{--        border-radius: 16px;--}}
{{--        font-family: Helvetica;--}}
{{--        font-style: normal;--}}
{{--        font-weight: bold;--}}
{{--        font-size: 18px;--}}
{{--        line-height: 21px;--}}
{{--        display: flex;--}}
{{--        align-items: center;--}}
{{--        text-align: center;--}}
{{--        padding: 15px 60px;--}}
{{--        color: #F7FAFC;--}}
{{--    }--}}

{{--    .charge-box {--}}
{{--        padding-top: 20px;--}}
{{--        box-shadow: -1px 0px 0px 0px #6f8098, 1px 0px 0px 0px #6f8098;--}}
{{--        border-top: 6px solid transparent;--}}
{{--        border-bottom: 6px solid transparent;--}}
{{--        background: #F7FAFC;--}}
{{--    }--}}

{{--    .charge-box .charge-box__item {--}}
{{--        display: flex;--}}
{{--        justify-content: space-between;--}}
{{--        padding-left: 10px;--}}
{{--        padding-right: 10px;--}}

{{--    }--}}

{{--    .charge-box .charge-box__item .charge-box__item-left {--}}
{{--        font-size: 14px;--}}
{{--        line-height: 19px;--}}
{{--        color: #000000;--}}
{{--    }--}}

{{--    .charge-box .charge-box__item .charge-box__item-right {--}}
{{--        font-size: 14px;--}}
{{--        line-height: 19px;--}}
{{--        color: #000000;--}}
{{--    }--}}

{{--    .hashtag {--}}
{{--        font-family: Helvetica;--}}
{{--        font-style: normal;--}}
{{--        font-weight: bold;--}}
{{--        font-size: 24px;--}}
{{--        line-height: 28px;--}}
{{--        color: #15577C;--}}
{{--    }--}}

{{--</style>--}}

{{--<div class="email-box">--}}
{{--    <div class="email">--}}
{{--        <div class="email-header">--}}
{{--            <a href="https://coachsome.com/">--}}
{{--                <img--}}
{{--                    class="logo"--}}
{{--                    src="https://api.dev.coachsome.com/server/public/assets/images/logo.png"--}}
{{--                    alt="logo"--}}
{{--                />--}}
{{--            </a>--}}
{{--        </div>--}}
{{--        <div class="email-body">--}}
{{--            <div class="email-body-top">--}}
{{--                <div class="message">--}}
{{--                    <div class="message-greeting">Dear</div>--}}
{{--                    <div class="message-name">:packageBuyerFirstName,</div>--}}
{{--                    <div class="message-body">--}}
{{--                        <span><b>Get ready!</b></span>--}}
{{--                        <br />Coach :packageSellerFullName just confirmed your package--}}
{{--                        request. You are now one step closer to becoming better and we are--}}
{{--                        happy that we can help you with this. Your next step will be to--}}
{{--                        chat with your coach and schedule your first session.--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="email-body-bottom">--}}
{{--                <img--}}
{{--                    class="profile-image"--}}
{{--                    src=":packageSellerProfileImage"--}}
{{--                    alt="Profile Image"--}}
{{--                />--}}
{{--                <a--}}
{{--                    class="btn--chat-now"--}}
{{--                    href=":chatNowUrl"--}}
{{--                    style="margin-top: 30px; margin-bottom: 30px;text-decoration: none;"--}}
{{--                >Chat now</a--}}
{{--                >--}}
{{--                <div--}}
{{--                    class="charge-box"--}}
{{--                    :style="{--}}
{{--              borderImage: `url(':stairCaseUrl') 30 space`,--}}
{{--            }"--}}
{{--                >--}}
{{--                    <div class="charge-box__item">--}}
{{--                        <div>--}}
{{--                            <div style="font-weight: bolder;font-size: 18px;">--}}
{{--                                Receipt #:invoiceNumber--}}
{{--                            </div>--}}
{{--                            <sm style="font-size: 12px;line-height: 16px;color: #2C3749;"--}}
{{--                            >Paid with :paymentMethod</sm--}}
{{--                            >--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <br />--}}
{{--                    <div class="charge-box__item">--}}
{{--                        <div class="charge-box__item-left">--}}
{{--                            Price for package--}}
{{--                        </div>--}}
{{--                        <div class="charge-box__item-right">--}}
{{--                            :packagePrice--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="charge-box__item" style="background: #ECF2F7;">--}}
{{--                        <div class="charge-box__item-left">--}}
{{--                            Coachsome service fee--}}
{{--                        </div>--}}
{{--                        <div class="charge-box__item-right">--}}
{{--                            :serviceFee--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <br />--}}
{{--                    <div--}}
{{--                        class="charge-box__item"--}}
{{--                        style="font-weight: 800;font-size: 18px;line-height: 25px;color: #000000;"--}}
{{--                    >--}}
{{--                        <div class="charge-box__item-left">--}}
{{--                            Total DKK--}}
{{--                        </div>--}}
{{--                        <div class="charge-box__item-right">--}}
{{--                            :totalPrice--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div--}}
{{--                        class="charge-box__item"--}}
{{--                    >--}}
{{--                        <div class="charge-box__item-left"  style="font-weight: 300;font-size: 12px;color: #49556A;">--}}
{{--                            Hereof VAT is--}}
{{--                        </div>--}}
{{--                        <div class="charge-box__item-right"  style="font-weight: 300;font-size: 12px;color: #49556A;">--}}
{{--                            :vat--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <br />--}}
{{--                    <div--}}
{{--                        class="charge-box__item"--}}
{{--                        style="display:flex;justify-content:center; font-family: Roboto;font-weight: 300;font-size: 12px;color: #49556A;"--}}
{{--                    >--}}
{{--                        <div>--}}
{{--                            Coachsome Aps · Århusvej 201 · 8464 Galten · DK · CVR 40927417--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <a style="margin-top: 10px;" href=":hashTagUrl" class="hashtag"--}}
{{--                >#bettertogether</a--}}
{{--                >--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="email-footer">--}}
{{--            <div class="email-footer-content">--}}
{{--                Coachsome is your security for a well executed trainning. <br>Please read--}}
{{--                our <a href=":termsUrl" style="color:blue;">Terms</a> and <a style="color:blue;" href=":conditionUrl">conditions</a>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
