<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{Setting::get('site_name')}}</title>
    <style type="text/css">
        p {
            margin: 10px 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
        }
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            display: block;
            margin: 0;
            padding: 0;
        }
        img,
        a img {
            border: 0;
            height: auto;
            outline: none;
            text-decoration: none;
        }
        
        body,
        #bodyTable,
        #bodyCell {
            height: 100%;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        
        .mcnPreviewText {
            display: none !important;
        }
        
        #outlook a {
            padding: 0;
        }
        
        img {
            -ms-interpolation-mode: bicubic;
        }
        
        table {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        
        .ReadMsgBody {
            width: 100%;
        }
        
        .ExternalClass {
            width: 100%;
        }
        
        p,
        a,
        li,
        td,
        blockquote {
            mso-line-height-rule: exactly;
        }
        
        a[href^=tel],
        a[href^=sms] {
            color: inherit;
            cursor: default;
            text-decoration: none;
        }
        
        p,
        a,
        li,
        td,
        body,
        table,
        blockquote {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        
        .ExternalClass,
        .ExternalClass p,
        .ExternalClass td,
        .ExternalClass div,
        .ExternalClass span,
        .ExternalClass font {
            line-height: 100%;
        }
        
        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }
        
        .templateContainer {
            max-width: 600px !important;
        }
        
        a.mcnButton {
            display: block;
        }
        
        .mcnImage {
            vertical-align: bottom;
        }
        
        .mcnTextContent {
            word-break: break-word;
        }
        
        .mcnTextContent img {
            height: auto !important;
        }
        
        .mcnDividerBlock {
            table-layout: fixed !important;
        }
        
        h1 {
            color: #222222;
            font-family: 'Lato', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 40px;
            font-style: normal;
            font-weight: bold;
            line-height: 150%;
            letter-spacing: normal;
            text-align: center;
        }
        
        h2 {
            color: #222222;
            font-family: 'Lora', Georgia, 'Times New Roman', serif;
            font-size: 34px;
            font-style: normal;
            font-weight: bold;
            line-height: 150%;
            letter-spacing: normal;
            text-align: left;
        }
        
        h3 {
            color: #444444;
            font-family: 'Courier New', Courier;
            font-size: 22px;
            font-style: normal;
            font-weight: bold;
            line-height: 150%;
            letter-spacing: normal;
            text-align: left;
        }
        
        h4 {
            color: #999999;
            font-family: 'Courier New', Courier;
            font-size: 20px;
            font-style: italic;
            font-weight: normal;
            line-height: 125%;
            letter-spacing: normal;
            text-align: left;
        }
        
        #templateHeader {
            background-color: #000000;
            background-image: none;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            border-top: 0;
            border-bottom: 0;
            padding-top: 0;
            padding-bottom: 0px;
        }
        
        .headerContainer {
            background-color: #transparent;
            background-image: none;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            border-top: 0;
            border-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
        }
        
        .headerContainer .mcnTextContent,
        .headerContainer .mcnTextContent p {
            color: #3D3D3D;
            font-family: 'Lora', Georgia, 'Times New Roman', serif;
            font-size: 16px;
            line-height: 150%;
            text-align: left;
        }
        
        .headerContainer .mcnTextContent a,
        .headerContainer .mcnTextContent p a {
            color: #3D3D3D;
            font-weight: normal;
            text-decoration: underline;
        }
        
        #templateBody {
            background-color: #141414;
            /*background-image: url("https://cdn-images.mailchimp.com/template_images/gallery/bg_skulltile.png");*/
            background-image:url({{asset('images/email1.jpg')}});
            background-position: center;
            background-repeat: repeat;
            background-size: auto;
            border-top: 0;
            border-bottom: 0;
            padding-top: 50px;
            padding-right: 18px;
            padding-bottom: 50px;
            padding-left: 18px;
        }
        
        .bodyContainer {
            background-color: #ffffff;
            background-image: none;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            border: 0;
            padding-top: 18px;
            padding-bottom: 18px;
        }
        
        .bodyContainer .mcnTextContent,
        .bodyContainer .mcnTextContent p {
            color: #3D3D3D;
            font-family: 'Lato', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 16px;
            line-height: 150%;
            text-align: left;
        }
        
        .bodyContainer .mcnTextContent a,
        .bodyContainer .mcnTextContent p a {
            color: #5E5163;
            font-weight: normal;
            text-decoration: underline;
        }
        
        #templateFooter {
            background-color: #000000;
            background-image: none;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            border-top: 0;
            border-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
        }
        
        .footerContainer {
            background-color: #transparent;
            background-image: none;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            border-top: 0;
            border-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
        }
        
        .footerContainer .mcnTextContent,
        .footerContainer .mcnTextContent p {
            color: #FFFFFF;
            font-family: Helvetica;
            font-size: 12px;
            line-height: 150%;
            text-align: center;
        }
        
        .footerContainer .mcnTextContent a,
        .footerContainer .mcnTextContent p a {
            color: #FFFFFF;
            font-weight: normal;
            text-decoration: underline;
        }
        
        @media only screen and (max-width: 480px) {
            .columnWrapper {
                max-width: 100% !important;
                width: 100% !important;
            }
            body,
            table,
            td,
            p,
            a,
            li,
            blockquote {
                -webkit-text-size-adjust: none !important;
            }
            body {
                width: 100% !important;
                min-width: 100% !important;
            }
            .mcnImage {
                width: 100% !important;
            }
            .mcnCartContainer,
            .mcnCaptionTopContent,
            .mcnRecContentContainer,
            .mcnCaptionBottomContent,
            .mcnTextContentContainer,
            .mcnBoxedTextContentContainer,
            .mcnImageGroupContentContainer,
            .mcnCaptionLeftTextContentContainer,
            .mcnCaptionRightTextContentContainer,
            .mcnCaptionLeftImageContentContainer,
            .mcnCaptionRightImageContentContainer,
            .mcnImageCardLeftTextContentContainer,
            .mcnImageCardRightTextContentContainer,
            .mcnImageCardLeftImageContentContainer,
            .mcnImageCardRightImageContentContainer {
                max-width: 100% !important;
                width: 100% !important;
            }
            .mcnBoxedTextContentContainer {
                min-width: 100% !important;
            }
            .mcnImageGroupContent {
                padding: 9px !important;
            }
            .mcnCaptionLeftContentOuter .mcnTextContent,
            .mcnCaptionRightContentOuter .mcnTextContent {
                padding-top: 9px !important;
            }
            .mcnImageCardTopImageContent,
            .mcnCaptionBottomContent:last-child .mcnCaptionBottomImageContent,
            .mcnCaptionBlockInner .mcnCaptionTopContent:last-child .mcnTextContent {
                padding-top: 18px !important;
            }
            .mcnImageCardBottomImageContent {
                padding-bottom: 9px !important;
            }
            .mcnImageGroupBlockInner {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }
            .mcnImageGroupBlockOuter {
                padding-top: 9px !important;
                padding-bottom: 9px !important;
            }
            .mcnTextContent,
            .mcnBoxedTextContentColumn {
                padding-right: 18px !important;
                padding-left: 18px !important;
            }
            .mcnImageCardLeftImageContent,
            .mcnImageCardRightImageContent {
                padding-right: 18px !important;
                padding-bottom: 0 !important;
                padding-left: 18px !important;
            }
            .mcpreview-image-uploader {
                display: none !important;
                width: 100% !important;
            }
            h1 {
                font-size: 30px !important;
                line-height: 125% !important;
            }
            h2 {
                font-size: 26px !important;
                line-height: 125% !important;
            }
            h3 {
                font-size: 20px !important;
                line-height: 150% !important;
            }
            h4 {
                font-size: 18px !important;
                line-height: 150% !important;
            }
            .mcnBoxedTextContentContainer .mcnTextContent,
            .mcnBoxedTextContentContainer .mcnTextContent p {
                font-size: 14px !important;
                line-height: 150% !important;
            }
            .headerContainer .mcnTextContent,
            .headerContainer .mcnTextContent p {
                font-size: 16px !important;
                line-height: 150% !important;
            }
            .bodyContainer .mcnTextContent,
            .bodyContainer .mcnTextContent p {
                font-size: 16px !important;
                line-height: 150% !important;
            }
            .footerContainer .mcnTextContent,
            .footerContainer .mcnTextContent p {
                font-size: 14px !important;
                line-height: 150% !important;
            }
        }
    </style>

