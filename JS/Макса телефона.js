                let phoneInputs = form.querySelectorAll('input[placeholder*=телефон], input[placeholder*=Телефон]');
                phoneInputs.forEach(function (item) {
                    item.addEventListener('keydown', function (event) {
                        if (!(event.key == 'ArrowLeft' || event.key == 'ArrowRight' || event.key == 'Tab')) {
                            event.preventDefault();
                        };
                        const mask = '+7 (999) 999-99-99';
                        let currentString = this.value;
                        let currentLength = currentString.length;
                        if ((event.key == 'Backspace') && (currentLength > 0)) {
                            currentLength = currentLength - 1
                            while ((mask[currentLength] != '9') && (currentLength > 0)) {
                                currentLength--;
                            }
                            this.value = currentString.substring(0, currentLength);
                        };
                        if (/[0-9\+\ \-\(\)]/.test(event.key)) {
                            if (/[0-9]/.test(event.key)) {
                                let keyval = event.key;
                                if ((currentLength == 0) && (event.key == '8')) {
                                    keyval = '';
                                }
                                if (mask[currentLength] == '9') {
                                    this.value = currentString + keyval;
                                } else {
                                    for (var i = currentLength; i < mask.length; i++) {
                                        if (mask[i] == '9') {
                                            this.value = currentString + keyval;
                                            break;
                                        };
                                        currentString += mask[i];
                                    };
                                };
                            };
                        };
                    });
                });




/* Первый вариант по моему лучший */
var maskloaded = false;
function InitPhoneInputs() {
    const SettingMask = 'mask'; /* mask/inputmask */

    if (SettingMask == 'mask') {
        if (typeof $.mask !== 'function') {
            $.getScript('https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js',
                function () {
                    maskloaded = false;
                });
        } else {
            maskloaded = true;
        };
    };
    if (SettingMask == 'inputmask') {
        if (typeof $.inputmask !== 'function') {
            $.getScript('https://rawgit.com/RobinHerbots/Inputmask/5.x/dist/jquery.inputmask.js',
            function () {
                maskloaded = false;
            });
        } else {
            maskloaded = true;
        };
    };
    $('body').bind("DOMSubtreeModified", function () {
        if (maskloaded) {
            $('input[placeholder*=телефон], input[placeholder*=Телефон]').each(function () {
                if (!$(this).hasClass('maskedphone')) {
                    if (SettingMask == 'mask') {
                        $(this).mask('+7(999)999-99-99').attr('placeholder', '+7(___)___-__-__');
                    };
                    if (SettingMask == 'inputmask') {
                        $(this).inputmask({
                            mask: "+7(999)999-99-99",
                            showMaskOnHover: true,
                            clearIncomplete: true
                        });
                    };
                    $(this).addClass('maskedphone');
                };
            });
        }
    });
};


/* Еще вариант */
///www.bgm42.ru/local/templates/axioma/assets/modules/inputmask/dist/min/jquery.inputmask.bundle.min.js?153205654889224

$('input[name=ANSWER_PHONE]').inputmask({
    mask: "+7 (999) 999-99-99",
    showMaskOnHover: false,
    clearIncomplete: true
});


/********************************/
/* И третий вариант */
//cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js

        $('.phone').mask('+7(999)999-99-99');



/********************************/
/* Сложная маска */
//cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js

$('input[name="moe-pole"]').mask('00 AA 0000', {
    translation: {
        "0": { pattern: /[0-9]/, recursive: true },
        "A": { pattern: /[а-яА-ЯёЁ]/, recursive: true }
    }
});
