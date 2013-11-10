function comboClick() {
        if($(this).parents('.combo').hasClass('active')){
            $(this).parents('.combo').find("li").removeClass("active");
            $(this).addClass("active");
            var formId = $(this).parent().attr('form-input-id');
            if (typeof formId == 'undefined') {
                formId = $(this).parents('.combo_list').attr('form-input-id');
            }
            $('#'+ formId).val($(this).attr('data-value'));
            var event;
            var eventName = 'dataaviable';
            var element = document.getElementById(formId);
            if (document.createEvent) {
              event = document.createEvent("HTMLEvents");
              event.initEvent("dataavailable", true, true);
            } else {
              event = document.createEventObject();
              event.eventType = "dataavailable";
            }

            event.eventName = eventName;
            event.memo = { };

            if (document.createEvent) {
              if (element) element.dispatchEvent(event);
            } else {
              if (element) element.fireEvent("on" + event.eventType, event);
            }
            $(this).parents(".combo").find(".current").html($(this).html());
            //$(this).parents(".combo").find("input").val($(this).data('id')).trigger('change');
            $(this).parents(".combo").find('.scroll_box').css('opacity', '0.0');
        }
    }
$(document).ready(function(){


    $(".dotted_box span.text.show").click(function () {
		$(this).parents('.text_box').children(".drop_text").slideDown();
        $(this).parents(".dotted_box").addClass("open");
	});

    $(".dotted_box span.text.hide").click(function () {
		$(this).parents('.text_box').children(".drop_text").slideUp();
        $(this).parents(".dotted_box").removeClass("open");
	});

    $("ul.list li.last_visible").click(function () {
		$(this).parents('ul.list').children("li.hidden").fadeIn(500);
        $(this).css("display", "none");
	});

    /*
    $(".change.for_adres").click(function () {
		$(this).parents('.line').next(".line.adres_fields").slideDown();
	});
    */

    $(".payment_point").click(function () {
        $(".payment_point").removeClass("current");
        $(this).addClass("current");
	});



    /////////////////////////////////////////////////////////////////////////////////////
    $(".combo > .current, .combo > .arrow").on('click', function () {
        $(this).parent().find('.scroll_box').css('opacity', '1.0');
        $(this).parents(".combo").addClass("active");
    });
    $(".combo ul li").on('click', comboClick);
    //$(".combo ul ul li").on('click', comboClick);

    $('html').click(function(e){
       var currentCombo = $('.combo.active');
       if(currentCombo.length && undefined != e.pageX) {
            var offset= currentCombo.offset();
            if(!(e.pageX >= offset.left && e.pageX <= offset.left+currentCombo.width() && e.pageY>=offset.top && e.pageY<=offset.top+currentCombo.height())) {
                currentCombo.find('.scroll_box').css('opacity', '0.0').parent().removeClass('active');
            }
       }
    });
    /////////////////////////////////////////////////////////////////////////////////////

});
//Timer init
$(document).ready(
    function() {
        function toggleTimer()
        {
            if (window.timerEnd) return;
            if (!window.endTime) {
                var now = new Date(); //получаем текущую дату
                var finish = new Date(end_time); //дата до которой ведется отсчет
                //All next varaibles must be in seconds
                window.endTime = parseInt((finish.getTime() - now.getTime()) / 1000);
            }
            if (window.endTime <= 0) {
                finish.setTime((now.getTime() + 10800000));
                window.endTime = parseInt((finish.getTime() - now.getTime()) / 1000);
                //$('.hours .tdigit').text('00');
                //$('.minutes .tdigit').text('00');
                //$('.seconds .tdigit').text('00');
                //window.timerEnd = true;
                //return;
            }
            window.endTime--;
            var txtYears = Math.floor(window.endTime / (3600*24*30*365));
            var txtMonths = Math.floor((window.endTime - txtYears*3600*24*30*365) / (3600*24*30));
            var txtDays = Math.floor((window.endTime - txtYears*3600*24*30*365 - txtMonths*3600*24*30) / (3600*24));
            var txtHours = Math.floor((window.endTime - txtYears*3600*24*30*365 - txtMonths*3600*24*30 - txtDays*3600*24)/ 3600);
            var txtMinutes = Math.floor((window.endTime - txtYears*3600*24*30*365 - txtMonths*3600*24*30 - txtDays*3600*24 - txtHours*3600)/ 60);
            var txtSeconds = window.endTime - txtYears*3600*24*30*365 - txtMonths*3600*24*30 - txtDays*3600*24 - txtHours*3600 - txtMinutes*60;
            if (String(txtHours).length == 1) txtHours = '0' + String(txtHours);
            else if (String(txtHours).length == 0) txtHours = '00';
            if (String(txtMinutes).length == 1) txtMinutes = '0' + txtMinutes;
            else if (String(txtMinutes).length == 0) txtMinutes = '00';
            if (String(txtSeconds).length == 1) txtSeconds = '0' + txtSeconds;
            else if (String(txtSeconds).length == 0) txtSeconds = '00';
            $('.hours .tdigit').text(txtHours);
            $('.minutes .tdigit').text(txtMinutes);
            $('.seconds .tdigit').text(txtSeconds);
        }
//Теперь осталось запустить таймер в window.onLoad
        if(typeof end_time !== 'undefined') {
            toggleTimer(); //Начальная инициализация
            setInterval(toggleTimer, 1000); //Смена цифр каждую секунду
        }
    }
);
