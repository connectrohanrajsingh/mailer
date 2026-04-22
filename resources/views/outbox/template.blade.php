<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email</title>

        <style>
            body {
                margin: 0;
                padding: 0;
                background-color: #f2f4f8;
                font-family: 'Segoe UI', Arial, sans-serif;
                color: #333;
            }

            .wrapper {
                width: 100%;
                padding: 30px 15px;
            }

            .container {
                max-width: 650px;
                margin: auto;
                background: #ffffff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            }

            .header {
                /* background: linear-gradient(135deg, #15a362, #0d8a52); */
                color: #000000;
                text-align: center;
                padding: 25px 20px;
            }

            .header h1 {
                margin: 0;
                font-size: 24px;
                letter-spacing: 0.5px;
            }

            .body {
                padding: 30px 25px;
                font-size: 15px;
                line-height: 1.7;
            }

            .body p {
                margin: 0 0 15px;
            }

            .message-greet {
                font-weight: bold;
                font-size: 16px;

            }

            .message-body {
                margin: 10px 0 0 0;
                text-align: justify;
            }

            .divider {
                margin: 25px 0;
                height: 1px;
                background: #e5e7eb;
            }

            .signature {
                margin-top: 35px;
                font-size: 14px;
            }

            .signature strong {
                color: #111;
            }

            .footer {
                background: linear-gradient(135deg, #15a362, #0d8a52);
                text-align: center;
                padding: 18px;
                font-size: 14px;
                color: #ffffff;
            }

            .footer a {
                color: #15a362;
                text-decoration: none;
            }

            @media (max-width: 600px) {
                .body {
                    padding: 20px;
                }
            }
        </style>
    </head>

    <body>

        <div class="wrapper">
            <div class="container">

                {{-- HEADER --}}
                <div class="header">
                    <h1>{{ $title ?? 'Mailer Notification' }}</h1>
                </div>
                <div class="divider"></div>
                {{-- BODY --}}
                <div class="body">

                    {{-- Greeting --}}
                    <div class="message-greet">
                        Dear Rohan,
                    </div>

                    <div class="message-body">
                        Lorem ipsum dolor sit, amet consectetur adipisicing elit.
                        Eaque, impedit laboriosam praesentium excepturi quod autem repellendus et cumque reprehenderit nostrum quas modi iusto ad harum adipisci ducimus quis recusandae dicta.
                        Voluptatibus dolore obcaecati nam consequuntur excepturi odit assumenda, dicta, vel corporis repellat earum eaque optio tempora.
                        Officia iste necessitatibus eveniet, at porro numquam iure nesciunt voluptatibus! Aspernatur officiis temporibus dolorum.
                    </div>

                    @if(!empty($name))
                        <p>Dear {{ $name }},</p>
                    @endif

                    {{-- Main Content --}}
                    @if(!empty($content))
                        {!! $content !!}
                    @endif




                    {{-- Signature --}}
                    <div class="signature">
                        <p>
                            Regards,<br>
                            <strong>Rohan Singh</strong><br>
                            <span style="color:#777;">Mailer Team</span>
                        </p>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="footer">
                    &copy; {{ date('Y') }} Mailer. All rights reserved.<br>
                    <small>If you get this email, mailer is configured.</small>
                </div>

            </div>
        </div>

    </body>

</html>