</head>

<body>
    <span class="mcnPreviewText" style="display:none; font-size:0px; line-height:0px; max-height:0px; max-width:0px; opacity:0; overflow:hidden; visibility:hidden; mso-hide:all;">
        </span>

    <center>
        <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
            <tr>
                <td align="center" valign="top" id="bodyCell">
                    <!-- BEGIN TEMPLATE // -->
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="center" valign="top" id="templateHeader" data-template-container>

                                <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
                                    <tr>
                                        <td valign="top" class="headerContainer">
                                            <table class="mcnImageBlock" style="min-width:100%;" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                <tbody class="mcnImageBlockOuter">
                                                    <tr>
                                                        <td style="padding:9px" class="mcnImageBlockInner" valign="top">
                                                            <table class="mcnImageContentContainer" style="min-width:100%;" width="100%" cellspacing="0" cellpadding="0" border="0" align="left">
                                                                <tbody>
                                                                    <tr>
                                                                        <td class="mcnImageContent" style="padding-right: 9px; padding-left: 9px; padding-top: 0; padding-bottom: 0; text-align:center;" valign="top">

                                                                            <img alt="Bookhaus" src="{{Setting::get('site_logo')}}" style="max-width:100%;width:auto !important;height:50px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnImage" width="441.5" align="middle">

                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                               
                            </td>
                        </tr>
                        <tr>
                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td align="center" valign="top" id="templateBody" data-template-container>
                                        
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
                                            <tr>
                                                <td valign="top" class="bodyContainer">
                                                   
                                                    <table class="mcnTextBlock" style="min-width:100%;" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                        <tbody class="mcnTextBlockOuter">
                                                            <tr>
                                                                <td class="mcnTextBlockInner" style="padding-top:9px;" valign="top">
                                                                   
                                                                    <table style="max-width:100%; min-width:100%;" class="mcnTextContentContainer" width="100%" cellspacing="0" cellpadding="0" border="0" align="left">
                                                                        <tbody>
                                                                            <tr>

                                                                                <td class="mcnTextContent" style="padding: 0px 18px 9px; font-size: 19px; " valign="top">

                                                                                <?= $email_data['content']?>
                                                                                    
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                   
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table class="mcnTextBlock" style="min-width:100%;" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                        <tbody class="mcnTextBlockOuter">
                                                            <tr>
                                                                <td class="mcnTextBlockInner" style="padding-top:9px;" valign="top">
                                                                    
                                                                    <table style="max-width:100%; min-width:100%;" class="mcnTextContentContainer" width="100%" cellspacing="0" cellpadding="0" border="0" align="left">
                                                                        <tbody>
                                                                            <tr>

                                                                                <td class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;" valign="top">

                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                   
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                   
                                                    <table class="mcnTextBlock" style="min-width:100%;" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                        <tbody class="mcnTextBlockOuter">
                                                            <tr>
                                                                <td class="mcnTextBlockInner" style="padding-top:9px;" valign="top">
                                                                    
                                                                    <table style="max-width:100%; min-width:100%;" class="mcnTextContentContainer" width="100%" cellspacing="0" cellpadding="0" border="0" align="left">
                                                                        <tbody>
                                                                            <tr>

                                                                                <td class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;" valign="top">

                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                   
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table class="mcnButtonBlock" style="min-width:100%;" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                        <tbody class="mcnButtonBlockOuter">
                                                            <tr>
                                                                <td style="padding-top:0; padding-right:18px; padding-bottom:18px; padding-left:18px;" class="mcnButtonBlockInner" valign="top" align="center">
                                                                    <table class="mcnButtonContentContainer" style="border-collapse: separate !important;border-radius: 3px;background-color: #E50914;" cellspacing="0" cellpadding="0" border="0">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td class="mcnButtonContent" style=" font-size: 18px; font-family:'Lato', 'Helvetica Neue', Helvetica, Arial, sans-serif;padding: 10px 25px;" valign="middle" align="center">
                                                                                    <a class="mcnButton " title="Visit Video" href="{{Setting::get('ANGULAR_SITE_URL')}}" target="_self" style="letter-spacing: 1px;line-height: 100%;text-align: center;text-decoration: none;color: #FFFFFF;">View Website</a>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" valign="top" id="templateFooter" data-template-container>
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
                                            <tr>
                                                <td valign="top" class="footerContainer">
                                                    <table class="mcnFollowBlock" style="min-width:100%;" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                        <tbody class="mcnFollowBlockOuter">
                                                            <tr>
                                                                <td style="" class="mcnFollowBlockInner" valign="top" align="center">
                                                                    <table class="mcnFollowContentContainer" style="min-width:100%;" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="padding-left:9px;padding-right:9px;" align="center">
                                                                                    <table style="min-width:100%;" class="mcnFollowContent" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                                                        <tbody>
                                                                                            <tr>
                                                                                                <td style="padding-top:9px; padding-right:9px; padding-left:9px;" valign="top" align="center">
                                                                                                    <table cellspacing="0" cellpadding="0" border="0" align="center">
                                                                                                        <tbody>
                                                                                                            <tr>
                                                                                                                <td valign="top" align="center">
                                                                                                                   
                                                                                                                    <table style="display:inline;" cellspacing="0" cellpadding="0" border="0" align="left">
                                                                                                                        <tbody>
                                                                                                                            <tr>
                                                                                                                                <td style="padding-right:10px; padding-bottom:9px;" class="mcnFollowContentItemContainer" valign="top">
                                                                                                                                    <table class="mcnFollowContentItem" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                                                                                                        <tbody>
                                                                                                                                            <tr>
                                                                                                                                                <td style="padding-top:5px; padding-right:10px; padding-bottom:5px; padding-left:9px;" valign="middle" align="left">
                                                                                                                                                    <table width="" cellspacing="0" cellpadding="0" border="0" align="left">
                                                                                                                                                        <tbody>
                                                                                                                                                            <tr>

                                                                                                                                                                <td class="mcnFollowIconContent" width="24" valign="middle" align="center">
                                                                                                                                                                    <a href="{{Setting::get('facebook_link')}}" target="_blank"><img src="https://cdn-images.mailchimp.com/icons/social-block-v2/color-facebook-48.png" style="display:block;" class="" width="24" height="24"></a>
                                                                                                                                                                </td>

                                                                                                                                                            </tr>
                                                                                                                                                        </tbody>
                                                                                                                                                    </table>
                                                                                                                                                </td>
                                                                                                                                            </tr>
                                                                                                                                        </tbody>
                                                                                                                                    </table>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                        </tbody>
                                                                                                                    </table>

                                                                                                                    <table style="display:inline;" cellspacing="0" cellpadding="0" border="0" align="left">
                                                                                                                        <tbody>
                                                                                                                            <tr>
                                                                                                                                <td style="padding-right:10px; padding-bottom:9px;" class="mcnFollowContentItemContainer" valign="top">
                                                                                                                                    <table class="mcnFollowContentItem" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                                                                                                        <tbody>
                                                                                                                                            <tr>
                                                                                                                                                <td style="padding-top:5px; padding-right:10px; padding-bottom:5px; padding-left:9px;" valign="middle" align="left">
                                                                                                                                                    <table width="" cellspacing="0" cellpadding="0" border="0" align="left">
                                                                                                                                                        <tbody>
                                                                                                                                                            <tr>

                                                                                                                                                                <td class="mcnFollowIconContent" width="24" valign="middle" align="center">
                                                                                                                                                                    <a href="{{Setting::get('twitter_link')}}" target="_blank"><img src="https://cdn-images.mailchimp.com/icons/social-block-v2/color-twitter-48.png" style="display:block;" class="" width="24" height="24"></a>
                                                                                                                                                                </td>

                                                                                                                                                            </tr>
                                                                                                                                                        </tbody>
                                                                                                                                                    </table>
                                                                                                                                                </td>
                                                                                                                                            </tr>
                                                                                                                                        </tbody>
                                                                                                                                    </table>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                        </tbody>
                                                                                                                    </table>


                                                                                                                    <table style="display:inline;" cellspacing="0" cellpadding="0" border="0" align="left">
                                                                                                                        <tbody>
                                                                                                                            <tr>
                                                                                                                                <td style="padding-right:0; padding-bottom:9px;" class="mcnFollowContentItemContainer" valign="top">
                                                                                                                                    <table class="mcnFollowContentItem" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                                                                                                        <tbody>
                                                                                                                                            <tr>
                                                                                                                                                <td style="padding-top:5px; padding-right:10px; padding-bottom:5px; padding-left:9px;" valign="middle" align="left">
                                                                                                                                                    <table width="" cellspacing="0" cellpadding="0" border="0" align="left">
                                                                                                                                                        <tbody>
                                                                                                                                                            <tr>

                                                                                                                                                                <td class="mcnFollowIconContent" width="24" valign="middle" align="center">
                                                                                                                                                                    <a href="{{Setting::get('ANGULAR_SITE_URL')}}" target="_blank"><img src="https://cdn-images.mailchimp.com/icons/social-block-v2/color-link-48.png" style="display:block;" class="" width="24" height="24"></a>
                                                                                                                                                                </td>

                                                                                                                                                            </tr>
                                                                                                                                                        </tbody>
                                                                                                                                                    </table>
                                                                                                                                                </td>
                                                                                                                                            </tr>
                                                                                                                                        </tbody>
                                                                                                                                    </table>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                        </tbody>
                                                                                                                    </table>

                                                                                                                    <!--[if mso]>
                                        </td>
                                        <![endif]-->

                                                                                                                    <!--[if mso]>
                                    </tr>
                                    </table>
                                    <![endif]-->
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                        </tbody>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>

                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table class="mcnTextBlock" style="min-width:100%;" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                        <tbody class="mcnTextBlockOuter">
                                                            <tr>
                                                                <td class="mcnTextBlockInner" style="padding-top:9px;" valign="top">
                                                                    
                                                                    <table style="max-width:100%; min-width:100%;" class="mcnTextContentContainer" width="100%" cellspacing="0" cellpadding="0" border="0" align="left">
                                                                        <tbody>
                                                                            <tr>

                                                                                <td class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;" valign="top">

                                                                                    <em>{{Setting::get('copyright_content')}}</em>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                       
                                    </td>
                                </tr>
                            </table>
                            <!-- // END TEMPLATE -->
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </center>
</body>

</html>