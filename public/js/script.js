let editor        = $('[data-editor="true"]');
if (editor.length > 0)
{
    createEditor('[data-editor="true"]');
}

function createEditor(selector)
{
    document.querySelectorAll(selector).forEach(function(textarea)
    {
        const example_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) =>
        {
            const xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', $('.editor_upload_url').val());

            xhr.upload.onprogress = (e) => {
                progress(e.loaded / e.total * 100);
            };

            xhr.onload = () => {
                if (xhr.status === 403) {
                    reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                    return;
                }

                if (xhr.status < 200 || xhr.status >= 300) {
                    reject('HTTP Error: ' + xhr.status);
                    return;
                }

                const json = JSON.parse(xhr.responseText);

                if (!json || typeof json.location != 'string') {
                    reject('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                resolve(json.location);
            };

            xhr.onerror = () => {
                reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
            };

            const formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            xhr.send(formData);
        });

        tinymce.init({
            target: textarea,
            entity_encoding: 'raw',
            height: 400,
            plugins: 'image code lists link table',
            toolbar: 'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code',
            images_upload_url: $('.editor_upload_url').val(),
            automatic_uploads: true,
            license_key: 'gpl',
            relative_urls: false,
            remove_script_host: false,
            convert_urls: false,
            images_upload_handler: example_image_upload_handler
        });
    });
}
$('#login').submit(function (e)
{
    e.preventDefault();
    let formSelector = '#login';
    formSend(formSelector,this);
});

$('#relationshipForm').submit(function (e)
{
    e.preventDefault();
    let formSelector = '#relationshipForm';
    formSend(formSelector,this);
});

$('#billingInformationForm').submit(function (e)
{
    e.preventDefault();
    let formSelector = '#billingInformationForm';
    formSend(formSelector,this);
});

$('#customerLimitsForm').submit(function (e)
{
    e.preventDefault();
    let formSelector = '#customerLimitsForm';
    formSend(formSelector,this);
});

$('#productsForm').submit(function (e)
{
    e.preventDefault();
    let formSelector = '#productsForm';
    formSend(formSelector,this);
});

$('#overview').submit(function (e)
{
    e.preventDefault();
    let formSelector = '#overview';
    formSend(formSelector,this);
});

$('#productRoutesForm').submit(function (e)
{
    e.preventDefault();
    let formSelector = '#productRoutesForm';
    formSend(formSelector,this);
});

$('#repeaterForm').submit(function (e)
{
    e.preventDefault();
    formSend('#repeaterForm',this);
});

$('#addUpdateForm').submit(function (e)
{
    e.preventDefault();
    formSend('#addUpdateForm',this);
});

let jsonEditors = $('[data-json="true"]');
let editorsJson = [];

if (jsonEditors.length > 0)
{
    jsonOrganizers(jsonEditors);
}

function jsonOrganizers(jsonEditors,value = null)
{
    jsonEditors.each(function(index)
    {
        let textarea = $(this)[0];

        if (textarea.value == '')
        {
            textarea.value = '{}';
        }

        if (value)
        {
            textarea.value = value;
        }

        let jsonEditor = CodeMirror.fromTextArea(textarea,
            {
                mode: { name: "javascript", json: true },
                lineNumbers: true,
                matchBrackets: true,
                autoCloseBrackets: true,
            });

        jsonOrganizer(jsonEditor);
        editorsJson.push(jsonEditor);

        let btnId = $(this).nextAll('.jsonOrganizerButton').attr('id');

        document.getElementById(btnId).onclick = function()
        {
            jsonOrganizer(jsonEditor);
        };
    });
}

function jsonOrganizer(jsonEditor)
{
    try
    {
        const value = jsonEditor.getValue();
        const json = JSON.parse(value);
        const pretty = JSON.stringify(json,null, 2);
        jsonEditor.setValue(pretty);
    }
    catch (err)
    {
        messageAlert(0,'Hatalı JSON, düzenlenemiyor!');
    }
}

