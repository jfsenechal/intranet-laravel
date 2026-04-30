<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pst</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"
          media="screen">
    <style>
        .hover-bg-slate-800:hover {
            background-color: #1e293b !important
        }

        @media (max-width: 600px) {
            .sm-p-6 {
                padding: 24px !important
            }

            .sm-px-4 {
                padding-left: 16px !important;
                padding-right: 16px !important
            }
        }

        @media (prefers-color-scheme: dark) {
            .dark-border-green-800 {
                border-color: #166534 !important
            }

            .dark-bg-gray-800 {
                background-color: #1f2937 !important
            }

            .dark-text-green-400 {
                color: #4ade80 !important
            }
        }
    </style>
</head>
<body
    style="background-color: #f8fafc; font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif">
<div class="sm-px-4"
     style="background-color: #f8fafc; font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif">
    <table align="center" style="margin: 0 auto" cellpadding="0" cellspacing="0" role="none">
        <tr>
            <td style="width: 752px; max-width: 100%">
                <div role="separator" style="line-height: 24px">&zwj;</div>
                <table style="width: 100%" cellpadding="0" cellspacing="0" role="none">
                    <tr>
                        <td class="sm-p-6"
                            style="border-radius: 8px; background-color: #fffffe; padding: 24px 36px; border: 1px solid #e2e8f0">
                            <a href="https://pst.marche.be">
                                <img src="{{$message->embed($logo)}}" width="70" alt="logo"
                                     style="max-width: 100%; vertical-align: middle">
                            </a>
                        </td>
                        <td class="sm-p-6"
                            style="border-radius: 8px; background-color: #fffffe; padding: 24px 36px; border: 1px solid #e2e8f0">
                            {{ config('app.name') }}
                        </td>
                    </tr>
                </table>
                <div role="separator" style="line-height: 24px">&zwj;</div>
                <table style="width: 100%" cellpadding="0" cellspacing="0" role="none">
                    <tr>
                        <td class="sm-p-6"
                            style="border-radius: 8px; background-color: #fffffe; padding: 24px 36px; border: 1px solid #e2e8f0">
                            <p style="margin: 0 0 24px; font-size: 16px; color: #475569">
                                Une nouvelle action a été ajouté par {{$action->user_add}}
                            </p>
                            <div role="separator" style="line-height: 24px">&zwj;</div>
                            {{$action->name}}
                            <br/>
                            <br/>
                            Gérer l'action:
                            <a href="{{$url}}">{{$url}}</a>
                            <br/>
                            <br/>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>

