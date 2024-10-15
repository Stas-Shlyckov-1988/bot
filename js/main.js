$(document).ready(function() {
    function loadMessages() {
        $('.message, .callout-warning').remove();
        $.ajax({
            url: '/scripts/messages.php?username=' + window.username,
            method: 'get',
            async: false,
            type: 'json',
            success: function(res) {
                for (var i = 0; i <= res.length-1; i++) {
                    if (!res[i].bot_responce) {
                        if (typeof res[i].date_format != "undefined") {
                            var date_format = res[i].date_format;
                        }
                        else {
                            var date_format = new Date(res[i].callback_query.message.date*1000);
                            date_format =  (date_format.getDay() < 10 ? '0' + date_format.getDay() : date_format.getDay()) 
                            + '.' + (date_format.getMonth() < 10 ? '0' + date_format.getMonth() : date_format.getMonth()) 
                            + '.' + date_format.getFullYear() 
                            + ' ' + date_format.getHours() 
                            + ':' + date_format.getMinutes() 
                            + ':' + date_format.getSeconds();
                        }
                        $('.container h1')
                            .append('<div class="callout message">' + date_format + '> ' 
                            + (typeof res[i].callback_query != "undefined" ? res[i].callback_query.message.text.replace("\n", "<br>") : res[i].message.text.replace("\n", "<br>")) 
                            + '</div>');
                            if(typeof res[i].callback_query != "undefined") {
                                for (var row in res[i].callback_query.message.reply_markup.inline_keyboard[0])
                                    if (res[i].callback_query.data == res[i].callback_query.message.reply_markup.inline_keyboard[0][row].callback_data)
                                        $('.container h1').append('<div class="callout-info message">' + (res[i].callback_query.message.reply_markup.inline_keyboard[0][row].text) + '</div>');
                                
                            }
                           
                    }
                    else {
                        $('.container h1').append('<div class="callout-info message">' 
                        + (res[i].bot_responce.replace("\n", "<br>")) 
                        + '</div>');
                    }
                }
                
                if(res.length == 0) {
                    $('.callout-warning').remove();
                    $('.container h1').append('<div class="callout-warning">Сообщений нет на сервере</div>');
                }
                else $('.send-message').data('from_chat_id', res[0].message.from.id).data('chat_id', res[0].message.chat.id);
            },
            error: function() {
                $('.callout-warning').remove();
                $('.container h1').append('<div class="callout-warning">Ошибка соеденения</div>');
            }
        });
    }
    if(!window.username) $('.container h1').append('<div class="callout-warning">Не указан пользователь</div>');
    else { window.timerId = setInterval(loadMessages, 30000); loadMessages(); }

    $('.send-message').on('click', function() {
        clearInterval(window.timerId);
        if ($('#flexCheckGroup').is(':checked')) {
            var data =  {
                send_support: 1,
                chat_id: $('.send-message').data('chat_id'),
                msg: $('#exampleFormControlTextarea1').val()
            };
        }
        else {
            var data =  {
                from_chat_id: $('.send-message').data('from_chat_id'),
                chat_id: $('.send-message').data('chat_id'),
                msg: $('#exampleFormControlTextarea1').val()
            };
        }
       
        $.ajax({
            url: '/scripts/messages.php',
            dataType: 'json',
            type: 'post',
            data: data,
            success: function(res) {
                if(res) {
                   $('#exampleFormControlTextarea1').val('');
                   window.timerId = setInterval(loadMessages, 30000); loadMessages();
                }
            }
        });
        if (typeof data.chat_id == "undefined") {
            $('.username_panel').show();
            $('#exampleFormControlTextarea1').val('');
            $('#flexCheckGroup').prop('checked', false);
        } else
        if (typeof data.send_support != "undefined") {
            $('.username_panel, .message').toggle();
            $('#exampleFormControlTextarea1').val('');
            $('#flexCheckGroup').prop('checked', false);;
        }
       
    });

    $('#flexCheckGroup').on('click', function() {
        $('.username_panel, .message').toggle();
    });

    $('#exampleFormControlTextarea1').keyup(function(e){
        // ctrl + enter && comand + enter
        if((e.ctrlKey || e.metaKey) && (e.keyCode == 13 || e.keyCode == 10))
        {
            var text = '';
            $('.message').each(function() {
                text += $(this).text() + "\n";
            });
            $.ajax({
                url: '/scripts/messages.php',
                type: 'post',
                data: {
                    username: window.username,
                    text: text
                },
                success: function(res) {
                    location = res;
                }
            });
        }
    });

    $('.username_apply').on('click', function(event) {
        location = "/chat.php?username=" + $('input[name=username]').val();
        event.preventDefault();
    });

    $('input[name=username]').keyup(function(e){
        if(e.keyCode == 13)
        {
            location = "/chat.php?username=" + $(this).val();
        }
    });

    $.ajax({
        url: '/scripts/ban_user.php?isban=1&username=' + window.username,
        success: function(res) {
            if (res == "1") {
                $('.ban_user').prop('disabled', 'disabled').text('Пользователь внесён в бан');
                $('.send-message, #exampleFormControlTextarea1').prop('disabled', 'disabled');
                clearInterval(window.timerId);
            }
        }
    });

    $('.ban_user').on('click', function() {
        $.ajax({
            url: '/scripts/ban_user.php?username=' + window.username,
            success: function(res) {
                if (res) {
                    $('.message').remove();
                    $('.ban_user').prop('disabled', 'disabled').text('Пользователь внесён в бан');
                    $('.send-message, #exampleFormControlTextarea1').prop('disabled', 'disabled');
                    clearInterval(window.timerId);
                }
            }
        });
    });

    $('.save_messages').on('click', function() {
        var text = '';
        $('.message').each(function() {
            text += $(this).text() + "\n";
        });
        $.ajax({
            url: '/scripts/messages.php',
            type: 'post',
            data: {
                username: window.username,
                text: text
            },
            success: function(res) {
                location = res;
            }
        });
    });
});