function formSend(formSelector,bu)
{
    let disabledSelects = $(formSelector).find('select:disabled');
    let switchAll     = $('.crud-switch');
    let checkboxAll   = $('.crud-checkbox');

    disabledSelects.prop('disabled', false);

    let saveButton   = $(formSelector+' .buttonForm');
    let loading      = $(formSelector+' .loading');


    saveButton.addClass('d-none');
    loading.removeClass('d-none');

    $(formSelector+' .is-invalid').removeClass('is-invalid');
    $(formSelector+' .is-valid').removeClass('is-valid');

    if (editor.length > 0)
    {
        tinymce.triggerSave();
    }

    if (jsonEditors.length > 0)
    {
        editorsJson.forEach(ed => ed.save());
    }

    if ($(formSelector).find('[data-repeater-item]').length > 0)
    {
        renumberItems(formSelector);
    }

    let formData = new FormData(bu);

    if (switchAll.length > 0)
    {
        switchAll.each(function(swIndex)
        {
            let sw      = switchAll.eq(swIndex);
            let input   = sw.find('input');
            let status  = input.prop('checked');
            let dataOn  = input.data('on');
            let dataOff = input.data('off');
            let name    = input.attr('name');

            formData.set(name, (status ? dataOn : dataOff));
        });
    }

    if (checkboxAll.length > 0)
    {
        let checkAllData = [];
        let checkName = '';

        checkboxAll.each(function(swIndex)
        {
            let ch      = checkboxAll.eq(swIndex);
            let input   = ch.find('input');
            let status  = input.prop('checked');
            let name    = input.attr('name');
            let value   = input.val();

            if (status)
            {
                checkAllData.push(value);
            }

            checkName = name;
            formData.delete(name);
        });

        let [checkBoxName, index] = checkName.split(/\[|\]/).filter(Boolean);

        formData.set(checkBoxName,JSON.stringify(checkAllData));
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: $(formSelector).attr('method'),
        url:  $(formSelector).attr('action'),
        data: formData,
        dataType: "json",
        cache:false,
        contentType: false,
        processData: false,
        success: function (response)
        {
            saveButton.removeClass('d-none');
            loading.addClass('d-none');
            disabledSelects.prop('disabled', true);

            messageAlert(1,response.message);

            if (response.route != undefined)
            {
                setTimeout(function() {location = response.route;}, 2000);

                setTimeout(function()
                {
                    if (response.file)
                    {
                        swal.close();
                    }
                }, 3000);
            }
            else
            {
                setTimeout(function() {location.reload()}, 2000);
            }
        },
        error : function (response)
        {
            saveButton.removeClass('d-none');
            loading.addClass('d-none');
            disabledSelects.prop('disabled', true);

            $(formSelector+" .invalid-feedback").remove();

            $('html,body').animate({scrollTop: ($(formSelector).offset().top - 130)}, 200);

            if (response.responseJSON.result == 2)
            {
                let errorLists = '<ul class="list-group">';

                $.each(response.responseJSON.message, function(i, item)
                {
                    let errorMessage = item[0];

                    if (i.includes('.'))
                    {
                        let index = i.split('.');
                        let newName = '';

                        $.each(index, function(key, value)
                        {
                            if (key > 0)
                            {
                                newName += '['+ value + ']';
                            }
                            else
                            {
                                newName += value;
                            }
                        });

                        $(formSelector+' [name="' + newName + '"]').addClass('is-invalid');
                        $(formSelector+' [name="' + newName + '"]').closest('div.form-group').append('<div class="invalid-feedback">'+errorMessage+'</div>');

                        errorMessage = (parseInt(index[1]) + 1) + '. sıradaki ' + errorMessage;
                    }
                    else
                    {
                        $(formSelector+' [name="'+i+'"]').addClass('is-invalid');
                        $(formSelector+' [name="'+i+'"]').closest('div.form-group').append('<div class="invalid-feedback">'+errorMessage+'</div>');
                    }

                    errorLists += '<li class="list-group-item list-group-item-danger">' + errorMessage + '</li>';
                });

                errorLists += '</ul>';

                alertMessage(2,'Lütfen Alanları Doldurun.',errorLists);
            }
            else if (response.responseJSON.message)
            {
                messageAlert(0,response.responseJSON.message);
            }
            else
            {
                messageAlert(0,'İşlem Başarısız. Lütfen daha sonra tekrar deneyiniz.');
            }
        }
    });
}

