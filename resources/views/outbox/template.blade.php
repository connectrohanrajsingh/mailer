<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background: #f4f4f7;
                color: #333;
            }

            .email-wrapper {
                width: 100%;
                padding: 20px;
                background: #f4f4f7;
            }

            .email-container {
                max-width: 700px;
                margin: auto;
                background: #ffffff;
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid #ddd;
            }

            .email-header {
                background: #15a362;
                color: #ffffff;
                padding: 20px;
                text-align: center;
            }

            .email-header h1 {
                margin: 0;
                font-size: 22px;
            }

            .email-body {
                padding: 25px;
                line-height: 1.6;
                font-size: 15px;
            }

            .email-footer {
                background: #f4f4f7;
                text-align: center;
                padding: 15px;
                font-size: 13px;
                color: #777;
            }

            .btn {
                display: inline-block;
                margin-top: 15px;
                padding: 10px 20px;
                background: #004aad;
                color: #ffffff !important;
                text-decoration: none;
                border-radius: 5px;
                font-size: 14px;
            }

            .signature {
                margin-top: 30px;
                padding-top: 15px;
                border-top: 1px solid #ddd;
            }
        </style>
    </head>

    <body>
        <div class="email-wrapper">
            <div class="email-container">

                {{-- HEADER --}}
                <div class="email-header">
                    <h1>{{ $title ?? 'Greetings from HP Pay' }}</h1>
                </div>

                {{-- BODY --}}
                <div class="email-body">

                    {{-- Greeting --}}
                    @if(!empty($name))
                        <p>Dear {{ $name }},</p>
                    @endif

                    {{-- Main Content --}}
                    @if(!empty($content))
                        {!! $content !!}
                    @endif

                    {{-- Signature --}}
                    <div class="signature">
                        <p>Regards,<br><strong>Rohan Singh</strong></p>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="email-footer">
                    &copy; {{ date('Y') }} Rohan Singh. All rights reserved.
                </div>

            </div>
        </div>
    </body>

</html>