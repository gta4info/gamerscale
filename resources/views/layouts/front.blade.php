<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Турниры, картомки, box fights от GamerScale</title>
    <meta name="theme-color" content="#010818">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="/img/favicons/favicon.ico" type="image/x-icon">
    <link rel="icon" sizes="16x16" href="/img/favicons/favicon-16x16.png" type="image/png">
    <link rel="icon" sizes="32x32" href="/img/favicons/favicon-32x32.png" type="image/png">
    <link rel="apple-touch-icon-precomposed" href="/img/favicons/apple-touch-icon-precomposed.png">
    <link rel="apple-touch-icon" href="/img/favicons/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="57x57" href="/img/favicons/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/img/favicons/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/img/favicons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/favicons/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/img/favicons/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/img/favicons/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/img/favicons/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/img/favicons/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/img/favicons/apple-touch-icon-167x167.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favicons/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="1024x1024" href="/img/favicons/apple-touch-icon-1024x1024.png">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        @media (min-width: 1920px) {
            .v-container {
                max-width: 1200px !important;
            }
        }

        .v-application {
            background: url(/img/bg-profile.png) no-repeat #010818 !important;
            background-size: 100% auto !important;
            padding-bottom: 50px;
        }

        @font-face {
            font-family: "Proxima Nova";
            src: url(/fonts/ProximaNova-Regular.woff);
            src: local("Proxima Nova Regular"), local("ProximaNova-Regular"), url(/fonts/ProximaNova-Regular.woff) format("woff"), url(/fonts/ProximaNova-Regular.woff2) format("woff2");
            font-weight: 400;
            font-style: normal
        }

        @font-face {
            font-family: "Proxima Nova";
            src: url(/fonts/ProximaNova-Semibold.woff);
            src: local("Proxima Nova Semibold"), local("ProximaNova-Semibold"), url(/fonts/ProximaNova-Semibold.woff) format("woff"), url(/fonts/ProximaNova-Semibold.woff2) format("woff2");
            font-weight: 600;
            font-style: normal
        }

        @font-face {
            font-family: "Proxima Nova";
            src: url(/fonts/ProximaNova-Bold.woff);
            src: local("Proxima Nova Bold"), local("ProximaNova-Bold"), url(/fonts/ProximaNova-Bold.woff) format("woff"), url(/fonts/ProximaNova-Bold.woff2) format("woff2");
            font-weight: 700;
            font-style: normal
        }

        body, html {
            font-family: "Proxima Nova", sans-serif !important
        }

        .table-hide-controls {
            .v-data-table-footer__items-per-page, .v-data-table-footer__info {
                display: none !important;
            }
            .v-pagination__list {
                margin-bottom: 0 !important;
            }
        }

        .v-table {
            background: #0F1627 !important;
            border-radius: 16px !important;
            border: 1px solid rgba(255, 255, 255, 0.05);

            .v-table__wrapper {
                border-bottom-left-radius: 0 !important;
                border-bottom-right-radius: 0 !important;
            }

            .v-divider {
                border-color: rgba(255, 255, 255, 0.05);
                opacity: 1;
                margin-top: 0;
            }

            th {
                border-color: rgba(255, 255, 255, 0.05) !important;
            }

            .v-data-table-header__content span, td, a {
                color: #68889C;
                font-weight: 700;
            }

            tr.active {
                background: rgba(8, 217, 214, 0.20) !important;

                td, a {
                    color: #f5f5f5 !important;
                }
            }

            a {
                text-decoration: none;
                transition: .3s;
            }

            a:hover {
                color: #08D9D6;
            }

            .v-data-table-footer {
                padding: 8px 16px 16px;
            }

            .v-field__outline {
                display: none !important;
            }
        }
    </style>
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