function alertMessage(status,title,message)
{
    let statusClass = status == 1 ? 'success' : 'danger';
    let html =
        '        <div class="alert alert-' + statusClass + ' d-flex align-items-center p-5">\n' +
        '            <i class="ki-outline ki-shield-tick fs-2hx text-' + statusClass + ' me-4"></i>\n' +
        '            <div class="d-flex flex-column">\n' +
        '                <h4 class="mb-1 text-' + statusClass + '">'+ title +'</h4>\n' +
        '                <div class="d-inline-block">' + message + '</div>\n' +
        '            </div>\n' +
        '        </div>';

    $('#formResponse').html(html);
}


function messageAlert(status,message,title = null)
{
    let statusIcon = status == 1 ? 'success' : 'error';

    Swal.fire({
        icon: statusIcon,
        title: message,
        showConfirmButton: false,
        showCancelButton: status == 1 ? false : true,
        heightAuto: false,
        cancelButtonText: 'Kapat'
    })
}

function messageToast(formSelector,status,message)
{
    let statusClass = status == 1 ? 'success' : 'warning';
    let hide = 1500;
    let html =
        '<div class="position-absolute top-0 end-0 p-3 z-index-3" id="messageToastDiv">' +
        '  <div class="toast text-bg-'+statusClass+' border-0" id="messageToast" role="alert" aria-live="assertive" aria-atomic="true">\n' +
        '    <div class="toast-body text-center text-white fs-3 text-body">\n'
        + message +
        '    </div>\n' +
        '  </div>\n' +
        '</div>';

    $(formSelector).closest('div').prepend(html);
    const toastLiveExample = document.getElementById('messageToast')
    const toast = new bootstrap.Toast(toastLiveExample)

    toast.show({delay : hide});
    setTimeout(function ()
    {
        $('#messageToastDiv').remove();

    },(hide + 5000));
}

function statusUpdate(bu)
{

    let status    = $(bu).data('status');
    let id        = $(bu).data('id');
    let route     = $(bu).data('route');
    let modelName = $(bu).data('model-name');
    let message   = $(bu).data('message');
    let text      = message ? message :  (status == 0 ? 'Aktif' : 'Pasif')+"  etmek istediğinize emin misiniz?";
    Swal.fire({
        text: text,
        icon: "warning",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "Evet",
        cancelButtonText: "Hayır",
        customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-active-light"
        }
    }).then(function (result)
    {
        if (result.value)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: 'post',
                url:  route,
                data: {id:id,status:status,modelName:modelName},
                dataType: "json",
                success: function (response)
                {
                    messageAlert(1,response.message);

                    if (response.route != undefined)
                    {
                        location = response.route;
                        setTimeout(function() {location}, 2000);
                    }
                    else
                    {
                        setTimeout(function() {location.reload()}, 2000);
                    }
                },
                error : function (response)
                {
                    if (response.responseJSON.message)
                    {
                        messageAlert(0,response.responseJSON.message);
                    }
                    else
                    {
                        messageAlert(0,'İşlem Başarısız. Lütfen daha sonra tekrar deneyiniz.');
                    }
                }
            });
        }
    });
}

function destroy(bu)
{
    let route  = $(bu).data('route');
    let title  = $(bu).data('title');

    Swal.fire({
        text: title+" silmek istediğinize emin misiniz?",
        icon: "warning",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "Evet",
        cancelButtonText: "Hayır",
        customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-active-light"
        }
    }).then(function (result)
    {
        if (result.value)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: 'delete',
                url:  route,
                dataType: "json",
                success: function (response)
                {
                    messageAlert(1,response.message);

                    if (response.route != undefined)
                    {
                        location = response.route;
                        setTimeout(function() {location}, 1500);
                    }
                    else
                    {
                        setTimeout(function() {location.reload()}, 1500);
                    }
                },
                error : function (response)
                {
                    if (response.responseJSON.message)
                    {
                        messageAlert(0,response.responseJSON.message);
                    }
                    else
                    {
                        messageAlert(0,'İşlem Başarısız. Lütfen daha sonra tekrar deneyiniz.');
                    }
                }
            });
        }
    });
}

function modalWithSwal(modalName)
{
    let modalSelector = $(modalName);
    let cancelButton  = $(modalName+' [data-dismiss="modal"]')

    modalSelector.modal(
        {
            focus: false,
            show: false
        });

    cancelButton.click(function (e)
    {
        e.preventDefault();

        Swal.fire({
            text: "Kayıt işleminden vazgeçmek istediğinize emin misiniz?",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "Evet",
            cancelButtonText: "Hayır",
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-active-light"
            }
        }).then(function (result)
        {
            if (result.value)
            {
                modalSelector.find('form')[0].reset();
                modalSelector.modal('hide');
            }
        });
    });
}

