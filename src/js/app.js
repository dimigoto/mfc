/*jshint esversion: 6 */

require('jquery-form/dist/jquery.form.min.js');
require('./expromptum');

document.addEventListener("DOMContentLoaded", function (event) {
    expromptum();

    if ($('#mfcEnquiryForm').length) {
        $('#mfcEnquiryForm').submit(function (e) {
            e.preventDefault();

            let preloader = $(this).find('.js-spinner');
            let description = $('#mfc-enquiry-description');
            let downloads = $('#mfc-enquiry-downloads');
            let formContainer = $('#mfcEnquiryFormContainer');
            let resultContainer = $('#mfcEnquiryFormResult');
            let button = $(this).find('button');

            $(this).ajaxSubmit({
                target: resultContainer,
                beforeSend: function () {
                    resultContainer.html('');
                    preloader.show();
                    button.attr('disabled', true);
                },
                success: function (responseText, statusText, xhr, $form) {
                    let message = '';
                    let mfcBanner = '<div class="alert alert-info">' + '<p>Оцените качество сервиса</p><p><a href="https://forms.office.com/r/n8fXsF3X6r">Помогите сделать «Единое окно» более полезным и доступным</a></p>' + '</div>';
                    if (responseText.success) {
                        message = '<div class="alert alert-success">' + responseText.message + '</div>';
                        message += mfcBanner;
                        message += '<a href="/mfc/" class="btn btn-primary">Отправить новую заявку</a>';
                    } else {
                        message = '<div class="alert alert-danger">' + responseText.message + '</div>';
                        button.attr('disabled', false);
                    }

                    description.hide();
                    downloads.hide();
                    formContainer.hide();
                    resultContainer.html(message);
                    preloader.hide();
                }
            })
        });

        $('#mfcEnquiryFormSubmit').click(function () {
            $(this).parents('form').submit();
        });

        xP('#mfcEnquiryForm').first().change(function () {
            updateMultiselectButtonText();

            if ($('fieldset.repeated_template').length > 0) {
                updateMultiselect();
            }

            $('.form-control.datetime.picker').each(function () {
                $(this).removeClass('is-invalid');
            });
        });
    }
});

function updateMultiselectButtonText() {
    $('select[multiple=multiple]').each(function () {
        $(this).multiselect('updateButtonText');
    });
}

function updateMultiselect() {
    $('select[multiple=multiple]').each(function () {
        if (!$(this).parent().is('span.multiselect-native-select')
            && !$(this).parents('fieldset').first().hasClass('repeated_template')
        ) {
            $(this).multiselect({
                nonSelectedText: 'Ничего не выбрано',
                nSelectedText: 'выбрано',
                allSelectedText: 'Выбрано всё',
                disabledText: 'Заблокировано...',
                resetText: 'Отменить выбор',
                maxHeight: 400
            });
            expromptum();
        }
    });
}

/*
  Изменения в исходном коде expromptum:

  1.
  this.min_hour = params.min_hour || 0;
  this.max_hour = params.max_hour || 23;

  2.
  this.only_workdays = params.only_workdays || 0;

  3.
  Класс CSS для контейнера поля выбора даты изменён с date на datePicker, чтобы не пересекаться с виджетом dosamigos\datepicker\DatePicker.
*/