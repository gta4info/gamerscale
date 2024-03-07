<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Добавление розыгрыша</title>
    <link rel="shortcut icon" href="img/favicons/favicon.ico" type="image/x-icon">
    <link rel="icon" sizes="16x16" href="img/favicons/favicon-16x16.png" type="image/png">
    <link rel="icon" sizes="32x32" href="img/favicons/favicon-32x32.png" type="image/png">
    <link rel="apple-touch-icon-precomposed" href="img/favicons/apple-touch-icon-precomposed.png">
    <link rel="apple-touch-icon" href="img/favicons/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="57x57" href="img/favicons/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="img/favicons/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="img/favicons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="img/favicons/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="img/favicons/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="img/favicons/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="img/favicons/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="img/favicons/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="167x167" href="img/favicons/apple-touch-icon-167x167.png">
    <link rel="apple-touch-icon" sizes="180x180" href="img/favicons/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="1024x1024" href="img/favicons/apple-touch-icon-1024x1024.png">
    <link rel="stylesheet" type="text/css" media="screen" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css" rel="stylesheet">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap');

        *, body {
            font-family: 'Roboto', sans-serif;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            -moz-osx-font-smoothing: grayscale;
        }

        html, body {
            height: 100%;
            background-color: #152733;
            overflow-x: hidden;
        }

        .form-holder {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            min-height: 100vh;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-holder .form-content {
            position: relative;
            text-align: center;
            display: -webkit-box;
            display: -moz-box;
            display: -ms-flexbox;
            display: -webkit-flex;
            display: flex;
            -webkit-justify-content: center;
            justify-content: center;
            -webkit-align-items: center;
            align-items: center;
            padding: 60px;
        }

        .form-content .form-items {
            border: 3px solid #fff;
            padding: 40px;
            display: inline-block;
            width: 100%;
            min-width: 540px;
            -webkit-border-radius: 10px;
            -moz-border-radius: 10px;
            border-radius: 10px;
            text-align: left;
            -webkit-transition: all 0.4s ease;
            transition: all 0.4s ease;
        }

        .form-content h3 {
            color: #fff;
            text-align: left;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .form-content h3.form-title {
            margin-bottom: 30px;
        }

        .form-content p {
            color: #fff;
            text-align: left;
            font-size: 17px;
            font-weight: 300;
            line-height: 20px;
            margin-bottom: 30px;
        }


        .form-content label, .was-validated .form-check-input:invalid ~ .form-check-label, .was-validated .form-check-input:valid ~ .form-check-label {
            color: #fff;
        }

        .form-content input[type=text], .form-content input[type=password], .form-content input[type=email], .form-content select {
            width: 100%;
            padding: 9px 20px;
            text-align: left;
            border: 0;
            outline: 0;
            border-radius: 6px;
            background-color: #fff;
            font-size: 15px;
            font-weight: 300;
            color: #8D8D8D;
            -webkit-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }


        .btn-primary {
            background-color: #6C757D;
            outline: none;
            border: 0px;
            box-shadow: none;
        }

        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background-color: #495056;
            outline: none !important;
            border: none !important;
            box-shadow: none;
        }

        .form-content textarea {
            position: static !important;
            width: 100%;
            padding: 8px 20px;
            border-radius: 6px;
            text-align: left;
            background-color: #fff;
            border: 0;
            font-size: 15px;
            font-weight: 300;
            color: #8D8D8D;
            outline: none;
            resize: none;
            height: 120px;
            -webkit-transition: none;
            transition: none;
            margin-bottom: 14px;
        }

        .form-content textarea:hover, .form-content textarea:focus {
            border: 0;
            background-color: #ebeff8;
            color: #8D8D8D;
        }

        .mv-up {
            margin-top: -9px !important;
            margin-bottom: 8px !important;
        }

        .invalid-feedback {
            color: #ff606e;
        }

        .valid-feedback {
            color: #2acc80;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="form-body">
        <div class="row">
            <div class="form-holder">
                <div class="form-content">
                    <div class="success" style="display: none; margin: auto;">
                        <h3 style="text-align:center;">Розыгрыш успешно добавлен</h3>
                        <p style="text-align:center;" class="mt-3">Закройте окно и продолжите в дискорде!</p>
                    </div>
                    <div class="form-items">
                        <h3>Форма добавления розыгрыша</h3>
                        <p>Заполните поля</p>
                        <div class="requires-validation">
                            <div id="errors" class="mb-3" style="display: none;"></div>
                            <div>
                                <label for="title" class="">Название*</label>
                                <input class="form-control" type="text" name="title" id="title" placeholder="Название" required>
                            </div>

                            <div class="mt-3">
                                <label for="description" class="">Описание/условия*</label>
                                <textarea class="form-control" name="description" id="description" placeholder="Описание/условия" required></textarea>
                            </div>

                            <div class="mt-3">
                                <label for="currency_type" class="">Тип розыгрыша*</label>
                                <select name="currency_type" id="currency_type" class="form-select" required>
                                    <option value="0" selected>Бесплатный</option>
                                    <option value="1">За В-Баксы</option>
                                    <option value="2">За рубли</option>
                                </select>
                            </div>

                            <div class="mt-3 cost-wrapper" style="display: none;">
                                <label for="cost" class="">Стоимость билета</label>
                                <input class="form-control" type="number" id="cost" name="cost" value="0" placeholder="Стоимость билета"
                                       required>
                            </div>

                            <div class="mt-3">
                                <label for="winners_amount" class="">Кол-во победителей*</label>
                                <input class="form-control" type="number" id="winners_amount" name="winners_amount"
                                       placeholder="Кол-во победителей" value="1" min="1" required>
                            </div>

                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_at" class="">Дата начала*</label>
                                            <input type='text' class="form-control" id="start_at" name="start_at" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="end_at" class="">Дата завершения*</label>
                                            <input type='text' class="form-control" id="end_at" name="end_at" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label for="discord_message_content" class="">Текст сообщения (необязательное поле)</label>
                                <textarea class="form-control" name="discord_message_content" id="discord_message_content" placeholder="Текст сообщения" required>@everyone Стартует новый розыгрыш, успейте поучаствовать! Всем удачи!</textarea>
                            </div>

                            <div class="form-button mt-3">
                                <button id="submit" type="submit" class="btn btn-danger">Создать</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="//code.jquery.com/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script type="text/javascript">
    $(function () {
        $('#start_at').datetimepicker({
            locale: 'ru',
            format: 'YYYY-MM-DD HH:mm'
        });
        $('#end_at').datetimepicker({
            locale: 'ru',
            format: 'YYYY-MM-DD HH:mm'
        });

        $('#currency_type').on('change', function () {
            if($('#currency_type').val() !== '0') {
                $('.cost-wrapper').show();
            } else {
                $('.cost-wrapper').hide();
            }
        })

        $('#start_at').val(moment().format('YYYY-MM-DD HH:mm'))
        $('#end_at').val(moment().add(1, 'hours').format('YYYY-MM-DD HH:mm'))

        $('#submit').on('click', function () {
            $('#errors').empty();
            $('#errors').hide();

            axios.post('{{env('APP_URL')}}/api/raffles/create', {
                title: $('#title').val(),
                description: $('#description').val(),
                currency_type: parseInt($('#currency_type').val()),
                cost: parseFloat($('#cost').val()),
                winners_amount: parseInt($('#winners_amount').val()),
                start_at: $('#start_at').val(),
                end_at: $('#end_at').val(),
                discord_message_content: $('#discord_message_content').val(),
            }).then(res => {
                $('.form-items').remove();
                $('.success').show();
            }).catch(err => {
                if(err.response.data.errors) {
                    for (const [key, value] of Object.entries(err.response.data.errors)) {
                        let name = key;

                        if(name === 'title') name = 'Название';
                        if(name === 'description') name = 'Описание/условия';
                        if(name === 'currency_type') name = 'Тип розыгрыша';
                        if(name === 'cost') name = 'Стоимость билета';
                        if(name === 'winners_amount') name = 'Кол-во победителей';
                        if(name === 'start_at') name = 'Дата начала';
                        if(name === 'end_at') name = 'Дата завершения';
                        if(name === 'discord_message_content') name = 'Текст сообщения';

                        $('#errors').append(`<div style="color: red;"><span style="font-weight: 700;">${name}</span>: ${value}</div>`)
                        $('#errors').show()
                    }
                }
            })
        })
    });
</script>
<script>
    (function () {
        'use strict'
        const forms = document.querySelectorAll('.requires-validation')
        Array.from(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>
</body>
</html>