$(function()
{
    if ($('#insertModal').length > 0)
    {
        modalWithSwal('#insertModal');
    }

    if ($('#repeaterModal').length > 0)
    {
        let modalSelector = 'repeaterModal';
        modalWithSwal('#' + modalSelector);
        let modalShowNo = 0;

        document.getElementById(modalSelector).addEventListener('shown.bs.modal', () =>
        {
            if (modalShowNo == 0)
            {
                jsonEditors = $('[data-modal-json="true"]');
                let jsonEditorFirstValue = $('#' + modalSelector).find('[data-modal-json="true"]').eq(0).data('value');
                jsonEditorFirstValue = JSON.stringify(jsonEditorFirstValue,null, 2);
                jsonOrganizers(jsonEditors,jsonEditorFirstValue);
            }

            modalShowNo = 1;
        })
    }

    if ($('[name="city_id"]').length > 0)
    {
        $('[name="city_id"]').change(function (e)
        {
            let token      = $('meta[name="csrf-token"]').attr('content');
            let cityId     = $(this).val();
            let districtId = $(this).data('district-id');
            let options = '<option>Yükleniyor...</option>';
            $('[name="district_id"]').html(options);

            $.ajax({
                type: "POST",
                dataType: "json",
                url: $(this).data('action') + '/' + cityId,
                data: {
                    _token: token,
                    city: cityId
                },
                success: function(response)
                {
                    if (response.result == 1)
                    {
                        options = '<option>Seçiniz</option>';

                        $(response.response).each(function(index,element) {
                            options += '<option ' + (districtId == element.id ? "selected" : null )   + ' value="'+ element.id +'">'+ element.title +'</option>';
                        });

                        $('[name="district_id"]').html(options);
                    }
                }
            });
        })
    }
});

function getDatatable(id,exportButtons = null,documentTitle = null)
{
    let datatableOptions =
        {
            language:
                {
                    "url":"/assets/vendor/datatables/turkish.json",
                    "lengthMenu": "_MENU_",
                },
            dom:
                "<'row mb-2'" +
                "<'col-sm-6 d-flex align-items-center justify-conten-start dt-toolbar'l>" +
                "<'col-sm-6 d-flex align-items-center justify-content-end dt-toolbar'f>" +
                ">" +

                "<'table-responsive'tr>" +

                "<'row'" +
                "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                ">",
            scrollCollapse: true,
            scrollY: '200px'
        };

    let table = $(id).DataTable(datatableOptions);

    if (exportButtons)
    {
        let newData = null;
        let newData2 = null;
        let newData3 = null;
        let exportFormatter = {
            format: {
                body: function (data, row, column, node)
                {
                    if (column == 6 && documentTitle == 'Bütün Satışlar')
                    {
                        return  data.replaceAll('.', '').replaceAll('₺', '').replaceAll(',', '.');
                    }
                    else
                    {
                        return data;
                    }
                }
            }
        };

        let buttons = new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: '',
                    exportOptions: exportFormatter
                },
                {
                    extend: 'pdfHtml5',
                    title: documentTitle
                }
            ]
        }).container().appendTo($(id));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#kt_datatable_example_export_menu [data-kt-export]');
        exportButtons.forEach(exportButton => {
            exportButton.addEventListener('click', e => {
                e.preventDefault();

                // Get clicked export value
                const exportValue = e.target.getAttribute('data-kt-export');
                const target = document.querySelector('.dt-buttons .buttons-' + exportValue);

                // Trigger click event on hidden datatable export buttons
                target.click();
            });
        });
    }
}

const images = document.getElementById('imageUpdate'),
    preview = document.getElementById('imageUpdatePreview');

if (images)
{
    images.addEventListener('change', function() {
        [...this.files].map(file => {
            const reader = new FileReader();
            reader.addEventListener('load', function(){
                preview.src = this.result;
            });
            reader.readAsDataURL(file);
        })
    })
}

function slug1(ad,yazilacakyer)
{
    str = turkcekarekteryoket(ad,yazilacakyer);
    str = str.toLowerCase();
    str = str.replace(/\s\s+/g,' ').replace(/[^a-z0-9\s]/gi,',').replace(/[^\w]/ig,"-");
    turkcekarekteryoket(str,yazilacakyer);
}

function turkcekarekteryoket(gelenler,yazilacakyer)
{
    var specialChars = [["Ã…Å¸","s"],["Ã…Â","s"],["Ã„Å¸","g"],["Ã„Â","g"],["ÃƒÂ¼","u"],["ÃƒÅ“","u"],["Ã„Â°","i"],["Ã„Â±","i"],["_","-"],["Ãƒâ€“","o"],["ÃƒÂ¶","o"],["Ãƒâ€¦Ã‚Â","S"],["Ãƒâ€Ã‚Â","G"],["ÃƒÆ’Ã¢â‚¬Â¡","C"],["Ãƒâ€¡","c"],["ÃƒÂ§","c"],["ÃƒÆ’Ã…â€œ","U"],["Ãƒâ€Ã‚Â°","I"],["ÃƒÆ’Ã¢â‚¬â€œ","O"],["Ãƒâ€¦Ã…Â¸","s"],["ç","c"],["Ç","c"],["ş","s"],["Ş","s"],["İ","i"],["I","i"],["ı","i"],["Ü","u"],["ü","u"]];

    for(var i=0;i<specialChars.length;i++)
    {
        gelenler=gelenler.replace(eval("/"+specialChars[i][0]+"/ig"),specialChars[i][1]);
        $(yazilacakyer).val(gelenler);
    }
    return gelenler;
}

$("#datePickerFilter").daterangepicker({
    startDate: moment().startOf("month"),
    locale:
        {
            cancelLabel: 'Vazgeç',
            applyLabel : 'Filtrele',
            format : 'DD.MM.YYYY'
        }
});

function allCheck(self)
{
    let checkClass = self.getAttribute('data-checkbox-class');
    let others     = $('.' + checkClass);

    if(self.checked)
    {
        others.prop('checked',true);
    }
    else
    {
        others.prop('checked',false);
    }
}

function permissionCheckboxCheck()
{
    let allCheck = $('[name="permissions_all"]');

    allCheck.each(function (index,element)
    {
        let checkboxClass = $(element).data('checkbox-class');
        let checkbox      = $('.' + checkboxClass);
        let checked       = checkbox.filter(':checked').length;

        if (checked == checkbox.length)
        {
            $(element).prop('checked',true);
        }
    })
}

function getCrud(self)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: "POST",
        dataType: "json",
        url: $(self).data('action') + '/' + $(self).val(),
        success: function (response)
        {
            let crud = response.response;
            let item = $(self).closest('[data-repeater-item]');

            item.find('input[name$="[title]"]').val(crud.title);
            item.find('input[name$="[route]"]').val(crud.slug + '.index');
            item.find('input[name$="[icon]"]').val(crud.icon);

            let formCheck = item.find('.form-check')[1];
            $('input',formCheck).prop('checked',true);

        },
        error : function (response)
        {
            if (response.responseJSON.message)
            {
                messageAlert(0,response.responseJSON.message);
            }
            else
            {
                messageAlert(0,'İşlem Başarısız. Lütfen daha sonra tekrar deneyiniz.');
            }
        }
    });
}

if ($('#items [data-repeater-list="items"]').length > 0)
{
    let itemsSelector = '[data-repeater-list="columns"]';

    repeaterGenerate('#items');

    $( itemsSelector).sortable(
        {
            opacity: 0.5,
            revert:200,
            handle : '.handle',
            update: function( event, ui )
            {
                renumberItems('#items');
            }
        });
}

if ($('#columns [data-repeater-list="columns"]').length > 0)
{
    let columnsSelector = '#columns [data-repeater-list="columns"]';

    repeaterGenerate('#columns');

    $( columnsSelector).sortable(
        {
            opacity: 0.5,
            revert:200,
            handle : '.handle',
            update: function( event, ui )
            {
                renumberItems('#columns');
            }
        });
}

if ($('[data-repeater-crud]').length > 0)
{
    $('[data-repeater-crud]').each(function (index, element)
    {
        let selectorId = $(this).attr('id');
        let repeaterSelector = '#'+ selectorId +' [data-repeater-list]';

        repeaterGenerate('#'+ selectorId);

        $( repeaterSelector ).sortable(
            {
                opacity: 0.5,
                revert:200,
                handle : '.handle',
                update: function( event, ui )
                {
                    renumberItems('#'+ selectorId);
                }
            });
    });
}

if ($('#repeaterForm [data-repeater-list="repeaterArea"]').length > 0)
{
    repeaterGenerate('#repeaterForm',1);

    $( '#repeaterForm [data-repeater-list="repeaterArea"]' ).sortable(
        {
            opacity: 0.5,
            revert:200,
            handle : '.handle',
            update: function( event, ui )
            {
                renumberItems('#repeaterForm');
            }
        });
}

function repeaterGenerate(selector,modalJsonEditorValue = null)
{
    $(selector).repeater({
        initEmpty: false,

        show: function ()
        {
            console.log($(this));
            $(this).slideDown();

            $(selector).find('[maxlength]').maxlength({
                warningClass: "badge badge-primary",
                limitReachedClass: "badge badge-success"
            });

            let jsonEditorFirstValue = null;

            if (modalJsonEditorValue)
            {
                jsonEditorFirstValue = $(selector).find('[data-modal-json="true"]').eq(0).data('value');
                jsonEditorFirstValue = JSON.stringify(jsonEditorFirstValue,null, 2);
            }

            jsonOrganizers($(this).find('[data-modal-json="true"]'),jsonEditorFirstValue);

            createEditor('[data-editor="true"]');
        },

        hide: function (deleteElement) {
            $(this).slideUp(deleteElement);
        },

        ready : function ()
        {
            $(selector).find('[maxlength]').maxlength(
                {
                    warningClass: "badge badge-primary",
                    limitReachedClass: "badge badge-success"
                });
        }
    });
}

function renumberItems(selector)
{
    $(selector).find('[data-repeater-item]').each(function(index)
    {
        let item = $(this);
        item.attr('data-item-no', index);
    });

    $(selector).data('plugin_repeater', null);
    $(selector).repeater();
}

$(document).ready(function ()
{
    if ($('.moduleSortable').length > 0)
    {
        let sortableSelector = '.moduleSortable';
        setTimeout(function()
        {
            $( sortableSelector + " tbody" ).sortable({
                items: "tr",
                handle: '.bi-arrows-move',
                opacity: 0.6,
                update: function()
                {
                    moduleSortableUpdate(sortableSelector)
                }
            });
        }, 300);
    }

    if ($('body').find('[maxlength]').length > 0)
    {
        let allMaxlength = $('body').find('[maxlength]');

        allMaxlength.each(function()
        {
            $(this).maxlength({
                warningClass: "badge badge-primary",
                limitReachedClass: "badge badge-success"
            });
        });
    }
});

function moduleSortableUpdate(sortableSelector)
{
    let position = '';
    let order = [];
    let token = $('meta[name="csrf-token"]').attr('content');
    let direction = $(sortableSelector).data('order-direction');

    $(sortableSelector + ' tbody tr').each(function(index,element) {
        order.push({
            id: $(sortableSelector+' tbody tr:eq('+index+') td [data-id]').attr('data-id'),
            position: index+1,
        });
    });

    $.ajax({
        type: "POST",
        dataType: "json",
        url: $(sortableSelector).data('link'),
        data: {
            order: order,
            order_column_name : $(sortableSelector).data('order-column'),
            order_direction   : direction,
            _token: token
        },
        success: function(response)
        {
            if (response.result == 1)
            {
                $(sortableSelector + ' tbody tr').each(function(index,element)
                {
                    if (direction == 'asc')
                    {
                        position = index+1;
                    }
                    else
                    {
                        position = ($(sortableSelector + ' tbody tr').length - index);
                    }

                    $(sortableSelector + ' tbody tr:eq('+index+') td:eq(1)').text(position);
                });
            }
        }
    });
}

function crudRealtime(self)
{
    let token      = $('meta[name="csrf-token"]').attr('content');
    let columnName = $(self).attr('name');
    let route      = $(self).data('route');
    let value      = $(self).val();

    if ($(self).closest('.crud-switch').length > 0)
    {
        let input   = $(self);
        let status  = input.prop('checked');
        let dataOn  = input.data('on');
        let dataOff = input.data('off');
        let name    = input.attr('name');

        value = (status ? dataOn : dataOff);
    }

    $.ajax({
        type: "POST",
        dataType: "json",
        url: route,
        data:
            {
                value : value,
                column_name: columnName,
                _token: token
            },
        success: function(response)
        {

        }
    });
}